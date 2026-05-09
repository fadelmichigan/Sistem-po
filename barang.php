<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Set halaman ini sebagai 'barang' untuk sidebar
$active_page = 'barang'; 

// === LOGIKA PENCARIAN ===
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$params = [];
$sql = "SELECT * FROM barang"; // Tidak perlu status 'Aktif' untuk barang

if (!empty($search)) {
    // Tambahkan kondisi pencarian (bisa cari nama atau merek)
    $sql .= " WHERE (nama_barang LIKE ? OR merek LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY nama_barang ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_barang = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
include 'admin_header.php'; 
?>

<!-- NOTIFIKASI ALERT -->
<?php if(isset($_GET['status'])): ?>
    <?php
        $alert_class = 'alert-success'; // Default
        $message = '';

        if ($_GET['status'] == 'sukses') {
            $message = 'Data barang baru berhasil ditambahkan!';
        } elseif ($_GET['status'] == 'update_sukses') {
            $message = 'Data barang berhasil diperbarui!';
        } elseif ($_GET['status'] == 'hapus_sukses') {
            $message = 'Data barang berhasil dihapus.';
        } elseif ($_GET['status'] == 'hapus_gagal') {
            $alert_class = 'alert-danger';
            if (isset($_GET['error']) && $_GET['error'] == 'barang_ada_di_pesanan') {
                $message = 'Gagal! Barang tidak bisa dihapus karena sudah ada di riwayat pesanan.';
            } else {
                $message = 'Aksi gagal!';
            }
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


<div class="main-header d-flex justify-content-between align-items-center">
    <h2 class="mb-0">Manajemen Barang</h2>
    <a href="tambah_barang.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Barang Baru
    </a>
</div>

<!-- FORM PENCARIAN -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="barang.php">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari berdasarkan nama barang atau merek..." name="search" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Cari
                </button>
                <?php if(!empty($search)): ?>
                    <a href="barang.php" class="btn btn-outline-secondary" title="Reset Pencarian">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<!-- BATAS FORM PENCARIAN -->

<!-- Tabel untuk menampilkan data barang -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Daftar Barang</h5>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nama Produk</th>
                    <th>Merek</th>
                    <th>Jenis</th>
                    <th>Satuan</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daftar_barang)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            <?php if(!empty($search)): ?>
                                '<?= htmlspecialchars($search) ?>' tidak ditemukan.
                            <?php else: ?>
                                Belum ada data barang.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar_barang as $barang): ?>
                        <tr>
                            <td><?= htmlspecialchars($barang['id']) ?></td>
                            <td><strong><?= htmlspecialchars($barang['nama_barang']) ?></strong></td>
                            <td><?= htmlspecialchars($barang['merek']) ?></td>
                            <td><?= htmlspecialchars($barang['jenis']) ?></td>
                            <td><?= htmlspecialchars($barang['satuan']) ?></td>
                            <td>Rp <?= number_format($barang['harga'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($barang['stok']) ?></td>
                            <td>
                                <a href="edit_barang.php?id=<?= $barang['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="hapus_barang.php?id=<?= $barang['id'] ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">
                                    <i class="bi bi-trash-fill"></i>
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