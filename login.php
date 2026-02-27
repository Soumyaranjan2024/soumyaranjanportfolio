<?php
require 'auth.php';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $register_error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = 'Invalid email format';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->rowCount() > 0) {
                $register_error = 'Username or email already exists';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                $register_success = 'Registration successful! You can now login.';
            }
        } catch (PDOException $e) {
            $register_error = 'Registration failed: ' . $e->getMessage();
        }
    }
}

// Redirect logged-in users
if (isAdminLoggedIn()) {
    header('Location: admin.php');
    exit;
} elseif (isUserLoggedIn()) {
    header('Location: user_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register - Soumya's Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background particles */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            33% {
                transform: translateY(-30px) rotate(120deg);
            }

            66% {
                transform: translateY(30px) rotate(240deg);
            }
        }

        .auth-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .sliding-tabs {
            display: flex;
            position: relative;
            background: rgba(255, 255, 255, 0.1);
            margin: 20px;
            border-radius: 15px;
            padding: 5px;
        }

        .tab-button {
            flex: 1;
            padding: 15px 20px;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }

        .tab-button.active {
            color: #1a1a2e;
        }

        .sliding-indicator {
            position: absolute;
            top: 5px;
            left: 5px;
            width: calc(50% - 5px);
            height: calc(100% - 10px);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            border-radius: 12px;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .sliding-indicator.register {
            transform: translateX(100%);
        }

        .form-container {
            position: relative;
            height: 500px;
            overflow: hidden;
        }

        .form-wrapper {
            display: flex;
            width: 200%;
            height: 100%;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-wrapper.show-register {
            transform: translateX(-50%);
        }

        .form-section {
            width: 50%;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .form-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 16px;
            z-index: 2;
        }

        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(10px);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-group input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.15);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .checkbox-group input[type="checkbox"] {
            display: none;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 400;
            user-select: none;
        }

        .checkmark {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            margin-right: 8px;
            position: relative;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
        }

        .checkmark::after {
            content: '';
            position: absolute;
            left: 4px;
            top: 1px;
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .checkbox-group input[type="checkbox"]:checked+.checkbox-label .checkmark {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.6);
        }

        .checkbox-group input[type="checkbox"]:checked+.checkbox-label .checkmark::after {
            opacity: 1;
        }

        .btn {
            width: 100%;
            padding: 16px 24px;
            color: #1a1a2e;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:active {
            transform: translateY(0);
        }

        .error-message,
        .success-message {
            margin: 15px 0 0 0;
            text-align: center;
            font-size: 13px;
            font-weight: 500;
            padding: 12px 16px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .error-message {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        .success-message {
            color: #51cf66;
            background: rgba(81, 207, 102, 0.1);
            border: 1px solid rgba(81, 207, 102, 0.3);
        }

        @media (max-width: 480px) {
            .auth-container {
                margin: 20px;
                max-width: none;
            }

            .form-section {
                padding: 30px 20px;
            }

            .form-header h2 {
                font-size: 24px;
            }

            .sliding-tabs {
                margin: 15px;
            }

            .tab-button {
                padding: 12px 15px;
                font-size: 14px;
            }
        }

        /* Floating animation for icons */
        @keyframes iconFloat {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-3px);
            }
        }

        .input-wrapper i {
            animation: iconFloat 2s ease-in-out infinite;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <!-- Sliding Tabs -->
        <div class="sliding-tabs">
            <div class="sliding-indicator" id="slidingIndicator"></div>
            <button class="tab-button active" id="loginTab" onclick="switchTab('login')">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
            <button class="tab-button" id="registerTab" onclick="switchTab('register')">
                <i class="fas fa-user-plus"></i> Register
            </button>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <div class="form-wrapper" id="formWrapper">
                <!-- Login Section -->
                <div class="form-section">
                    <div class="form-header">
                        <h2>Welcome Back</h2>
                        <p>Sign in to your account</p>
                    </div>
                    <form method="POST" action="auth.php">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" id="username" name="username" placeholder="Enter your username"
                                    required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Enter your password"
                                    required>
                            </div>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" id="remember_me" name="remember_me" value="1">
                            <label for="remember_me" class="checkbox-label">
                                <span class="checkmark"></span>
                                Remember me for 30 days
                            </label>
                        </div>
                        <button type="submit" name="login" class="btn">
                            <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
                            Sign In
                        </button>
                        <?php if (isset($login_error)): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
                                <?php echo $login_error; ?>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Registration Section -->
                <div class="form-section">
                    <div class="form-header">
                        <h2>Join Us Today</h2>
                        <p>Create your new account</p>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label>Username</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="username" placeholder="Choose a username" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="Create a password" required>
                            </div>
                        </div>
                        <button type="submit" name="register" class="btn">
                            <i class="fas fa-user-plus" style="margin-right: 8px;"></i>
                            Create Account
                        </button>
                        <?php if (isset($register_error)): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
                                <?php echo $register_error; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($register_success)): ?>
                            <div class="success-message">
                                <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
                                <?php echo $register_success; ?>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            const loginTab = document.getElementById('loginTab');
            const registerTab = document.getElementById('registerTab');
            const slidingIndicator = document.getElementById('slidingIndicator');
            const formWrapper = document.getElementById('formWrapper');

            if (tab === 'login') {
                loginTab.classList.add('active');
                registerTab.classList.remove('active');
                slidingIndicator.classList.remove('register');
                formWrapper.classList.remove('show-register');
            } else {
                registerTab.classList.add('active');
                loginTab.classList.remove('active');
                slidingIndicator.classList.add('register');
                formWrapper.classList.add('show-register');
            }
        }

        // Auto-switch to register tab if there's a registration error or success message
        <?php if (isset($register_error) || isset($register_success)): ?>
            switchTab('register');
        <?php endif; ?>
    </script>
</body>

</html>