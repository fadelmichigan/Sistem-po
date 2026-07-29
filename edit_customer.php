<?php
session_start();
include 'koneksi.php'; 

// Proteksi: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    if (!isset($_SESSION['user_id'])) { 
        header("Location: login.php");
        exit;
    }
}

// Ambil ID dari URL
$id_perusahaan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_perusahaan <= 0) die("ID Customer tidak valid.");

// Ambil data profil perusahaan
$stmt_p = $pdo->prepare("SELECT * FROM perusahaan WHERE id = ?");
$stmt_p->execute([$id_perusahaan]);
$perusahaan = $stmt_p->fetch(PDO::FETCH_ASSOC);

if (!$perusahaan) die("Customer tidak ditemukan.");

// Ambil data akun login (username)
$id_user = $perusahaan['id_user'];
$username = "";
if (!empty($id_user)) {
    $stmt_u = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt_u->execute([$id_user]);
    $user = $stmt_u->fetch(PDO::FETCH_ASSOC);
    if ($user) $username = $user['username'];
}

// Set halaman aktif untuk sidebar
$active_page = 'customer';
include 'admin_header.php';
?>

<div class="main-content">
    <div class="main-header">
        <h2 class="mb-0">Edit Customer</h2>
        <a href="customer.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <!-- PENTING: enctype="multipart/form-data" WAJIB untuk form yang ada upload file-nya -->
            <form action="update_customer.php" method="POST" enctype="multipart/form-data">
                
                <!-- Data tersembunyi yang dibutuhkan untuk proses update -->
                <input type="hidden" name="id_perusahaan" value="<?= $perusahaan['id'] ?>">
                <input type="hidden" name="id_user" value="<?= $perusahaan['id_user'] ?>">
                
                <!-- Simpan nama logo lama untuk dicek nanti (dihapus jika ada logo baru) -->
                <input type="hidden" name="logo_lama" value="<?= htmlspecialchars($perusahaan['logo'] ?? '') ?>">

                <h5 class="text-primary">Data Profil Perusahaan</h5>
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="nama_perusahaan" class="form-label">Nama Perusahaan</label>
                            <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" value="<?= htmlspecialchars($perusahaan['nama_perusahaan']) ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($perusahaan['email']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="no_telepon" class="form-label">No. Telepon</label>
                                <input type="text" class="form-control" id="no_telepon" name="no_telepon" value="<?= htmlspecialchars($perusahaan['no_telepon']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="npwp" class="form-label">NPWP</label>
                            <input type="text" class="form-control" id="npwp" name="npwp" value="<?= htmlspecialchars($perusahaan['npwp']) ?>">
                        </div>
                    </div>
                    
                    <!-- Area Preview Logo & Upload Logo Baru -->
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Logo Saat Ini</label>
                            <div class="border p-2 text-center rounded mb-2 bg-light">
                                <?php if (!empty($perusahaan['logo']) && file_exists('uploads/' . $perusahaan['logo'])): ?>
                                    <!-- Jika logo ada, tampilkan gambarnya -->
                                    <img src="uploads/<?= htmlspecialchars($perusahaan['logo']) ?>" alt="Logo" class="img-fluid" style="max-height: 100px;">
                                <?php else: ?>
                                    <!-- Jika tidak ada logo, tampilkan teks placeholder -->
                                    <span class="text-muted"><i class="bi bi-image display-4 d-block mb-2"></i> Tidak ada logo</span>
                                <?php endif; ?>
                            </div>
                            
                            <label for="logo" class="form-label">Ganti Logo</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                            <div class="form-text">Biarkan kosong jika tidak ingin mengganti logo.</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                
                <h5 class="text-primary">Data Login Customer</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password customer.</small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Update Customer</button>
            </form>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>