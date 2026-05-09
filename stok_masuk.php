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

// 1. Logika PHP
// Ambil daftar distributor untuk dropdown
$stmt_dist = $pdo->query("SELECT * FROM distributor ORDER BY nama_distributor ASC");
$distributors = $stmt_dist->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar barang untuk dropdown
$stmt_brg = $pdo->query("SELECT * FROM barang ORDER BY nama_barang ASC");
$barang = $stmt_brg->fetchAll(PDO::FETCH_ASSOC);

// 2. Set Halaman Aktif
$active_page = 'stok_masuk';

// 3. Panggil Header
include 'admin_header.php';
?>

<!-- 4. KONTEN UTAMA HALAMAN INI -->
<div class="main-header">
    <h2 class="mb-0">Catat Stok Masuk (dari Distributor)</h2>
</div>

<!-- Form akan dikirim ke 'simpan_stok_masuk.php' -->
<form action="simpan_stok_masuk.php" method="POST">
    
    <!-- Bagian Info Pengiriman -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Data Penerimaan</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="id_distributor" class="form-label">Asal Distributor</label>
                    <select name="id_distributor" id="id_distributor" class="form-select">
                        <option value="">-- Pilih Distributor (Opsional) --</option>
                        <?php foreach($distributors as $dist): ?>
                            <option value="<?= $dist['id'] ?>"><?= htmlspecialchars($dist['nama_distributor']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tanggal_masuk" class="form-label">Tanggal & Waktu Masuk</label>
                    <input type="datetime-local" class="form-control" name="tanggal_masuk" id="tanggal_masuk" required>
                </div>
                <div class="col-12">
                    <label for="catatan" class="form-label">Catatan (Opsional)</label>
                    <textarea name="catatan" id="catatan" class="form-control" rows="2" placeholder="Cth: Pembelian rutin bulanan"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Daftar Barang -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Barang yang Masuk</h5>
            <button type="button" id="tambah-baris" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah Barang
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered align-middle" id="tabel-barang-masuk">
                <thead class="table-light">
                    <tr>
                        <th>Nama Barang</th>
                        <th style="width: 150px;">Jumlah Masuk</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Template Baris (akan di-clone oleh jQuery) -->
                    <tr>
                        <td>
                            <select name="id_barang[]" class="form-select barang-select" required>
                                <option value="">-- Pilih Barang --</option>
                                <?php foreach($barang as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nama_barang']) ?> (Stok Saat Ini: <?= $b['stok'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="jumlah[]" class="form-control" min="1" value="1" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm hapus-baris">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">
        <i class="bi bi-box-arrow-in-right"></i> Simpan Stok Masuk
    </button>
</form>

<!-- 5. Panggil Footer (yang berisi Bootstrap JS) -->
<?php include 'admin_footer.php'; ?>

<!-- 6. Script Tambahan (jQuery) -->
<!-- Muat jQuery (diperlukan untuk script tabel dinamis) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
$(document).ready(function() {
    // Simpan template baris pertama
    var rowTemplate = $('#tabel-barang-masuk tbody tr:first').clone();
    
    // Aksi Tombol Tambah Baris
    $('#tambah-baris').click(function(){
        let newRow = rowTemplate.clone();
        newRow.find('select').val(''); // Reset dropdown
        newRow.find('input').val('1'); // Set jumlah default ke 1
        $('#tabel-barang-masuk tbody').append(newRow);
    });

    // Aksi Tombol Hapus Baris
    // Gunakan $(document).on() agar berfungsi pada baris yang baru ditambah
    $(document).on('click', '.hapus-baris', function(){
        // Pastikan minimal ada 1 baris
        if($('#tabel-barang-masuk tbody tr').length > 1){
            $(this).closest('tr').remove();
        } else {
            alert('Minimal harus ada satu barang.');
        }
    });
});
</script>