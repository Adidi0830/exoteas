<?php
// exotea/pages/laporan.php
require_once __DIR__ . '/../config/database.php';

session_start();

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: laporan.php");
    exit;
}

// Handle Login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === 'admin123') { // Simple password
        $_SESSION['logged_in'] = true;
    } else {
        $error = 'Password salah!';
    }
}

// Check Login
if (!isset($_SESSION['logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Login Laporan - Exotea</title>
        <link rel="stylesheet" href="../style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>

    <body>
        <div class="login-overlay">
            <div class="login-box">
                <h2>Admin Login</h2>
                <form method="POST">
                    <input type="password" name="password" class="input-field" placeholder="Password" required>
                    <?php if ($error): ?>
                        <p style="color:red">
                            <?= $error ?>
                        </p>
                    <?php endif; ?>
                    <button type="submit" class="btn-submit">Masuk</button>
                    <a href="../index.php" style="display:block; margin-top:1rem; color:#666; text-decoration:none">Kembali
                        ke Menu</a>
                </form>
            </div>
        </div>
    </body>

    </html>
    <?php
    exit;
}

// Fetch Reports
try {
    // 1. Total Penjualan Hari Ini
    $stmtToday = $pdo->query("
        SELECT 
            COUNT(DISTINCT t.id) as total_transaksi,
            COALESCE(SUM(dt.jumlah * m.harga), 0) as omzet
        FROM transaksi t
        LEFT JOIN detail_transaksi dt ON t.id = dt.transaksi_id
        LEFT JOIN menus m ON dt.menu_id = m.id
        WHERE t.tanggal = CURDATE()
    ");
    $statToday = $stmtToday->fetch();

    // 2. Penjualan Per Menu (Hari Ini)
    $stmtMenu = $pdo->query("
        SELECT 
            m.nama_menu,
            SUM(dt.jumlah) as terjual,
            SUM(dt.jumlah * m.harga) as total_pendapatan
        FROM detail_transaksi dt
        JOIN menus m ON dt.menu_id = m.id
        JOIN transaksi t ON dt.transaksi_id = t.id
        WHERE t.tanggal = CURDATE()
        GROUP BY m.id
        ORDER BY terjual DESC
    ");
    $menuStats = $stmtMenu->fetchAll();

    // 3. Omzet 7 Hari Terakhir
    $stmtDaily = $pdo->query("
        SELECT 
            t.tanggal,
            COUNT(DISTINCT t.id) as jumlah_transaksi,
            COALESCE(SUM(dt.jumlah * m.harga), 0) as omzet
        FROM transaksi t
        LEFT JOIN detail_transaksi dt ON t.id = dt.transaksi_id
        LEFT JOIN menus m ON dt.menu_id = m.id
        WHERE t.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY t.tanggal
        ORDER BY t.tanggal DESC
    ");
    $dailyStats = $stmtDaily->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Penjualan - Exotea</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <header class="header">
        <div class="brand">Exotea Admin</div>
        <div>
            <a href="../index.php" class="nav-link">Menu</a>
            <a href="?logout=1" class="nav-link" style="color:var(--danger)">Logout</a>
        </div>
    </header>

    <div class="report-container">
        <!-- Summary Cards -->
        <div class="report-card">
            <h3 class="category-title" style="margin-top:0">Ringkasan Hari Ini (
                <?= date('d M Y') ?>)
            </h3>
            <div class="stat-grid">
                <div class="stat-box">
                    <span class="stat-value">
                        <?= $statToday['total_transaksi'] ?>
                    </span>
                    <span class="stat-label">Transaksi</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value">Rp
                        <?= number_format($statToday['omzet'], 0, ',', '.') ?>
                    </span>
                    <span class="stat-label">Omzet</span>
                </div>
            </div>
        </div>

        <!-- Menu Stats -->
        <div class="report-card">
            <h3 class="category-title" style="margin-top:0">Penjualan Menu Hari Ini</h3>
            <?php if (empty($menuStats)): ?>
                <p style="text-align:center; color:#666; padding:1rem">Belum ada transaksi hari ini.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th style="text-align:center">Qty</th>
                            <th style="text-align:right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menuStats as $row): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($row['nama_menu']) ?>
                                </td>
                                <td style="text-align:center">
                                    <?= $row['terjual'] ?>
                                </td>
                                <td style="text-align:right">Rp
                                    <?= number_format($row['total_pendapatan'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Daily History -->
        <div class="report-card">
            <h3 class="category-title" style="margin-top:0">Riwayat 7 Hari Terakhir</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th style="text-align:center">Tx</th>
                        <th style="text-align:right">Omzet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dailyStats as $row): ?>
                        <tr>
                            <td>
                                <?= date('d M Y', strtotime($row['tanggal'])) ?>
                            </td>
                            <td style="text-align:center">
                                <?= $row['jumlah_transaksi'] ?>
                            </td>
                            <td style="text-align:right">Rp
                                <?= number_format($row['omzet'], 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>