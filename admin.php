<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Set halaman ini sebagai 'dashboard' untuk sidebar
$active_page = 'dashboard'; 

// =======================================================
// TAMBAHAN BARU: QUERY UNTUK KARTU STATISTIK
// =======================================================

// 1. Pesanan Baru (Status 'Baru')
$stmt_baru = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status = 'Baru'");
$stat_pesanan_baru = $stmt_baru->fetchColumn();

// 2. Total Customer Aktif
$stmt_cust = $pdo->query("SELECT COUNT(*) FROM perusahaan WHERE status = 'Aktif'");
$stat_total_customer = $stmt_cust->fetchColumn();

// 3. Stok Akan Habis (Stok <= 10)
$stmt_stok = $pdo->query("SELECT COUNT(*) FROM barang WHERE stok <= 10");
$stat_stok_habis = $stmt_stok->fetchColumn();

// 4. Total Penjualan (Bulan Ini) - Hanya hitung PO yang sudah diproses
$stmt_penjualan = $pdo->query("
    SELECT SUM(total_harga) 
    FROM pemesanan 
    WHERE MONTH(tanggal_pesan) = MONTH(CURRENT_DATE()) 
      AND YEAR(tanggal_pesan) = YEAR(CURRENT_DATE())
      AND status != 'Baru' 
");
// Gunakan ?? 0 untuk mencegah error jika hasilnya NULL (belum ada penjualan)
$stat_penjualan_bulan_ini = $stmt_penjualan->fetchColumn() ?? 0;

// =======================================================
// BATAS QUERY STATISTIK
// =======================================================


// === LOGIKA PENCARIAN (Sudah ada dari Saran 1) ===
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$params = [];
$sql = "SELECT 
            po.id, 
            po.tanggal_pesan, 
            po.total_harga, 
            po.status, 
            p.nama_perusahaan 
        FROM pemesanan po
        JOIN perusahaan p ON po.id_perusahaan = p.id";

if (!empty($search)) {
    $sql .= " WHERE p.nama_perusahaan LIKE ?";
    $params[] = "%$search%";
}
$sql .= " ORDER BY po.id DESC"; 

$stmt_daftar_po = $pdo->prepare($sql);
$stmt_daftar_po->execute($params);
$daftar_pesanan = $stmt_daftar_po->fetchAll(PDO::FETCH_ASSOC);

// Include header
include 'admin_header.php'; 
?>

<div class="main-header d-flex justify-content-between align-items-center">
    <h2 class="mb-0">Dashboard</h2>
    <a href="form_pemesanan.php" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Buat PO Baru
    </a>
</div>

<!-- ======================================================= -->
<!-- TAMBAHAN BARU: KARTU STATISTIK -->
<!-- ======================================================= -->
<div class="row g-4 mb-4">
    <!-- Kartu 1: Pesanan Baru -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-box-seam-fill text-warning display-5 me-3"></i>
                <div>
                    <h5 class="card-title text-muted mb-1">Pesanan Baru</h5>
                    <h2 class="mb-0 display-6 fw-bold"><?= $stat_pesanan_baru ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Kartu 2: Total Penjualan Bulan Ini -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-cash-stack text-success display-5 me-3"></i>
                <div>
                    <h5 class="card-title text-muted mb-1">Penjualan (Bln Ini)</h5>
                    <h4 class="mb-0 fw-bold">Rp</h4>
                    <h5 class="mb-0 fw-bold"><?= number_format($stat_penjualan_bulan_ini, 0, ',', '.') ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu 3: Total Customer -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-people-fill text-primary display-5 me-3"></i>
                <div>
                    <h5 class="card-title text-muted mb-1">Customer Aktif</h5>
                    <h2 class="mb-0 display-6 fw-bold"><?= $stat_total_customer ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu 4: Stok Akan Habis -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-archive-fill text-danger display-5 me-3"></i>
                <div>
                    <h5 class="card-title text-muted mb-1">Stok Akan Habis</h5>
                    <h2 class="mb-0 display-6 fw-bold"><?= $stat_stok_habis ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ======================================================= -->
<!-- BATAS KARTU STATISTIK -->
<!-- ======================================================= -->


<!-- FORM PENCARIAN (Sudah ada dari Saran 1) -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="admin.php">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari PO berdasarkan nama perusahaan..." name="search" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Cari
                </button>
                <?php if(!empty($search)): ?>
                    <a href="admin.php" class="btn btn-outline-secondary" title="Reset Pencarian">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<!-- BATAS FORM PENCARIAN -->

<!-- Tabel untuk menampilkan daftar pesanan -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Daftar Semua Purchase Order</h5>
    </div>
    <div class="card-body table-responsive">
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
                <?php if (empty($daftar_pesanan)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <?php if(!empty($search)): ?>
                                '<?= htmlspecialchars($search) ?>' tidak ditemukan.
                            <?php else: ?>
                                Belum ada pesanan yang masuk.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar_pesanan as $po): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($po['id']) ?></strong></td>
                            <td><?= htmlspecialchars($po['nama_perusahaan']) ?></td>
                            <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($po['tanggal_pesan']))) ?></td>
                            <td>Rp <?= number_format($po['total_harga'], 0, ',', '.') ?></td>
                            <td>
                                <?php
                                    $status = htmlspecialchars($po['status']);
                                    $badge_class = 'bg-secondary';
                                    if ($status == 'Baru') { $badge_class = 'bg-warning text-dark'; } 
                                    elseif ($status == 'Selesai') { $badge_class = 'bg-info'; } 
                                    elseif ($status == 'Dikirim') { $badge_class = 'bg-primary'; } 
                                    elseif ($status == 'Diterima') { $badge_class = 'bg-success'; }
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                            </td>
                            <td>
                                <a href="invoice.php?id=<?= $po['id'] ?>" class="btn btn-info btn-sm" target="_blank" title="Lihat Invoice">
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

<?php
// Include footer
include 'admin_footer.php'; 
?>