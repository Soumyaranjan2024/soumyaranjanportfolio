<?php
require 'auth.php';

// Redirect if not in OTP verification state
if (!isset($_SESSION['otp_email']) || !isset($_SESSION['otp_user_data'])) {
    header('Location: login.php');
    exit;
}

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdminLoggedIn()) {
        header('Location: admin.php');
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
}

$email = $_SESSION['otp_email'];
$masked_email = substr($email, 0, 2) . str_repeat('*', strlen($email) - 6) . substr($email, -4);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Soumya's Portfolio</title>
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

        .otp-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 450px;
            padding: 40px 30px;
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .otp-header {
            margin-bottom: 30px;
        }

        .otp-header h2 {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .otp-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            font-weight: 400;
        }

        .email-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .email-info i {
            color: rgba(255, 255, 255, 0.8);
            margin-right: 8px;
        }

        .email-info span {
            color: white;
            font-weight: 500;
        }

        .otp-input-group {
            margin-bottom: 20px;
        }

        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .otp-input {
            width: 50px;
            height: 60px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .otp-input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.15);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            justify-content: center;
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

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .resend-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .resend-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            margin-bottom: 10px;
        }

        .resend-btn {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .resend-btn:hover {
            border-color: rgba(255, 255, 255, 0.6);
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .back-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: white;
        }

        .error-message {
            margin: 15px 0;
            text-align: center;
            font-size: 13px;
            font-weight: 500;
            padding: 12px 16px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        .timer {
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            margin-top: 10px;
        }

        @media (max-width: 480px) {
            .otp-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .otp-inputs {
                gap: 8px;
            }

            .otp-input {
                width: 45px;
                height: 55px;
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="otp-container">
        <div class="otp-header">
            <h2><i class="fas fa-shield-alt"></i> Verify Your Identity</h2>
            <p>Enter the 6-digit code sent to your email</p>
        </div>

        <div class="email-info">
            <i class="fas fa-envelope"></i>
            <span><?php echo $masked_email; ?></span>
        </div>

        <form method="POST" id="otpForm">
            <div class="otp-input-group">
                <div class="otp-inputs">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                </div>
                <input type="hidden" name="otp_code" id="otpCode">
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="remember_me" name="remember_me" value="1">
                <label for="remember_me" class="checkbox-label">
                    <span class="checkmark"></span>
                    Remember me for 30 days
                </label>
            </div>

            <button type="submit" name="verify_otp" class="btn">
                <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
                Verify & Login
            </button>

            <?php if (isset($otp_error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
                    <?php echo $otp_error; ?>
                </div>
            <?php endif; ?>
        </form>

        <div class="resend-section">
            <p class="resend-text">Didn't receive the code?</p>
            <button class="resend-btn" onclick="resendOTP()">
                <i class="fas fa-redo" style="margin-right: 5px;"></i>
                Resend Code
            </button>
            <div class="timer" id="timer"></div>
        </div>

        <div style="margin-top: 20px;">
            <a href="login.php" class="back-link">
                <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>
                Back to Login
            </a>
        </div>
    </div>

    <script>
        // OTP Input handling
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpCodeInput = document.getElementById('otpCode');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;

                if (value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }

                updateOTPCode();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const paste = e.clipboardData.getData('text');
                const digits = paste.replace(/\D/g, '').slice(0, 6);

                digits.split('').forEach((digit, i) => {
                    if (otpInputs[i]) {
                        otpInputs[i].value = digit;
                    }
                });

                updateOTPCode();

                if (digits.length === 6) {
                    document.getElementById('otpForm').submit();
                }
            });
        });

        function updateOTPCode() {
            const code = Array.from(otpInputs).map(input => input.value).join('');
            otpCodeInput.value = code;
        }

        // Timer for resend
        let timeLeft = 60;
        const timerElement = document.getElementById('timer');
        const resendBtn = document.querySelector('.resend-btn');

        function updateTimer() {
            if (timeLeft > 0) {
                timerElement.textContent = `Resend available in ${timeLeft}s`;
                resendBtn.disabled = true;
                resendBtn.style.opacity = '0.5';
                timeLeft--;
                setTimeout(updateTimer, 1000);
            } else {
                timerElement.textContent = '';
                resendBtn.disabled = false;
                resendBtn.style.opacity = '1';
            }
        }

        updateTimer();

        function resendOTP() {
            if (timeLeft <= 0) {
                // Reset timer
                timeLeft = 60;
                updateTimer();

                // Here you would make an AJAX call to resend OTP
                fetch('resend_otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('New OTP sent to your email!');
                        } else {
                            alert('Failed to resend OTP. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to resend OTP. Please try again.');
                    });
            }
        }

        // Auto-submit when all fields are filled
        otpInputs.forEach(input => {
            input.addEventListener('input', () => {
                const allFilled = Array.from(otpInputs).every(inp => inp.value.length === 1);
                if (allFilled) {
                    setTimeout(() => {
                        document.getElementById('otpForm').submit();
                    }, 500);
                }
            });
        });
    </script>
</body>

</html>