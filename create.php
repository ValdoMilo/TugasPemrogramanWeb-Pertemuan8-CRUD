<?php
require 'Config.php';
$db = Database::getInstance()->getConnection();

$kategori = $db->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$supplier = $db->query("SELECT * FROM supplier ORDER BY nama_supplier")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama_produk'] ?? '');
    $harga = $_POST['harga'] ?? 0;
    $stok = $_POST['stok'] ?? 0;
    $kategori_id = $_POST['kategori_id'] ?? null;
    $supplier_id = $_POST['supplier_id'] ?? null;

    // Validasi
    if ($nama === '' || $harga === '' || $stok === '' || !$kategori_id || !$supplier_id) {
        setFlash('Semua field wajib diisi!', 'error');
    } elseif (!is_numeric($harga) || $harga < 0) {
        setFlash('Harga harus berupa angka positif!', 'error');
    } elseif (!is_numeric($stok) || $stok < 0) {
        setFlash('Stok harus berupa angka positif!', 'error');
    } else {
        // [Req 7] Prepared statement
        $stmt = $db->prepare("INSERT INTO produk (nama_produk, harga, stok, kategori_id, supplier_id) VALUES (?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$nama, $harga, $stok, $kategori_id, $supplier_id]);
            setFlash('Produk berhasil ditambahkan!', 'success');
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            setFlash('Gagal menambahkan data: ' . $e->getMessage(), 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Tambah Produk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app app--narrow">

        <header class="topbar">
            <div class="topbar__left">
                <a href="index.php" class="back-btn" title="Kembali">←</a>
                <div>
                    <h1 class="topbar__title">Tambah Produk</h1>
                    <p class="topbar__subtitle">Isi data produk baru</p>
                </div>
            </div>
        </header>

        <?php displayFlash(); ?>

        <div class="card">
            <form method="POST" class="form">
                <div class="form__group">
                    <label for="nama_produk" class="form__label">Nama Produk <span class="req">*</span></label>
                    <input type="text" id="nama_produk" name="nama_produk" class="form__input" 
                           placeholder="Contoh: Laptop Asus ROG" required maxlength="150">
                </div>

                <div class="form__row">
                    <div class="form__group">
                        <label for="harga" class="form__label">Harga (Rp) <span class="req">*</span></label>
                        <input type="number" id="harga" name="harga" class="form__input" 
                               placeholder="15000000" min="0" step="1" required>
                    </div>

                    <div class="form__group">
                        <label for="stok" class="form__label">Stok <span class="req">*</span></label>
                        <input type="number" id="stok" name="stok" class="form__input" 
                               placeholder="10" min="0" step="1" required>
                    </div>
                </div>

                <div class="form__group">
                    <label for="kategori_id" class="form__label">Kategori <span class="req">*</span></label>
                    <select id="kategori_id" name="kategori_id" class="form__input" required>
                        <option value="">— Pilih Kategori —</option>
                        <?php foreach($kategori as $k): ?>
                            <option value="<?= (int)$k['id'] ?>"><?= sanitize($k['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form__group">
                    <label for="supplier_id" class="form__label">Supplier <span class="req">*</span></label>
                    <select id="supplier_id" name="supplier_id" class="form__input" required>
                        <option value="">— Pilih Supplier —</option>
                        <?php foreach($supplier as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"><?= sanitize($s['nama_supplier']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form__actions">
                    <button type="submit" class="btn btn-primary btn-block">Simpan Produk</button>
                    <a href="index.php" class="btn btn-ghost btn-block">Batal</a>
                </div>
            </form>
        </div>

    </div>
</body>
</html>