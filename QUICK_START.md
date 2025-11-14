# Quick Start Guide

Get your Product Proposal Management Portal up and running in 5 minutes!

## Prerequisites

- XAMPP/WAMP/LAMP installed
- PHP 8.0+
- MySQL/MariaDB
- Composer installed

## Installation (5 Steps)

### 1. Install Dependencies (1 minute)

```bash
cd C:\xampp\htdocs\product_proposal_management
composer install
```

### 2. Create Database (1 minute)

**Option A: Using phpMyAdmin**
1. Go to `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Name it: `product_proposal_db`
4. Click "Import"
5. Select `database_schema.sql`
6. Click "Go"

**Option B: Using Command Line**
```bash
mysql -u root -p < database_schema.sql
```

### 3. Configure Database (30 seconds)

Edit `config/db.php`:
```php
define('DB_USER', 'root');     // Your MySQL username
define('DB_PASS', '');         // Your MySQL password
```

### 4. Configure URL (30 seconds)

Edit `config/constants.php`:
```php
define('BASE_URL', 'http://localhost/product_proposal_management/public/');
```

### 5. Set Permissions (30 seconds)

**Windows:**
- Right-click `public/assets/uploads/logo/`
- Properties → Security → Edit
- Give "Write" permission

**Linux/Mac:**
```bash
chmod 755 public/assets/uploads/logo/
```

## Access the Application

Open browser: `http://localhost/product_proposal_management/public/`

## Login

- **Username:** `admin`
- **Password:** `admin123`

## First Steps After Login

1. **Change Password** (Important!)
   - Go to Settings (if available) or update directly in database

2. **Configure Settings**
   - Go to Settings
   - Set default margins, duty, shipping percentages
   - Upload company logo
   - Set default event name

3. **Import Products**
   - Go to "Import Excel"
   - Upload your Excel file with products
   - Products will be automatically imported with pricing calculated

4. **Create Your First Proposal**
   - Go to "Products"
   - Click "Add to Proposal" on products you want
   - Go to "Build Proposal"
   - Add event name, customer name, notes
   - Click "Save Proposal"

5. **Export Proposal**
   - Go to "Proposals"
   - Click on a proposal
   - Click "Export" → Choose format (Excel/Word/PowerPoint)

## Common Issues

### "Composer not found"
- Install Composer: https://getcomposer.org/download/
- Or use: `php composer.phar install`

### "Database connection failed"
- Check if MySQL is running
- Verify credentials in `config/db.php`
- Ensure database exists

### "Class not found"
```bash
composer dump-autoload
```

### "Permission denied" on uploads
- Check folder permissions
- Ensure web server can write to `public/assets/uploads/logo/`

## Need Help?

- Check `README.md` for detailed documentation
- Check `INSTALLATION.md` for troubleshooting
- Check `PROJECT_SUMMARY.md` for feature overview

## Excel Import Format

Your Excel file should have these columns in order:
1. Sr No
2. Image (URL)
3. SKU
4. Description
5. Final Cost
6. Proposal Margin
7. Price
8. Selection
9. Product Link
10. Unit Price
11. 30% Duty
12. Shipping Cost 5%
13. Box Price
14. US Landing Cost
15. Margin
16. Final Price

**Note:** SKU is required. If SKU exists, product will be updated.

---

**That's it! You're ready to go! 🚀**

