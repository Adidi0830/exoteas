<?php
// exotea/pages/input_transaksi.php
require_once __DIR__ . '/../config/database.php';

// Fetch Menus
try {
    $stmt = $pdo->query("SELECT * FROM menus ORDER BY kategori DESC, nama_menu ASC");
    $menus = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching menus: " . $e->getMessage());
}

$grouped_menus = [];
foreach ($menus as $menu) {
    $grouped_menus[$menu['kategori']][] = $menu;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Transaksi - Exotea</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <header class="header">
        <div class="brand">Exotea</div>
        <a href="laporan.php"
            style="text-decoration: none; color: var(--primary); font-size: 0.9rem; font-weight: 600;">Laporan</a>
    </header>

    <form action="../actions/simpan_transaksi.php" method="POST" id="orderForm" onsubmit="confirmOrder(event)">
        <div
            style="background: white; padding: 1rem; margin: 1rem auto; max-width: 600px; border-radius: 12px; box-shadow: var(--shadow);">
            <label style="font-weight: 600; color: var(--text); display: block; margin-bottom: 0.5rem;">Tanggal
                Transaksi</label>
            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>"
                style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: inherit;">
        </div>

        <div class="menu-container">
            <?php foreach ($grouped_menus as $kategori => $items): ?>
                <h2 class="category-title">
                    <?= htmlspecialchars($kategori) ?>
                </h2>

                <?php foreach ($items as $item): ?>
                    <div class="menu-item">
                        <div class="menu-info">
                            <span class="menu-name">
                                <?= htmlspecialchars($item['nama_menu']) ?>
                            </span>
                            <span class="menu-price" id="price-display-<?= $item['id'] ?>">Rp
                                <?= number_format($item['harga'], 0, ',', '.') ?>
                            </span>
                        </div>
                        <div class="menu-actions">
                            <button type="button" class="btn-qty" onclick="updateQty(<?= $item['id'] ?>, -1)">-</button>
                            <input type="hidden" name="orders[<?= $item['id'] ?>]" id="qty-input-<?= $item['id'] ?>" value="0">
                            <span class="qty-display" id="qty-display-<?= $item['id'] ?>">0</span>
                            <button type="button" class="btn-qty" onclick="updateQty(<?= $item['id'] ?>, 1)">+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <div class="checkout-bar">
            <button type="submit" class="btn-submit" id="submitBtn" disabled>
                Proses Transaksi (<span id="total-items">0</span> item) - Rp <span id="total-price">0</span>
            </button>
        </div>
    </form>

    <script>
        const menus = <?= json_encode($menus, JSON_NUMERIC_CHECK) ?>;
        const state = {};

        // Initialize state
        menus.forEach(m => state[m.id] = 0);

        function formatRupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }

        function updateQty(id, change) {
            const current = state[id] || 0;
            const newVal = Math.max(0, current + change); // Prevent negative

            state[id] = newVal;

            // Update DOM
            document.getElementById(`qty-input-${id}`).value = newVal;
            document.getElementById(`qty-display-${id}`).textContent = newVal;

            // Update Price Display
            const priceSpan = document.getElementById(`price-display-${id}`);
            const menu = menus.find(m => m.id == id);

            if (newVal > 0) {
                // Change style for active item
                priceSpan.innerHTML = `<span style="color:var(--primary); font-weight:700">${newVal} x ${formatRupiah(menu.harga)} = ${formatRupiah(newVal * menu.harga)}</span>`;
            } else {
                // Revert to original style
                priceSpan.innerHTML = formatRupiah(menu.harga);
                priceSpan.style.color = '';
                priceSpan.style.fontWeight = '';
            }

            // Highlight active controls
            const display = document.getElementById(`qty-display-${id}`);
            const minusBtn = display.previousElementSibling.previousElementSibling;

            if (newVal > 0) {
                minusBtn.classList.add('active');
            } else {
                minusBtn.classList.remove('active');
            }

            updateTotal();
        }

        function updateTotal() {
            let totalItems = 0;
            let totalPrice = 0;

            menus.forEach(m => {
                const qty = state[m.id] || 0;
                totalItems += qty;
                totalPrice += qty * m.harga;
            });

            const btn = document.getElementById('submitBtn');
            btn.disabled = totalItems === 0;

            if (totalItems > 0) {
                btn.innerHTML = `Proses Transaksi (${totalItems} item) • Total Bayar: ${formatRupiah(totalPrice)}`;
            } else {
                btn.innerHTML = `Pilih Menu Dulu`;
            }
        }

        function confirmOrder(e) {
            e.preventDefault();

            let totalPrice = 0;
            menus.forEach(m => {
                const qty = state[m.id] || 0;
                totalPrice += qty * m.harga;
            });

            Swal.fire({
                title: 'Konfirmasi Pesanan',
                text: `Total transaksi adalah ${formatRupiah(totalPrice)}. Simpan data?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2d6a4f',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('orderForm').submit();
                }
            });
        }
    </script>
</body>

</html>