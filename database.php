<?php
// --- DATABASE CONFIGURATION ---
// FOR LOCALHOST (XAMPP/MAMP)
$host = 'localhost';
$dbname = 'portfolio_db';
$username = 'root';
$password = '';

// FOR PRODUCTION (INFINITYFREE/HOSTINGER)
// Uncomment and fill these when you deploy:
/*
$host = 'sqlXXX.infinityfree.com';
$dbname = 'epiz_XXX_portfolio_db';
$username = 'epiz_XXX';
$password = 'your_password_here';
*/

try {
    // Try MySQL first
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback to SQLite if MySQL fails
    try {
        $db_file = __DIR__ . "/portfolio.sqlite";
        $pdo = new PDO("sqlite:" . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if projects table has the new columns
        $cols = $pdo->query("PRAGMA table_info(projects)")->fetchAll(PDO::FETCH_ASSOC);
        $has_live = false;
        foreach ($cols as $col) {
            if ($col['name'] == 'live_link') $has_live = true;
        }
        if (!$has_live && count($cols) > 0) {
            $pdo->exec("ALTER TABLE projects ADD COLUMN live_link VARCHAR(255) DEFAULT '#'");
            $pdo->exec("ALTER TABLE projects ADD COLUMN github_link VARCHAR(255) DEFAULT '#'");
        }

        // Adjust SQL for SQLite compatibility
        $schema = "
            CREATE TABLE IF NOT EXISTS projects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                image_url VARCHAR(255),
                category VARCHAR(50) NOT NULL,
                tags TEXT,
                live_link VARCHAR(255) DEFAULT '#',
                github_link VARCHAR(255) DEFAULT '#',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS skills (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category VARCHAR(50) NOT NULL,
                name VARCHAR(100) NOT NULL,
                proficiency INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                subject VARCHAR(255),
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_read BOOLEAN DEFAULT FALSE
            );

            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                is_admin TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME NULL
            );

            CREATE TABLE IF NOT EXISTS remember_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INT NOT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS bulk_email_campaigns (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                recipient_count INT NOT NULL,
                sent_count INT DEFAULT 0,
                failed_count INT DEFAULT 0,
                attachment_name VARCHAR(255) DEFAULT '',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                created_by VARCHAR(50) NOT NULL
            );

            CREATE TABLE IF NOT EXISTS sent_emails (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                campaign_id INT,
                recipient_email VARCHAR(100) NOT NULL,
                recipient_name VARCHAR(100) NOT NULL,
                sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                success BOOLEAN DEFAULT FALSE,
                FOREIGN KEY (campaign_id) REFERENCES bulk_email_campaigns(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS excel_uploads (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                rows_imported INT DEFAULT 0,
                upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                uploaded_by VARCHAR(50) NOT NULL,
                status TEXT CHECK(status IN ('pending', 'completed', 'failed')) DEFAULT 'pending'
            );

            CREATE TABLE IF NOT EXISTS imported_data (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100),
                email VARCHAR(100),
                phone VARCHAR(20),
                company VARCHAR(100),
                position VARCHAR(100),
                notes TEXT,
                import_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                uploaded_by VARCHAR(50)
            );

            CREATE TABLE IF NOT EXISTS pdf_reports (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                report_type VARCHAR(50) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                generated_by VARCHAR(50) NOT NULL,
                generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                record_count INT DEFAULT 0
            );

            CREATE TABLE IF NOT EXISTS blog_posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                content TEXT NOT NULL,
                excerpt TEXT,
                featured_image VARCHAR(255),
                author VARCHAR(100) DEFAULT 'Admin',
                status VARCHAR(20) DEFAULT 'published',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS services (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                icon VARCHAR(100) NOT NULL,
                description TEXT NOT NULL,
                order_num INTEGER DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS journey (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                year_range VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                company VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                position_side VARCHAR(10) DEFAULT 'left',
                order_num INTEGER DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS message_replies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                message_id INTEGER NOT NULL,
                reply_text TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
            );
        ";
        
        // SQLite doesn't support multiple statements in exec() easily in some versions, 
        // but PDO::exec should handle it if separated by ;
        // We'll split it just to be safe.
        foreach (explode(';', $schema) as $stmt_sql) {
            $stmt_sql = trim($stmt_sql);
            if (!empty($stmt_sql)) {
                $pdo->exec($stmt_sql);
            }
        }
        
        // Re-run the admin check and cleanup with $pdo (which is now SQLite)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'soumya'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $hashedPassword = password_hash('soumya123', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (username, password, email, is_admin) VALUES (?, ?, ?, 1)")
                ->execute(['soumya', $hashedPassword, 'admin@example.com']);
        }
        $pdo->exec("DELETE FROM remember_tokens WHERE expires_at < datetime('now')");
        
        // Skip the rest of the original file which is MySQL specific
        goto finish;

    } catch (PDOException $e2) {
        die("Database connection failed (MySQL & SQLite): " . $e2->getMessage());
    }
}

try {
    // Create tables if they don't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            image_url VARCHAR(255),
            category VARCHAR(50) NOT NULL,
            tags TEXT,
            live_link VARCHAR(255),
            github_link VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS skills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category VARCHAR(50) NOT NULL,
            name VARCHAR(100) NOT NULL,
            proficiency INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(255),
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_read BOOLEAN DEFAULT FALSE
        );

        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            is_admin TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME NULL
        );

        CREATE TABLE IF NOT EXISTS remember_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_token (token),
            INDEX idx_expires (expires_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS bulk_email_campaigns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            recipient_count INT NOT NULL,
            sent_count INT DEFAULT 0,
            failed_count INT DEFAULT 0,
            attachment_name VARCHAR(255) DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            created_by VARCHAR(50) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS sent_emails (
            id INT AUTO_INCREMENT PRIMARY KEY,
            campaign_id INT,
            recipient_email VARCHAR(100) NOT NULL,
            recipient_name VARCHAR(100) NOT NULL,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            success BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (campaign_id) REFERENCES bulk_email_campaigns(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS excel_uploads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            rows_imported INT DEFAULT 0,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            uploaded_by VARCHAR(50) NOT NULL,
            status ENUM('pending', 'completed', 'failed') DEFAULT 'pending'
        );

        CREATE TABLE IF NOT EXISTS imported_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            email VARCHAR(100),
            phone VARCHAR(20),
            company VARCHAR(100),
            position VARCHAR(100),
            notes TEXT,
            import_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            uploaded_by VARCHAR(50)
        );

        CREATE TABLE IF NOT EXISTS pdf_reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_type VARCHAR(50) NOT NULL,
            filename VARCHAR(255) NOT NULL,
            generated_by VARCHAR(50) NOT NULL,
            generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            record_count INT DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS blog_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            content LONGTEXT NOT NULL,
            excerpt TEXT,
            featured_image VARCHAR(255),
            author VARCHAR(100) DEFAULT 'Admin',
            status ENUM('draft', 'published') DEFAULT 'published',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_slug (slug),
            INDEX idx_status (status)
        );

        CREATE TABLE IF NOT EXISTS services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            icon VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            order_num INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS journey (
            id INT AUTO_INCREMENT PRIMARY KEY,
            year_range VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            company VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            position_side ENUM('left', 'right') DEFAULT 'left',
            order_num INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS message_replies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message_id INT NOT NULL,
            reply_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
        );
    ");

    // Insert default admin if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'soumya'");
    $stmt->execute();

    if ($stmt->fetchColumn() == 0) {
        $hashedPassword = password_hash('soumya123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password, email, is_admin) VALUES (?, ?, ?, 1)")
            ->execute(['soumya', $hashedPassword, 'admin@example.com']);
    }

    // Clean up expired remember tokens (run periodically)
    $pdo->exec("DELETE FROM remember_tokens WHERE expires_at < NOW()");

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
finish:
?>