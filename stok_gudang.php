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
$stmt = $pdo->query("SELECT id, nama_barang, merek, jenis, stok, satuan FROM barang ORDER BY stok ASC, nama_barang ASC");
$daftar_stok = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Set Halaman Aktif
$active_page = 'stok_gudang';

// 3. Panggil Header
include 'admin_header.php';
?>

<!-- 4. KONTEN UTAMA HALAMAN INI -->

<!-- 
=======================================================
PERUBAHAN DI SINI: Menambahkan cek 'stok_masuk_sukses'
=======================================================
-->
<?php if(isset($_GET['status']) && $_GET['status'] == 'stok_masuk_sukses'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Berhasil!</strong> Stok barang telah berhasil ditambahkan ke gudang.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<!-- BATAS NOTIFIKASI -->


<div class="main-header">
    <h2 class="mb-0">Stok Gudang</h2>
    <a href="barang.php" class="btn btn-primary">
        <i class="bi bi-pencil-fill"></i> Kelola Barang (Tambah/Edit)
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Daftar Stok Barang Saat Ini</h5>
    </div>
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nama Barang</th>
                    <th>Merek</th>
                    <th>Jenis</th>
                    <th>Sisa Stok</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daftar_stok)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada data barang.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar_stok as $barang): ?>
                        <!-- Memberi class 'stok-rendah' jika stok <= 10 -->
                        <tr class="<?= ($barang['stok'] <= 10) ? 'stok-rendah' : '' ?>">
                            <td><?= htmlspecialchars($barang['id']) ?></td>
                            <td><strong><?= htmlspecialchars($barang['nama_barang']) ?></strong></td>
                            <td><?= htmlspecialchars($barang['merek']) ?></td>
                            <td><?= htmlspecialchars($barang['jenis']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($barang['stok']) ?></strong>
                                <?= htmlspecialchars($barang['satuan']) ?>
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