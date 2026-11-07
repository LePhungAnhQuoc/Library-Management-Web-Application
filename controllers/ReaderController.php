<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Reader.php';

class ReaderController
{
    private $db;
    private $maxIndexId = -1;
    private $isDataFetch = false;
    private $error_message = '';

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    private function validate($reader): bool
    {
        if (empty($reader->FirstName))
            return false;
        if (empty($reader->LastName))
            return false;
        if (empty($reader->Email))
            return false;

        // Checking if the email is exists in the database
        $filterByEmail = array_filter($this->readers, function ($r) use ($reader) {
            return $r['Email'] === $reader->Email;
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

            $newReader->FirstName = trim($_POST['firstName'] ?? '');
            $newReader->LastName = trim($_POST['lastName'] ?? '');
            $newReader->Email = trim($_POST['email'] ?? '');
            $newReader->MembershipDate = trim($_POST['membershipDate'] ?? '');
            $newReader->Status = trim($_POST['status'] ?? 'active');
            $newReader->CreatedAt = date('Y-m-d H:i:s');

            if ($this->validate($newReader)) {
                if ($readerModel->create($newReader)) {
                    $success_message = "New reader added successfully!";
                    $this->readers[] = (array) $newReader;
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