-- Product Proposal Management Portal - Database Schema
-- MySQL / MariaDB

-- Create database
CREATE DATABASE IF NOT EXISTS product_proposal_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE product_proposal_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'viewer') DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY DEFAULT 1,
    default_margin_percentage DECIMAL(5,2) DEFAULT 35.00,
    default_duty_percentage DECIMAL(5,2) DEFAULT 30.00,
    default_shipping_percentage DECIMAL(5,2) DEFAULT 5.00,
    default_box_price DECIMAL(10,2) DEFAULT 1.00,
    company_logo VARCHAR(255) DEFAULT NULL,
    default_event_name VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_one_row CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT DEFAULT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image_url VARCHAR(500) DEFAULT NULL,
    product_link VARCHAR(500) DEFAULT NULL,
    unit_price DECIMAL(10,2) DEFAULT 0.00,
    duty DECIMAL(10,2) DEFAULT 0.00,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    box_price DECIMAL(10,2) DEFAULT 0.00,
    landing_cost DECIMAL(10,2) DEFAULT 0.00,
    margin_percentage DECIMAL(5,2) DEFAULT 35.00,
    final_price DECIMAL(10,2) DEFAULT 0.00,
    proposal_margin DECIMAL(5,2) DEFAULT NULL,
    selection_flag TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_sku (sku),
    INDEX idx_category (category_id),
    INDEX idx_selection (selection_flag),
    INDEX idx_final_price (final_price),
    INDEX idx_landing_cost (landing_cost)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Proposals table
CREATE TABLE IF NOT EXISTS proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_name VARCHAR(255) DEFAULT NULL,
    customer_name VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Proposal items table
CREATE TABLE IF NOT EXISTS proposal_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proposal_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    custom_margin DECIMAL(5,2) DEFAULT NULL,
    landing_cost DECIMAL(10,2) DEFAULT 0.00,
    final_price DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_proposal (proposal_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
-- Password hash for 'admin123' using MD5
INSERT INTO users (username, password, role) VALUES 
('admin', '0192023a7bbd73250516f069df18b500', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Insert default settings
INSERT INTO settings (id, default_margin_percentage, default_duty_percentage, default_shipping_percentage, default_box_price, default_event_name) 
VALUES (1, 35.00, 30.00, 5.00, 1.00, 'Default Event')
ON DUPLICATE KEY UPDATE id=id;

-- Insert sample categories
INSERT INTO categories (name) VALUES 
('Electronics'),
('Gift Items'),
('Promotional Items'),
('Casino Promotions')
ON DUPLICATE KEY UPDATE name=name;

