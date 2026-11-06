<?php
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $userModel;

    public function __construct($pdo)
    {
        $this->userModel = new User($pdo);
    }

    public function login()
    {
        $error = '';

        // Handle Form Submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $error = 'Please fill in all required fields.';
            } else {
                $user = $this->userModel->findUserByEmail($email);

                // Verify user existence and password hash
                if ($user && $this->userModel->verifyPassword($password, $user['Password'])) {
                    // Regenerate session ID to prevent session fixation attacks
                    session_regenerate_id(true);

                    // Start session and save user info
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['user_id'] = $user['Id'];
                    $_SESSION['user_email'] = $user['Email'];

                    // Redirect to dashboard or home page
                    header('Location: index.php?action=dashboard');
                    exit;
                } else {
                    $error = 'Invalid email or password.';
                }
            }
        }

        // Render the view with potential error message
        require __DIR__ . '/../views/login/login.php';
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_unset();
        session_destroy();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        header('Location: index.php?action=login');
        exit;
    }
}