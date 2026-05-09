<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan ini adalah admin yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    if (!isset($_SESSION['user_id'])) { 
        header("Location: login.php");
        exit;
    }
}

// 1. Ambil ID dari URL
$id_perusahaan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_perusahaan <= 0) {
    die("ID Customer tidak valid.");
}

// 2. Ambil data profil perusahaan
$stmt_p = $pdo->prepare("SELECT * FROM perusahaan WHERE id = ?");
$stmt_p->execute([$id_perusahaan]);
$perusahaan = $stmt_p->fetch(PDO::FETCH_ASSOC);

if (!$perusahaan) {
    die("Customer tidak ditemukan.");
}

// 3. Ambil data login (username) dari tabel users
$id_user = $perusahaan['id_user'];
$username = "";
if (!empty($id_user)) {
    $stmt_u = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt_u->execute([$id_user]);
    $user = $stmt_u->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $username = $user['username'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Admin - Edit Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        /* (Salin CSS Sidebar yang sama dari file admin.php/customer.php Anda ke sini) */
        body { display: flex; min-height: 100vh; flex-direction: row; background-color: #f8f9fa; }
        .sidebar { width: 260px; background-color: #fff; border-right: 1px solid #dee2e6; display: flex; flex-direction: column; padding: 1rem; position: fixed; height: 100%; }
        .sidebar-header { font-size: 1.5rem; font-weight: bold; color: #0d6efd; padding-bottom: 1rem; border-bottom: 1px solid #dee2e6; margin-bottom: 1rem; text-align: center; }
        .sidebar-nav { list-style: none; padding-left: 0; flex-grow: 1; }
        .sidebar-nav .nav-item { margin-bottom: 0.25rem; }
        .sidebar-nav .nav-link { display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: 0.5rem; color: #343a40; text-decoration: none; font-size: 1.05rem; }
        .sidebar-nav .nav-link i { margin-right: 1rem; width: 20px; text-align: center; }
        .sidebar-nav .nav-link:hover { background-color: #e9ecef; }
        .sidebar-nav .nav-link.active { background-color: #0d6efd; color: #fff; font-weight: 500; }
        .sidebar-footer { border-top: 1px solid #dee2e6; padding-top: 1rem; }
        .main-content { flex-grow: 1; padding: 2rem; margin-left: 260px; }
        .main-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">Admin Panel</div>
    <ul class="sidebar-nav nav flex-column">
        <li class="nav-item"><a class="nav-link" href="admin.php"><i class="bi bi-grid-fill"></i> Dashboard (PO)</a></li>
        <li class="nav-item"><a class="nav-link active" href="customer.php"><i class="bi bi-people-fill"></i> Customer</a></li>
        <li class="nav-item"><a class="nav-link" href="barang.php"><i class="bi bi-box-seam-fill"></i> Barang</a></li>
        </ul>
    <div class="sidebar-footer"><a href="logout.php" class="btn btn-danger w-100"><i class="bi bi-box-arrow-left"></i> Logout</a></div>
</div>

<div class="main-content">
    
    <div class="main-header">
        <h2 class="mb-0">Edit Customer</h2>
        <a href="customer.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="update_customer.php" method="POST">
                <input type="hidden" name="id_perusahaan" value="<?= $perusahaan['id'] ?>">
                <input type="hidden" name="id_user" value="<?= $perusahaan['id_user'] ?>">

                <h5 class="text-primary">Data Profil Perusahaan</h5>
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

                <hr class="my-4">
                
                <h5 class="text-primary">Data Login Customer</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username (untuk login)</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Update Customer</button>
            </form>
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>