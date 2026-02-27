# 🚀 Deployment Guide: Soumya's Portfolio

This document provides step-by-step instructions for deploying your portfolio to **GitHub** (for version control) and **InfinityFree** (for live hosting).

---

## **1. GitHub Deployment (Version Control)**

GitHub is used to store your code and track changes.

### **Steps:**
1. **Initialize Git**:
   ```bash
   git init
   ```
2. **Add Files**:
   ```bash
   git add .
   ```
3. **Commit**:
   ```bash
   git commit -m "Initial commit: Professional Portfolio with Admin Panel"
   ```
4. **Create Repository**:
   - Go to [GitHub](https://github.com/) and create a new repository named `my-portfolio`.
5. **Push Code**:
   ```bash
   git remote add origin https://github.com/YOUR_USERNAME/my-portfolio.git
   git branch -M main
   git push -u origin main
   ```

---

## **2. InfinityFree Deployment (Live Hosting)**

InfinityFree provides free PHP & MySQL hosting.

### **Phase A: Preparation**
1. **Zip Your Project**: Compress all files in your project folder (except `vendor/` if you use Composer, though it's recommended to include it for easy deployment).
2. **Database Export**:
   - If using MySQL locally, export your `portfolio_db` to a `.sql` file.
   - If using SQLite, the `portfolio.sqlite` file is already in your project.

### **Phase B: Hosting Setup**
1. **Create Account**: Sign up at [InfinityFree](https://www.infinityfree.com/).
2. **Create Hosting Account**: Choose a subdomain (e.g., `soumyaportfolio.infy.uk`).
3. **Control Panel**: Enter the **Control Panel (vPanel)**.

### **Phase C: Database (MySQL)**
1. **Create Database**: Go to **MySQL Databases** in vPanel.
2. **Create a new database** (e.g., `epiz_XXX_portfolio`).
3. **Import Data**: Open **phpMyAdmin**, select your database, and import your `.sql` file.
4. **Update `database.php`**: Update your `database.php` with the InfinityFree credentials:
   - `host`: Found in MySQL Databases section.
   - `username`: Your hosting account username (e.g., `epiz_XXX`).
   - `password`: Your hosting account password.

### **Phase D: File Upload**
1. **Online File Manager**: Go to **Online File Manager** in vPanel.
2. **Navigate to `htdocs/`**: **IMPORTANT**: All files must go inside the `htdocs` folder.
3. **Upload & Extract**: Upload your `.zip` file and extract it directly into `htdocs`.

---

## **3. Final Verification Checklist**

- [ ] **Links**: Ensure all navigation links (`index.php#projects`, etc.) work.
- [ ] **Admin Login**: Test login at `yourdomain.com/login.php`.
- [ ] **Contact Form**: Send a test message to verify the AJAX handler and email system.
- [ ] **Images**: Check that Unsplash images and uploaded assets display correctly.
- [ ] **Mobile**: Open the site on a phone to check responsiveness.

---

**Need Help?**
Check the `README.md` in your project root for full feature documentation.

*Generated on 2026-02-26*
