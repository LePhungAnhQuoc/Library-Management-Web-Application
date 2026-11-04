<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Loan.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Reader.php';

class LoanController
{
    private $db;
    private $allReaders;
    private $allBooks;
    public function __construct()
    {
        $database = new Database();
        $db = $database->connect();
        $this->db = $db;
    }
    public function dashboard()
    {
        $readerController = new ReaderController();
        $bookController = new BookController();

        $loanModel = new Loan($this->db);
        $bookModel = new Book($this->db);
        $readerModel = new Reader($this->db);
        $userModel = new User($this->db);

        $userId = $_SESSION['user_id'] ?? 1;
        $user = $userModel->GetById($userId);
        $readerId = $user['reader_id'] ?? 1;
        $this->allReaders = $readerModel->getAll();
        $this->allBooks = $bookModel->getAll();
        $allBooks = $this->allBooks;
        $allReaders = $this->allReaders;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loan_book_id'])) {
            $bookId = (int) ($_POST['loan_book_id'] ?? 0);

            // Use the reader_id from the form submission if available, otherwise default to the current readerId (current logged-in user)
            $selectedReaderId = (int) ($_POST['reader_id'] ?? $readerId);
            $dueDate = trim($_POST['due_date'] ?? '');
            $status = trim($_POST['status'] ?? 'Checked out');
            $renewals = (int) ($_POST['renewals'] ?? 0);

            if ($bookId > 0 && $selectedReaderId > 0 && $dueDate !== '') {
                $success = $loanModel->create([
                    'book_id' => $bookId,
                    'reader_id' => $selectedReaderId,
                    'checkout_date' => date('Y-m-d'),
                    'due_date' => $dueDate,
                    'status' => $status,
                    'renewals' => $renewals,
                    'fee_amount' => 0.00,
                ]);

                if ($success) {
                    $_SESSION['loan_success'] = 'Loan created successfully.';
                } else {
                    $_SESSION['loan_success'] = 'Unable to create the loan.';
                }
            } else {
                $_SESSION['loan_success'] = 'Please select a book and due date.';
            }
        }

        // Determine the current reader based on the logged-in user or default to the first reader in the list
        $currentReader = $readerModel->GetById($readerId);
        if ($currentReader === null && !empty($allReaders)) {
            return;
        }

        $loans = $loanModel->getByReader($readerId);

        $currentDate = new DateTime('now');
        $bookMap = $bookController->CreateBookMap($allBooks);

        $loanRows = [];
        $feeRows = [];
        $dueThisWeek = 0;
        $feesTotal = 0.0;

        foreach ($loans as $loan) {
            $book = $bookMap[(int) ($loan['book_id'] ?? 0)] ?? [];
            $title = $loan['book_title'] ?? ($book['title'] ?? 'Unknown title');
            $dueDateRaw = $loan['due_date'] ?? date('Y-m-d');
            $dueDate = new DateTime($dueDateRaw);
            $daysUntilDue = (int) $currentDate->diff($dueDate)->format('%r%a');

            if ($daysUntilDue >= 0 && $daysUntilDue <= 7) {
                $dueThisWeek++;
            }

            $getLoanStatus = strtolower($loan['status'] ?? Constants::DEFAULT_LOAN_STATUS);
            $badgeClass = 'badge-info';
            if ($getLoanStatus === 'overdue') {
                $badgeClass = 'badge-alert';
            }
            else {
                if ($getLoanStatus === 'due soon') {
                    $badgeClass = 'badge-warning';
                }
            }
            $loanRows[] = [
                'title' => $title,
                'due_date' => $dueDate->format('M j, Y'),
                'status' => $loan['status'] ?? Constants::DEFAULT_LOAN_STATUS,
                'renewals' => (int) ($loan['renewals'] ?? 0),
                'badge_class' => $badgeClass,
            ];

            $amount = (float) ($loan['fee_amount'] ?? 0.00);
            $feesTotal += $amount;
        }

        if (empty($feeRows)) {
            $feeRows = [
                [
                    'fee' => 'No current fees',
                    'reason' => 'Everything is up to date.',
                    'due_date' => '—',
                    'amount' => '$0.00',
                ],
            ];
        }

        $memberName = trim(($currentReader['FirstName'] ?? '') . ' ' . ($currentReader['LastName'] ?? ''));
        if ($memberName === '') {
            $memberName = 'name unknown';
        }

        $memberId = $currentReader['Id'] ?? $currentReader['Id'] ?? 'id unknown';
        $memberNumber = 'Member #' . $memberId;

        $summaryCards = [
            [
                'label' => 'Current loans',
                'value' => count($loanRows),
                'caption' => $dueThisWeek . ' due this week',
                'class' => 'accent',
            ],
            [
                'label' => 'Fines / fees',
                'value' => '$' . number_format($feesTotal, 2),
                'caption' => 'Due by ' . date('M j'),
                'class' => 'warning',
            ],
        ];

        $formBookOptions = $allBooks;
        $formReaderOptions = $allReaders;
        $successMessage = $_SESSION['loan_success'] ?? '';
        unset($_SESSION['loan_success']);

        ob_start();
        require_once __DIR__ . '/../views/patron_dashboard.php';

        return ob_get_clean();
    }
}
