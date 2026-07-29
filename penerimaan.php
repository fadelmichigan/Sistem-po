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

// Query mengambil pesanan 'Baru' beserta kolom bukti_bayar
$sql = "SELECT 
            po.id, po.tanggal_pesan, po.total_harga, po.status, po.bukti_bayar, p.nama_perusahaan 
        FROM pemesanan po
        JOIN perusahaan p ON po.id_perusahaan = p.id
        WHERE po.status = 'Baru'
        ORDER BY po.tanggal_pesan ASC";
$stmt = $pdo->query($sql);
$daftar_po = $stmt->fetchAll(PDO::FETCH_ASSOC);

$active_page = 'penerimaan';
include 'admin_header.php';
?>

<!-- NOTIFIKASI ALERT -->
<?php if(isset($_GET['status']) && $_GET['status'] == 'proses_sukses'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Berhasil! Pesanan telah diproses dan siap dikirim.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<!-- BATAS NOTIFIKASI -->

<div class="main-header">
    <h2 class="mb-0">Penerimaan Pesanan</h2>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white pt-3 pb-2">
        <h5 class="mb-0 text-primary">Daftar PO Menunggu Diproses (Status: Baru)</h5>
    </div>
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID PO</th>
                    <th>Customer</th>
                    <th>Tgl Pesan</th>
                    <th>Total Harga</th>
                    <th>Bukti Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daftar_po)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Tidak ada PO baru.</td></tr>
                <?php else: ?>
                    <?php foreach ($daftar_po as $po): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($po['id']) ?></strong></td>
                            <td><?= htmlspecialchars($po['nama_perusahaan']) ?></td>
                            <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($po['tanggal_pesan']))) ?></td>
                            <td class="fw-bold">Rp <?= number_format($po['total_harga'], 0, ',', '.') ?></td>
                            <td>
                                <!-- Indikator Bukti Bayar -->
                                <?php if (!empty($po['bukti_bayar'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($po['bukti_bayar']) ?>" target="_blank" class="badge bg-success text-decoration-none p-2">
                                        <i class="bi bi-file-earmark-check"></i> Lihat Bukti
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-danger p-2"><i class="bi bi-x-circle"></i> Belum Bayar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="proses_penerimaan.php?id=<?= $po['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Proses pesanan ini? Pastikan Anda sudah mengecek bukti transfer (jika ada).')">
                                    <i class="bi bi-check-circle-fill"></i> Proses
                                </a>
                                <a href="invoice.php?id=<?= $po['id'] ?>" class="btn btn-info btn-sm text-white" target="_blank" title="Lihat Detail">
                                    <i class="bi bi-search"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'admin_footer.php'; ?>