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
    $nama_distributor = $_POST['nama_distributor'];
    $kontak_person = $_POST['kontak_person'];
    $no_telepon = $_POST['no_telepon'];
    $alamat = $_POST['alamat'];

    // Validasi data
    if (empty($nama_distributor)) {
        die("Data tidak lengkap. Nama Distributor wajib diisi.");
    }

    try {
        // Buat query SQL untuk INSERT
        $sql = "INSERT INTO distributor (nama_distributor, kontak_person, no_telepon, alamat) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        // Eksekusi query
        $stmt->execute([
            $nama_distributor,
            $kontak_person,
            $no_telepon,
            $alamat
        ]);

        // Jika berhasil, redirect kembali ke halaman distributor
        header("Location: distributor.php?status=sukses");
        exit;

    } catch (PDOException $e) {
        // Jika gagal, tampilkan error
        die("Gagal menyimpan data: ". $e->getMessage());
    }

} else {
    // Jika file diakses langsung tanpa POST, redirect
    header("Location: distributor.php");
    exit;
}
?>