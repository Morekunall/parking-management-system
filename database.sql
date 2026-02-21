-- Smart Parking Management System Database Schema
-- Run this SQL script in your XAMPP MySQL database

CREATE DATABASE IF NOT EXISTS smartpark;
USE smartpark;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('provider', 'customer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Parking spaces table
CREATE TABLE parking_spaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    location TEXT NOT NULL,
    available_slots INT NOT NULL,
    price_per_hour DECIMAL(10,2) NOT NULL,
    available_date DATE NOT NULL,
    available_time TIME NOT NULL,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    parking_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parking_id) REFERENCES parking_spaces(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO users (name, email, password, user_type) VALUES
('Kunal More', 'kunal@provider.com', '2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider'),
('Parth Gujar', 'parth@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Mike Provider', 'mike@provider.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider'),
('Sarah Customer', 'sarah@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- Note: The password hash above is for 'password' - change this in production!

INSERT INTO parking_spaces (provider_id, name, location, available_slots, price_per_hour, available_date, available_time, image_url) VALUES
(1, 'Downtown Plaza Parking', '123 Main Street, Downtown', 50, 5.00, '2024-01-15', '08:00:00', 'https://via.placeholder.com/300x200'),
(1, 'City Center Garage', '456 Oak Avenue, City Center', 30, 7.50, '2024-01-15', '09:00:00', 'https://via.placeholder.com/300x200'),
(3, 'Mall Parking Lot', '789 Pine Street, Shopping Mall', 100, 3.00, '2024-01-16', '10:00:00', 'https://via.placeholder.com/300x200'),
(3, 'Office Building Parking', '321 Elm Street, Business District', 25, 8.00, '2024-01-16', '08:30:00', 'https://via.placeholder.com/300x200');

INSERT INTO bookings (customer_id, parking_id, booking_date, start_time, end_time, total_cost, status) VALUES
(2, 1, '2024-01-15', '09:00:00', '17:00:00', 40.00, 'confirmed'),
(4, 2, '2024-01-15', '10:00:00', '18:00:00', 60.00, 'pending'),
(2, 3, '2024-01-16', '11:00:00', '15:00:00', 12.00, 'completed');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_parking_provider ON parking_spaces(provider_id);
CREATE INDEX idx_parking_date ON parking_spaces(available_date);
CREATE INDEX idx_bookings_customer ON bookings(customer_id);
CREATE INDEX idx_bookings_parking ON bookings(parking_id);
CREATE INDEX idx_bookings_date ON bookings(booking_date);
CREATE INDEX idx_bookings_status ON bookings(status);

give me the postgresql code for this same use name and ids as i mentioned