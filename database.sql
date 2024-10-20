-- Membuat database
CREATE DATABASE IF NOT EXISTS hotel_db;

-- Menggunakan database yang baru dibuat
USE hotel_db;

-- Membuat tabel rooms
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    room_number VARCHAR(10) NOT NULL UNIQUE,        
    room_type VARCHAR(10) NOT NULL,  -- Menghapus UNIQUE di sini agar bisa memiliki tipe yang sama
    price_per_night DECIMAL(10, 2) NOT NULL,           
    availability BOOLEAN DEFAULT TRUE, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
    deleted_at TIMESTAMP NULL DEFAULT NULL  -- Mengubah DEFAULT menjadi NULL agar sesuai
);
