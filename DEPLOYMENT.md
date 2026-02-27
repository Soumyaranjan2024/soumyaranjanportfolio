# Deployment Guide for Soumya Portfolio

## 1. Deploying to GitHub (Source Code Hosting)

1.  **Initialize Git**:
    ```bash
    git init
    ```
2.  **Add Files**:
    ```bash
    git add .
    ```
3.  **Commit**:
    ```bash
    git commit -m "Initial commit - Portfolio ready for deployment"
    ```
4.  **Create Repository on GitHub** and push:
    ```bash
    git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
    git push -u origin main
    ```

## 2. Deploying to InfinityFree (PHP & MySQL Hosting)

### Prerequisites
- Create an account on [InfinityFree](https://infinityfree.net/).
- Create a new hosting account and a MySQL database.

### Step-by-Step Deployment

1.  **Upload Files**:
    - Use an FTP client (like FileZilla) or the online File Manager.
    - Upload all project files to the `htdocs/` folder.
    - **Note**: Do NOT upload the `sessions/` or `portfolio.sqlite` files as InfinityFree uses MySQL.

2.  **Configure Database**:
    - Go to **MySQL Databases** in your InfinityFree control panel.
    - Create a database named `portfolio_db` (or as provided by InfinityFree).
    - Note down your DB Host, DB Name, DB User, and DB Password.
    - Update `database.php` with these credentials:
      ```php
      $host = 'your_infinityfree_db_host';
      $dbname = 'your_infinityfree_db_name';
      $username = 'your_infinityfree_db_user';
      $password = 'your_infinityfree_db_password';
      ```

3.  **Run Migrations**:
    - Import your SQL schema using phpMyAdmin on InfinityFree.
    - You can export the schema from your local environment or use the `database.php` logic to auto-create tables on the first visit.

4.  **Email Configuration**:
    - Ensure `config/email_config.php` has your correct Gmail App Password.
    - InfinityFree might have SMTP restrictions on free accounts; if Gmail SMTP fails, consider using a service like SendGrid or Mailjet.

5.  **Sessions**:
    - InfinityFree handles sessions automatically, but if you see session errors, the local `sessions/` folder we configured will work as a fallback.

## 3. Security Notes
- Ensure `database.php` is not accessible to the public (InfinityFree's `htdocs` structure handles this).
- Change your admin password immediately after deployment.

## 4. API & Integration Documentation

### Email Service (SMTP)
- **Provider**: Gmail (Recommended) or any SMTP-compliant provider.
- **API/Endpoint**: `smtp.gmail.com`
- **Port**: 587 (TLS)
- **Configuration File**: `config/email_config.php`
- **Required Credentials**: 
  - `SMTP_USERNAME`: Your email address
  - `SMTP_PASSWORD`: App-specific password (not your main password)
- **Features**: Automated contact form confirmation, bulk email campaigns.

### Image Management
- **Type**: Local File System (Production Optimized)
- **Base Paths**: 
  - Projects: `uploads/projects/`
  - Blog: `uploads/blog/`
- **Handling**: Images are uniquely renamed using `uniqid()` to prevent caching issues and overwrites.

### Contact Form API
- **Endpoint**: `submit_contact.php`
- **Method**: POST
- **Data**: `name`, `email`, `subject`, `message`
- **Response**: JSON (success/message)

### Admin Management APIs
- **Service CRUD**: `admin_operations.php` (Processes additions, edits, and deletions of services).
- **Service Display**: `services.php` (Retrieves and displays services from the database).
- **Blog CRUD**: `admin_operations.php` (Processes blog post management).

### Third-Party Libraries
- **PHPMailer**: Used for all SMTP communications.
- **FontAwesome**: Used for all interactive icons and micro-interactions.
- **TinyMCE**: Used for the rich text editor in the blog admin panel.

