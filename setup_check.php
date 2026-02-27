<?php
// Database setup verification script
// Run this file first to check your setup

echo "<h2>Email System Setup Check</h2>";

// Check if XAMPP/MySQL is running
echo "<h3>1. Checking MySQL Connection...</h3>";

$host = 'localhost';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password);
    if ($conn->connect_error) {
        echo "❌ <strong>MySQL Connection Failed:</strong> " . $conn->connect_error . "<br>";
        echo "<strong>Solutions:</strong><br>";
        echo "• Start XAMPP Control Panel and start MySQL service<br>";
        echo "• Check if port 3306 is available<br>";
        echo "• Verify MySQL is installed correctly<br>";
    } else {
        echo "✅ <strong>MySQL Connection Successful!</strong><br>";

        // Check MySQL version
        echo "MySQL Version: " . $conn->server_info . "<br>";

        // Try to create database
        $db_name = 'email_system';
        $sql = "CREATE DATABASE IF NOT EXISTS $db_name";
        if ($conn->query($sql)) {
            echo "✅ <strong>Database '$db_name' created/verified successfully!</strong><br>";
        } else {
            echo "❌ <strong>Error creating database:</strong> " . $conn->error . "<br>";
        }
    }
    $conn->close();
} catch (Exception $e) {
    echo "❌ <strong>Exception:</strong> " . $e->getMessage() . "<br>";
}

// Check PHP extensions
echo "<h3>2. Checking PHP Extensions...</h3>";

$required_extensions = ['mysqli', 'openssl', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ <strong>$ext:</strong> Loaded<br>";
    } else {
        echo "❌ <strong>$ext:</strong> Not loaded<br>";
    }
}

// Check file permissions
echo "<h3>3. Checking File Permissions...</h3>";
$upload_dir = 'uploads';
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "✅ <strong>Upload directory created successfully!</strong><br>";
    } else {
        echo "❌ <strong>Failed to create upload directory</strong><br>";
    }
} else {
    echo "✅ <strong>Upload directory exists</strong><br>";
}

if (is_writable($upload_dir)) {
    echo "✅ <strong>Upload directory is writable</strong><br>";
} else {
    echo "❌ <strong>Upload directory is not writable</strong><br>";
}

// Check Composer/PHPMailer
echo "<h3>4. Checking PHPMailer...</h3>";
if (file_exists('vendor/autoload.php')) {
    echo "✅ <strong>Composer autoload found</strong><br>";
    require_once 'vendor/autoload.php';
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✅ <strong>PHPMailer class loaded successfully!</strong><br>";
    } else {
        echo "❌ <strong>PHPMailer class not found</strong><br>";
    }
} else {
    echo "❌ <strong>Composer autoload not found</strong><br>";
    echo "<strong>Solution:</strong> Run 'composer install' in the project directory<br>";
}

echo "<h3>Setup Summary</h3>";
echo "If all checks show ✅, your system is ready!<br>";
echo "If you see ❌, please fix the issues before proceeding.<br><br>";
echo "<a href='login.php'>Go to Login Page</a>";
?>