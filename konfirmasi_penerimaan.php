<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan ini adalah user yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    die("Akses ditolak.");
}

// Cek apakah data dikirim via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Ambil ID pemesanan dari form
    $id_pemesanan = isset($_POST['id_pemesanan']) ? (int)$_POST['id_pemesanan'] : 0;
    
    // Ambil ID user yang sedang login
    $id_user_login = $_SESSION['user_id'];

    if ($id_pemesanan <= 0) {
        die("ID Pesanan tidak valid.");
    }

    try {
        // 1. Dapatkan ID Perusahaan berdasarkan user yang login
        $stmt_perusahaan = $pdo->prepare("SELECT id FROM perusahaan WHERE id_user = ?");
        $stmt_perusahaan->execute([$id_user_login]);
        $perusahaan = $stmt_perusahaan->fetch(PDO::FETCH_ASSOC);
        $id_perusahaan = $perusahaan ? $perusahaan['id'] : 0;

        // 2. Query UPDATE: Ubah status MENJADI 'Diterima'
        // TAMBAHAN KEAMANAN: Pastikan statusnya 'Dikirim' DAN id_perusahaan-nya cocok
        $sql = "UPDATE pemesanan 
                SET status = 'Diterima' 
                WHERE id = ? AND status = 'Dikirim' AND id_perusahaan = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_pemesanan, $id_perusahaan]);

        // Cek apakah berhasil di-update
        if ($stmt->rowCount() > 0) {
            // Berhasil
            header("Location: daftar_pesanan.php?status=terima_sukses");
        } else {
            // Gagal (mungkin pesanan bukan miliknya atau status salah)
            header("Location: daftar_pesanan.php?status=terima_gagal");
        }
        exit;

    } catch (PDOException $e) {
        die("Gagal memproses konfirmasi: " . $e->getMessage());
    }

} else {
    // Jika file diakses langsung, redirect
    header("Location: daftar_pesanan.php");
    exit;
}
?>