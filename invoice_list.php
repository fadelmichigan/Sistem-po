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
        WHERE po.status != 'Baru'
        ORDER BY po.tanggal_pesan DESC";
$stmt = $pdo->query($sql);
$daftar_invoice = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Set Halaman Aktif
$active_page = 'invoice_list';

// 3. Panggil Header
include 'admin_header.php';
?>

<!-- 4. KONTEN UTAMA HALAMAN INI -->

<div class="main-header">
    <h2 class="mb-0">Daftar Invoice (Pesanan Selesai/Dikirim)</h2>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Arsip Pesanan</h5>
    </div>
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID Pesanan</th>
                    <th>Nama Perusahaan</th>
                    <th>Tanggal Pesan</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daftar_invoice)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada pesanan yang selesai diproses.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar_invoice as $po): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($po['id']) ?></strong></td>
                            <td><?= htmlspecialchars($po['nama_perusahaan']) ?></td>
                            <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($po['tanggal_pesan']))) ?></td>
                            <td>Rp <?= number_format($po['total_harga'], 0, ',', '.') ?></td>
                            <td>
                                <?php
                                    $status = htmlspecialchars($po['status']);
                                    $badge_class = 'bg-secondary';
                                    if ($status == 'Selesai') { $badge_class = 'bg-success'; } 
                                    elseif ($status == 'Dikirim') { $badge_class = 'bg-primary'; }
                                    elseif ($status == 'Diterima') { $badge_class = 'bg-info'; }
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                            </td>
                            <td>
                                <a href="invoice.php?id=<?= $po['id'] ?>" class="btn btn-info btn-sm" target="_blank">
                                    <i class="bi bi-receipt"></i> Lihat Invoice
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