<?php
// exotea/actions/simpan_transaksi.php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orders = $_POST['orders'] ?? [];

    // Filter out items with 0 quantity
    $items = [];
    foreach ($orders as $id => $qty) {
        if ($qty > 0) {
            $items[] = ['id' => $id, 'qty' => $qty];
        }
    }

    if (empty($items)) {
        header("Location: ../pages/input_transaksi.php?error=empty");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Create Transaction
        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
        $stmt = $pdo->prepare("INSERT INTO transaksi (tanggal) VALUES (?)");
        $stmt->execute([$tanggal]);
        $transaksi_id = $pdo->lastInsertId();

        // 2. Insert Details
        $stmtDetail = $pdo->prepare("INSERT INTO detail_transaksi (transaksi_id, menu_id, jumlah) VALUES (?, ?, ?)");

        foreach ($items as $item) {
            $stmtDetail->execute([$transaksi_id, $item['id'], $item['qty']]);
        }

        $pdo->commit();
        header("Location: ../pages/input_transaksi.php?success=1");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Terjadi kesalahan: " . $e->getMessage());
    }
} else {
    header("Location: ../pages/input_transaksi.php");
    exit;
}
