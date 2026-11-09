<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Reader.php';

class ReaderController
{
    private $db;
    private $maxIndexId = -1;
    private $isDataFetch = false;
    private $error_message = '';
    private $readers = [];

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    private function validate($reader): bool
    {
        if (empty($reader->first_name))
            return false;
        if (empty($reader->last_name))
            return false;
        if (empty($reader->email))
            return false;

        // Checking if the email is exists in the database
        $filterByEmail = array_filter($this->readers, function ($r) use ($reader) {
            return $r['email'] === $reader->email;
        });
        if (!empty($filterByEmail)) {
            $this->error_message = "Email already exists!";
            return false;
        }
        return true;
    }

    public function index()
    {
        $db = $this->db;
        $readerModel = new Reader($db);

        $success_message = '';
        $this->error_message = '';

        $this->readers = $readerModel->getAll();
        $this->isDataFetch = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newReader = new Reader($db);

            $newReader->first_name = trim($_POST['first_name'] ?? '');
            $newReader->last_name = trim($_POST['last_name'] ?? '');
            $newReader->email = trim($_POST['email'] ?? '');
            $newReader->membership_date = trim($_POST['membership_date'] ?? '');
            $newReader->status = trim($_POST['status'] ?? 'active');
            $newReader->created_at = date('Y-m-d H:i:s');

            if ($this->validate($newReader)) {
                if ($readerModel->create($newReader)) {
                    $success_message = "New reader added successfully!";
                    $this->readers = $readerModel->getAll();
                } else {
                    $this->error_message = "Failed to insert the reader.";
                }
            } else {
                if ($this->error_message === '') {
                    $this->error_message = "Please enter all required fields!";
                }
            }
        }

        $readers = $this->readers;
        $error_message = $this->error_message;

        ob_start();

        require_once __DIR__ . '/../views/reader_view/reader_view.php';

        return ob_get_clean();
    }
}