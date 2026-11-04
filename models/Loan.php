<?php
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

    public function getByReader($readerId): array
    {
        $query = "SELECT * FROM " . $this->table . " WHERE reader_id = :reader_id ORDER BY due_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':reader_id', (int) $readerId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $loanData): bool
    {
        $query = "INSERT INTO " . $this->table . " (book_id, reader_id, checkout_date, due_date, status, renewals, fee_amount)
                  VALUES (:book_id, :reader_id, :checkout_date, :due_date, :status, :renewals, :fee_amount)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':book_id' => (int) ($loanData['book_id'] ?? 0),
            ':reader_id' => (int) ($loanData['reader_id'] ?? 0),
            ':checkout_date' => $loanData['checkout_date'] ?? date('Y-m-d'),
            ':due_date' => $loanData['due_date'] ?? date('Y-m-d', strtotime('+14 days')),
            ':status' => $loanData['status'] ?? 'Checked out',
            ':renewals' => (int) ($loanData['renewals'] ?? 0),
            ':fee_amount' => (float) ($loanData['fee_amount'] ?? 0.00),
        ]);
    }
}
