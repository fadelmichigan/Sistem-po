<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login sebagai 'user' (pembeli)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

$id_user_login = $_SESSION['user_id'];

// Ambil data perusahaan berdasarkan user yang login
$stmt = $pdo->prepare("SELECT * FROM perusahaan WHERE id_user = ?");
$stmt->execute([$id_user_login]);
$perusahaan = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika data perusahaan tidak ditemukan
if (!$perusahaan) {
    echo "Data perusahaan tidak ditemukan. Silakan hubungi admin.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .company-logo-container {
            width: 120px;
            height: 120px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
            overflow: hidden;
            border: 4px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .company-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* Memastikan logo tidak terpotong */
            padding: 5px;
        }
        .info-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .info-card:hover { transform: translateY(-5px); }
        .action-btn { padding: 15px 30px; font-size: 1.2rem; border-radius: 50px; }
    </style>
</head>
<body class="bg-light">

<!-- Navbar -->
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

<!-- Hero Section -->
<div class="hero-section text-center">
    
    <!-- ========================================== -->
    <!-- BAGIAN UNTUK MENAMPILKAN LOGO PERUSAHAAN   -->
    <!-- ========================================== -->
    <div class="company-logo-container">
        <?php 
        // Cek apakah database memiliki nama file logo, dan apakah file tersebut benar-benar ada di folder 'uploads/'
        if (!empty($perusahaan['logo']) && file_exists('uploads/' . $perusahaan['logo'])): 
        ?>
            <!-- Tampilkan Logo dari folder uploads/ -->
            <img src="uploads/<?= htmlspecialchars($perusahaan['logo']) ?>" alt="Logo" class="company-logo-img">
        <?php else: ?>
            <!-- Tampilkan Icon Gedung jika logo kosong atau file tidak ditemukan -->
            <i class="bi bi-building text-primary" style="font-size: 3.5rem;"></i>
        <?php endif; ?>
    </div>
    <!-- ========================================== -->

    <h2 class="fw-bold">Selamat Datang, <?= htmlspecialchars($perusahaan['nama_perusahaan']) ?>!</h2>
    <p class="lead mb-0">Selamat berbelanja di portal pemesanan resmi kami.</p>
</div>

<div class="container mb-5">
    <div class="row g-4 justify-content-center">
        
        <!-- Kartu Profil -->
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
                        <i class="bi bi-pencil"></i> Lihat Detail Profil
                    </a>
                </div>
            </div>
        </div>

        <!-- Kartu Aksi Transaksi -->
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