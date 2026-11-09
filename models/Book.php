<?php
require_once __DIR__ . '/../Utilities/Enums.php';

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
    public $status = BookStatus::AVAILABLE->value;
    public $summary;
    public $emoji;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Fetch all books from database
    public function getAll() : array
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
    public function create($book): bool
    {
        $bookController = new BookController();
        if (!is_object($book)) {
            return false;
        }

        $bookData = [
            'title' => trim((string) ($book->title ?? '')),
            'author' => trim((string) ($book->author ?? '')),
            'isbn' => trim((string) ($book->isbn ?? '')),
            'category' => trim((string) ($book->category ?? '')),
            'genre' => trim((string) ($book->genre ?? '')),
            'format' => trim((string) ($book->format ?? '')),
            'status' => trim((string) ($book->status ?? '')),
            'summary' => trim((string) ($book->summary ?? '')),
            'emoji' => trim((string) ($book->emoji ?? '')),
        ];

        foreach ($bookData as $field => $value) {
            if ($value === '') {
                if ($field === 'summary' || $field === 'emoji') {
                    continue;
                }
                return false;
            }
        }

        if (!in_array($bookData['status'], [BookStatus::AVAILABLE->value, BookStatus::BORROWED->value], true)) {
            return false;
        }

        if (!$bookController->ValidateISBN($bookData['isbn'])) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . " (title, author, isbn, category, genre, format, status, summary, emoji) VALUES (:title, :author, :isbn, :category, :genre, :format, :status, :summary, :emoji)";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($bookData);
    }
}