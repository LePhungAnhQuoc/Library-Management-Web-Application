<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Login</title>
    <link rel="stylesheet" href="../views/login/login.css">
</head>

<body>
    <div class="auth-shell">
        <div class="brand-panel">
            <div class="brand-content">
                <span class="brand-badge">Library System</span>
                <h1>Welcome back</h1>
                <p>Access your account to manage loans, readers, and the catalog with ease.</p>
            </div>
        </div>

        <div class="login-card">
            <div class="card-header">
                <p class="eyebrow">Sign in</p>
                <h2>Account Access</h2>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?action=login" class="login-form">
                <div class="form-group">
                    <label for="email">Email address</label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" value="<?php echo htmlspecialchars($_COOKIE['remembered_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                    <div class="form-group password-group">
                    <label for="password">Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="toggle-password" aria-label="Show password" aria-pressed="false" title="Show password">
                                <svg aria-hidden="true" viewBox="0 0 24 24" class="eye-icon">
                                    <path d="M1.5 12s3.75-6 10.5-6 10.5 6 10.5 6-3.75 6-10.5 6S1.5 12 1.5 12zm10.5 3.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7zm0-1.6A1.9 1.9 0 1 1 12 9.1a1.9 1.9 0 0 1 0 3.8z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" value="1" <?php echo (!empty($_COOKIE['remembered_email']) ? 'checked' : ''); ?>>
                            <span>Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="btn">Sign In</button>
                </form>
            </div>
    </div>

    <script>
            document.addEventListener('DOMContentLoaded', function () {
                const passwordInput = document.getElementById('password');
                const toggleButton = document.querySelector('.toggle-password');

                if (passwordInput && toggleButton) {
                    toggleButton.addEventListener('click', function () {
                        const shouldShow = passwordInput.type === 'password';
                        passwordInput.type = shouldShow ? 'text' : 'password';
                        toggleButton.title = shouldShow ? 'Hide password' : 'Show password';
                        toggleButton.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
                        toggleButton.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
                        toggleButton.classList.toggle('is-visible', shouldShow);
                    });
                }
            });
    </script>
</body>

</html>
