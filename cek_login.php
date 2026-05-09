<?php
session_start();
require 'koneksi.php'; // Menyediakan $pdo
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? ''); // Password plain text dari form

if ($username === '' || $password === '') {
    echo "<script>alert('Username atau password kosong'); window.location='login.php';</script>";
    exit;
}

// 1. Ambil data user dari database
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<script>alert('User tidak ditemukan'); window.location='login.php';</script>";
    exit;
}

// 2. Ambil password yang ada di database
$dbPass = $user['password'];

$loginSuccess = false;

// 3. Cek Login (Prioritas: Cara baru, lalu cara lama)

// Cara BARU (password_verify untuk hash)
if (password_verify($password, $dbPass)) {
    $loginSuccess = true;
}
// Cara LAMA (MD5)
elseif (strlen($dbPass) === 32 && ctype_xdigit($dbPass) && md5($password) === $dbPass) {
    $loginSuccess = true;
}
// Cara LAMA (Plain Text)
elseif ($password === $dbPass) {
    $loginSuccess = true;
}

// 4. Proses Hasil Login
if ($loginSuccess) {
    // Regenerate session ID untuk keamanan
    session_regenerate_id(true); 
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
    // Arahkan berdasarkan role
    if ($user['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        // =======================================================
        // PERUBAHAN DI SINI: Arahkan user ke index.php
        // =======================================================
        header("Location: index.php");
    }
    exit;
}

// Jika semua cara gagal
echo "<script>alert('Login gagal — username atau password salah.'); window.location='login.php';</script>";
exit;
?>