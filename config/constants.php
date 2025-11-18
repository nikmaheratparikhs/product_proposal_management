<?php
/**
 * Application Constants
 */

// Application settings
define('APP_NAME', 'Product Proposal Management Portal');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'https://polyvalent-rosalie-unflavoured.ngrok-free.app/product_proposal_management/public/');

// Define BASE_PATH only if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour

// File upload settings
define('UPLOAD_DIR', BASE_PATH . '/public/assets/uploads/logo/');
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Pagination
define('ITEMS_PER_PAGE', 20);

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_VIEWER', 'viewer');

// CSRF token name
define('CSRF_TOKEN_NAME', 'csrf_token');

// Flash message types
define('FLASH_SUCCESS', 'success');
define('FLASH_ERROR', 'danger');
define('FLASH_WARNING', 'warning');
define('FLASH_INFO', 'info');

