<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "db_po_invoice_final");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$id_pemesanan = isset($_GET['id_pemesanan']) ? (int)$_GET['id_pemesanan'] : 0;
if ($id_pemesanan <= 0) die("ID Pemesanan tidak valid");

// Ambil data pemesanan
$sql = "SELECT p.*, u.nama_perusahaan, u.email_perusahaan, u.npwp, u.no_telp 
        FROM pemesanan p
        JOIN users u ON p.id_user = u.id_user
        WHERE p.id_pemesanan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pemesanan);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) die("Data pemesanan tidak ditemukan");
$data = $result->fetch_assoc();

// Buat invoice baru
$tanggal_invoice = date("Y-m-d H:i:s");
$stmt2 = $conn->prepare("INSERT INTO invoice (id_pembeli, tanggal_invoice) VALUES (?, ?)");
$stmt2->bind_param("is", $data['id_user'], $tanggal_invoice);
$stmt2->execute();
$id_invoice = $conn->insert_id;

// Masukkan ke detail_pemesanan
$stmt3 = $conn->prepare("INSERT INTO detail_pemesanan (id_invoice, id_barang, jumlah)
                         VALUES (?, ?, ?)");
$stmt3->bind_param("iii", $id_invoice, $data['id_barang'], $data['jumlah']);
$stmt3->execute();

// Update status
$conn->query("UPDATE pemesanan SET status = 'Sudah Ada Invoice' WHERE id_pemesanan = $id_pemesanan");

$conn->close();
header("Location: invoice.php?id_invoice=$id_invoice");
exit;
?>
