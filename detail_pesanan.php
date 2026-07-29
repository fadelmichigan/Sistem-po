<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header('Location: login.php');
    exit;
}

$id_user_login = $_SESSION['user_id'];
$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pemesanan <= 0) die("ID pesanan tidak valid!");

$sql = "
    SELECT 
        po.id, po.tanggal_pesan, po.waktu_kirim, po.total_harga,
        po.dibuat_oleh, po.diketahui_oleh, po.status, po.bukti_bayar,
        p.nama_perusahaan, p.email, p.npwp, p.no_telepon, p.id_user,
        b.nama_barang, b.merek, b.jenis, b.satuan,
        pd.harga_satuan, pd.jumlah,
        k.kurir, k.no_resi
    FROM pemesanan po
    JOIN perusahaan p ON po.id_perusahaan = p.id
    JOIN pemesanan_detail pd ON pd.id_pemesanan = po.id
    JOIN barang b ON pd.id_barang = b.id
    LEFT JOIN pengiriman k ON k.id_pemesanan = po.id
    WHERE po.id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_pemesanan]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) die("Data pesanan tidak ditemukan!");
$data = $items[0];

// Keamanan: Cek apakah ini pesanan milik user yang login
if ($data['id_user'] != $id_user_login) {
    die("Akses ditolak. Ini bukan pesanan Anda.");
}

// Cek Pesan Sukses/Gagal dari proses upload
$pesan = '';
$alert_type = 'info';
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'upload_sukses') {
        $pesan = "Bukti pembayaran berhasil diunggah! Menunggu verifikasi admin.";
        $alert_type = 'success';
    } elseif ($_GET['status'] == 'gagal') {
        $pesan = "Gagal mengunggah: " . htmlspecialchars($_GET['msg'] ?? 'Kesalahan tidak diketahui.');
        $alert_type = 'danger';
    }
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
        .signature-name { margin-top: 70px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="daftar_pesanan.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
        </a>
        <button onclick="window.print()" class="btn btn-info text-white">
            <i class="bi bi-printer-fill"></i> Cetak Halaman Ini
        </button>
    </div>

    <?php if($pesan): ?>
        <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show no-print" role="alert">
            <?= $pesan ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($data['bukti_bayar'])): ?>
        <div class="card shadow-sm border-primary mb-4 no-print">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-wallet2"></i> Informasi Pembayaran & QRIS
            </div>
            <div class="card-body bg-primary bg-opacity-10 p-4">
                <div class="row align-items-center">
                    
                    <!-- Sisi Kiri: Gambar QRIS -->
                    <div class="col-md-5 text-center mb-4 mb-md-0 border-end border-primary border-opacity-25">
                        <h6 class="fw-bold text-primary mb-3">Scan QRIS untuk Membayar</h6>
                        <div class="bg-white p-2 d-inline-block rounded shadow-sm">
                            <?php 
                                // Cek apakah gambar QRIS statis tersedia di folder uploads
                                $file_qris = 'uploads/qris-dummy.png'; 
                                if (file_exists($file_qris)): 
                            ?>
                                <img src="<?= $file_qris ?>" alt="QRIS" class="img-fluid" style="max-width: 200px;">
                            <?php else: ?>
                                <!-- Tampilan jika gambar QRIS belum ada -->
                                <div class="text-muted p-4 border rounded" style="width: 200px; height: 200px; display:flex; align-items:center; justify-content:center; flex-direction:column;">
                                    <i class="bi bi-qr-code-scan display-4"></i>
                                    <span class="small mt-2">Gambar QRIS belum diatur (simpan qris-dummy.png di folder uploads)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="mt-2 mb-0 small text-muted">Menerima pembayaran dari semua e-Wallet & M-Banking.</p>
                    </div>

                    <!-- Sisi Kanan: Rekening & Upload -->
                    <div class="col-md-7 ps-md-4">
                        <h6 class="fw-bold text-dark">Atau Transfer Manual:</h6>
                        <div class="p-3 bg-white rounded border mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Bank BCA</span>
                                <strong class="fs-5">123 456 7890</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Atas Nama</span>
                                <strong>PT Sistem PO Indonesia</strong>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark mt-4 mb-2"><i class="bi bi-cloud-arrow-up"></i> Upload Bukti Pembayaran</h6>
                        <p class="small text-muted mb-3">Pesanan baru akan diproses setelah bukti transfer diunggah dan diverifikasi.</p>
                        
                        <form action="proses_upload_bukti.php" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
                            <input type="hidden" name="id_pemesanan" value="<?= $data['id'] ?>">
                            <input type="file" name="bukti_bayar" class="form-control" accept="image/*,application/pdf" required>
                            <button type="submit" class="btn btn-primary fw-bold px-4">Upload</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-success mb-4 no-print">
            <i class="bi bi-check-circle-fill"></i> Bukti pembayaran telah diunggah. 
            <a href="uploads/<?= htmlspecialchars($data['bukti_bayar']) ?>" target="_blank" class="alert-link">Lihat Bukti Anda</a>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary fw-bold mb-0">Detail Purchase Order #<?= $data['id'] ?></h3>
                <div>
                    <?php
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

            <table class="table table-bordered align-middle mt-4">
                <thead class="table-light">
                    <tr>
                        <th>Nama Produk</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): 
                        $subtotal = (float)$item['harga_satuan'] * (int)$item['jumlah'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                        <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($item['jumlah']) ?> <?= htmlspecialchars($item['satuan']) ?></td>
                        <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold fs-5">
                        <td colspan="3" class="text-end">Grand Total:</td>
                        <td class="text-success">Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>

            <?php if ($data['status'] == 'Dikirim' || $data['status'] == 'Diterima'): ?>
            <div class="alert alert-info mt-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-truck"></i> Informasi Pengiriman:</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Jasa Ekspedisi / Kurir:</strong></p>
                        <p class="fs-5"><?= htmlspecialchars($data['kurir'] ?? '-') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Nomor Resi:</strong></p>
                        <p class="fs-5 fw-bold font-monospace"><?= htmlspecialchars($data['no_resi'] ?? '-') ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>