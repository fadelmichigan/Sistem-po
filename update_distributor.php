<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan ini adalah admin yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

// Cek apakah data dikirim via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil data dari form
    $id = $_POST['id'];
    $nama_distributor = $_POST['nama_distributor'];
    $kontak_person = $_POST['kontak_person'];
    $no_telepon = $_POST['no_telepon'];
    $alamat = $_POST['alamat'];

    // Validasi data
    if (empty($id) || empty($nama_distributor)) {
        die("Data tidak lengkap. Gagal mengupdate.");
    }

    try {
        // Buat query SQL untuk UPDATE
        $sql = "UPDATE distributor 
                SET nama_distributor = ?, kontak_person = ?, no_telepon = ?, alamat = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        
        // Eksekusi query
        $stmt->execute([
            $nama_distributor,
            $kontak_person,
            $no_telepon,
            $alamat,
            $id // ID diletakkan terakhir untuk WHERE
        ]);

        // Jika berhasil, redirect kembali ke halaman distributor
        header("Location: distributor.php?status=update_sukses");
        exit;

    } catch (PDOException $e) {
        // Jika gagal, tampilkan error
        die("Gagal mengupdate data: ". $e->getMessage());
    }

} else {
    // Jika file diakses langsung tanpa POST, redirect
    header("Location: distributor.php");
    exit;
}
?>