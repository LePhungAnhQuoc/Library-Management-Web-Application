<?php
class Book
{
    private $conn;
    private $table = 'books';

    public $id;
    public $title;
    public $author;
    public $isbn;
    public $category;
    public $genre;
    public $format;
    public $status = 'Available';
    public $summary;
    public $emoji;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Fetch all books from database
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Insert a new book
    public function create($book)
    {
        $query = "INSERT INTO " . $this->table . " (title, author, isbn, category, genre, format, status, summary, emoji) VALUES (:title, :author, :isbn, :category, :genre, :format, :status, :summary, :emoji)";
        $stmt = $this->conn->prepare($query);

        $arrayValues = [
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'category' => $book->category,
            'genre' => $book->genre,
            'format' => $book->format,
            'status' => $book->status,
            'summary' => $book->summary,
            'emoji' => $book->emoji
        ];

        return $stmt->execute($arrayValues);
    }
}