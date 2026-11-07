<?php
require_once __DIR__ . '/../Utilities/Constants.php';
require_once __DIR__ . '/../Utilities/Enums.php';

class Loan
{
    private $conn;
    private $table = 'loan_records';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll(): array
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY due_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($loanId): ?array
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :loan_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':loan_id', (int) $loanId, PDO::PARAM_INT);
        $stmt->execute();

        $loan = $stmt->fetch(PDO::FETCH_ASSOC);
        return $loan ?: null;
    }

    public function getByReader($readerId, $includeReturned = true): array
    {
        $query = "SELECT * FROM " . $this->table . " WHERE reader_id = :reader_id";
        if (!$includeReturned) {
            $query .= " AND LOWER(status) NOT IN (:returned_status, :returned_late_status)";
        }
        $query .= " ORDER BY due_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':reader_id', (int) $readerId, PDO::PARAM_INT);
        if (!$includeReturned) {
            $stmt->bindValue(':returned_status', strtolower(LoanStatus::RETURNED->value), PDO::PARAM_STR);
            $stmt->bindValue(':returned_late_status', strtolower(LoanStatus::RETURNED_LATE->value), PDO::PARAM_STR);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveByReader($readerId): array
    {
        return $this->getByReader($readerId, false);
    }

    public function getReturnedByReader($readerId): array
    {
        $query = "SELECT * FROM " . $this->table . " WHERE reader_id = :reader_id AND (LOWER(status) = :returned_status OR LOWER(status) = :returned_late_status) ORDER BY returned_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':reader_id', (int) $readerId, PDO::PARAM_INT);
        $stmt->bindValue(':returned_status', strtolower(LoanStatus::RETURNED->value), PDO::PARAM_STR);
        $stmt->bindValue(':returned_late_status', strtolower(LoanStatus::RETURNED_LATE->value), PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $loanData): bool
    {
        $bookId = (int) ($loanData['book_id'] ?? 0);
        $readerId = (int) ($loanData['reader_id'] ?? 0);
        $checkoutDate = $loanData['checkout_date'] ?? null;
        $dueDate = $loanData['due_date'] ?? null;
        $status = $loanData['status'] ?? LoanStatus::CHECKED_OUT->value;
        $renewals = (int) ($loanData['renewals'] ?? -1);
        $feeAmount = (float) ($loanData['fee_amount'] ?? -1);

        if ($bookId <= 0 || $readerId <= 0) {
            return false;
        }

        if (!strtotime($dueDate) || !strtotime($checkoutDate)) {
            return false;
        }

        if ($status !== LoanStatus::CHECKED_OUT->value && $status !== LoanStatus::ON_HOLD->value) {
            return false;
        }
        if ($renewals < 0 || $feeAmount < 0) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . " (book_id, reader_id, checkout_date, due_date, status, renewals, fee_amount)
                  VALUES (:book_id, :reader_id, :checkout_date, :due_date, :status, :renewals, :fee_amount)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':book_id' => $bookId,
            ':reader_id' => $readerId,
            ':checkout_date' => $checkoutDate,
            ':due_date' => $dueDate,
            ':status' => $status,
            ':renewals' => $renewals,
            ':fee_amount' => $feeAmount,
        ]);
    }

    public function markReturned($loanId, $returnedAt = null): bool
    {
        $loan = $this->getById($loanId);
        if ($loan === null) {
            return false;
        }

        $returnTimestamp = $returnedAt ?: date('Y-m-d H:i:s');
        $lateFee = $this->calculateLateFee($loan, $returnTimestamp);
        $status = LoanStatus::RETURNED->value;
        if ($lateFee > 0) {
            $status = LoanStatus::RETURNED_LATE->value;
        }

        $query = "UPDATE " . $this->table . " SET status = :status, fee_amount = :fee_amount, returned_at = :returned_at";
        $params = [
            ':status' => $status,
            ':fee_amount' => (float) ($loan['fee_amount'] ?? 0.00) + $lateFee,
            ':returned_at' => $returnTimestamp,
            ':loan_id' => (int) $loanId,
        ];
        $query .= " WHERE id = :loan_id";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    private function calculateLateFee(array $loan, $returnedAt): float
    {
        $dueDate = $loan['due_date'];
        $returnDate = $returnedAt ?: date('Y-m-d H:i:s');

        try {
            $dueDateTime = new DateTime($dueDate);
            $returnedDateTime = new DateTime($returnDate);
        } catch (Exception $e) {
            return 0.0;
        }

        if ($returnedDateTime <= $dueDateTime) {
            return 0.0;
        }

        $daysLate = (int) $dueDateTime->diff($returnedDateTime)->format('%a');
        return $daysLate > 0 ? round($daysLate * 2.50, 2) : 0.0;
    }
}
