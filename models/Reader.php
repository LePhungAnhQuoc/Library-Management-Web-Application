<?php
class Reader
{
    public $Id;
    public $FirstName;
    public $LastName;
    public $Email;
    public $MembershipDate;
    public $Status;
    public $CreatedAt;

    private $conn;
    private $table = 'readers';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Fetch all readers from database
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY Id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Insert a new reader
    public function create($reader)
    {
        $query = "INSERT INTO " . $this->table . " (Id, FirstName, LastName, Email, MembershipDate, Status, CreatedAt) VALUES (:Id, :FirstName, :LastName, :Email, :MembershipDate, :Status, :CreatedAt)";
        $stmt = $this->conn->prepare($query);

        $arrayValues = [
            ':Id' => $reader->Id,
            ':FirstName' => $reader->FirstName,
            ':LastName' => $reader->LastName,
            ':Email' => $reader->Email,
            ':MembershipDate' => $reader->MembershipDate,
            ':Status' => $reader->Status,
            ':CreatedAt' => $reader->CreatedAt
        ];

        return $stmt->execute($arrayValues);
    }
}