<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan ini adalah admin yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

// Ambil ID perusahaan dari URL
$id_perusahaan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_perusahaan <= 0) {
    die("ID Customer tidak valid.");
}

try {
    // BUKAN MENGHAPUS, TAPI MENGUBAH STATUS
    $sql = "UPDATE perusahaan SET status = 'Nonaktif' WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_perusahaan]);

    // Kembalikan ke halaman daftar
    header("Location: customer.php?status=arsip_sukses");
    exit;

} catch (PDOException $e) {
    die("Gagal mengarsipkan data: " . $e->getMessage());
    exit;
}
?>