<?php
/**
 * Main Entry Point
 * Routes all requests to appropriate controllers
 */

// Start session
session_start();

// Define base path (before including constants.php which also defines it)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Include configuration and helpers
require_once BASE_PATH . '/config/constants.php';
require_once BASE_PATH . '/includes/helpers.php';

// Include all controllers
require_once BASE_PATH . '/controllers/AuthController.php';
require_once BASE_PATH . '/controllers/ProductController.php';
require_once BASE_PATH . '/controllers/ImportController.php';
require_once BASE_PATH . '/controllers/ProposalController.php';
require_once BASE_PATH . '/controllers/SettingsController.php';
require_once BASE_PATH . '/controllers/ExportController.php';

// Get action from URL
$action = $_GET['action'] ?? 'login';

// Route to appropriate controller
switch ($action) {
    // Authentication
    case 'login':
        $controller = new AuthController();
        $controller->showLogin();
        break;
        
    case 'login_process':
        $controller = new AuthController();
        $controller->login();
        break;
        
    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;
        
    // Dashboard
    case 'dashboard':
        requireLogin();
        include BASE_PATH . '/views/dashboard.php';
        break;
        
    // Products
    case 'products':
        $controller = new ProductController();
        $controller->index();
        break;
        
    case 'product_form':
        $controller = new ProductController();
        $controller->form();
        break;
        
    case 'product_save':
        $controller = new ProductController();
        $controller->save();
        break;
        
    case 'product_show':
        $controller = new ProductController();
        $controller->show();
        break;
        
    case 'product_delete':
        $controller = new ProductController();
        $controller->delete();
        break;
        
    // Import
    case 'import':
        $controller = new ImportController();
        $controller->form();
        break;
        
    case 'import_process':
        $controller = new ImportController();
        $controller->process();
        break;
        
    case 'download_sample':
        $controller = new ImportController();
        $controller->downloadSample();
        break;
        
    // Proposals
    case 'proposal_builder':
        $controller = new ProposalController();
        $controller->builder();
        break;
        
    case 'add_to_cart':
        $controller = new ProposalController();
        $controller->addToCart();
        break;
        
    case 'add_multiple_to_cart':
        $controller = new ProposalController();
        $controller->addMultipleToCart();
        break;
        
    case 'update_cart':
        $controller = new ProposalController();
        $controller->updateCart();
        break;
        
    case 'remove_from_cart':
        $controller = new ProposalController();
        $controller->removeFromCart();
        break;
        
    case 'proposal_save':
        $controller = new ProposalController();
        $controller->save();
        break;
        
    case 'proposals':
        $controller = new ProposalController();
        $controller->index();
        break;
        
    case 'proposal_view':
        $controller = new ProposalController();
        $controller->view();
        break;
        
    // Export
    case 'export_excel':
        $controller = new ExportController();
        $controller->exportExcel();
        break;
        
    case 'export_word':
        $controller = new ExportController();
        $controller->exportWord();
        break;
        
    case 'export_pptx':
        $controller = new ExportController();
        $controller->exportPowerPoint();
        break;
        
    // Settings
    case 'settings':
        $controller = new SettingsController();
        $controller->index();
        break;
        
    case 'settings_update':
        $controller = new SettingsController();
        $controller->update();
        break;
        
    default:
        // Default to login if not logged in, otherwise dashboard
        if (isLoggedIn()) {
            redirect(BASE_URL . 'index.php?action=dashboard');
        } else {
            redirect(BASE_URL . 'index.php?action=login');
        }
        break;
}

