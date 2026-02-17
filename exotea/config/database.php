<?php
// exotea/config/database.php

$host = $_ENV['MYSQLHOST'] ?? 'localhost';
$port = $_ENV['MYSQLPORT'] ?? '3306';
$dbname = $_ENV['MYSQLDATABASE'] ?? 'exotea';
$username = $_ENV['MYSQLUSER'] ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If database doesn't exist locally, try to create it (for local dev convenience)
    if (strpos($e->getMessage(), "Unknown database") !== false && $host === 'localhost') {
        try {
            $dsn_no_db = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo_temp = new PDO($dsn_no_db, $username, $password);
            $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e2) {
             die("Connection failed: " . $e2->getMessage());
        }
    } else {
        die("Connection failed: " . $e->getMessage());
    }
}
