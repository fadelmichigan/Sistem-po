<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Set halaman aktif untuk sidebar
$active_page = 'pengiriman'; 

// Ambil semua data pemesanan yang statusnya 'Selesai' (siap kirim)
$sql = "SELECT 
            po.id, 
            po.tanggal_pesan, 
            po.total_harga, 
            po.status, 
            p.nama_perusahaan 
        FROM pemesanan po
        JOIN perusahaan p ON po.id_perusahaan = p.id
        WHERE po.status = 'Selesai'
        ORDER BY po.tanggal_pesan ASC";

$stmt = $pdo->query($sql);
$daftar_kirim = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'admin_header.php'; // Panggil header
?>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

    <!-- NOTIFIKASI ALERT -->
    <?php if(isset($_GET['status']) && $_GET['status'] == 'kirim_sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Berhasil! Pesanan telah dicatat sebagai "Dikirim" dan dipindahkan ke Arsip Invoice.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <!-- BATAS NOTIFIKASI -->

    <div class="main-header">
        <h2 class="mb-0">Manajemen Pengiriman</h2>
        <!-- Tidak ada tombol "Tambah" di sini -->
    </div>

    <!-- Tabel untuk menampilkan PO yang siap kirim -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Daftar Pesanan Siap Kirim (Status: Selesai)</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Nama Perusahaan</th>
                        <th>Tanggal Pesan</th>
                        <th>Total Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_kirim)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Tidak ada pesanan yang siap dikirim.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_kirim as $po): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($po['id']) ?></strong></td>
                                <td><?= htmlspecialchars($po['nama_perusahaan']) ?></td>
                                <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($po['tanggal_pesan']))) ?></td>
                                <td>Rp <?= number_format($po['total_harga'], 0, ',', '.') ?></td>
                                <td>
                                    <!-- 
                                        TOMBOL INI DIGANTI:
                                        - Menggunakan <button>
                                        - Menargetkan 'shippingModal'
                                        - Membawa 'data-po-id'
                                    -->
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shippingModal" data-po-id="<?= $po['id'] ?>">
                                        <i class="bi bi-truck"></i> Catat Pengiriman
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>
<!-- ===== AKHIR MAIN CONTENT ===== -->


<!-- 
=====================================
MODAL (POPUP) INPUT RESI
Tambahkan ini di bagian bawah file
=====================================
-->
<div class="modal fade" id="shippingModal" tabindex="-1" aria-labelledby="shippingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="shippingModalLabel">Catat Detail Pengiriman</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Form ini akan mengirim data ke 'proses_pengiriman.php' -->
            <form action="proses_pengiriman.php" method="POST">
                <div class="modal-body">
                    <p>Anda akan memproses pesanan <strong id="modal-po-id-display">#...</strong></p>
                    
                    <!-- Input ID Pesanan (Tersembunyi) -->
                    <input type="hidden" name="id_pemesanan" id="modal-po-id-input" value="">
                    
                    <div class="mb-3">
                        <label for="kurir" class="form-label">Nama Kurir / Ekspedisi</label>
                        <input type="text" class="form-control" id="kurir" name="kurir" placeholder="Contoh: JNE, SiCepat, Kurir Internal" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_resi" class="form-label">No. Resi / ID Pengiriman</label>
                        <input type="text" class="form-control" id="no_resi" name="no_resi" placeholder="Kosongkan jika tidak ada">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan & Tandai Dikirim</button>
                </div>
            </form>
            
        </div>
    </div>
</div>

<!-- Panggil Footer (berisi script Bootstrap JS) -->
<?php include 'admin_footer.php'; ?>

<!-- 
=====================================
JAVASCRIPT UNTUK MODAL
Tambahkan ini di paling bawah, setelah admin_footer.php
=====================================
-->
<script>
// Tangkap event saat modal akan dibuka
const shippingModal = document.getElementById('shippingModal');
if (shippingModal) {
    shippingModal.addEventListener('show.bs.modal', event => {
        // Tombol yang di-klik
        const button = event.relatedTarget;
        
        // Ambil ID pesanan dari 'data-po-id'
        const poId = button.getAttribute('data-po-id');
        
        // Cari elemen di dalam modal
        const modalIdDisplay = shippingModal.querySelector('#modal-po-id-display');
        const modalIdInput = shippingModal.querySelector('#modal-po-id-input');
        
        // Set nilai ke dalam modal
        modalIdDisplay.textContent = '#' + poId;
        modalIdInput.value = poId;
    });
}
</script>