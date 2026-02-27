<?php
require 'auth.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

require_once 'database.php';

// Handle CRUD operations
require 'admin_operations.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Soumya's Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: relative;
        }

        .sidebar-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .sidebar-header h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .sidebar-header p {
            font-size: 14px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin: 0 15px 8px;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #64748b;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 14px;
            position: relative;
            overflow: hidden;
        }

        .sidebar-menu li a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transition: left 0.5s;
        }

        .sidebar-menu li a:hover::before {
            left: 100%;
        }

        .sidebar-menu li a:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            color: #667eea;
            transform: translateX(5px);
        }

        .sidebar-menu li a.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .sidebar-menu li a i {
            margin-right: 12px;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            min-height: 100vh;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 30px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .content-area {
            padding: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 30px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .card h3 {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
            background: white;
            transition: background-color 0.3s ease;
        }

        table tr:hover td {
            background-color: #f8fafc;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        /* Button Styles */
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            border: none;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.5);
        }

        .btn-success {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(81, 207, 102, 0.4);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(81, 207, 102, 0.5);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: rgba(255, 255, 255, 1);
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-card i {
            font-size: 32px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card h3 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }

        /* Mobile Responsive */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .header {
                padding-left: 70px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .content-area {
                padding: 20px;
            }

            .card {
                padding: 20px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(102, 126, 234, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(102, 126, 234, 0.5);
        }
    </style>
</head>

<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="admin-container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-crown"></i> Admin Panel</h2>
                <p>Welcome, <?php echo $_SESSION['username']; ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin.php?section=dashboard"
                        class="<?php echo (!isset($_GET['section']) || $_GET['section'] == 'dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin.php?section=projects"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'projects') ? 'active' : ''; ?>">
                        <i class="fas fa-project-diagram"></i> Projects</a></li>
                <li><a href="admin.php?section=skills"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'skills') ? 'active' : ''; ?>">
                        <i class="fas fa-code"></i> Skills</a></li>
                <li><a href="admin.php?section=journey"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'journey') ? 'active' : ''; ?>">
                        <i class="fas fa-route"></i> Journey</a></li>
                <li><a href="admin.php?section=services"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'services') ? 'active' : ''; ?>">
                        <i class="fas fa-tools"></i> Services</a></li>
                <li><a href="admin.php?section=blog"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'blog') ? 'active' : ''; ?>">
                        <i class="fas fa-newspaper"></i> Blog</a></li>
                <li><a href="admin.php?section=messages"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'messages') ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="admin.php?section=users"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'users') ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin.php?section=bulk_email"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'bulk_email') ? 'active' : ''; ?>">
                        <i class="fas fa-paper-plane"></i> Bulk Email</a></li>
                <li><a href="admin.php?section=sent_emails"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'sent_emails') ? 'active' : ''; ?>">
                        <i class="fas fa-history"></i> Sent Emails</a></li>
                <li><a href="admin.php?section=reports"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'reports') ? 'active' : ''; ?>">
                        <i class="fas fa-file-pdf"></i> PDF Reports</a></li>
                <li><a href="admin.php?section=excel_import"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'excel_import') ? 'active' : ''; ?>">
                        <i class="fas fa-file-excel"></i> Excel Import</a></li>
                <li><a href="admin.php?section=imported_data"
                        class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'imported_data') ? 'active' : ''; ?>">
                        <i class="fas fa-database"></i> Imported Data</a></li>
                <li><a href="?logout=1"
                        style="margin-top: 20px; border-top: 1px solid rgba(0,0,0,0.1); padding-top: 20px;">
                        <i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h2><?php echo ucfirst(isset($_GET['section']) ? str_replace('_', ' ', $_GET['section']) : 'Dashboard'); ?>
                </h2>
                <a href="../index.php" target="_blank" class="btn btn-primary">
                    <i class="fas fa-external-link-alt"></i> View Portfolio
                </a>
            </div>

            <div class="content-area">
                <?php
                $section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';

                switch ($section) {
                    case 'dashboard':
                        include 'admin_dashboard.php';
                        break;
                    case 'projects':
                        include 'admin_projects.php';
                        break;
                    case 'skills':
                        include 'admin_skills.php';
                        break;
                    case 'journey':
                        include 'admin_journey.php';
                        break;
                    case 'services':
                        include 'admin_services.php';
                        break;
                    case 'blog':
                        include 'admin_blog.php';
                        break;
                    case 'messages':
                        include 'admin_messages.php';
                        break;
                    case 'users':
                        include 'admin_users.php';
                        break;
                    case 'bulk_email':
                        include 'admin_bulk_email.php';
                        break;
                    case 'sent_emails':
                        include 'admin_sent_email.php';
                        break;
                    case 'reports':
                        include 'admin_reports.php';
                        break;
                    case 'excel_import':
                        include 'admin_excel_import.php';
                        break;
                    case 'imported_data':
                        include 'admin_imported_data.php';
                        break;
                    default:
                        include 'admin_dashboard.php';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Add smooth scrolling to sidebar links
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    document.getElementById('sidebar').classList.remove('active');
                }
            });
        });

        // Add loading animation to buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function () {
                if (this.type === 'submit' || this.href) {
                    this.style.opacity = '0.7';
                    this.style.pointerEvents = 'none';

                    setTimeout(() => {
                        this.style.opacity = '1';
                        this.style.pointerEvents = 'auto';
                    }, 2000);
                }
            });
        });
    </script>
</body>

</html>