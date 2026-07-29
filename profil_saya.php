<?php
session_start();
include 'koneksi.php'; 

// Proteksi: Pastikan user login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil data
$stmt_p = $pdo->prepare("SELECT * FROM perusahaan WHERE id_user = ?");
$stmt_p->execute([$id_user]);
$perusahaan = $stmt_p->fetch(PDO::FETCH_ASSOC);

if (!$perusahaan) die("Data perusahaan tidak ditemukan.");

// Ambil username
$stmt_u = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt_u->execute([$id_user]);
$user = $stmt_u->fetch(PDO::FETCH_ASSOC);
$username = $user ? $user['username'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold">Profil Saya</h3>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>

            <!-- Notifikasi Sukses/Gagal -->
            <?php if(isset($_GET['status'])): ?>
                <?php if($_GET['status'] == 'sukses'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Profil berhasil diperbarui!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif($_GET['status'] == 'gagal'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Gagal memperbarui profil: <?= htmlspecialchars($_GET['msg'] ?? 'Terjadi kesalahan.') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!-- Form dikirim ke update_profil_saya.php -->
                    <form action="update_profil_saya.php" method="POST">
                        <input type="hidden" name="id_perusahaan" value="<?= $perusahaan['id'] ?>">
                        <input type="hidden" name="id_user" value="<?= $perusahaan['id_user'] ?>">

                        <div class="row">
                            <!-- Kolom Kiri: Logo (Hanya Tampilan) -->
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <label class="form-label fw-bold d-block">Logo Perusahaan</label>
                                <div class="border p-3 rounded bg-white d-inline-block">
                                    <?php if (!empty($perusahaan['logo']) && file_exists('uploads/' . $perusahaan['logo'])): ?>
                                        <img src="uploads/<?= htmlspecialchars($perusahaan['logo']) ?>" alt="Logo" class="img-fluid" style="max-height: 120px;">
                                    <?php else: ?>
                                        <div class="text-muted py-4">
                                            <i class="bi bi-building display-4"></i>
                                            <p class="mb-0 mt-2 small">Belum ada logo</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted small mt-2">
                                    <i class="bi bi-info-circle"></i> Hubungi admin jika ingin mengganti logo.
                                </p>
                            </div>

                            <!-- Kolom Kanan: Form Data -->
                            <div class="col-md-8">
                                <h5 class="text-primary mb-3">Info Perusahaan</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nama Perusahaan</label>
                                    <input type="text" class="form-control" name="nama_perusahaan" value="<?= htmlspecialchars($perusahaan['nama_perusahaan']) ?>" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($perusahaan['email']) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">No. Telepon</label>
                                        <input type="text" class="form-control" name="no_telepon" value="<?= htmlspecialchars($perusahaan['no_telepon']) ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">NPWP</label>
                                    <input type="text" class="form-control" name="npwp" value="<?= htmlspecialchars($perusahaan['npwp']) ?>">
                                </div>

                                <hr class="my-4">
                                
                                <h5 class="text-primary mb-3">Akun Login</h5>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($username) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ganti Password</label>
                                    <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengganti password">
                                    <div class="form-text text-muted">Isi hanya jika ingin mengubah password.</div>
                                </div>
                                
                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">Simpan Perubahan</button>
                                </div>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>