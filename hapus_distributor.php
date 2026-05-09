<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan ini adalah admin yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

// Ambil ID distributor dari URL
$id_distributor = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_distributor <= 0) {
    die("ID Distributor tidak valid.");
}

try {
    // Hapus distributor secara permanen
    $sql = "DELETE FROM distributor WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_distributor]);

    // Kembalikan ke halaman daftar
    header("Location: distributor.php?status=hapus_sukses");
    exit;

} catch (PDOException $e) {
    // Tangani jika ada error
    die("Gagal menghapus data: " . $e->getMessage());
    exit;
}
?>