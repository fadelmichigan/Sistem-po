<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan ini adalah admin yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

// Ambil ID barang dari URL
$id_barang = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_barang <= 0) {
    die("ID Barang tidak valid.");
}

try {
    // Coba hapus barang
    $sql = "DELETE FROM barang WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_barang]);

    // Kembalikan ke halaman daftar
    header("Location: barang.php?status=hapus_sukses");
    exit;

} catch (PDOException $e) {
    // Cek jika errornya karena foreign key (barang ada di pesanan)
    if ($e->errorInfo[1] == 1451) {
        header("Location: barang.php?status=hapus_gagal&error=barang_ada_di_pesanan");
    } else {
        // Error lain
        die("Gagal menghapus data: " . $e->getMessage());
    }
    exit;
}
?>