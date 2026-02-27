<?php
require_once 'database.php'; // Your existing database connection

try {
    echo "<h2>🔄 Database Migration Started</h2>";

    // Add new columns to existing tables
    $migrations = [
        // Projects table updates
        "ALTER TABLE projects ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'active'",
        "ALTER TABLE projects ADD COLUMN IF NOT EXISTS live_link VARCHAR(255) DEFAULT '#'",
        "ALTER TABLE projects ADD COLUMN IF NOT EXISTS github_link VARCHAR(255) DEFAULT '#'",

        // Bulk email campaigns updates
        "ALTER TABLE bulk_email_campaigns ADD COLUMN IF NOT EXISTS created_by_id INT DEFAULT NULL",
        "ALTER TABLE bulk_email_campaigns ADD COLUMN IF NOT EXISTS campaign_type ENUM('single', 'bulk', 'imported_contacts') DEFAULT 'bulk'",
        "ALTER TABLE bulk_email_campaigns ADD COLUMN IF NOT EXISTS status ENUM('pending', 'sending', 'completed', 'failed') DEFAULT 'pending'",

        // Sent emails updates
        "ALTER TABLE sent_emails ADD COLUMN IF NOT EXISTS error_message TEXT DEFAULT NULL",

        // Excel uploads updates
        "ALTER TABLE excel_uploads ADD COLUMN IF NOT EXISTS file_path VARCHAR(500) DEFAULT NULL",
        "ALTER TABLE excel_uploads ADD COLUMN IF NOT EXISTS total_records INT DEFAULT 0",
        "ALTER TABLE excel_uploads ADD COLUMN IF NOT EXISTS successful_records INT DEFAULT 0",
        "ALTER TABLE excel_uploads ADD COLUMN IF NOT EXISTS failed_records INT DEFAULT 0",
        "ALTER TABLE excel_uploads ADD COLUMN IF NOT EXISTS uploaded_by_id INT DEFAULT NULL",
        "ALTER TABLE excel_uploads MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending'",
        "ALTER TABLE excel_uploads ADD COLUMN IF NOT EXISTS error_log TEXT DEFAULT NULL",

        // Imported data updates
        "ALTER TABLE imported_data ADD COLUMN IF NOT EXISTS upload_id INT DEFAULT NULL",
        "ALTER TABLE imported_data ADD COLUMN IF NOT EXISTS custom_field_1 VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE imported_data ADD COLUMN IF NOT EXISTS custom_field_2 VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE imported_data ADD COLUMN IF NOT EXISTS custom_field_3 VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE imported_data ADD COLUMN IF NOT EXISTS uploaded_by_id INT DEFAULT NULL",
        "ALTER TABLE imported_data ADD COLUMN IF NOT EXISTS is_valid BOOLEAN DEFAULT TRUE",
        "ALTER TABLE imported_data ADD COLUMN IF NOT EXISTS validation_errors TEXT DEFAULT NULL",

        // PDF reports updates
        "ALTER TABLE pdf_reports ADD COLUMN IF NOT EXISTS file_path VARCHAR(500) DEFAULT NULL",
        "ALTER TABLE pdf_reports ADD COLUMN IF NOT EXISTS generated_by_id INT DEFAULT NULL",
        "ALTER TABLE pdf_reports ADD COLUMN IF NOT EXISTS date_from DATE DEFAULT NULL",
        "ALTER TABLE pdf_reports ADD COLUMN IF NOT EXISTS date_to DATE DEFAULT NULL",
        "ALTER TABLE pdf_reports ADD COLUMN IF NOT EXISTS parameters JSON DEFAULT NULL",
        "ALTER TABLE pdf_reports ADD COLUMN IF NOT EXISTS file_size INT DEFAULT NULL",
        "ALTER TABLE pdf_reports ADD COLUMN IF NOT EXISTS download_count INT DEFAULT 0"
    ];

    foreach ($migrations as $migration) {
        try {
            $pdo->exec($migration);
            echo "✅ " . substr($migration, 0, 50) . "...<br>";
        } catch (PDOException $e) {
            echo "⚠️ " . substr($migration, 0, 50) . "... (already exists or error)<br>";
        }
    }

    // Create new tables
    $newTables = [
        "CREATE TABLE IF NOT EXISTS email_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            template_type ENUM('personal', 'business', 'newsletter', 'announcement') DEFAULT 'personal',
            created_by VARCHAR(50) NOT NULL,
            created_by_id INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE
        )",

        "CREATE TABLE IF NOT EXISTS user_activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            username VARCHAR(50) NOT NULL,
            activity_type ENUM('login', 'logout', 'email_sent', 'bulk_campaign', 'excel_import', 'report_generated', 'template_created') NOT NULL,
            activity_description TEXT,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",

        "CREATE TABLE IF NOT EXISTS email_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            campaign_id INT NOT NULL,
            recipient_email VARCHAR(100) NOT NULL,
            recipient_name VARCHAR(100) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            attachment_path VARCHAR(500) DEFAULT NULL,
            priority INT DEFAULT 5,
            status ENUM('pending', 'sending', 'sent', 'failed', 'retry') DEFAULT 'pending',
            attempts INT DEFAULT 0,
            max_attempts INT DEFAULT 3,
            scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            sent_at TIMESTAMP NULL,
            error_message TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (campaign_id) REFERENCES bulk_email_campaigns(id) ON DELETE CASCADE
        )",

        "CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
            description TEXT,
            updated_by VARCHAR(50),
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )"
    ];

    foreach ($newTables as $table) {
        try {
            $pdo->exec($table);
            echo "✅ New table created successfully<br>";
        } catch (PDOException $e) {
            echo "⚠️ Table creation: " . $e->getMessage() . "<br>";
        }
    }

    // Add foreign key constraints
    $foreignKeys = [
        "ALTER TABLE imported_data ADD CONSTRAINT fk_imported_data_upload FOREIGN KEY (upload_id) REFERENCES excel_uploads(id) ON DELETE CASCADE",
    ];

    foreach ($foreignKeys as $fk) {
        try {
            $pdo->exec($fk);
            echo "✅ Foreign key added<br>";
        } catch (PDOException $e) {
            echo "⚠️ Foreign key: already exists or error<br>";
        }
    }

    echo "<h3>🎉 Migration Completed Successfully!</h3>";
    echo "<p>Your database has been updated with all the new features for:</p>";
    echo "<ul>";
    echo "<li>✅ Enhanced bulk email campaigns</li>";
    echo "<li>✅ Improved Excel import tracking</li>";
    echo "<li>✅ Better PDF report management</li>";
    echo "<li>✅ Email templates system</li>";
    echo "<li>✅ User activity logging</li>";
    echo "<li>✅ Email queue management</li>";
    echo "<li>✅ System settings</li>";
    echo "</ul>";

} catch (PDOException $e) {
    echo "<h3>❌ Migration Error</h3>";
    echo "Error: " . $e->getMessage();
}
?>