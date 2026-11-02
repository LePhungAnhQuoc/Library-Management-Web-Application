<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/BookController.php';

session_start();

// Database Connection
$database = new Database();
$db = $database->connect();

// Route parameters
$action = $_GET['action'] ?? 'login';

switch ($action) {
    case 'login':
        $auth = new AuthController($db);
        $auth->login();
        break;

    case 'logout':
        $auth = new AuthController($db);
        $auth->logout();
        break;

    case 'dashboard':
        // Ensure the user is logged in before handling route switches
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        require_once __DIR__ . '/../views/dashboard.php';
        break;
    default:
        echo "404 Page Not Found";
        break;
}