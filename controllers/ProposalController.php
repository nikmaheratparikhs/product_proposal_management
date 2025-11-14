<?php
/**
 * Proposal Controller
 * Handles proposal creation, editing, and export
 */

require_once __DIR__ . '/../includes/helpers.php';

class ProposalController {
    
    /**
     * Show proposal builder/cart
     */
    public function builder() {
        requireLogin();
        startSession();
        
        // Get cart items from session
        $cart = $_SESSION['proposal_cart'] ?? [];
        
        // Get product details for cart items
        $conn = getDBConnection();
        $products = [];
        $totalAmount = 0;
        
        foreach ($cart as $item) {
            $productId = intval($item['product_id']);
            $quantity = intval($item['quantity'] ?? 1);
            $customMargin = floatval($item['custom_margin'] ?? null);
            
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $product = $result->fetch_assoc();
            } else {
                $product = null;
            }
            
            if ($product) {
                
                // Use custom margin if provided, otherwise use product margin
                $margin = $customMargin !== null ? $customMargin : $product['margin_percentage'];
                
                // Recalculate final price with custom margin
                $pricing = calculatePricing(
                    $product['unit_price'],
                    ($product['duty'] / $product['unit_price']) * 100,
                    ($product['shipping_cost'] / ($product['unit_price'] + $product['duty'])) * 100,
                    $product['box_price'],
                    $margin
                );
                
                $product['proposal_quantity'] = $quantity;
                $product['proposal_margin'] = $margin;
                $product['proposal_final_price'] = $pricing['final_price'];
                $product['proposal_total'] = $pricing['final_price'] * $quantity;
                
                $products[] = $product;
                $totalAmount += $product['proposal_total'];
            }
            $stmt->close();
        }
        
        // Get default settings for event name
        $settings = getDefaultSettings();
        
        include __DIR__ . '/../views/proposals/builder.php';
    }
    
    /**
     * Add product to cart
     */
    public function addToCart() {
        requireLogin();
        startSession();
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('Invalid security token. Please try again.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=products');
        }
        
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        
        if ($productId <= 0) {
            setFlashMessage('Invalid product ID.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=products');
        }
        
        // Initialize cart if not exists
        if (!isset($_SESSION['proposal_cart'])) {
            $_SESSION['proposal_cart'] = [];
        }
        
        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['proposal_cart'] as &$item) {
            if ($item['product_id'] == $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $_SESSION['proposal_cart'][] = [
                'product_id' => $productId,
                'quantity' => $quantity
            ];
        }
        
        setFlashMessage('Product added to proposal cart.', FLASH_SUCCESS);
        redirect(BASE_URL . 'index.php?action=proposal_builder');
    }
    
    /**
     * Add multiple products to cart
     */
    public function addMultipleToCart() {
        requireLogin();
        startSession();
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('Invalid security token. Please try again.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=products');
        }
        
        $productIds = $_POST['product_ids'] ?? '';
        
        if (empty($productIds)) {
            setFlashMessage('Please select at least one product.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=products');
        }
        
        // Parse product IDs
        $ids = array_filter(array_map('intval', explode(',', $productIds)));
        
        if (empty($ids)) {
            setFlashMessage('Invalid product selection.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=products');
        }
        
        // Initialize cart if not exists
        if (!isset($_SESSION['proposal_cart'])) {
            $_SESSION['proposal_cart'] = [];
        }
        
        $addedCount = 0;
        foreach ($ids as $productId) {
            if ($productId > 0) {
                // Check if product already in cart
                $found = false;
                foreach ($_SESSION['proposal_cart'] as &$item) {
                    if ($item['product_id'] == $productId) {
                        $item['quantity'] += 1;
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $_SESSION['proposal_cart'][] = [
                        'product_id' => $productId,
                        'quantity' => 1
                    ];
                    $addedCount++;
                }
            }
        }
        
        setFlashMessage($addedCount . ' product(s) added to proposal cart.', FLASH_SUCCESS);
        redirect(BASE_URL . 'index.php?action=proposal_builder');
    }
    
    /**
     * Update cart item
     */
    public function updateCart() {
        requireLogin();
        startSession();
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('Invalid security token. Please try again.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposal_builder');
        }
        
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        $customMargin = !empty($_POST['custom_margin']) ? floatval($_POST['custom_margin']) : null;
        
        if (!isset($_SESSION['proposal_cart'])) {
            redirect(BASE_URL . 'index.php?action=proposal_builder');
        }
        
        foreach ($_SESSION['proposal_cart'] as &$item) {
            if ($item['product_id'] == $productId) {
                $item['quantity'] = $quantity;
                if ($customMargin !== null) {
                    $item['custom_margin'] = $customMargin;
                }
                break;
            }
        }
        
        setFlashMessage('Cart updated successfully.', FLASH_SUCCESS);
        redirect(BASE_URL . 'index.php?action=proposal_builder');
    }
    
    /**
     * Remove from cart
     */
    public function removeFromCart() {
        requireLogin();
        startSession();
        
        // Verify CSRF token
        if (!isset($_GET['csrf_token']) || !verifyCSRFToken($_GET['csrf_token'])) {
            setFlashMessage('Invalid security token. Please try again.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposal_builder');
        }
        
        $productId = intval($_GET['product_id'] ?? 0);
        
        if (isset($_SESSION['proposal_cart'])) {
            $_SESSION['proposal_cart'] = array_filter($_SESSION['proposal_cart'], function($item) use ($productId) {
                return $item['product_id'] != $productId;
            });
            $_SESSION['proposal_cart'] = array_values($_SESSION['proposal_cart']); // Re-index
        }
        
        setFlashMessage('Product removed from cart.', FLASH_SUCCESS);
        redirect(BASE_URL . 'index.php?action=proposal_builder');
    }
    
    /**
     * Save proposal
     */
    public function save() {
        requireLogin();
        startSession();
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('Invalid security token. Please try again.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposal_builder');
        }
        
        $cart = $_SESSION['proposal_cart'] ?? [];
        
        if (empty($cart)) {
            setFlashMessage('Cart is empty. Please add products first.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposal_builder');
        }
        
        $eventName = sanitizeInput($_POST['event_name'] ?? '');
        $customerName = sanitizeInput($_POST['customer_name'] ?? '');
        $notes = sanitizeInput($_POST['notes'] ?? '');
        $userId = $_SESSION['user_id'];
        
        $conn = getDBConnection();
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert proposal
            $stmt = $conn->prepare("INSERT INTO proposals (user_id, event_name, customer_name, notes) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $userId, $eventName, $customerName, $notes);
            $stmt->execute();
            $proposalId = $conn->insert_id;
            $stmt->close();
            
            // Insert proposal items
            $itemStmt = $conn->prepare("INSERT INTO proposal_items (proposal_id, product_id, quantity, custom_margin, landing_cost, final_price) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($cart as $item) {
                $productId = intval($item['product_id']);
                $quantity = intval($item['quantity'] ?? 1);
                $customMargin = !empty($item['custom_margin']) ? floatval($item['custom_margin']) : null;
                
                // Get product details
                $productStmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                $productStmt->bind_param("i", $productId);
                $productStmt->execute();
                $productResult = $productStmt->get_result();
                
                if ($productResult && $productResult->num_rows === 1) {
                    $product = $productResult->fetch_assoc();
                    $margin = $customMargin !== null ? $customMargin : $product['margin_percentage'];
                    
                    // Recalculate pricing
                    $pricing = calculatePricing(
                        $product['unit_price'],
                        ($product['duty'] / $product['unit_price']) * 100,
                        ($product['shipping_cost'] / ($product['unit_price'] + $product['duty'])) * 100,
                        $product['box_price'],
                        $margin
                    );
                    
                    $itemStmt->bind_param("iiiddd",
                        $proposalId, $productId, $quantity, $customMargin,
                        $pricing['landing_cost'], $pricing['final_price']
                    );
                    $itemStmt->execute();
                }
                $productStmt->close();
            }
            
            $itemStmt->close();
            
            // Clear cart
            unset($_SESSION['proposal_cart']);
            
            // Commit transaction
            $conn->commit();
            
            setFlashMessage('Proposal saved successfully.', FLASH_SUCCESS);
            redirect(BASE_URL . 'index.php?action=proposals');
            
        } catch (Exception $e) {
            $conn->rollback();
            setFlashMessage('Error saving proposal: ' . $e->getMessage(), FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposal_builder');
        }
    }
    
    /**
     * List all proposals
     */
    public function index() {
        requireLogin();
        startSession();
        
        $userId = $_SESSION['user_id'];
        $isAdmin = isAdmin();
        
        $conn = getDBConnection();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        // Get total count
        if ($isAdmin) {
            $countResult = $conn->query("SELECT COUNT(*) as total FROM proposals");
            $totalItems = $countResult->fetch_assoc()['total'];
            $countResult->close();
        } else {
            $countSql = "SELECT COUNT(*) as total FROM proposals WHERE user_id = " . intval($userId);
            $countResult = $conn->query($countSql);
            $totalItems = $countResult->fetch_assoc()['total'];
            $countResult->close();
        }
        
        $totalPages = ceil($totalItems / $limit);
        
        // Get proposals
        if ($isAdmin) {
            $sql = "SELECT p.*, u.username FROM proposals p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);
            $result = $conn->query($sql);
            $proposals = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $proposals[] = $row;
                }
                $result->close();
            }
        } else {
            $sql = "SELECT * FROM proposals WHERE user_id = " . intval($userId) . " ORDER BY created_at DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);
            $result = $conn->query($sql);
            $proposals = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $proposals[] = $row;
                }
                $result->close();
            }
        }
        
        include __DIR__ . '/../views/proposals/index.php';
    }
    
    /**
     * View proposal details
     */
    public function view() {
        requireLogin();
        startSession();
        
        $id = intval($_GET['id'] ?? 0);
        $userId = $_SESSION['user_id'];
        $isAdmin = isAdmin();
        
        $conn = getDBConnection();
        
        if ($isAdmin) {
            $sql = "SELECT p.*, u.username FROM proposals p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = " . intval($id);
            $result = $conn->query($sql);
        } else {
            $sql = "SELECT * FROM proposals WHERE id = " . intval($id) . " AND user_id = " . intval($userId);
            $result = $conn->query($sql);
        }
        
        if (!$result || $result->num_rows === 0) {
            setFlashMessage('Proposal not found.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposals');
        }
        
        $proposal = $result->fetch_assoc();
        if ($result) $result->close();
        
        // Get proposal items
        $itemsSql = "SELECT pi.*, pr.* FROM proposal_items pi 
            INNER JOIN products pr ON pi.product_id = pr.id 
            WHERE pi.proposal_id = " . intval($id);
        $itemsResult = $conn->query($itemsSql);
        $items = [];
        if ($itemsResult) {
            while ($row = $itemsResult->fetch_assoc()) {
                $items[] = $row;
            }
            $itemsResult->close();
        }
        
        include __DIR__ . '/../views/proposals/view.php';
    }
}

