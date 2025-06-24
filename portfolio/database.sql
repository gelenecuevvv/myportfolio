-- Create database for portfolio contact forms
CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

-- Create contacts table
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    status ENUM('new', 'read', 'replied') DEFAULT 'new'
);

-- Create index for faster queries
CREATE INDEX idx_email ON contacts(email);
CREATE INDEX idx_submitted_at ON contacts(submitted_at);
CREATE INDEX idx_status ON contacts(status); 