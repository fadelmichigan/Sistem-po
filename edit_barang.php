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
$id_barang = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_barang <= 0) {
    die("ID barang tidak valid.");
}

// 2. Ambil data barang dari database
$stmt = $pdo->prepare("SELECT * FROM barang WHERE id = ?");
$stmt->execute([$id_barang]);
$barang = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$barang) {
    die("Barang tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Admin - Edit Barang</title>
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
        <li class="nav-item"><a class="nav-link active" href="barang.php"><i class="bi bi-box-seam-fill"></i> Barang</a></li>
        </ul>
    <div class="sidebar-footer"><a href="logout.php" class="btn btn-danger w-100"><i class="bi bi-box-arrow-left"></i> Logout</a></div>
</div>

<div class="main-content">
    
    <div class="main-header">
        <h2 class="mb-0">Edit Barang</h2>
        <a href="barang.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Barang</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="update_barang.php" method="POST">
                <input type="hidden" name="id" value="<?= $barang['id'] ?>">

                <div class="mb-3">
                    <label for="nama_barang" class="form-label">Nama Barang</label>
                    <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?= htmlspecialchars($barang['nama_barang']) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="merek" class="form-label">Merek</label>
                        <input type="text" class="form-control" id="merek" name="merek" value="<?= htmlspecialchars($barang['merek']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="jenis" class="form-label">Jenis</label>
                        <input type="text" class="form-control" id="jenis" name="jenis" value="<?= htmlspecialchars($barang['jenis']) ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="satuan" class="form-label">Satuan</label>
                        <input type="text" class="form-control" id="satuan" name="satuan" value="<?= htmlspecialchars($barang['satuan']) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="harga" class="form-label">Harga</label>
                        <input type="number" class="form-control" id="harga" name="harga" value="<?= htmlspecialchars($barang['harga']) ?>" min="0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" value="<?= htmlspecialchars($barang['stok']) ?>" min="0" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Update Barang</button>
            </form>
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>