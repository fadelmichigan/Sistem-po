<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo
require 'fpdf.php';     // Menggunakan file fpdf.php yang Anda miliki

// 1. Ambil ID dari URL (dikirim dari admin.php)
$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pemesanan <= 0) {
    die("ID pesanan tidak valid!");
}

// 2. Query yang BENAR (sesuai database kita)
$sql = "
    SELECT 
        po.id, po.tanggal_pesan, po.waktu_kirim, po.total_harga,
        p.nama_perusahaan, p.email, p.npwp, p.no_telepon,
        b.nama_barang, b.merek, b.jenis, b.satuan,
        pd.harga_satuan, pd.jumlah
    FROM pemesanan po
    JOIN perusahaan p ON po.id_perusahaan = p.id
    JOIN pemesanan_detail pd ON pd.id_pemesanan = po.id
    JOIN barang b ON pd.id_barang = b.id
    WHERE po.id = ?
";

// 3. Eksekusi query menggunakan $pdo
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_pemesanan]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    die("Data pesanan tidak ditemukan!");
}

// Ambil data info perusahaan & pesanan dari baris pertama
$data = $items[0];

// 4. Mulai buat PDF
$pdf = new FPDF('P', 'mm', 'A4'); // Menggunakan class FPDF dasar
$pdf->AddPage();

// Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'INVOICE PEMBELIAN', 0, 1, 'C');
$pdf->Ln(10); // Jarak

// Info Perusahaan (menggunakan kolom yang benar)
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Data Pembeli:', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 8, 'Nama Perusahaan', 0);
$pdf->Cell(5, 8, ':', 0);
$pdf->Cell(0, 8, $data['nama_perusahaan'], 0, 1);

$pdf->Cell(40, 8, 'Email', 0);
$pdf->Cell(5, 8, ':', 0);
$pdf->Cell(0, 8, $data['email'], 0, 1);

$pdf->Cell(40, 8, 'NPWP', 0);
$pdf->Cell(5, 8, ':', 0);
$pdf->Cell(0, 8, $data['npwp'], 0, 1);

$pdf->Cell(40, 8, 'No. Telepon', 0);
$pdf->Cell(5, 8, ':', 0);
$pdf->Cell(0, 8, $data['no_telepon'], 0, 1);

// Info Tanggal
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 12, 'Detail Pesanan:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 8, 'Tanggal Pesan', 0);
$pdf->Cell(5, 8, ':', 0);
$pdf->Cell(0, 8, date('d F Y, H:i', strtotime($data['tanggal_pesan'])), 0, 1);

$pdf->Cell(40, 8, 'Waktu Kirim', 0);
$pdf->Cell(5, 8, ':', 0);
$pdf->Cell(0, 8, date('d F Y, H:i', strtotime($data['waktu_kirim'])), 0, 1);
$pdf->Ln(5);

// 5. Tabel Produk
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(230, 230, 230); // Warna abu-abu untuk header
$pdf->Cell(65, 10, 'Produk', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Merek', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Harga Satuan', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Jumlah', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Subtotal', 1, 1, 'C', true); // '1' di akhir untuk pindah baris

// 6. Loop untuk isi tabel
$pdf->SetFont('Arial', '', 11);
foreach ($items as $item) {
    $subtotal = $item['harga_satuan'] * $item['jumlah'];
    $pdf->Cell(65, 10, $item['nama_barang'], 1);
    $pdf->Cell(30, 10, $item['merek'], 1);
    $pdf->Cell(40, 10, 'Rp ' . number_format($item['harga_satuan'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(20, 10, $item['jumlah'] . ' ' . $item['satuan'], 1, 0, 'C');
    $pdf->Cell(35, 10, 'Rp ' . number_format($subtotal, 0, ',', '.'), 1, 1, 'R'); // '1' di akhir
}

// 7. Grand Total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(155, 10, 'GRAND TOTAL', 1, 0, 'R');
$pdf->Cell(35, 10, 'Rp ' . number_format($data['total_harga'], 0, ',', '.'), 1, 1, 'R');
$pdf->Ln(20);

// 8. Tanda Tangan
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(95, 10, 'Disetujui oleh,', 0, 0, 'C');
$pdf->Cell(95, 10, 'Diterima oleh,', 0, 1, 'C');
$pdf->Ln(20); // Jarak untuk ttd
$pdf->Cell(95, 10, '( Admin Cemara Ban )', 0, 0, 'C');
$pdf->Cell(95, 10, '( ' . $data['nama_perusahaan'] . ' )', 0, 1, 'C');


// 9. Output PDF
$pdf->Output('I', 'Invoice-PO-' . $id_pemesanan . '.pdf');
?>