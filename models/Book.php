<?php
class Book
{
    private $conn;
    private $table = 'books';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Fetch all books from database
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY Id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Insert a new book
    public function create($book)
    {
        $query = "INSERT INTO " . $this->table . " (id, name, idcategory) VALUES (:id, :name, :idcategory)";
        $stmt = $this->conn->prepare($query);

        $arrayValues = [
            'id' => $book->id,
            'name' => $book->name,
            'idcategory' => $book->idcategory
        ];

        return $stmt->execute($arrayValues);
    }
}