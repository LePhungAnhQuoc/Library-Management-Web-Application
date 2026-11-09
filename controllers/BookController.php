<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Loan.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Reader.php';
require_once __DIR__ . '/../Utilities/Constants.php';
require_once __DIR__ . '/../Utilities/Enums.php';

class BookController
{
    private $db;
    private $idPrefix = 'BT';
    private $maxIndexId = -1;
    private $isDataFetch = false;
    private $books = [];

    public function ValidateISBN($isbn): bool
    {
        // Basic ISBN validation (you can expand this based on your requirements)
        return (bool) preg_match('/^\d{10}(\d{3})?$/', $isbn);
    }

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    private function loadBooks(): array
    {
        if (!empty($this->books)) {
            return $this->books;
        }

        $db = $this->db;
        $bookModel = new Book($db);

        $this->books = $bookModel->getAll();
        $this->isDataFetch = true;

        return $this->books;
    }

    public function CreateBookMap($books): array
    {
        $bookMap = [];
        foreach ($books as $book) {
            $bookMap[(int) ($book['id'] ?? 0)] = $book;
        }
        return $bookMap;
    }

    private function validate($book): bool
    {
        if (empty($book->title))
            return false;
        if (empty($book->author))
            return false;
        if (empty($book->isbn))
            return false;
        if (empty($book->category))
            return false;
        if (empty($book->genre))
            return false;
        if (empty($book->format))
            return false;
        return true;
    }

    public function index()
    {
        // Initialize database and model
        $db = $this->db;
        $bookModel = new Book($db);
        $categoryModel = new Category($db);

        $success_message = '';
        $error_message = '';

        // Reuse the cached books instead of loading them again
        $books = $this->loadBooks();
        $this->categories = $categoryModel->getAll();

        $categoryMap = [];
        foreach ($this->categories as $cat) {
            $categoryMap[$cat['id']] = $cat['name'];
        }

        // Handle POST submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newBook = new Book($db);
            $newBook->title = trim($_POST['title'] ?? '');
            $newBook->author = trim($_POST['author'] ?? '');
            $newBook->isbn = trim($_POST['isbn'] ?? '');
            $newBook->category = trim($_POST['category'] ?? '');
            $newBook->genre = trim($_POST['genre'] ?? '');
            $newBook->format = trim($_POST['format'] ?? '');
            $newBook->status = trim($_POST['status'] ?? BookStatus::AVAILABLE->value);
            $newBook->summary = trim($_POST['summary'] ?? '');
            $newBook->emoji = trim($_POST['emoji'] ?? '');

            if ($this->validate($newBook)) {
                if ($bookModel->create($newBook)) {
                    $success_message = "New book added successfully!";
                    $this->books[] = [
                        'id' => $newBook->id,
                        'title' => $newBook->title,
                        'author' => $newBook->author,
                        'isbn' => $newBook->isbn,
                        'category' => $newBook->category,
                        'genre' => $newBook->genre,
                        'format' => $newBook->format,
                        'status' => $newBook->status,
                        'summary' => $newBook->summary,
                        'emoji' => $newBook->emoji,
                    ];
                } else {
                    $error_message = "Failed to insert the book.";
                }
            } else {
                $error_message = "Please enter all required fields!";
            }
        }

        $categories = $this->categories;

        // 1. Turn on output buffering
        ob_start();

        require_once __DIR__ . '/../views/book_view/book_view.php';

        // 3. Get the contents of the buffer and clean it, returning it as a string
        return ob_get_clean();
    }

    private function getCurrentReaderId(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            return 0;
        }
        $userModel = new User($this->db);
        $user = $userModel->GetById($userId);
        if (!empty($user['reader_id']) && (int) $user['reader_id'] > 0) {
            return (int) $user['reader_id'];
        }

        return 0;
    }

    private function getReservedBookIds(): array
    {
        $readerId = $this->getCurrentReaderId();
        if ($readerId <= 0) {
            return [];
        }

        $loanModel = new Loan($this->db);
        $activeLoans = $loanModel->getByReader($readerId, false);

        $reservedBookIds = [];
        foreach ($activeLoans as $loan) {
            $status = strtolower(trim((string) ($loan['status'] ?? '')));
            if ($status === strtolower(LoanStatus::ON_HOLD->value) || $status === strtolower(LoanStatus::CHECKED_OUT->value)) {
                $reservedBookIds[] = (int) ($loan['book_id'] ?? 0);
            }
        }

        return array_values(array_unique(array_filter($reservedBookIds, fn ($bookId) => $bookId > 0)));
    }

    private function reserveBook(int $bookId): array
    {
        $loanController = new LoanController();
        
        $db = $this->db;
        if (!$db) {
            return [
                'success' => false,
                'message' => 'Database is unavailable right now.',
            ];
        }

        $bookModel = new Book($db);
        $book = $bookModel->getById($bookId);
        if (empty($book)) {
            return [
                'success' => false,
                'message' => 'This book is not available to reserve.',
            ];
        }

        $readerId = $this->getCurrentReaderId();
        if ($readerId <= 0) {
            return [
                'success' => false,
                'message' => 'Please sign in to reserve a book.',
            ];
        }

        $loanModel = new Loan($db);
        $existingLoans = $loanModel->getByReader($readerId);
        foreach ($existingLoans as $loan) {
            $loanStatus = strtolower(trim((string) ($loan['status'] ?? '')));
            if ($loanStatus === '') {
                return [
                    'success' => false,
                    'message' => 'An unexpected error occurred while checking your existing loans.',
                ];
            }
            if ((int) ($loan['book_id'] ?? 0) === $bookId && !$loanController->IsReturned($loan)) {
                return [
                    'success' => false,
                    'message' => 'You already have this book reserved or checked out.',
                ];
            }
        }

        $reserved = $loanModel->create([
            'book_id' => $bookId,
            'reader_id' => $readerId,
            'checkout_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+' . Constants::LOAN_DAYS . ' days')),
            'status' => LoanStatus::ON_HOLD->value,
            'renewals' => 0,
            'fee_amount' => 0.00,
        ]);

        if (!$reserved) {
            return [
                'success' => false,
                'message' => 'Unable to place the reservation at the moment.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Book reserved successfully.',
        ];
    }

    private function handleReserveRequest(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['reserve_book_id'])) {
            return ['success' => false, 'message' => ''];
        }

        $bookId = (int) ($_POST['reserve_book_id'] ?? 0);
        if ($bookId <= 0) {
            return [
                'success' => false,
                'message' => 'A valid book selection is required.',
            ];
        }

        return $this->reserveBook($bookId);
    }

    public function browse()
    {
        $reserveResult = $this->handleReserveRequest(); 
        $reserveMessage = $reserveResult['message'] ?? '';
        $reserveSuccess = !empty($reserveResult['success']);

        $db = $this->db;
        $reservedBookIds = $db ? $this->getReservedBookIds() : [];

        $books = $this->loadBooks();

        $keyword = trim($_GET['keyword'] ?? '');

        $filteredBooks = $books;

        if ($keyword !== '') {
            $filteredBooks = array_values(array_filter($books, function ($book) use ($keyword) {
                $searchTarget = strtolower(
                    $book['title'] . ' ' .
                    $book['author'] . ' ' .
                    $book['isbn'] . ' ' .
                    $book['category']
                );

                return strpos($searchTarget, strtolower($keyword)) !== false;
            }));
        }

        usort($filteredBooks, function ($first, $second) {
            return strcmp($first['title'], $second['title']);
        });

        ob_start();
        require_once __DIR__ . '/../views/book_view/search_browse.php';
        return ob_get_clean();
    }

    public function detail()
    {
        $reserveResult = $this->handleReserveRequest();
        $reserveMessage = $reserveResult['message'] ?? '';
        $reserveSuccess = !empty($reserveResult['success']);

        $db = $this->db;
        $reservedBookIds = $db ? $this->getReservedBookIds() : [];
        $bookModel = new Book($db);

        $bookId = $_GET['id'] ?? $_GET['book_id'] ?? 0;
        $bookId = (int) $bookId;
        $book = $bookId > 0 ? $bookModel->getById($bookId) : null;

        if ($book === false) {
            $book = null;
        }

        ob_start();
        require_once __DIR__ . '/../views/book_view/book_detail.php';
        return ob_get_clean();
    }
}