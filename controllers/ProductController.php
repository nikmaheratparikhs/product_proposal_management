<?php
/**
 * Product Controller
 * Handles product CRUD operations
 */

require_once __DIR__ . '/../includes/helpers.php';

class ProductController {
    
    /**
     * List all products with filters and pagination
     */
    public function index() {
        requireLogin();
        
        $conn = getDBConnection();
        
        // Get filters
        $searchSku = sanitizeInput($_GET['search_sku'] ?? '');
        $searchDescription = sanitizeInput($_GET['search_description'] ?? '');
        $categoryId = intval($_GET['category_id'] ?? 0);
        $marginMin = floatval($_GET['margin_min'] ?? 0);
        $marginMax = floatval($_GET['margin_max'] ?? 0);
        $landingCostMin = floatval($_GET['landing_cost_min'] ?? 0);
        $landingCostMax = floatval($_GET['landing_cost_max'] ?? 0);
        $finalPriceMin = floatval($_GET['final_price_min'] ?? 0);
        $finalPriceMax = floatval($_GET['final_price_max'] ?? 0);
        $sortBy = sanitizeInput($_GET['sort_by'] ?? 'id');
        $sortOrder = sanitizeInput($_GET['sort_order'] ?? 'DESC');
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        // Validate sort fields
        $allowedSorts = ['id', 'sku', 'description', 'unit_price', 'duty', 'shipping_cost', 'box_price', 'landing_cost', 'margin_percentage', 'final_price', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // Build WHERE clause with actual values (properly escaped)
        $whereConditions = [];
        if (!empty($searchSku)) {
            $whereConditions[] = "p.sku LIKE '%" . $conn->real_escape_string($searchSku) . "%'";
        }
        if (!empty($searchDescription)) {
            $whereConditions[] = "p.description LIKE '%" . $conn->real_escape_string($searchDescription) . "%'";
        }
        if ($categoryId > 0) {
            $whereConditions[] = "p.category_id = " . intval($categoryId);
        }
        if ($marginMin > 0) {
            $whereConditions[] = "p.margin_percentage >= " . floatval($marginMin);
        }
        if ($marginMax > 0) {
            $whereConditions[] = "p.margin_percentage <= " . floatval($marginMax);
        }
        if ($landingCostMin > 0) {
            $whereConditions[] = "p.landing_cost >= " . floatval($landingCostMin);
        }
        if ($landingCostMax > 0) {
            $whereConditions[] = "p.landing_cost <= " . floatval($landingCostMax);
        }
        if ($finalPriceMin > 0) {
            $whereConditions[] = "p.final_price >= " . floatval($finalPriceMin);
        }
        if ($finalPriceMax > 0) {
            $whereConditions[] = "p.final_price <= " . floatval($finalPriceMax);
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM products p $whereClause";
        $countResult = $conn->query($countSql);
        if ($countResult) {
            $totalItems = $countResult->fetch_assoc()['total'];
            $countResult->close();
        } else {
            error_log("Count SQL Error: " . $conn->error . " | Query: " . $countSql);
            $totalItems = 0;
        }
        $totalPages = ceil($totalItems / $limit);
        
        // Get products
        // Escape sortBy to prevent SQL injection (already validated, but extra safety)
        $sortByEscaped = $conn->real_escape_string($sortBy);
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                $whereClause 
                ORDER BY p.`$sortByEscaped` $sortOrder 
                LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        
        $result = $conn->query($sql);
        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            $result->close();
        } else {
            // Log error for debugging
            error_log("SQL Error: " . $conn->error . " | Query: " . $sql);
        }
        
        // Get categories for filter
        $categoriesResult = $conn->query("SELECT id, name FROM categories ORDER BY name");
        $categories = [];
        if ($categoriesResult) {
            while ($row = $categoriesResult->fetch_assoc()) {
                $categories[] = $row;
            }
            $categoriesResult->close();
        }
        
        include __DIR__ . '/../views/products/index.php';
    }
    
    /**
     * Show product details
     */
    public function show() {
        requireLogin();
        
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            setFlashMessage('Invalid product ID.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=products');
        }
        
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            setFlashMessage('Product not found.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=products');
        }
        
        $product = $result->fetch_assoc();
        $stmt->close();
        
        include __DIR__ . '/../views/products/show.php';
    }
    
    /**
     * Show create/edit form
     */
    public function form() {
        requireAdmin();
        
        $id = intval($_GET['id'] ?? 0);
        $product = null;
        
        if ($id > 0) {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $product = $result->fetch_assoc();
            }
            $stmt->close();
        }
        
        // Get categories
        $conn = getDBConnection();
        $categoriesStmt = $conn->query("SELECT id, name FROM categories ORDER BY name");
        $categories = $categoriesStmt->fetch_all(MYSQLI_ASSOC);
        $categoriesStmt->close();
        
        // Get default settings
        $settings = getDefaultSettings();
        
        include __DIR__ . '/../views/products/form.php';
    }
    
    /**
     * Save product (create or update)
     */
    public function save() {
        requireAdmin();
        startSession();
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('Invalid security token. Please try again.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=products');
        }
        
        $id = intval($_POST['id'] ?? 0);
        $sku = sanitizeInput($_POST['sku'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $imageUrl = sanitizeInput($_POST['image_url'] ?? '');
        $productLink = sanitizeInput($_POST['product_link'] ?? '');
        $categoryId = intval($_POST['category_id'] ?? 0);
        $categoryId = $categoryId > 0 ? $categoryId : null;
        
        // Get settings for defaults
        $settings = getDefaultSettings();
        $unitPrice = floatval($_POST['unit_price'] ?? 0);
        $dutyPercentage = floatval($_POST['duty_percentage'] ?? $settings['default_duty_percentage']);
        $shippingPercentage = floatval($_POST['shipping_percentage'] ?? $settings['default_shipping_percentage']);
        $boxPrice = floatval($_POST['box_price'] ?? $settings['default_box_price']);
        $marginPercentage = floatval($_POST['margin_percentage'] ?? $settings['default_margin_percentage']);
        
        if (empty($sku)) {
            setFlashMessage('SKU is required.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=product_form&id=' . $id);
        }
        
        // Calculate pricing
        $pricing = calculatePricing($unitPrice, $dutyPercentage, $shippingPercentage, $boxPrice, $marginPercentage);
        
        $conn = getDBConnection();
        
        if ($id > 0) {
            // Update existing product
            $stmt = $conn->prepare("UPDATE products SET 
                category_id = ?, sku = ?, description = ?, image_url = ?, product_link = ?,
                unit_price = ?, duty = ?, shipping_cost = ?, box_price = ?,
                landing_cost = ?, margin_percentage = ?, final_price = ?
                WHERE id = ?");
            
            $stmt->bind_param("issssdddddddi",
                $categoryId, $sku, $description, $imageUrl, $productLink,
                $pricing['unit_price'], $pricing['duty'], $pricing['shipping_cost'], $pricing['box_price'],
                $pricing['landing_cost'], $pricing['margin_percentage'], $pricing['final_price'],
                $id
            );
        } else {
            // Insert new product
            $stmt = $conn->prepare("INSERT INTO products 
                (category_id, sku, description, image_url, product_link,
                unit_price, duty, shipping_cost, box_price,
                landing_cost, margin_percentage, final_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("issssddddddd",
                $categoryId, $sku, $description, $imageUrl, $productLink,
                $pricing['unit_price'], $pricing['duty'], $pricing['shipping_cost'], $pricing['box_price'],
                $pricing['landing_cost'], $pricing['margin_percentage'], $pricing['final_price']
            );
        }
        
        if ($stmt->execute()) {
            setFlashMessage($id > 0 ? 'Product updated successfully.' : 'Product created successfully.', FLASH_SUCCESS);
            redirect(BASE_URL . 'index.php?action=products');
        } else {
            setFlashMessage('Error saving product: ' . $conn->error, FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=product_form&id=' . $id);
        }
        
        $stmt->close();
    }
    
    /**
     * Delete product
     */
    public function delete() {
        requireAdmin();
        startSession();
        
        // Verify CSRF token
        if (!isset($_GET['csrf_token']) || !verifyCSRFToken($_GET['csrf_token'])) {
            setFlashMessage('Invalid security token. Please try again.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=products');
        }
        
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            setFlashMessage('Invalid product ID.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=products');
        }
        
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            setFlashMessage('Product deleted successfully.', FLASH_SUCCESS);
        } else {
            setFlashMessage('Error deleting product: ' . $conn->error, FLASH_ERROR);
        }
        
        $stmt->close();
        redirect(BASE_URL . 'index.php?action=products');
    }
}

