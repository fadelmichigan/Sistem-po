<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo
require 'fpdf.php';     // Memanggil library FPDF

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

// 1. Validasi Input Tanggal
if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST['tanggal_mulai']) || empty($_POST['tanggal_selesai'])) {
    die("Harap pilih rentang tanggal yang valid.");
}

$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];

// Tambahkan ' 23:59:59' ke tanggal selesai agar mencakup seluruh hari itu
$tanggal_selesai_full = $tanggal_selesai . ' 23:59:59';

// 2. Query Data Laporan
// Kita ambil semua pesanan yang statusnya BUKAN 'Baru' dalam rentang tanggal
$sql = "
    SELECT 
        po.id, 
        po.tanggal_pesan, 
        po.total_harga, 
        po.status, 
        p.nama_perusahaan
    FROM pemesanan po
    JOIN perusahaan p ON po.id_perusahaan = p.id
    WHERE 
        po.status != 'Baru' AND 
        po.tanggal_pesan BETWEEN ? AND ?
    ORDER BY po.tanggal_pesan ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$tanggal_mulai, $tanggal_selesai_full]);
$laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Mulai Pembuatan PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// === Header Dokumen ===
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Laporan Penjualan', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 7, 'Periode: ' . date('d M Y', strtotime($tanggal_mulai)) . ' s/d ' . date('d M Y', strtotime($tanggal_selesai)), 0, 1, 'C');
$pdf->Ln(10); // Jarak

// === Header Tabel ===
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230); // Warna abu-abu
$pdf->Cell(15, 10, 'ID PO', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Customer', 1, 0, 'C', true);
$pdf->Cell(45, 10, 'Tanggal Pesan', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Total Harga', 1, 1, 'C', true); // '1' di akhir untuk pindah baris

// === Isi Tabel ===
$pdf->SetFont('Arial', '', 9);
$grand_total = 0;
if (empty($laporan)) {
    $pdf->Cell(0, 10, 'Tidak ada data penjualan pada periode ini.', 1, 1, 'C');
} else {
    foreach ($laporan as $item) {
        $pdf->Cell(15, 8, '#' . $item['id'], 1);
        $pdf->Cell(60, 8, $item['nama_perusahaan'], 1);
        $pdf->Cell(45, 8, date('d M Y, H:i', strtotime($item['tanggal_pesan'])), 1);
        $pdf->Cell(30, 8, $item['status'], 1);
        $pdf->Cell(40, 8, 'Rp ' . number_format($item['total_harga'], 0, ',', '.'), 1, 1, 'R');
        
        $grand_total += $item['total_harga'];
    }
}

// === Total Keseluruhan ===
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(150, 10, 'TOTAL PENJUALAN KESELURUHAN', 1, 0, 'R', true);
$pdf->Cell(40, 10, 'Rp ' . number_format($grand_total, 0, ',', '.'), 1, 1, 'R', true);

// 4. Output PDF
$pdf->Output('I', 'Laporan-Penjualan-' . $tanggal_mulai . '-' . $tanggal_selesai . '.pdf');
?>