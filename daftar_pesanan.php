<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan ini adalah user yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

// Ambil ID user yang sedang login
$id_user_login = $_SESSION['user_id'];

// 1. Dapatkan ID Perusahaan berdasarkan user yang login
$stmt_perusahaan = $pdo->prepare("SELECT id FROM perusahaan WHERE id_user = ?");
$stmt_perusahaan->execute([$id_user_login]);
$perusahaan = $stmt_perusahaan->fetch(PDO::FETCH_ASSOC);

$id_perusahaan = $perusahaan ? $perusahaan['id'] : 0;

// 2. Ambil semua data pemesanan HANYA untuk perusahaan ini
$sql = "SELECT * FROM pemesanan 
        WHERE id_perusahaan = ? 
        ORDER BY tanggal_pesan DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_perusahaan]);
$daftar_pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pesanan Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    
    <!-- Header Halaman User -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Riwayat Pesanan Saya</h3>
            <small class="text-muted">Halo, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>!</small>
        </div>
        <div>
            <a href="form_pemesanan.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Buat PO Baru
            </a>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
    
    <!-- NOTIFIKASI ALERT -->
    <?php if(isset($_GET['status'])): ?>
        <?php
            $alert_class = 'alert-success'; // Default
            $message = '';

            if ($_GET['status'] == 'terima_sukses') {
                $message = 'Konfirmasi penerimaan barang berhasil!';
            } elseif ($_GET['status'] == 'terima_gagal') {
                $alert_class = 'alert-danger';
                $message = 'Gagal melakukan konfirmasi. Pesanan mungkin belum dikirim.';
            }
        ?>
        <?php if($message): ?>
        <div class="alert <?= $alert_class ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    <!-- BATAS NOTIFIKASI -->

    <!-- Tabel Riwayat Pesanan -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Tanggal Pesan</th>
                        <th>Waktu Kirim (Diminta)</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_pesanan)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Anda belum memiliki riwayat pesanan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_pesanan as $po): ?>
                            <tr>
                                <td>
                                    <!-- ID SEKARANG BISA DIKLIK -->
                                    <a href="detail_pesanan.php?id=<?= $po['id'] ?>" class="fw-bold text-decoration-none">
                                        #<?= htmlspecialchars($po['id']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($po['tanggal_pesan']))) ?></td>
                                <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($po['waktu_kirim']))) ?></td>
                                <td>Rp <?= number_format($po['total_harga'], 0, ',', '.') ?></td>
                                <td>
                                    <?php
                                        // Beri warna status
                                        $status = htmlspecialchars($po['status']);
                                        $badge_class = 'bg-secondary';
                                        if ($status == 'Baru') {
                                            $badge_class = 'bg-warning text-dark';
                                        } elseif ($status == 'Selesai') {
                                            $badge_class = 'bg-info'; // Siap kirim
                                        } elseif ($status == 'Dikirim') {
                                            $badge_class = 'bg-primary'; // Sedang dikirim
                                        } elseif ($status == 'Diterima') {
                                            $badge_class = 'bg-success'; // Diterima
                                        }
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                                </td>
                                <td>
                                    <!-- Tombol Konfirmasi -->
                                    <?php if ($status == 'Dikirim'): ?>
                                        <form action="konfirmasi_penerimaan.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_pemesanan" value="<?= $po['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Apakah Anda yakin sudah menerima barang ini?')">
                                                <i class="bi bi-check-lg"></i> Terima Barang
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>