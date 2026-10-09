<?php
session_start();

// [Req 3] Koneksi PDO dengan Singleton pattern
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = 'localhost';
        $db = 'inventaris_db';
        $user = 'root';
        $pass = '';

        try {
            $this->conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Koneksi gagal: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}

// [Req 9] Flash Message
function setFlash($pesan, $tipe = 'success') {
    $_SESSION['flash'] = ['pesan' => $pesan, 'tipe' => $tipe];
}

function displayFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo "<div class='alert alert-{$flash['tipe']}' role='alert'>" . htmlspecialchars($flash['pesan']) . "</div>";
        unset($_SESSION['flash']);
    }
}

// [Req 8] Sanitasi output
function sanitize($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

// Format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}
?>