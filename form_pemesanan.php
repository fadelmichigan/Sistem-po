<?php 
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'user'){ 
    header("Location: login.php"); 
    exit; 
}

include "koneksi.php";

// Ambil data barang
$barang = $pdo->query("SELECT * FROM barang")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Form Purchase Order</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">

<!-- Header Tombol Navigasi -->
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <!-- Tombol Kembali ke Home -->
        <a href="index.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>
        
        <div class="d-flex gap-2">
            <a href="daftar_pesanan.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-list-ul"></i> Riwayat
            </a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</div>

<!-- Form Utama -->
<div class="container mt-2">
  
  <?php if(isset($_GET['status']) && $_GET['status'] == 'gagal'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Gagal Menyimpan Pesanan!</strong><br>
        <?php echo isset($_GET['msg']) ? $_GET['msg'] : 'Terjadi kesalahan saat memproses pesanan.'; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <b>Form Purchase Order Baru</b>
    </div>
    <div class="card-body">

      <!-- DATA PEMBELI DIHAPUS DARI TAMPILAN (Sesuai Permintaan) -->
      <!-- Data pembeli sudah diambil otomatis di backend (simpan_po.php) -->

      <h5 class="text-primary mb-3">Pilih Barang</h5>
      
      <form action="simpan_po.php" method="post">
      <table class="table table-bordered align-middle" id="tabel-barang">
        <thead class="table-light">
          <tr>
            <th>Nama Produk</th>
            <th>Merek</th>
            <th>Jenis</th>
            <th>Satuan</th>
            <th>Harga</th>
            <th>Jumlah</th>
            <th>Total</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <select name="id_barang[]" class="form-select barang-select" required>
                <option value="">Pilih Barang</option>
                <?php foreach($barang as $b): ?>
                  <option 
                    value="<?= $b['id'] ?>" 
                    data-merek="<?= htmlspecialchars($b['merek'] ?? '') ?>" 
                    data-jenis="<?= htmlspecialchars($b['jenis'] ?? '') ?>" 
                    data-satuan="<?= htmlspecialchars($b['satuan'] ?? '') ?>" 
                    data-harga="<?= htmlspecialchars($b['harga'] ?? 0) ?>">
                    <?= htmlspecialchars($b['nama_barang'] ?? '') ?> - Rp<?= number_format($b['harga'] ?? 0,0,',','.') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="text" name="merek[]" class="form-control" readonly></td>
            <td><input type="text" name="jenis[]" class="form-control" readonly></td>
            <td><input type="text" name="satuan[]" class="form-control" readonly></td>
            <td><input type="number" name="harga[]" class="form-control" readonly></td>
            <td><input type="number" name="jumlah[]" class="form-control jumlah" min="1" value="1"></td>
            <td><input type="number" name="total[]" class="form-control total" value="0" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm hapus-baris">Hapus</button></td>
          </tr>
        </tbody>
        <tfoot class="table-group-divider fw-bold fs-5">
            <tr>
                <td colspan="6" class="text-end">Grand Total:</td>
                <td id="grand-total-display" class="text-success">Rp 0</td>
                <td></td>
            </tr>
        </tfoot>
      </table>

      <button type="button" id="tambah-baris" class="btn btn-success mb-3">+ Tambah Barang</button>

      <hr class="my-4">
      <h5 class="text-primary mb-3">Data Invoice & Tanda Tangan</h5>
      
      <div class="row g-3 mb-4">
          <div class="col-md-6">
              <label>Tanggal Pesan</label>
              <input type="datetime-local" name="tanggal_pesan" class="form-control" required>
          </div>
          <div class="col-md-6">
              <label>Waktu Kirim</label>
              <input type="datetime-local" name="waktu_kirim" class="form-control" required>
          </div>
          
          <div class="col-md-6">
              <label>Dibuat Oleh (Nama Staff Anda)</label>
              <input type="text" name="dibuat_oleh" class="form-control" placeholder="Contoh: Budi Santoso" required>
          </div>
          <div class="col-md-6">
              <label>Diketahui Oleh (Manager/SPV Anda)</label>
              <input type="text" name="diketahui_oleh" class="form-control" placeholder="Contoh: Siti Aminah, S.E." required>
          </div>
      </div>
      
      <button type="submit" class="btn btn-primary w-100">Simpan Pemesanan</button>
      </form>

    </div>
  </div>
</div>

<script>
function hitungTotal(row){
  let harga = parseFloat(row.find('input[name="harga[]"]').val()) || 0;
  let qty = parseFloat(row.find('input[name="jumlah[]"]').val()) || 0;
  row.find('input[name="total[]"]').val(harga * qty);
  updateGrandTotal();
}

function updateGrandTotal() {
    let grandTotal = 0;
    $('#tabel-barang tbody .total').each(function() {
        grandTotal += parseFloat($(this).val()) || 0;
    });
    
    let formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
    $('#grand-total-display').text(formatter.format(grandTotal));
}

$(document).on('change', '.barang-select', function() {
  let row = $(this).closest('tr');
  let selected = $(this).find('option:selected');
  row.find('input[name="merek[]"]').val(selected.data('merek'));
  row.find('input[name="jenis[]"]').val(selected.data('jenis'));
  row.find('input[name="satuan[]"]').val(selected.data('satuan'));
  row.find('input[name="harga[]"]').val(selected.data('harga'));
  
  let jumlahInput = row.find('input[name="jumlah[]"]');
  if (jumlahInput.val() === '' || parseFloat(jumlahInput.val()) <= 0) {
      jumlahInput.val(1);
  }
  hitungTotal(row);
});

$(document).on('input', '.jumlah', function() {
  hitungTotal($(this).closest('tr'));
});

$('#tambah-baris').click(function(){
  let newRow = $('#tabel-barang tbody tr:first').clone();
  newRow.find('select.barang-select').val('');
  newRow.find('input[type="text"]').val('');
  newRow.find('input[type="number"]').val('');
  newRow.find('input[name="jumlah[]"]').val('1');
  newRow.find('input[name="total[]"]').val('0'); 
  $('#tabel-barang tbody').append(newRow);
  updateGrandTotal();
});

$(document).on('click', '.hapus-baris', function(){
  if($('#tabel-barang tbody tr').length > 1){
    $(this).closest('tr').remove();
    updateGrandTotal();
  } else {
    alert('Minimal satu barang!');
  }
});

$(document).ready(function() {
    hitungTotal($('#tabel-barang tbody tr:first'));
});
</script>

</body>
</html>