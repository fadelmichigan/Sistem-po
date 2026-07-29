<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    if (!isset($_SESSION['user_id'])) { 
        header("Location: login.php");
        exit;
    }
}
$active_page = 'customer';
include 'admin_header.php';
?>

<div class="main-content">
    <div class="main-header">
        <h2 class="mb-0">Tambah Customer Baru</h2>
        <a href="customer.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <!-- PENTING: enctype="multipart/form-data" WAJIB untuk upload file -->
            <form action="simpan_customer.php" method="POST" enctype="multipart/form-data">
                
                <h5 class="text-primary">Data Profil Perusahaan</h5>
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="nama_perusahaan" class="form-label">Nama Perusahaan</label>
                            <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="no_telepon" class="form-label">No. Telepon</label>
                                <input type="text" class="form-control" id="no_telepon" name="no_telepon">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="npwp" class="form-label">NPWP</label>
                            <input type="text" class="form-control" id="npwp" name="npwp">
                        </div>
                    </div>
                    
                    <!-- Kolom Upload Logo -->
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo Perusahaan</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                            <div class="form-text">Format: JPG, PNG, GIF. Maks 2MB.</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                
                <h5 class="text-primary">Data Login Customer</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username (untuk login)</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password (untuk login)</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Simpan Customer dan Buat Login</button>
            </form>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>