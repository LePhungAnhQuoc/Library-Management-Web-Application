<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Category.php';

class BookController
{
    private $idPrefix = 'BT';
    private $maxIndexId = -1;
    private $isDataFetch = false;
    private $books = [];

    private function loadBooks(): array
    {
        if (!empty($this->books)) {
            return $this->books;
        }

        $database = new Database();
        $db = $database->connect();
        $bookModel = new Book($db);

        $this->books = $bookModel->getAll();
        $this->isDataFetch = true;

        return $this->books;
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
        $database = new Database();
        $db = $database->connect();
        $bookModel = new Book($db);
        $categoryModel = new Category($db);

        $success_message = '';
        $error_message = '';

        // Reuse the cached books instead of loading them again
        $books = $this->loadBooks();
        $this->categories = $categoryModel->getAll();

        $categoryMap = [];
        foreach ($this->categories as $cat) {
            $categoryMap[$cat['Id']] = $cat['Name'];
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
            $newBook->status = trim($_POST['status'] ?? 'Available');
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

    public function browse()
    {
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
}