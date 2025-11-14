<?php
/**
 * Settings Controller
 * Handles application settings management
 */

require_once __DIR__ . '/../includes/helpers.php';

class SettingsController {
    
    /**
     * Show settings form
     */
    public function index() {
        requireAdmin();
        
        $conn = getDBConnection();
        $stmt = $conn->query("SELECT * FROM settings WHERE id = 1");
        $settings = $stmt->fetch_assoc();
        $stmt->close();
        
        include __DIR__ . '/../views/settings/index.php';
    }
    
    /**
     * Update settings
     */
    public function update() {
        requireAdmin();
        startSession();
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('Invalid security token. Please try again.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=settings');
        }
        
        $defaultMargin = floatval($_POST['default_margin_percentage'] ?? 35);
        $defaultDuty = floatval($_POST['default_duty_percentage'] ?? 30);
        $defaultShipping = floatval($_POST['default_shipping_percentage'] ?? 5);
        $defaultBoxPrice = floatval($_POST['default_box_price'] ?? 1);
        $defaultEventName = sanitizeInput($_POST['default_event_name'] ?? '');
        
        // Handle logo upload
        $logoPath = null;
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['company_logo'];
            $fileName = $file['name'];
            $fileTmp = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileType = $file['type'];
            
            // Validate file
            if ($fileSize > UPLOAD_MAX_SIZE) {
                setFlashMessage('Logo file is too large. Maximum size is 5MB.', FLASH_ERROR);
                redirect(BASE_URL . 'index.php?action=settings');
            }
            
            if (!in_array($fileType, ALLOWED_IMAGE_TYPES)) {
                setFlashMessage('Invalid file type. Allowed types: JPEG, PNG, GIF, WebP.', FLASH_ERROR);
                redirect(BASE_URL . 'index.php?action=settings');
            }
            
            // Generate unique filename
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = 'logo_' . time() . '.' . $ext;
            $uploadPath = UPLOAD_DIR . $newFileName;
            
            // Create upload directory if not exists
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Delete old logo if exists
                $conn = getDBConnection();
                $oldStmt = $conn->query("SELECT company_logo FROM settings WHERE id = 1");
                $oldSettings = $oldStmt->fetch_assoc();
                $oldStmt->close();
                
                if ($oldSettings && !empty($oldSettings['company_logo'])) {
                    $oldLogoPath = UPLOAD_DIR . basename($oldSettings['company_logo']);
                    if (file_exists($oldLogoPath)) {
                        unlink($oldLogoPath);
                    }
                }
                
                $logoPath = 'assets/uploads/logo/' . $newFileName;
            } else {
                setFlashMessage('Error uploading logo file.', FLASH_ERROR);
                redirect(BASE_URL . 'index.php?action=settings');
            }
        }
        
        $conn = getDBConnection();
        
        if ($logoPath) {
            $stmt = $conn->prepare("UPDATE settings SET 
                default_margin_percentage = ?, 
                default_duty_percentage = ?, 
                default_shipping_percentage = ?, 
                default_box_price = ?, 
                default_event_name = ?,
                company_logo = ?
                WHERE id = 1");
            $stmt->bind_param("ddddss", $defaultMargin, $defaultDuty, $defaultShipping, $defaultBoxPrice, $defaultEventName, $logoPath);
        } else {
            $stmt = $conn->prepare("UPDATE settings SET 
                default_margin_percentage = ?, 
                default_duty_percentage = ?, 
                default_shipping_percentage = ?, 
                default_box_price = ?, 
                default_event_name = ?
                WHERE id = 1");
            $stmt->bind_param("dddds", $defaultMargin, $defaultDuty, $defaultShipping, $defaultBoxPrice, $defaultEventName);
        }
        
        if ($stmt->execute()) {
            setFlashMessage('Settings updated successfully.', FLASH_SUCCESS);
        } else {
            setFlashMessage('Error updating settings: ' . $conn->error, FLASH_ERROR);
        }
        
        $stmt->close();
        redirect(BASE_URL . 'index.php?action=settings');
    }
}

