<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Set halaman aktif untuk sidebar
$active_page = 'laporan'; 

include 'admin_header.php'; // Panggil header
?>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

    <div class="main-header">
        <h2 class="mb-0">Laporan Penjualan</h2>
    </div>

    <!-- Form untuk memilih rentang tanggal -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Pilih Rentang Tanggal</h5>
        </div>
        <div class="card-body">
            <!-- 
                Form ini akan dikirim ke 'cetak_laporan.php'
                method="POST" agar tanggalnya tidak tampil di URL
                target="_blank" agar PDF terbuka di tab baru
            -->
            <form action="cetak_laporan.php" method="POST" target="_blank">
                <div class="row">
                    <div class="col-md-5">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                    </div>
                    <div class="col-md-5">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-printer-fill"></i> Cetak Laporan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
</div>
<!-- ===== AKHIR MAIN CONTENT ===== -->

<!-- Panggil Footer -->
<?php include 'admin_footer.php'; ?>