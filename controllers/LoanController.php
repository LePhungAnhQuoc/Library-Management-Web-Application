<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Utilities/Constants.php';
require_once __DIR__ . '/../Utilities/Enums.php';
require_once __DIR__ . '/../models/Loan.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Reader.php';
require_once __DIR__ . '/../models/User.php';

class LoanController
{
    private $db;
    private $allReaders;
    private $allBooks;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_loan_id'])) {
            $loanId = (int) ($_POST['return_loan_id'] ?? 0);
            if ($loanId > 0) {
                $returned = $loanModel->markReturned($loanId);
                $_SESSION['loan_success'] = $returned ? 'Book returned successfully.' : 'Unable to process the return.';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loan_book_id'])) {
            $bookId = (int) ($_POST['loan_book_id'] ?? 0);

            // Use the reader_id from the form submission if available, otherwise default to the current readerId (current logged-in user)
            $selectedReaderId = (int) ($_POST['reader_id'] ?? $readerId);
            $dueDate = trim($_POST['due_date'] ?? '');
            $status = trim($_POST['status'] ?? LoanStatus::CHECKED_OUT->value);
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

        $allLoans = $loanModel->getByReader($readerId);
        $activeLoans = $loanModel->getActiveByReader($readerId);
        $returnedLoans = $loanModel->getReturnedByReader($readerId);

        $currentDate = new DateTime('now');
        $bookMap = $bookController->CreateBookMap($allBooks);

        $loanRows = [];
        $holdRows = [];
        $historyRows = [];
        $feeRows = [];
        $dueThisWeek = 0;
        $feesTotal = 0.0;
        $activeHolds = 0;

        foreach ($activeLoans as $loan) {
            $book = $bookMap[(int) ($loan['book_id'] ?? 0)] ?? [];
            $title = $loan['book_title'] ?? ($book['title'] ?? 'Unknown title');
            $dueDateRaw = $loan['due_date'] ?? date('Y-m-d');
            $dueDate = new DateTime($dueDateRaw);
            $daysUntilDue = (int) $currentDate->diff($dueDate)->format('%r%a');

            if ($daysUntilDue >= 0 && $daysUntilDue <= 7) {
                $dueThisWeek++;
            }

            $loanStatus = LoanStatus::fromValue($loan['status']);
            $badgeClass = $loanStatus->badgeClass()->value;

            $loanRows[] = [
                'id' => (int) ($loan['id'] ?? 0),
                'title' => $title,
                'due_date' => $dueDate->format('M j, Y'),
                'status' => $loanStatus->value,
                'renewals' => (int) ($loan['renewals'] ?? 0),
                'badge_class' => $badgeClass,
            ];

            if ($loanStatus === LoanStatus::ON_HOLD) {
                $activeHolds++;
                $holdRows[] = [
                    'id' => (int) ($loan['id'] ?? 0),
                    'title' => $title,
                    'held_on' => !empty($loan['checkout_date']) ? (new DateTime($loan['checkout_date']))->format('M j, Y') : '—',
                    'due_date' => $dueDate->format('M j, Y'),
                    'status' => $loanStatus->value,
                    'badge_class' => $badgeClass,
                ];
            }
        }

        foreach ($returnedLoans as $loan) {
            $book = $bookMap[(int) ($loan['book_id'] ?? 0)] ?? [];
            $title = $loan['book_title'] ?? ($book['title'] ?? 'Unknown title');
            $returnTimestamp = $loan['returned_at'] ?? $loan['return_date'] ?? $loan['returned_date'] ?? ($loan['due_date'] ?? date('Y-m-d'));
            $historyRows[] = [
                'title' => $title,
                'due_date' => !empty($loan['due_date']) ? (new DateTime($loan['due_date']))->format('M j, Y') : '—',
                'returned_at' => $this->formatReturnTimestamp($returnTimestamp),
                'status' => $loan['status'] ?? 'Returned',
                'amount' => '$' . number_format((float) ($loan['fee_amount'] ?? 0.00), 2),
            ];
        }

        foreach ($allLoans as $loan) {
            $amount = (float) ($loan['fee_amount'] ?? 0.00);
            if ($amount > 0) {
                $feeType = FeeType::fromLoanStatus($loan['status'] ?? LoanStatus::RETURNED->value);
                if ($feeType === FeeType::LATE_RETURN) {
                    $feeRows[] = [
                        'fee' => $feeType->value,
                        'reason' => 'Late item return',
                        'due_date' => !empty($loan['due_date']) ? (new DateTime($loan['due_date']))->format('M j, Y') : '—',
                        'amount' => '$' . number_format($amount, 2),
                    ];
                }
            }
            $feesTotal += $amount;
        }

        if (empty($feeRows)) {
            $feeRows = [
                [
                    'fee' => FeeType::NO_CURRENT_FEES->value,
                    'reason' => 'Everything is up to date.',
                    'due_date' => '—',
                    'amount' => '$' . number_format(0, 2),
                ]
            ];
        }

        $memberName = trim(($currentReader['FirstName'] ?? '') . ' ' . ($currentReader['LastName'] ?? ''));
        if ($memberName === '') {
            $memberName = 'name unknown';
        }

        $memberId = $currentReader['Id'] ?? 'id unknown';
        $memberNumber = 'Member #' . $memberId;

        $summaryCards = [
            [
                'label' => 'Current loans',
                'value' => count($loanRows),
                'caption' => $dueThisWeek . ' due this week',
                'class' => 'accent',
            ],
            [
                'label' => 'Active holds',
                'value' => $activeHolds,
                'caption' => $activeHolds === 1 ? '1 item on hold' : $activeHolds . ' items on hold',
                'class' => 'info',
            ],
            [
                'label' => 'Fines / fees',
                'value' => '$' . number_format($feesTotal, 2),
                'caption' => 'Due by ' . date('M j'),
                'class' => 'warning',
            ],
             [
                'label' => 'Returned loans',
                'value' => count($returnedLoans),
                'caption' => count($returnedLoans) . ' total returned',
                'class' => 'success',
            ],
        ];

        $formBookOptions = $allBooks;
        $formReaderOptions = $allReaders;
        $successMessage = $_SESSION['loan_success'] ?? '';
        unset($_SESSION['loan_success']);

        ob_start();
        require_once __DIR__ . '/../views/patron_dashboard/patron_dashboard.php';

        return ob_get_clean();
    }

    private function formatReturnTimestamp($value): string
    {
        if (empty($value)) {
            return '—';
        }

        try {
            $dateTime = new DateTime($value);
            return $dateTime->format('M j, Y g:i A');
        } catch (Exception $e) {
            return (string) $value;
        }
    }

    public function IsReturned($loan): bool
    {
        $status = strtolower(trim((string) ($loan['status'] ?? '')));
        return $status === strtolower(LoanStatus::RETURNED->value) || $status === strtolower(LoanStatus::RETURNED_LATE->value);
    }
}
