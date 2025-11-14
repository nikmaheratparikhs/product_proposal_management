# Installation Guide

## Quick Start

### Step 1: Install Composer Dependencies

Open terminal/command prompt in the project root directory and run:

```bash
composer install
```

This will install:
- PhpSpreadsheet (Excel import/export)
- PHPWord (Word export)
- PHPPresentation (PowerPoint export)

### Step 2: Create Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named: `product_proposal_db`
3. Import the `database_schema.sql` file:
   - Click on the database
   - Go to "Import" tab
   - Choose file: `database_schema.sql`
   - Click "Go"

**OR** use command line:

```bash
mysql -u root -p < database_schema.sql
```

### Step 3: Configure Database

Edit `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Your MySQL username
define('DB_PASS', '');            // Your MySQL password
define('DB_NAME', 'product_proposal_db');
```

### Step 4: Configure Base URL

Edit `config/constants.php`:

```php
define('BASE_URL', 'http://localhost/product_proposal_management/public/');
```

**Important:** Update this to match your actual domain/path.

### Step 5: Set Folder Permissions

**Windows:**
- Right-click on `public/assets/uploads/logo/` folder
- Properties → Security → Edit
- Give "Write" permission to your web server user

**Linux/Mac:**
```bash
chmod 755 public/assets/uploads/logo/
```

### Step 6: Access the Application

Open your browser and go to:

```
http://localhost/product_proposal_management/public/
```

### Step 7: Login

- **Username:** `admin`
- **Password:** `admin123`

**⚠️ IMPORTANT:** Change the password immediately after first login!

## Troubleshooting

### "Composer not found"

Install Composer from: https://getcomposer.org/download/

### "Database connection failed"

1. Check if MySQL/MariaDB is running
2. Verify credentials in `config/db.php`
3. Ensure database `product_proposal_db` exists

### "Class not found" errors

Run:
```bash
composer dump-autoload
```

### "Permission denied" on uploads

Ensure the `public/assets/uploads/logo/` folder is writable by the web server.

### Export not working

1. Check PHP memory limit (should be at least 128M)
2. Verify all Composer packages are installed
3. Check PHP error logs

## Verification Checklist

- [ ] Composer dependencies installed
- [ ] Database created and schema imported
- [ ] Database credentials configured
- [ ] Base URL configured
- [ ] Upload folder has write permissions
- [ ] Can access login page
- [ ] Can login with admin/admin123
- [ ] Can view dashboard

## Next Steps

1. Change admin password
2. Configure settings (default margins, logo, etc.)
3. Import products via Excel
4. Create your first proposal

## Support

If you encounter any issues, check:
1. PHP error logs
2. Apache/Nginx error logs
3. Browser console for JavaScript errors
4. Database connection status

