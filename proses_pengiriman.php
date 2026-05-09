<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan ini adalah admin yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

// 1. Cek apakah data dikirim via POST (dari form modal)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 2. Ambil data dari form modal
    $id_pemesanan = isset($_POST['id_pemesanan']) ? (int)$_POST['id_pemesanan'] : 0;
    $kurir = trim($_POST['kurir']);
    $no_resi = trim($_POST['no_resi']); // trim() untuk hapus spasi

    // Validasi
    if ($id_pemesanan <= 0 || empty($kurir)) {
        die("ID Pesanan atau Nama Kurir tidak boleh kosong.");
    }

    // Jika no_resi dikosongkan, simpan sebagai NULL
    if (empty($no_resi)) {
        $no_resi = null;
    }

    // Mulai Transaksi
    $pdo->beginTransaction();

    try {
        // 3. Query pertama: Update status di tabel 'pemesanan'
        // Ubah status dari 'Selesai' menjadi 'Dikirim'
        $stmt_update = $pdo->prepare("UPDATE pemesanan SET status = 'Dikirim' WHERE id = ? AND status = 'Selesai'");
        $stmt_update->execute([$id_pemesanan]);

        if ($stmt_update->rowCount() == 0) {
            throw new Exception("Status pesanan tidak bisa diubah (mungkin sudah dikirim).");
        }

        // 4. Query kedua: Masukkan data ke tabel 'pengiriman'
        $tanggal_kirim = date('Y-m-d H:i:s'); 
        $status_pengiriman = 'Dikirim';

        // Masukkan $kurir dan $no_resi yang baru
        $sql_insert = "INSERT INTO pengiriman (id_pemesanan, tanggal_kirim, kurir, no_resi, status) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$id_pemesanan, $tanggal_kirim, $kurir, $no_resi, $status_pengiriman]);

        // 5. Jika semua berhasil, simpan permanen
        $pdo->commit();

        // 6. Kembalikan ke halaman pengiriman dengan pesan sukses
        header("Location: pengiriman.php?status=kirim_sukses");
        exit;

    } catch (Exception $e) {
        // 7. Jika ada error, batalkan semua perubahan
        $pdo->rollBack();
        die("Gagal memproses pengiriman: " . $e->getMessage());
    }

} else {
    // Jika file diakses langsung (bukan via POST), redirect
    header("Location: pengiriman.php");
    exit;
}
?>