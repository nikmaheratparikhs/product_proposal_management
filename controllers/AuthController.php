<?php
/**
 * Authentication Controller
 * Handles login, logout, and session management
 */

require_once __DIR__ . '/../includes/helpers.php';

class AuthController {
    
    /**
     * Show login page
     */
    public function showLogin() {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            redirect(BASE_URL . 'index.php?action=dashboard');
        }
        
        include __DIR__ . '/../views/auth/login.php';
    }
    
    /**
     * Process login
     */
    public function login() {
        startSession();
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('Invalid security token. Please try again.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=login');
        }
        
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            setFlashMessage('Please enter both username and password.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=login');
        }
        
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password using MD5
            if (verifyPassword($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                setFlashMessage('Welcome back, ' . e($user['username']) . '!', FLASH_SUCCESS);
                redirect(BASE_URL . 'index.php?action=dashboard');
            } else {
                setFlashMessage('Invalid username or password.', FLASH_ERROR);
                redirect(BASE_URL . 'index.php?action=login');
            }
        } else {
            setFlashMessage('Invalid username or password.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=login');
        }
        
        $stmt->close();
    }
    
    /**
     * Logout user
     */
    public function logout() {
        startSession();
        
        // Destroy session
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        
        setFlashMessage('You have been logged out successfully.', FLASH_SUCCESS);
        redirect(BASE_URL . 'index.php?action=login');
    }
}

