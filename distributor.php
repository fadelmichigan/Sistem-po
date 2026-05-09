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
$stmt = $pdo->query("SELECT * FROM distributor ORDER BY nama_distributor ASC");
$daftar_distributor = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Set Halaman Aktif
$active_page = 'distributor';

// 3. Panggil Header
include 'admin_header.php';
?>

<!-- 4. KONTEN UTAMA HALAMAN INI -->

<!-- NOTIFIKASI ALERT -->
<?php if(isset($_GET['status'])): ?>
    <?php
        $alert_class = 'alert-success'; // Default
        $message = '';

        if ($_GET['status'] == 'sukses') {
            $message = 'Data distributor baru berhasil ditambahkan!';
        } elseif ($_GET['status'] == 'update_sukses') {
            $message = 'Data distributor berhasil diperbarui!';
        } elseif ($_GET['status'] == 'hapus_sukses') {
            $message = 'Data distributor berhasil dihapus.';
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

<div class="main-header">
    <h2 class="mb-0">Manajemen Distributor</h2>
    <a href="tambah_distributor.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Distributor
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Daftar Distributor</h5>
    </div>
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nama Distributor</th>
                    <th>Kontak Person</th>
                    <th>No. Telepon</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daftar_distributor)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada data distributor.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar_distributor as $dist): ?>
                        <tr>
                            <td><?= htmlspecialchars($dist['id']) ?></td>
                            <td><strong><?= htmlspecialchars($dist['nama_distributor']) ?></strong></td>
                            <td><?= htmlspecialchars($dist['kontak_person']) ?></td>
                            <td><?= htmlspecialchars($dist['no_telepon']) ?></td>
                            <td><?= htmlspecialchars($dist['alamat']) ?></td>
                            <td>
                                <a href="edit_distributor.php?id=<?= $dist['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="hapus_distributor.php?id=<?= $dist['id'] ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus distributor ini?')">
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

<!-- 5. Panggil Footer -->
<?php include 'admin_footer.php'; ?>