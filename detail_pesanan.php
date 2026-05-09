<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan ini adalah user yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header('Location: login.php');
    exit;
}

// 1. Ambil ID user yang sedang login
$id_user_login = $_SESSION['user_id'];

// 2. Ambil ID pesanan dari URL
$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pemesanan <= 0) {
    die("ID pesanan tidak valid!");
}

// 3. Query untuk mengambil semua data pesanan
// =======================================================
// PERBAIKAN SQL DI SINI
// =======================================================
// Kita tambahkan LEFT JOIN ke tabel 'pengiriman'
$sql = "
    SELECT 
        po.id, po.tanggal_pesan, po.waktu_kirim, po.total_harga,
        po.dibuat_oleh, po.diketahui_oleh, po.status,
        p.nama_perusahaan, p.email, p.npwp, p.no_telepon, p.id_user,
        b.nama_barang, b.merek, b.jenis, b.satuan,
        pd.harga_satuan, pd.jumlah,
        k.kurir, k.no_resi  -- Ambil kurir dan no_resi dari tabel pengiriman (alias 'k')
    FROM pemesanan po
    JOIN perusahaan p ON po.id_perusahaan = p.id
    JOIN pemesanan_detail pd ON pd.id_pemesanan = po.id
    JOIN barang b ON pd.id_barang = b.id
    LEFT JOIN pengiriman k ON k.id_pemesanan = po.id  -- Pakai LEFT JOIN
    WHERE po.id = ?
";
// =======================================================
// AKHIR PERBAIKAN SQL
// =======================================================

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_pemesanan]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    die("Data pesanan tidak ditemukan!");
}

// 4. Validasi Keamanan
$data = $items[0]; // Ambil data baris pertama untuk info utama
if ($data['id_user'] != $id_user_login) {
    die("Akses ditolak. Anda tidak memiliki izin untuk melihat pesanan ini.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pesanan #<?= $data['id'] ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .signature-section { margin-top: 30px; }
        .signature-name {
            margin-top: 70px; /* Jarak untuk tanda tangan */
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    
    <!-- Tombol Navigasi (no-print) -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="daftar_pesanan.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
        </a>
        <button onclick="window.print()" class="btn btn-info">
            <i class="bi bi-printer-fill"></i> Cetak Halaman Ini
        </button>
    </div>

    <!-- Konten Detail Pesanan -->
    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary fw-bold mb-0">Detail Purchase Order #<?= $data['id'] ?></h3>
                <div>
                    <?php
                        // Beri warna status
                        $status = htmlspecialchars($data['status']);
                        $badge_class = 'bg-secondary';
                        if ($status == 'Baru') { $badge_class = 'bg-warning text-dark'; }
                        elseif ($status == 'Selesai') { $badge_class = 'bg-info'; }
                        elseif ($status == 'Dikirim') { $badge_class = 'bg-primary'; }
                        elseif ($status == 'Diterima') { $badge_class = 'bg-success'; }
                    ?>
                    <span class="badge <?= $badge_class ?> fs-6">Status: <?= $status ?></span>
                </div>
            </div>

            <h5 class="fw-bold text-dark">Data Pembeli:</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Nama Perusahaan:</strong> <?= htmlspecialchars($data['nama_perusahaan']) ?></p>
                    <p class="mb-1"><strong>NPWP:</strong> <?= htmlspecialchars($data['npwp']) ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Email Perusahaan:</strong> <?= htmlspecialchars($data['email']) ?></p>
                    <p class="mb-1"><strong>No. Telepon:</strong> <?= htmlspecialchars($data['no_telepon']) ?></p>
                </div>
            </div>

            <h5 class="fw-bold text-dark mt-4">Data Barang:</h5>
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
                    <?php 
                    foreach ($items as $item): 
                        $subtotal = (float)$item['harga_satuan'] * (int)$item['jumlah'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama_barang']) ?> (<?= htmlspecialchars($item['jenis']) ?>)</td>
                        <td><?= htmlspecialchars($item['merek']) ?></td>
                        <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($item['jumlah']) ?> <?= htmlspecialchars($item['satuan']) ?></td>
                        <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold fs-5">
                        <td colspan="4" class="text-end">Grand Total:</td>
                        <td class="text-success">Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>

            <h5 class="fw-bold text-dark mt-4">Data Waktu:</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Tanggal Pesan:</strong> <?= htmlspecialchars(date('d F Y, H:i', strtotime($data['tanggal_pesan']))) ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Waktu Kirim:</strong> <?= htmlspecialchars(date('d F Y, H:i', strtotime($data['waktu_kirim']))) ?></p>
                </div>
            </div>

            <!-- 
            =======================================================
            TAMBAHAN BARU: DATA PENGIRIMAN
            =======================================================
            -->
            <?php if ($data['status'] == 'Dikirim' || $data['status'] == 'Diterima'): ?>
            <h5 class="fw-bold text-dark mt-4">Data Pengiriman:</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Kurir / Ekspedisi:</strong> <?= htmlspecialchars($data['kurir'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>No. Resi:</strong> <?= htmlspecialchars($data['no_resi'] ?? 'N/A') ?></p>
                </div>
            </div>
            <?php endif; ?>
            <!-- =================================================== -->
            
            <hr class="my-4">

            <!-- TANDA TANGAN DINAMIS -->
            <div class="row signature-section">
                <div class="col-md-6">
                    <h6 class="fw-bold">Catatan:</h6>
                    <p class="fst-italic text-muted small">
                        - Harap lakukan pembayaran sebelum Waktu Kirim.<br>
                        - Barang yang sudah dipesan tidak dapat dibatalkan.<br>
                        - Terima kasih atas kepercayaan Anda.
                    </p>
                </div>

                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-6">
                            <p class="mb-0">Dibuat,</p>
                            <p class="signature-name"><?= htmlspecialchars($data['dibuat_oleh']) ?></p>
                            <p class="small text-muted">Staff/Admin</p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0">Diketahui,</p>
                            <p class="signature-name"><?= htmlspecialchars($data['diketahui_oleh']) ?></p>
                            <p class="small text-muted">Manager/Pimpinan</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>