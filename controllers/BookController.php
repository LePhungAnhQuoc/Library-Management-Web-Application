<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Category.php';

class BookController
{
    private $idPrefix = 'BT';
    private $maxIndexId = -1;
    private $isDataFetch = false;

    private function GenerateId()
    {
        if ($this->isDataFetch == false) {
            return null;
        }

        if (empty($this->books)) {
            // Fallback ID if the list is completely empty
            return $this->idPrefix . '01';
        }

        if ($this->maxIndexId == -1) {
            $this->maxIndexId = 0;

            foreach ($this->books as $book) {
                $indexIdStr = str_replace($this->idPrefix, "", $book['Id']);
                $indexId = (int) $indexIdStr;

                // Find the maximum tail number
                if ($indexId > $this->maxIndexId) {
                    $this->maxIndexId = $indexId;
                }
            }
        }

        // Increment the highest tail number by 1
        $nextIndex = $this->maxIndexId + 1;

        return $this->idPrefix . str_pad($nextIndex, 2, '0', STR_PAD_LEFT);
    }

    private function validate($book): bool
    {
        if (empty($book->name))
            return false;
        if (empty($book->idcategory))
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

        // Fetch data to display
        $this->books = $bookModel->getAll();
        $this->isDataFetch = true;
        $this->categories = $categoryModel->getAll();

        $categoryMap = [];
        foreach ($this->categories as $cat) {
            $categoryMap[$cat['Id']] = $cat['Name'];
        }

        // Handle POST submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newBook = new Book($db);
            $newBook->id = $this->GenerateId();
            if ($newBook->id != null) {
                $newBook->name = trim($_POST['name'] ?? '');
                $newBook->idcategory = trim($_POST['category'] ?? '');

                if ($this->validate($newBook)) {
                    if ($bookModel->create($newBook)) {
                        $success_message = "New book added successfully!";
                    } else {
                        $error_message = "Failed to insert the book.";
                    }
                } else {
                    $error_message = "Please enter all required fields!";
                }
            }
        }

        $books = $this->books;
        $categories = $this->categories;

        // 1. Turn on output buffering
        ob_start();

        require_once __DIR__ . '/../views/book_view/book_view.php';

        // 3. Get the contents of the buffer and clean it, returning it as a string
        return ob_get_clean();
    }
}