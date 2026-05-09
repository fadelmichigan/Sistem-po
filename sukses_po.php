<?php
session_start();
include 'koneksi.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header('Location: login.php');
    exit;
}

$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pemesanan <= 0) {
    die("ID pesanan tidak valid!");
}

// Tambahkan kolom dibuat_oleh dan diketahui_oleh di SELECT
$sql = "
    SELECT 
        po.id,
        po.tanggal_pesan,
        po.waktu_kirim,
        po.total_harga,
        po.dibuat_oleh,      
        po.diketahui_oleh,   
        p.nama_perusahaan,
        p.email,
        p.npwp,
        p.no_telepon,
        b.nama_barang,
        b.merek,
        b.jenis,
        b.satuan,
        pd.harga_satuan,
        pd.jumlah
    FROM pemesanan po
    JOIN perusahaan p ON po.id_perusahaan = p.id
    JOIN pemesanan_detail pd ON pd.id_pemesanan = po.id
    JOIN barang b ON pd.id_barang = b.id
    WHERE po.id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_pemesanan]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    die("Data pesanan tidak ditemukan!");
}

$data = $items[0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PO Berhasil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .signature-section {
            margin-top: 30px;
        }
        /* Styling agar nama terlihat rapi */
        .signature-name {
            margin-top: 70px; /* Jarak untuk tanda tangan */
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase; /* Agar nama jadi huruf besar semua */
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow-lg border-success">
        <div class="card-body p-4 p-md-5">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-success fw-bold mb-0">✅ Purchase Order Berhasil Disimpan!</h3>
                <div>
                    <span class="me-3">👋 <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                </div>
            </div>

            <h5 class="fw-bold text-primary">Data Pembeli:</h5>
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

            <h5 class="fw-bold text-primary mt-4">Data Barang:</h5>
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

            <h5 class="fw-bold text-primary mt-4">Data Waktu:</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Tanggal Pesan:</strong> <?= htmlspecialchars(date('d F Y, H:i', strtotime($data['tanggal_pesan']))) ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Waktu Kirim:</strong> <?= htmlspecialchars(date('d F Y, H:i', strtotime($data['waktu_kirim']))) ?></p>
                </div>
            </div>

            <hr class="my-4">

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
            
            <hr class="my-4">

            <div class="mt-4 d-flex flex-wrap gap-2">
                <a href="form_pemesanan.php" class="btn btn-success">
                    + Buat PO Baru
                </a>
            </div>

        </div>
    </div>
</div>

</body>
</html>