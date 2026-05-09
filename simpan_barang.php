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
    $nama_barang = $_POST['nama_barang'];
    $merek = $_POST['merek'];
    $jenis = $_POST['jenis'];
    $satuan = $_POST['satuan'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    // Validasi data
    if (empty($nama_barang) || empty($harga) || $stok === '') {
        die("Data tidak lengkap. Nama, Harga, dan Stok wajib diisi.");
    }

    try {
        // Buat query SQL untuk INSERT
        $sql = "INSERT INTO barang (nama_barang, merek, jenis, satuan, harga, stok) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        // Eksekusi query
        $stmt->execute([
            $nama_barang,
            $merek,
            $jenis,
            $satuan,
            $harga,
            $stok
        ]);

        // Jika berhasil, redirect kembali ke halaman barang
        header("Location: barang.php?status=sukses");
        exit;

    } catch (PDOException $e) {
        // Jika gagal, tampilkan error
        die("Gagal menyimpan data: ". $e->getMessage());
    }

} else {
    // Jika file diakses langsung tanpa POST, redirect
    header("Location: barang.php");
    exit;
}
?>