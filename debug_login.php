<?php
// debug_login.php — jalankan sekali untuk cek DB & users
ini_set('display_errors',1); error_reporting(E_ALL);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=po2_db","root","");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

echo "<h3>Connected to DB</h3>";

// tampilkan struktur tabel users (kolom)
$stmt = $pdo->query("SHOW COLUMNS FROM users");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<h4>Columns in users</h4><pre>" . htmlspecialchars(print_r($cols, true)) . "</pre>";

// tampilkan isi users (tanpa menampilkan password mentah jika sensitif)
$stmt = $pdo->query("SELECT id, username, password, role FROM users");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<h4>Users rows</h4><pre>" . htmlspecialchars(print_r($rows, true)) . "</pre>";

echo "<p>Jika table kosong atau password bukan MD5, kamu perlu update DB atau gunakan reset password SQL.</p>";
