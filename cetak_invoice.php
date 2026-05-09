<?php
require 'vendor/autoload.php'; // Load dompdf

use Dompdf\Dompdf;

// Koneksi database
$conn = new mysqli("localhost","root","","db_po_invoice");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

// Ambil nomor pesanan
$no_pesan = $_GET['no_pesan'] ?? null;
if (!$no_pesan) die("Nomor pesanan tidak ditemukan!");

// Query invoice + pemesanan + barang
$sql = "
SELECT 
    i.id_invoice,
    i.tanggal_invoice,
    i.total_harga,
    p.total_pemesanan,
    p.tgl_pesan,
    p.jam_pesan,
    p.waktu_kirim,
    m.kode_produk,
    m.nama_produk,
    m.merek,
    m.jenis,
    m.satuan,
    m.harga1
FROM invoice i
JOIN pemesanan p ON i.no_pesan = p.no_pesan
JOIN master_barang m ON p.id_barang = m.id_barang
WHERE i.no_pesan = '$no_pesan'
";

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else die("Data invoice tidak ditemukan.");

$conn->close();

// Buat HTML invoice
$html = '
<h2 style="text-align:center;">Invoice Pemesanan</h2>
<p><strong>Tanggal Invoice:</strong> '.$row['tanggal_invoice'].'</p>
<table border="1" cellpadding="6" cellspacing="0" width="100%">
<tr><th>Kode Produk</th><td>'.$row['kode_produk'].'</td></tr>
<tr><th>Nama Produk</th><td>'.$row['nama_produk'].'</td></tr>
<tr><th>Merek</th><td>'.$row['merek'].'</td></tr>
<tr><th>Jenis</th><td>'.$row['jenis'].'</td></tr>
<tr><th>Satuan</th><td>'.$row['satuan'].'</td></tr>
<tr><th>Harga Satuan</th><td>Rp '.number_format($row['harga1'],0,',','.').'</td></tr>
<tr><th>Total Pemesanan</th><td>'.$row['total_pemesanan'].'</td></tr>
<tr><th>Tanggal Pesan</th><td>'.$row['tgl_pesan'].' '.$row['jam_pesan'].'</td></tr>
<tr><th>Waktu Kirim</th><td>'.$row['waktu_kirim'].'</td></tr>
<tr><th>Total Harga</th><td><strong>Rp '.number_format($row['total_harga'],0,',','.').'</strong></td></tr>
</table>
';

// Inisialisasi Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output PDF ke browser
$dompdf->stream("invoice_{$no_pesan}.pdf", ["Attachment" => false]);
exit;
