<?php
// exotea/migrate.php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Exotea Database Migration</h1>";

    // 1. Create Tables
    $sql_menus = "CREATE TABLE IF NOT EXISTS menus (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_menu VARCHAR(100) NOT NULL,
        kategori VARCHAR(50) NOT NULL,
        harga INT DEFAULT 0
    )";
    $pdo->exec($sql_menus);
    echo "<p>✅ Tabel 'menus' ready.</p>";

    $sql_transaksi = "CREATE TABLE IF NOT EXISTS transaksi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tanggal DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_transaksi);
    echo "<p>✅ Tabel 'transaksi' ready.</p>";

    $sql_detail = "CREATE TABLE IF NOT EXISTS detail_transaksi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaksi_id INT NOT NULL,
        menu_id INT NOT NULL,
        jumlah INT NOT NULL,
        FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
        FOREIGN KEY (menu_id) REFERENCES menus(id)
    )";
    $pdo->exec($sql_detail);
    echo "<p>✅ Tabel 'detail_transaksi' ready.</p>";

    // 2. Check & Seed Data
    $stmt = $pdo->query("SELECT COUNT(*) FROM menus");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        echo "<p>Seeding default menu data...</p>";

        $menus = [
            // Exotea's
            ['nama' => "Exotea's Original", 'kategori' => "Exotea's", 'harga' => 4000],
            ['nama' => "Exotea's Lechy", 'kategori' => "Exotea's", 'harga' => 8000],
            ['nama' => "Exotea's Choco", 'kategori' => "Exotea's", 'harga' => 10000],
            ['nama' => "Exotea's Avocado", 'kategori' => "Exotea's", 'harga' => 10000],
            ['nama' => "Exotea's Milk Tea", 'kategori' => "Exotea's", 'harga' => 10000],
            ['nama' => "Exotea's Red Velvet", 'kategori' => "Exotea's", 'harga' => 10000],
            // Non Tea
            ['nama' => "Cola Float", 'kategori' => "Non Tea", 'harga' => 10000],
            ['nama' => "Pink Lova", 'kategori' => "Non Tea", 'harga' => 10000],
            ['nama' => "Chocolate Milk", 'kategori' => "Non Tea", 'harga' => 10000],
        ];

        $insert = $pdo->prepare("INSERT INTO menus (nama_menu, kategori, harga) VALUES (:nama, :kategori, :harga)");

        foreach ($menus as $m) {
            $insert->execute($m);
            echo "<li>Inserted: {$m['nama']}</li>";
        }
        echo "<p>✅ Seeding completed.</p>";
    } else {
        echo "<p>ℹ️ Table 'menus' already has data. Seeding skipped.</p>";
    }

    echo "<h3>Migration Successful! 🎉</h3>";
    echo "<a href='index.php'>Go to App</a>";

} catch (PDOException $e) {
    die("<h3>❌ Migration Failed:</h3><pre>" . $e->getMessage() . "</pre>");
}
