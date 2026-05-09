<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['id_barang'])) {

    try {
        // Ambil ID user login
        $id_user_login = $_SESSION['user_id']; 

        // Ambil ID perusahaan
        $stmt_perusahaan = $pdo->prepare("SELECT id FROM perusahaan WHERE id_user = ?");
        $stmt_perusahaan->execute([$id_user_login]);
        $perusahaan = $stmt_perusahaan->fetch(PDO::FETCH_ASSOC);

        if (!$perusahaan) {
            throw new Exception("Data perusahaan tidak ditemukan.");
        }
        $id_perusahaan = $perusahaan['id'];

        // Ambil data form (termasuk nama penanda tangan)
        $tanggal_pesan = $_POST['tanggal_pesan'];
        $waktu_kirim = $_POST['waktu_kirim'];
        $dibuat_oleh = $_POST['dibuat_oleh'];       // <-- DATA BARU
        $diketahui_oleh = $_POST['diketahui_oleh']; // <-- DATA BARU
        
        $id_barang_arr = $_POST['id_barang'];
        $jumlah_arr = $_POST['jumlah'];
        $harga_arr = $_POST['harga']; 

        // Hitung Total
        $grand_total = 0;
        foreach ($jumlah_arr as $index => $jumlah) {
            $jumlah_int = (int)$jumlah;
            if ($jumlah_int > 0) {
                $grand_total += $jumlah_int * (float)$harga_arr[$index];
            }
        }

        $pdo->beginTransaction();

        // Simpan ke tabel pemesanan (TAMBAHKAN KOLOM BARU DI SINI)
        $sql_po = "INSERT INTO pemesanan (id_perusahaan, tanggal_pesan, waktu_kirim, total_harga, status, dibuat_oleh, diketahui_oleh) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_po = $pdo->prepare($sql_po);
        $stmt_po->execute([$id_perusahaan, $tanggal_pesan, $waktu_kirim, $grand_total, 'Baru', $dibuat_oleh, $diketahui_oleh]);

        $id_pemesanan = $pdo->lastInsertId();

        // Simpan detail barang
        $sql_detail = "INSERT INTO pemesanan_detail (id_pemesanan, id_barang, jumlah, harga_satuan, subtotal) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_detail = $pdo->prepare($sql_detail);

        $sql_stok = "UPDATE barang SET stok = stok - ? WHERE id = ?";
        $stmt_stok = $pdo->prepare($sql_stok);

        foreach ($id_barang_arr as $index => $id_barang) {
            $jumlah = (int)$jumlah_arr[$index];
            $harga_satuan = (float)$harga_arr[$index];
            $subtotal = $jumlah * $harga_satuan;

            if ($jumlah > 0 && !empty($id_barang)) {
                $stmt_detail->execute([$id_pemesanan, $id_barang, $jumlah, $harga_satuan, $subtotal]);
                $stmt_stok->execute([$jumlah, $id_barang]);
            }
        }

        $pdo->commit();
        header("Location: sukses_po.php?id=" . $id_pemesanan);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Gagal menyimpan pesanan: " . $e->getMessage();
        exit;
    }

} else {
    header("Location: form_pemesanan.php?status=gagal");
    exit;
}
?>