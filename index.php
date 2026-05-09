<?php
session_start();
include 'koneksi.php';

// Proteksi
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

$id_user_login = $_SESSION['user_id'];

// Ambil data perusahaan
$stmt = $pdo->prepare("SELECT * FROM perusahaan WHERE id_user = ?");
$stmt->execute([$id_user_login]);
$perusahaan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$perusahaan) {
    echo "Data perusahaan belum lengkap. Hubungi admin.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Selamat Datang - <?= htmlspecialchars($perusahaan['nama_perusahaan']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 3rem 1rem;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
        }
        .company-logo {
            width: 100px;
            height: 100px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
            font-size: 2.5rem;
            color: #0d6efd;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .info-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .info-card:hover {
            transform: translateY(-5px);
        }
        .action-btn {
            padding: 15px 30px;
            font-size: 1.2rem;
            border-radius: 50px;
        }
    </style>
</head>
<body class="bg-light">

<!-- Navbar Sederhana -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#">Sistem PO</a>
        <div class="d-flex">
            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>

<!-- Hero Section (Pembukaan) -->
<div class="hero-section text-center">
    <div class="company-logo">
        <!-- Ganti dengan <img> jika punya logo: <img src="logo.png" ...> -->
        <i class="bi bi-building"></i>
    </div>
    <h2 class="fw-bold">Selamat Datang, <?= htmlspecialchars($perusahaan['nama_perusahaan']) ?>!</h2>
    <p class="lead mb-0">Selamat berbelanja di portal pemesanan resmi kami.</p>
</div>

<div class="container">
    <div class="row g-4 justify-content-center">
        
        <!-- Kartu Data Pembeli (Profil) -->
        <div class="col-md-5">
            <div class="card info-card h-100">
                <div class="card-body p-4">
                    <h5 class="card-title text-primary mb-4">
                        <i class="bi bi-person-badge-fill me-2"></i> Data Profil Anda
                    </h5>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Nama Perusahaan</small>
                        <span class="fw-bold fs-5"><?= htmlspecialchars($perusahaan['nama_perusahaan']) ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Email</small>
                        <span><?= htmlspecialchars($perusahaan['email']) ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">NPWP</small>
                        <span><?= htmlspecialchars($perusahaan['npwp']) ?></span>
                    </div>
                    
                    <div class="mb-0">
                        <small class="text-muted d-block">No. Telepon</small>
                        <span><?= htmlspecialchars($perusahaan['no_telepon']) ?></span>
                    </div>
                    
                    <hr>
                    <a href="profil_saya.php" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-pencil"></i> Edit Profil
                    </a>
                </div>
            </div>
        </div>

        <!-- Kartu Aksi (Tombol PO) -->
        <div class="col-md-5">
            <div class="card info-card h-100 border-primary">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center align-items-center">
                    <h5 class="card-title text-primary mb-3">Mulai Transaksi</h5>
                    <p class="text-muted mb-4">Buat pesanan baru atau cek status pesanan Anda yang sedang berjalan.</p>
                    
                    <a href="form_pemesanan.php" class="btn btn-primary action-btn w-100 mb-3 shadow">
                        <i class="bi bi-cart-plus-fill me-2"></i> Buat Purchase Order
                    </a>
                    
                    <a href="daftar_pesanan.php" class="btn btn-outline-primary w-100">
                        <i class="bi bi-clock-history me-2"></i> Riwayat Pesanan
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>