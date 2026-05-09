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
$id_distributor = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_distributor <= 0) {
    die("ID distributor tidak valid.");
}

// 2. Ambil data distributor dari database
$stmt = $pdo->prepare("SELECT * FROM distributor WHERE id = ?");
$stmt->execute([$id_distributor]);
$dist = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dist) {
    die("Distributor tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Admin - Edit Distributor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        /* (Salin CSS Sidebar yang sama dari file admin.php Anda ke sini) */
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
    <div class="sidebar-header">
        Admin Panel
    </div>
    <ul class="sidebar-nav nav flex-column">
        <li class="nav-item"><a class="nav-link" href="admin.php"><i class="bi bi-grid-fill"></i> Dashboard (PO)</a></li>
        <li class="nav-item"><a class="nav-link" href="customer.php"><i class="bi bi-people-fill"></i> Customer</a></li>
        <li class="nav-item"><a class="nav-link" href="barang.php"><i class="bi bi-box-seam-fill"></i> Barang</a></li>
        <li class="nav-item"><a class="nav-link active" href="distributor.php"><i class="bi bi-truck"></i> Distributor</a></li>
        </ul>
    <div class="sidebar-footer"><a href="logout.php" class="btn btn-danger w-100"><i class="bi bi-box-arrow-left"></i> Logout</a></div>
</div>

<div class="main-content">
    
    <div class="main-header">
        <h2 class="mb-0">Edit Distributor</h2>
        <a href="distributor.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Distributor</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="update_distributor.php" method="POST">
                <input type="hidden" name="id" value="<?= $dist['id'] ?>">

                <div class="mb-3">
                    <label for="nama_distributor" class="form-label">Nama Distributor</label>
                    <input type="text" class="form-control" id="nama_distributor" name="nama_distributor" value="<?= htmlspecialchars($dist['nama_distributor']) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kontak_person" class="form-label">Kontak Person</label>
                        <input type="text" class="form-control" id="kontak_person" name="kontak_person" value="<?= htmlspecialchars($dist['kontak_person']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="no_telepon" class="form-label">No. Telepon</label>
                        <input type="text" class="form-control" id="no_telepon" name="no_telepon" value="<?= htmlspecialchars($dist['no_telepon']) ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($dist['alamat']) ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Update Distributor</button>
            </form>
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>