<?php

class User {
    private $db;

    public function __construct($pdoConnection) {
        $this->db = $pdoConnection;
    }

    // Find user by email or username
    public function findUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Validate password using PHP's native password_verify
    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }
}