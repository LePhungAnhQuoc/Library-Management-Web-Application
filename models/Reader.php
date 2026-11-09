<?php
class Reader
{
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $membership_date;
    public $status;
    public $created_at;

    private $conn;
    private $table = 'readers';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Fetch all readers from database
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Insert a new reader
    public function create($reader)
    {
        $query = "INSERT INTO " . $this->table . " (first_name, last_name, email, membership_date, status, created_at)
                  VALUES (:first_name, :last_name, :email, :membership_date, :status, :created_at)";
        $stmt = $this->conn->prepare($query);

        $arrayValues = [
            ':first_name' => $reader->first_name,
            ':last_name' => $reader->last_name,
            ':email' => $reader->email,
            ':membership_date' => $reader->membership_date !== '' ? $reader->membership_date : null,
            ':status' => $reader->status,
            ':created_at' => $reader->created_at
        ];

        return $stmt->execute($arrayValues);
    }
}