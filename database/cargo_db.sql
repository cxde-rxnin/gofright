-- Cargo Freight System Database
-- Created: November 9, 2025

CREATE DATABASE IF NOT EXISTS cargo_db;
USE cargo_db;

-- Login table
CREATE TABLE IF NOT EXISTS login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    state VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Route table
CREATE TABLE IF NOT EXISTS route (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route VARCHAR(200) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    type VARCHAR(50) NOT NULL,
    time VARCHAR(50) NOT NULL,
    capacity DECIMAL(10, 2) NOT NULL COMMENT 'Maximum weight capacity in kg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cargo table
CREATE TABLE IF NOT EXISTS cargo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    type VARCHAR(100) NOT NULL,
    weight DECIMAL(10, 2) NOT NULL,
    destination VARCHAR(200) NOT NULL,
    date DATE NOT NULL,
    status ENUM('pending', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending',
    route_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES route(id) ON DELETE CASCADE
);

-- Payment table
CREATE TABLE IF NOT EXISTS payment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cargo_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('online', 'on_delivery') NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cargo_id) REFERENCES cargo(id) ON DELETE CASCADE
);

-- NOTE: Admin account will be created by running setup.php after importing this database
-- Default credentials: username = admin, password = admin123

-- Insert sample routes
INSERT INTO route (route, price, type, time, capacity) VALUES
('Lagos to Abuja', 5000.00, 'Air Freight', '2 hours', 5000.00),
('Lagos to Port Harcourt', 3500.00, 'Air Freight', '1.5 hours', 4000.00),
('Abuja to Kano', 4000.00, 'Air Freight', '1 hour', 3500.00),
('Lagos to Enugu', 4500.00, 'Air Freight', '1.5 hours', 3000.00),
('Abuja to Lagos', 5000.00, 'Air Freight', '2 hours', 5000.00);
