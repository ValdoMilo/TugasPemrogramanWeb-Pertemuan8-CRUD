<?php
require 'Config.php';
$db = Database::getInstance()->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('ID tidak valid!', 'error');
    header("Location: index.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->execute([$id]);
$produk = $stmt->fetch();

if (!$produk) {
    setFlash('Data tidak ditemukan!', 'error');
    header("Location: index.php");
    exit;
}

$kategori = $db->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$supplier = $db->query("SELECT * FROM supplier ORDER BY nama_supplier")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama_produk'] ?? '');
    $harga = $_POST['harga'] ?? 0;
    $stok = $_POST['stok'] ?? 0;
    $kat_id = $_POST['kategori_id'] ?? null;
    $sup_id = $_POST['supplier_id'] ?? null;

    if ($nama === '' || $harga === '' || $stok === '' || !$kat_id || !$sup_id) {
        setFlash('Semua field wajib diisi!', 'error');
    } else {
        $updateStmt = $db->prepare("UPDATE produk SET nama_produk=?, harga=?, stok=?, kategori_id=?, supplier_id=? WHERE id=?");
        try {
            $updateStmt->execute([$nama, $harga, $stok, $kat_id, $sup_id, $id]);
            setFlash('Data produk berhasil diperbarui!', 'success');
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            setFlash('Gagal mengupdate data: ' . $e->getMessage(), 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Edit Produk</title>
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
                    <h1 class="topbar__title">Edit Produk</h1>
                    <p class="topbar__subtitle">ID: #<?= (int)$produk['id'] ?></p>
                </div>
            </div>
        </header>

        <?php displayFlash(); ?>

        <div class="card">
            <form method="POST" class="form">
                <div class="form__group">
                    <label for="nama_produk" class="form__label">Nama Produk <span class="req">*</span></label>
                    <input type="text" id="nama_produk" name="nama_produk" class="form__input" 
                           value="<?= sanitize($produk['nama_produk']) ?>" required maxlength="150">
                </div>

                <div class="form__row">
                    <div class="form__group">
                        <label for="harga" class="form__label">Harga (Rp) <span class="req">*</span></label>
                        <input type="number" id="harga" name="harga" class="form__input" 
                               value="<?= sanitize($produk['harga']) ?>" min="0" step="1" required>
                    </div>

                    <div class="form__group">
                        <label for="stok" class="form__label">Stok <span class="req">*</span></label>
                        <input type="number" id="stok" name="stok" class="form__input" 
                               value="<?= sanitize($produk['stok']) ?>" min="0" step="1" required>
                    </div>
                </div>

                <div class="form__group">
                    <label for="kategori_id" class="form__label">Kategori <span class="req">*</span></label>
                    <select id="kategori_id" name="kategori_id" class="form__input" required>
                        <?php foreach($kategori as $k): ?>
                            <option value="<?= (int)$k['id'] ?>" <?= $k['id'] == $produk['kategori_id'] ? 'selected' : '' ?>>
                                <?= sanitize($k['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form__group">
                    <label for="supplier_id" class="form__label">Supplier <span class="req">*</span></label>
                    <select id="supplier_id" name="supplier_id" class="form__input" required>
                        <?php foreach($supplier as $s): ?>
                            <option value="<?= (int)$s['id'] ?>" <?= $s['id'] == $produk['supplier_id'] ? 'selected' : '' ?>>
                                <?= sanitize($s['nama_supplier']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form__actions">
                    <button type="submit" class="btn btn-primary btn-block">Update Produk</button>
                    <a href="index.php" class="btn btn-ghost btn-block">Batal</a>
                </div>
            </form>
        </div>

    </div>
</body>
</html>