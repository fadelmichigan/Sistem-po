<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Set halaman ini sebagai 'customer' untuk sidebar
$active_page = 'customer'; 

// === LOGIKA PENCARIAN ===
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$params = [];
$sql = "SELECT * FROM perusahaan WHERE status = 'Aktif'";

if (!empty($search)) {
    // Tambahkan kondisi pencarian
    $sql .= " AND nama_perusahaan LIKE ?";
    $params[] = "%$search%"; // Tambahkan parameter binding
}
$sql .= " ORDER BY nama_perusahaan ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_customer = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
include 'admin_header.php'; 
?>

<!-- NOTIFIKASI ALERT -->
<?php if(isset($_GET['status'])): ?>
    <?php
        $alert_class = 'alert-success'; // Default
        $message = '';

        if ($_GET['status'] == 'sukses') {
            $message = 'Data customer baru berhasil ditambahkan!';
        } elseif ($_GET['status'] == 'update_sukses') {
            $message = 'Data customer berhasil diperbarui!';
        } elseif ($_GET['status'] == 'arsip_sukses') {
            $message = 'Customer berhasil diarsipkan (dinonaktifkan).';
        } elseif ($_GET['status'] == 'hapus_gagal') {
            $alert_class = 'alert-danger';
            if (isset($_GET['error']) && $_GET['error'] == 'customer_punya_pesanan') {
                $message = 'Gagal! Customer tidak bisa dihapus karena sudah memiliki riwayat pesanan.';
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
    <h2 class="mb-0">Manajemen Customer</h2>
    <a href="tambah_customer.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Customer Baru
    </a>
</div>

<!-- FORM PENCARIAN -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="customer.php">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari berdasarkan nama perusahaan..." name="search" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Cari
                </button>
                <?php if(!empty($search)): ?>
                    <a href="customer.php" class="btn btn-outline-secondary" title="Reset Pencarian">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<!-- BATAS FORM PENCARIAN -->

<!-- Tabel untuk menampilkan data customer -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Daftar Customer (Aktif)</h5>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nama Perusahaan</th>
                    <th>Email</th>
                    <th>No. Telepon</th>
                    <th>NPWP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daftar_customer)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <?php if(!empty($search)): ?>
                                '<?= htmlspecialchars($search) ?>' tidak ditemukan.
                            <?php else: ?>
                                Belum ada data customer.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar_customer as $customer): ?>
                        <tr>
                            <td><?= htmlspecialchars($customer['id']) ?></td>
                            <td><strong><?= htmlspecialchars($customer['nama_perusahaan']) ?></strong></td>
                            <td><?= htmlspecialchars($customer['email']) ?></td>
                            <td><?= htmlspecialchars($customer['no_telepon']) ?></td>
                            <td><?= htmlspecialchars($customer['npwp']) ?></td>
                            <td>
                                <a href="edit_customer.php?id=<?= $customer['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="hapus_customer.php?id=<?= $customer['id'] ?>" class="btn btn-danger btn-sm" title="Arsipkan" onclick="return confirm('Apakah Anda yakin ingin mengarsipkan customer ini? (Riwayat PO tetap aman)')">
                                    <i class="bi bi-archive-fill"></i>
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