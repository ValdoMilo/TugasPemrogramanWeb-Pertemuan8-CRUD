<?php
require 'Config.php';
$db = Database::getInstance()->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('ID tidak valid!', 'error');
    header("Location: index.php");
    exit;
}

try {
    $db->beginTransaction();

    // 1. Ambil nama produk untuk log
    $stmtCek = $db->prepare("SELECT nama_produk FROM produk WHERE id = ?");
    $stmtCek->execute([$id]);
    $data = $stmtCek->fetch();

    if ($data) {
        $nama = $data['nama_produk'];

        // 2. Hapus produk
        $stmtDel = $db->prepare("DELETE FROM produk WHERE id = ?");
        $stmtDel->execute([$id]);

        // 3. Log aktivitas
        $stmtLog = $db->prepare("INSERT INTO log_aktivitas (aksi) VALUES (?)");
        $stmtLog->execute(["Menghapus produk: $nama (ID: $id)"]);

        $db->commit();
        setFlash("Produk \"$nama\" berhasil dihapus!", 'success');
    } else {
        $db->rollBack();
        setFlash('Data tidak ditemukan!', 'error');
    }
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    setFlash('Gagal menghapus data: ' . $e->getMessage(), 'error');
}

header("Location: index.php");
exit;
?>