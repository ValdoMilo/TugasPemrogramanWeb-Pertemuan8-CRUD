<?php
require 'Config.php';
$db = Database::getInstance()->getConnection();

// [Bonus] Fitur Pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// [Req 4] JOIN 3 tabel
$query = "SELECT p.id, p.nama_produk, p.harga, p.stok, k.nama_kategori, s.nama_supplier 
          FROM produk p 
          LEFT JOIN kategori k ON p.kategori_id = k.id 
          LEFT JOIN supplier s ON p.supplier_id = s.id";

if ($search !== '') {
    // [Req 7] Prepared Statement + escape wildcard
    $searchEscaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
    $query .= " WHERE p.nama_produk LIKE :search OR k.nama_kategori LIKE :search OR s.nama_supplier LIKE :search";
    $query .= " ORDER BY p.id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute(['search' => "%$searchEscaped%"]);
} else {
    $query .= " ORDER BY p.id DESC";
    $stmt = $db->query($query);
}
$produk = $stmt->fetchAll();
$total = count($produk);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0b1220">
    <title>Dashboard Inventaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app">

        <!-- Header -->
        <header class="topbar">
            <div class="topbar__left">
                <div class="logo">📦</div>
                <div>
                    <h1 class="topbar__title">Inventaris</h1>
                    <p class="topbar__subtitle">Sistem Manajemen Produk</p>
                </div>
            </div>
            <a href="create.php" class="btn btn-primary">
                <span>+</span> Tambah Produk
            </a>
        </header>

        <!-- Stats -->
        <section class="stats">
            <div class="stat-card">
                <span class="stat-card__label">Total Produk</span>
                <span class="stat-card__value"><?= $total ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-card__label">Kategori</span>
                <span class="stat-card__value">
                    <?= count(array_unique(array_column($produk, 'nama_kategori'))) ?>
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-card__label">Supplier</span>
                <span class="stat-card__value">
                    <?= count(array_unique(array_column($produk, 'nama_supplier'))) ?>
                </span>
            </div>
        </section>

        <!-- Flash -->
        <?php displayFlash(); ?>

        <!-- Search -->
        <form method="GET" action="" class="search-bar">
            <span class="search-bar__icon">🔍</span>
            <input 
                type="text" 
                name="search" 
                class="search-bar__input" 
                placeholder="Cari produk, kategori, atau supplier..." 
                value="<?= sanitize($search) ?>"
                autocomplete="off">
            <?php if ($search !== ''): ?>
                <a href="index.php" class="search-bar__clear" title="Reset">✕</a>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        </form>

        <!-- Table -->
        <div class="card">
            <?php if ($produk): ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Supplier</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th class="th-action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach($produk as $row): ?>
                            <tr>
                                <td data-label="No" class="td-no"><?= $no++ ?></td>
                                <td data-label="Produk" class="td-produk">
                                    <strong><?= sanitize($row['nama_produk']) ?></strong>
                                </td>
                                <td data-label="Kategori">
                                    <?php if ($row['nama_kategori']): ?>
                                        <span class="badge badge-blue"><?= sanitize($row['nama_kategori']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Supplier">
                                    <?php if ($row['nama_supplier']): ?>
                                        <span class="badge badge-gray"><?= sanitize($row['nama_supplier']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Harga" class="td-harga"><?= formatRupiah($row['harga']) ?></td>
                                <td data-label="Stok">
                                    <span class="badge <?= $row['stok'] > 10 ? 'badge-success' : ($row['stok'] > 0 ? 'badge-warning' : 'badge-danger') ?>">
                                        <?= sanitize($row['stok']) ?>
                                    </span>
                                </td>
                                <td data-label="Aksi" class="td-action">
                                    <div class="action-group">
                                        <a href="edit.php?id=<?= (int)$row['id'] ?>" class="btn-icon btn-icon--edit" title="Edit">✎</a>
                                        <a href="delete.php?id=<?= (int)$row['id'] ?>" 
                                           class="btn-icon btn-icon--delete" 
                                           title="Hapus"
                                           onclick="return confirm('Yakin ingin menghapus produk ini?')">🗑</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state__icon">📭</div>
                    <h3 class="empty-state__title">
                        <?= $search !== '' ? 'Tidak ada hasil' : 'Belum ada produk' ?>
                    </h3>
                    <p class="empty-state__desc">
                        <?= $search !== '' 
                            ? 'Coba kata kunci lain atau reset pencarian.' 
                            : 'Mulai tambahkan produk pertama Anda.' ?>
                    </p>
                    <?php if ($search !== ''): ?>
                        <a href="index.php" class="btn btn-ghost">Reset Pencarian</a>
                    <?php else: ?>
                        <a href="create.php" class="btn btn-primary">+ Tambah Produk</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <p>&copy; <?= date('Y') ?> Inventaris App · Tugas 8</p>
        </footer>

    </div>
</body>
</html>