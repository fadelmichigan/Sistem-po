<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// 1. Pastikan user login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

$id_user_login = $_SESSION['user_id'];
$username = $_SESSION['username'];
$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pemesanan <= 0) die("ID pesanan tidak valid!");

// 2. Ambil data utama PO
$sql_po = "
    SELECT 
        po.id, po.tanggal_pesan, po.waktu_kirim, po.total_harga,
        po.dibuat_oleh, po.diketahui_oleh,
        p.nama_perusahaan, p.email, p.npwp, p.no_telepon, p.id_user
    FROM pemesanan po
    JOIN perusahaan p ON po.id_perusahaan = p.id
    WHERE po.id = ?
";
$stmt = $pdo->prepare($sql_po);
$stmt->execute([$id_pemesanan]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Data pesanan tidak ditemukan!");

// Proteksi agar user hanya bisa melihat pesanan perusahaannya sendiri
if ($data['id_user'] != $id_user_login) {
    die("Akses ditolak. Ini bukan pesanan Anda.");
}

// 3. Ambil data detail barang
$sql_detail = "
    SELECT 
        b.nama_barang, b.merek, b.satuan,
        pd.harga_satuan, pd.jumlah, pd.subtotal
    FROM pemesanan_detail pd
    JOIN barang b ON pd.id_barang = b.id
    WHERE pd.id_pemesanan = ?
";
$stmt_detail = $pdo->prepare($sql_detail);
$stmt_detail->execute([$id_pemesanan]);
$items = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PO Berhasil Disimpan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .ttd-box { text-align: center; margin-top: 20px; }
        .ttd-name { font-weight: bold; text-decoration: underline; margin-top: 60px; text-transform: uppercase; }
        .section-title { color: #0d6efd; font-weight: bold; margin-bottom: 15px; margin-top: 30px; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    
    <!-- Bagian Header Sukses -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-success fw-bold">
            <i class="bi bi-check-square-fill"></i> Purchase Order Berhasil Disimpan!
        </h2>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted">👋 <?= htmlspecialchars($username) ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>

    <!-- Peringatan Limit (Jika Ada) -->
    <?php if(isset($_GET['warning']) && $_GET['warning'] == 'limit_belanja'): ?>
        <div class="alert alert-warning mb-4">
            <i class="bi bi-exclamation-triangle-fill"></i> <strong>Peringatan Limit Belanja:</strong> Transaksi hari ini telah melebihi batas limit harian perusahaan Anda. Pesanan tetap disimpan dan menunggu persetujuan.
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 p-4">
        
        <!-- Data Pembeli -->
        <h5 class="section-title">Data Pembeli:</h5>
        <div class="row mb-3">
            <div class="col-md-6">
                <p class="mb-1"><strong>Nama Perusahaan:</strong> <?= htmlspecialchars($data['nama_perusahaan']) ?></p>
                <p class="mb-1"><strong>NPWP:</strong> <?= htmlspecialchars($data['npwp'] ?: '-') ?></p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Email Perusahaan:</strong> <?= htmlspecialchars($data['email']) ?></p>
                <p class="mb-1"><strong>No. Telepon:</strong> <?= htmlspecialchars($data['no_telepon'] ?: '-') ?></p>
            </div>
        </div>

        <!-- Data Barang -->
        <h5 class="section-title">Data Barang:</h5>
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nama Produk</th>
                    <th>Merek</th>
                    <th>Harga Satuan</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($item['merek']) ?></td>
                    <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($item['jumlah']) ?> <?= htmlspecialchars($item['satuan']) ?></td>
                    <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fs-5">
                    <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                    <td class="text-success fw-bold">Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Data Waktu -->
        <h5 class="section-title">Data Waktu:</h5>
        <div class="row mb-4">
            <div class="col-md-6">
                <p class="mb-1"><strong>Tanggal Pesan:</strong> <?= date('d F Y, H:i', strtotime($data['tanggal_pesan'])) ?></p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Waktu Kirim:</strong> <?= date('d F Y, H:i', strtotime($data['waktu_kirim'])) ?></p>
            </div>
        </div>
        
        <hr>

        <!-- Catatan dan Tanda Tangan -->
        <div class="row mt-4">
            <div class="col-md-6 text-muted small">
                <strong>Catatan:</strong><br>
                <em>- Harap lakukan pembayaran sebelum Waktu Kirim.<br>
                - Barang yang sudah dipesan tidak dapat dibatalkan.<br>
                - Terima kasih atas kepercayaan Anda.</em>
            </div>
            <div class="col-md-3 ttd-box">
                <p class="mb-0">Dibuat,</p>
                <p class="ttd-name"><?= htmlspecialchars($data['dibuat_oleh']) ?></p>
                <p class="small text-muted">Staff/Admin</p>
            </div>
            <div class="col-md-3 ttd-box">
                <p class="mb-0">Diketahui,</p>
                <p class="ttd-name"><?= htmlspecialchars($data['diketahui_oleh']) ?></p>
                <p class="small text-muted">Manager/Pimpinan</p>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- PERUBAHAN DI SINI: TOMBOL QRIS DAN BUAT PO BARU          -->
    <!-- ======================================================== -->
    <div class="mt-4 mb-5 d-flex gap-2">
        
        <!-- Tombol Utama untuk langsung bayar/scan QRIS -->
        <a href="detail_pesanan.php?id=<?= $data['id'] ?>" class="btn btn-primary btn-lg shadow-sm">
            <i class="bi bi-wallet2"></i> Informasi Pembayaran & QRIS
        </a>
        
        <!-- Tombol Tambahan -->
        <a href="form_pemesanan.php" class="btn btn-success btn-lg shadow-sm">
            <i class="bi bi-plus-lg"></i> Buat PO Baru
        </a>
        
        <!-- Link kembali ke riwayat -->
        <a href="daftar_pesanan.php" class="btn btn-outline-secondary btn-lg ms-auto">
            Ke Riwayat Pesanan <i class="bi bi-arrow-right"></i>
        </a>
        
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>