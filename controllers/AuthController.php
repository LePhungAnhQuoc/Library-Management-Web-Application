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
        $rememberedEmail = $_COOKIE['remembered_email'] ?? '';

        // Handle Form Submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $rememberMe = isset($_POST['remember']) && $_POST['remember'] == '1';

            if (empty($email) || empty($password)) {
                $error = 'Please fill in all required fields.';
            } else {
                $user = $this->userModel->findUserByEmail($email);

                // Verify user existence and password hash
                if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                    if ($rememberMe) {
                        setcookie('remembered_email', $email, time() + 60 * 60 * 24 * 30, '/');
                    } else {
                        setcookie('remembered_email', '', time() - 3600, '/');
                    }

                    // Regenerate session ID to prevent session fixation attacks
                    session_regenerate_id(true);

                    // Start session and save user info
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_id'] = $user['id'];

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

        setcookie('remembered_email', '', time() - 3600, '/');

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