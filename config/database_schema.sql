-- Product Inventory System Database Setup
-- Run this script in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS product_inventory;
USE product_inventory;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_price (price)
);

-- Insert sample products (optional - remove if you don't want sample data)
INSERT INTO products (product_name, description, category, price, quantity) VALUES
('Laptop Dell XPS 15', 'High-performance laptop with 16GB RAM', 'Electronics', 1299.99, 15),
('iPhone 14 Pro', 'Latest Apple smartphone', 'Electronics', 999.99, 25),
('Office Chair', 'Ergonomic office chair with lumbar support', 'Furniture', 249.99, 40),
('Desk Lamp', 'LED desk lamp with adjustable brightness', 'Furniture', 39.99, 60),
('Wireless Mouse', 'Bluetooth wireless mouse', 'Accessories', 29.99, 100),
('USB-C Cable', 'Fast charging USB-C cable 2m', 'Accessories', 12.99, 200),
('Monitor 27"', '4K UHD monitor with HDR support', 'Electronics', 449.99, 20),
('Keyboard Mechanical', 'RGB mechanical gaming keyboard', 'Accessories', 89.99, 35);