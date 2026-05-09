<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan ini adalah admin yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

// 1. Ambil ID pemesanan dari URL
$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pemesanan <= 0) {
    die("ID Pesanan tidak valid.");
}

// Mulai Transaksi
$pdo->beginTransaction();

try {
    // 2. Query pertama: Update status di tabel 'pemesanan'
    // Ubah status dari 'Baru' menjadi 'Selesai' (atau 'Diproses' jika mau)
    $stmt_update = $pdo->prepare("UPDATE pemesanan SET status = 'Selesai' WHERE id = ? AND status = 'Baru'");
    $stmt_update->execute([$id_pemesanan]);

    // Cek apakah ada baris yang ter-update. 
    // Jika tidak ada (misal, statusnya bukan 'Baru'), batalkan.
    if ($stmt_update->rowCount() == 0) {
        throw new Exception("Status pesanan tidak bisa diubah (mungkin sudah diproses).");
    }

    // 3. Query kedua: Masukkan data ke tabel 'penerimaan'
    // Kita gunakan waktu saat ini sebagai tanggal terima
    $tanggal_terima = date('Y-m-d H:i:s'); 
    $diterima_oleh = $_SESSION['username'] ?? 'Admin'; // Ambil nama admin dari session
    $status_penerimaan = 'Selesai';

    $sql_insert = "INSERT INTO penerimaan (id_pemesanan, tanggal_terima, diterima_oleh, status) 
                   VALUES (?, ?, ?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([$id_pemesanan, $tanggal_terima, $diterima_oleh, $status_penerimaan]);

    // 4. Jika semua berhasil, simpan permanen
    $pdo->commit();

    // 5. Kembalikan ke halaman penerimaan dengan pesan sukses
    header("Location: penerimaan.php?status=proses_sukses");
    exit;

} catch (Exception $e) {
    // 6. Jika ada error, batalkan semua perubahan
    $pdo->rollBack();
    die("Gagal memproses penerimaan: " . $e->getMessage());
}
?>