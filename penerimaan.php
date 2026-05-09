<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    if (!isset($_SESSION['user_id'])) { 
        header("Location: login.php");
        exit;
    }
}

// 1. Logika PHP khusus halaman ini
$sql = "SELECT 
            po.id, po.tanggal_pesan, po.total_harga, po.status, p.nama_perusahaan 
        FROM pemesanan po
        JOIN perusahaan p ON po.id_perusahaan = p.id
        WHERE po.status = 'Baru'
        ORDER BY po.tanggal_pesan ASC";
$stmt = $pdo->query($sql);
$daftar_po = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Set Halaman Aktif
$active_page = 'penerimaan';

// 3. Panggil Header
include 'admin_header.php';
?>

<!-- 4. KONTEN UTAMA HALAMAN INI -->

<!-- NOTIFIKASI ALERT -->
<?php if(isset($_GET['status']) && $_GET['status'] == 'proses_sukses'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Berhasil! Pesanan telah dicatat sebagai "Selesai" dan dipindahkan ke Pengiriman.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<!-- BATAS NOTIFIKASI -->

<div class="main-header">
    <h2 class="mb-0">Penerimaan Barang</h2>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Daftar PO Menunggu Penerimaan (Status: Baru)</h5>
    </div>
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID Pesanan</th>
                    <th>Nama Perusahaan</th>
                    <th>Tanggal Pesan</th>
                    <th>Total Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daftar_po)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Tidak ada PO yang menunggu diproses.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar_po as $po): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($po['id']) ?></strong></td>
                            <td><?= htmlspecialchars($po['nama_perusahaan']) ?></td>
                            <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($po['tanggal_pesan']))) ?></td>
                            <td>Rp <?= number_format($po['total_harga'], 0, ',', '.') ?></td>
                            <td>
                                <a href="proses_penerimaan.php?id=<?= $po['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Anda yakin ingin memproses pesanan ini?')">
                                    <i class="bi bi-check-circle-fill"></i> Catat Penerimaan
                                </a>
                                <a href="invoice.php?id=<?= $po['id'] ?>" class="btn btn-info btn-sm" target="_blank" title="Lihat Detail PO">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 5. Panggil Footer -->
<?php include 'admin_footer.php'; ?>