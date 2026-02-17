<?php
// exotea/seed.php
require_once __DIR__ . '/config/database.php';

try {
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($sql);
    echo "Database seeded successfully!";
} catch (PDOException $e) {
    echo "Error seeding database: " . $e->getMessage();
}
