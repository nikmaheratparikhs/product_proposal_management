<?php
/**
 * Helper Functions
 * Utility functions used throughout the application
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Start session if not already started
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'index.php?action=login');
        exit;
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'index.php?action=dashboard');
        exit;
    }
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    startSession();
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    startSession();
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Set flash message
 * @param string $message
 * @param string $type
 */
function setFlashMessage($message, $type = FLASH_SUCCESS) {
    startSession();
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlashMessage() {
    startSession();
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? FLASH_SUCCESS
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }
    return null;
}

/**
 * Escape HTML output
 * @param string $string
 * @return string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 * @param float $amount
 * @param int $decimals
 * @return string
 */
function formatCurrency($amount, $decimals = 2) {
    return number_format($amount, $decimals, '.', ',');
}

/**
 * Format percentage
 * @param float $value
 * @param int $decimals
 * @return string
 */
function formatPercentage($value, $decimals = 2) {
    return number_format($value, $decimals, '.', '') . '%';
}

/**
 * Calculate pricing for a product
 * @param float $unitPrice
 * @param float $dutyPercentage
 * @param float $shippingPercentage
 * @param float $boxPrice
 * @param float $marginPercentage
 * @return array
 */
function calculatePricing($unitPrice, $dutyPercentage = 30, $shippingPercentage = 5, $boxPrice = 1, $marginPercentage = 35) {
    // A = Unit Price
    $A = $unitPrice;
    
    // B = Duty = A × duty_percentage
    $B = $A * ($dutyPercentage / 100);
    
    // C = Shipping = (A + B) × shipping_percentage
    $C = ($A + $B) * ($shippingPercentage / 100);
    
    // D = Box Price
    $D = $boxPrice;
    
    // E = Landing Cost = A + B + C + D
    $E = $A + $B + $C + $D;
    
    // F = Margin percentage
    $F = $marginPercentage / 100;
    
    // G = Final Price = MROUND(E / (1 - F), 1)
    // MROUND rounds to nearest 1
    $G = round($E / (1 - $F));
    
    return [
        'unit_price' => $A,
        'duty' => $B,
        'shipping_cost' => $C,
        'box_price' => $D,
        'landing_cost' => $E,
        'margin_percentage' => $marginPercentage,
        'final_price' => $G
    ];
}

/**
 * Get default settings
 * @return array|null
 */
function getDefaultSettings() {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Generate pagination HTML
 * @param int $currentPage
 * @param int $totalPages
 * @param string $baseUrl
 * @return string
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $prevPage = $currentPage - 1;
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $prevPage . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=1">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $nextPage = $currentPage + 1;
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $nextPage . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Sanitize input
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Redirect to URL
 * @param string $url
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Hash password using MD5
 * @param string $password
 * @return string MD5 hash
 */
function hashPassword($password) {
    return md5($password);
}

/**
 * Verify password using MD5
 * @param string $password Plain text password
 * @param string $hash MD5 hash from database
 * @return bool
 */
function verifyPassword($password, $hash) {
    return md5($password) === $hash;
}

/**
 * Get result from prepared statement (compatible with both mysqlnd and libmysqlclient)
 * @param mysqli_stmt $stmt
 * @return mysqli_result|false
 */
function getStmtResult($stmt) {
    // Check if get_result() method is available (requires mysqlnd)
    if (method_exists($stmt, 'get_result')) {
        return $stmt->get_result();
    }
    
    // Fallback: use store_result() and return the statement itself
    // Note: This requires manual fetching with bind_result()
    $stmt->store_result();
    return $stmt;
}

/**
 * Fetch all results from a mysqli_result or mysqli_stmt
 * @param mysqli_result|mysqli_stmt $result
 * @return array
 */
function fetchAllResults($result) {
    $rows = [];
    
    if ($result instanceof mysqli_result) {
        // Standard mysqli_result
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    } elseif ($result instanceof mysqli_stmt) {
        // Prepared statement without mysqlnd - need to use bind_result
        // This is a simplified version - for complex queries, use direct query
        $meta = $result->result_metadata();
        if ($meta) {
            $fields = [];
            $fieldVars = [];
            while ($field = $meta->fetch_field()) {
                $fields[] = $field->name;
                $fieldVars[] = &$row[$field->name];
            }
            call_user_func_array([$result, 'bind_result'], $fieldVars);
            while ($result->fetch()) {
                $rows[] = array_combine($fields, $fieldVars);
            }
            $meta->close();
        }
    }
    
    return $rows;
}

