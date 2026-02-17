-- exotea/schema.sql

CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_menu VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    harga INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    menu_id INT NOT NULL,
    jumlah INT NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id)
);

-- Seed Data (Updated Prices)
-- Original: 4k, Lechy: 8k, Others: 10k
INSERT INTO menus (id, nama_menu, kategori, harga) VALUES
(1, 'Exotea''s Original', 'Exotea''s', 4000),
(2, 'Exotea''s Lechy', 'Exotea''s', 8000),
(3, 'Exotea''s Choco', 'Exotea''s', 10000),
(4, 'Exotea''s Avocado', 'Exotea''s', 10000),
(5, 'Exotea''s Milk Tea', 'Exotea''s', 10000),
(6, 'Exotea''s Red Velvet', 'Exotea''s', 10000),
(7, 'Cola Float', 'Non Tea', 10000),
(8, 'Pink Lova', 'Non Tea', 10000),
(9, 'Chocolate Milk', 'Non Tea', 10000)
ON DUPLICATE KEY UPDATE harga=VALUES(harga), nama_menu=VALUES(nama_menu);
