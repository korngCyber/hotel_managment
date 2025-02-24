<?php
session_start();
require_once 'core_config/db.php';

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_SESSION['login_attempts'] >= 5) {
        $error = "Too many failed attempts. Please try again later.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $conn = db_connect();
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['login_attempts'] = 0;
                session_regenerate_id(true);
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $user['id'];

                echo '<script>
                        setTimeout(function() {
                            window.location.href = "dashboard.php";
                        }, 1500);
                      </script>';
                exit();
            } else {
                $_SESSION['login_attempts']++;
                $error = "Invalid username or password.";
            }
        } else {
            $_SESSION['login_attempts']++;
            $error = "Invalid username or password.";
        }

        $stmt->close();
        db_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-container {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        width: 400px;
    }

    .login-header {
        background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        color: white;
        padding: 30px;
        text-align: center;
        font-size: 28px;
        font-weight: bold;
    }

    .login-form {
        padding: 40px;
    }

    .form-control {
        border-radius: 30px;
        padding: 12px 20px;
        border: 2px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #2a5298;
        box-shadow: 0 0 0 0.2rem rgba(42, 82, 152, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        border: none;
        border-radius: 30px;
        padding: 12px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(0, 0, 0, 0.1);
    }

    .input-group-text {
        background-color: transparent;
        border-right: none;
        border-radius: 30px 0 0 30px;
    }

    .error-message {
        color: #dc3545;
        text-align: center;
        margin-bottom: 20px;
        font-weight: bold;
    }

    .forgot-password {
        text-align: right;
        margin-top: 10px;
    }

    .forgot-password a {
        color: #2a5298;
        text-decoration: none;
        font-weight: bold;
    }

    .signup-link {
        text-align: center;
        margin-top: 20px;
    }

    .signup-link a {
        color: #2a5298;
        text-decoration: none;
        font-weight: bold;
    }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-hotel me-2"></i>Hotel Management
        </div>
        <div class="login-form">
            <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username"
                            required>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                            required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="forgot-password">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
            <div class="signup-link">
                Don't have an account? <a href="signup.php">Sign Up</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const loginForm = document.querySelector("form");
        const loginButton = document.querySelector("button[type='submit']");

        loginForm.addEventListener("submit", function(event) {
            event.preventDefault();
            loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            loginButton.disabled = true;

            setTimeout(() => {
                loginForm.submit();
            }, 1500);
        });
    });
    </script>
</body>

</html>