CREATE DATABASE IF NOT EXISTS vignesh_decorators;
USE vignesh_decorators;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO services (name) VALUES ('Decoration'), ('Sound'), ('Lighting');

CREATE TABLE IF NOT EXISTS bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_number VARCHAR(50) DEFAULT NULL,
    date DATE NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    description TEXT,
    grand_total DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bill_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT,
    category VARCHAR(50),
    item_name VARCHAR(255),
    quantity DECIMAL(10, 2),
    price DECIMAL(10, 2),
    total DECIMAL(10, 2),
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT,
    total_amount DECIMAL(10, 2),
    paid_amount DECIMAL(10, 2),
    balance_amount DECIMAL(10, 2),
    status ENUM('Paid', 'Partial', 'Pending') DEFAULT 'Pending',
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bill_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
);
