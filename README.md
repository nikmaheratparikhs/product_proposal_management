# Product Proposal Management Portal

A complete PHP-based web application for managing product proposals, pricing, and exports for events, casino promotions, and giveaways.

## Features

- ✅ User Authentication (Admin & Viewer roles)
- ✅ Product Database Management with CRUD operations
- ✅ Excel Import using PhpSpreadsheet
- ✅ Automatic Pricing Calculations
- ✅ Proposal Builder with Cart System
- ✅ Export to Excel, Word, and PowerPoint
- ✅ Settings Management
- ✅ Responsive Bootstrap 5 UI
- ✅ CSRF Protection
- ✅ SQL Injection Prevention (Prepared Statements)

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- Composer (for dependency management)

## Installation

### 1. Clone or Download the Project

```bash
cd C:\xampp\htdocs\product_proposal_management
```

### 2. Install Dependencies

```bash
composer install
```

This will install:
- `phpoffice/phpspreadsheet` - For Excel import/export
- `phpoffice/phpword` - For Word export
- `phpoffice/phppresentation` - For PowerPoint export

### 3. Database Setup

1. Open phpMyAdmin or MySQL command line
2. Import the database schema:

```bash
mysql -u root -p < database_schema.sql
```

Or manually:
- Create database: `product_proposal_db`
- Import `database_schema.sql`

### 4. Configure Database Connection

Edit `config/db.php` and update database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'product_proposal_db');
```

### 5. Configure Base URL

Edit `config/constants.php` and update `BASE_URL`:

```php
define('BASE_URL', 'http://localhost/product_proposal_management/public/');
```

### 6. Set Permissions

Ensure the uploads directory is writable:

```bash
chmod 755 public/assets/uploads/logo
```

On Windows, ensure the folder has write permissions.

### 7. Access the Application

Open your browser and navigate to:

```
http://localhost/product_proposal_management/public/
```

## Default Login Credentials

- **Username:** `admin`
- **Password:** `admin123`

**Important:** Change the default password after first login!

## Project Structure

```
product_proposal_management/
├── config/
│   ├── db.php              # Database configuration
│   └── constants.php       # Application constants
├── controllers/
│   ├── AuthController.php
│   ├── ProductController.php
│   ├── ImportController.php
│   ├── ProposalController.php
│   ├── SettingsController.php
│   └── ExportController.php
├── views/
│   ├── auth/
│   │   └── login.php
│   ├── products/
│   │   ├── index.php
│   │   ├── form.php
│   │   ├── show.php
│   │   └── import.php
│   ├── proposals/
│   │   ├── builder.php
│   │   ├── index.php
│   │   └── view.php
│   ├── settings/
│   │   └── index.php
│   ├── layout/
│   │   ├── header.php
│   │   └── footer.php
│   └── dashboard.php
├── includes/
│   └── helpers.php         # Helper functions
├── public/
│   ├── index.php           # Main entry point
│   └── assets/
│       ├── css/
│       │   └── style.css
│       ├── js/
│       │   └── main.js
│       └── uploads/
│           └── logo/
├── vendor/                 # Composer dependencies
├── database_schema.sql     # Database schema
├── composer.json
└── README.md
```

## Pricing Formula

The application automatically calculates pricing using the following formula:

1. **A** = Unit Price
2. **B** = Duty = A × 0.30 (default, configurable)
3. **C** = Shipping = (A + B) × 0.05 (default, configurable)
4. **D** = Box Price (default: 1.00, configurable)
5. **E** = Landing Cost = A + B + C + D
6. **F** = Margin Percentage (default: 35%, configurable)
7. **G** = Final Price = MROUND(E / (1 - F), 1)

Where MROUND rounds to the nearest 1.

## Excel Import Format

The Excel import expects the following column order:

1. Sr No
2. Image
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

**Note:** If SKU already exists, the product will be updated. Empty SKUs will be skipped.

## User Roles

### Admin
- Full access to all features
- Add/Edit/Delete products
- Import Excel files
- Manage settings
- View all proposals

### Viewer
- View products
- Build proposals
- Export proposals
- View own proposals only

## Export Formats

Proposals can be exported in three formats:

1. **Excel (.xlsx)** - Full spreadsheet with formatting
2. **Word (.docx)** - Professional document format
3. **PowerPoint (.pptx)** - Presentation format

All exports include:
- Company logo (if configured)
- Event name
- Customer name
- Date
- Product table with pricing
- Notes section

## Security Features

- ✅ CSRF token protection on all forms
- ✅ SQL injection prevention using prepared statements
- ✅ Password hashing using PHP's `password_hash()`
- ✅ Session-based authentication
- ✅ Input sanitization
- ✅ XSS protection with `htmlspecialchars()`

## Troubleshooting

### Composer Install Issues

If you encounter issues with Composer:

```bash
composer update --no-scripts
```

### Database Connection Error

1. Check database credentials in `config/db.php`
2. Ensure MySQL/MariaDB is running
3. Verify database exists: `product_proposal_db`

### File Upload Issues

1. Check `public/assets/uploads/logo/` directory permissions
2. Verify `upload_max_filesize` in `php.ini`
3. Check `post_max_size` in `php.ini`

### Export Not Working

1. Ensure all Composer dependencies are installed
2. Check PHP memory limit (should be at least 128M)
3. Verify write permissions

## Development

### Adding New Features

1. Create controller in `controllers/`
2. Add route in `public/index.php`
3. Create view in `views/`
4. Update navigation in `views/layout/header.php`

### Code Style

- Use prepared statements for all database queries
- Sanitize all user input
- Use helper functions from `includes/helpers.php`
- Follow existing code structure

## License

This project is proprietary software. All rights reserved.

## Support

For issues or questions, please contact the development team.

---

**Version:** 1.0.0  
**Last Updated:** 2025

