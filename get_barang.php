<?php
include 'koneksi.php';

if (isset($_GET['kodeproduk'])) {
    $kode = $_GET['kodeproduk'];
    $query = mysqli_query($conn, "SELECT * FROM master_barang WHERE kodeproduk='$kode'");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        echo "
        <h3>Form Input Master Barang</h3>
        <label>Kode Produk:</label><br>
        <input type='text' name='kodeproduk_detail' value='{$data['kodeproduk']}' readonly><br><br>

        <label>Nama Produk:</label><br>
        <input type='text' name='namaproduk' value='{$data['namaproduk']}' readonly><br><br>

        <label>Merek:</label><br>
        <input type='text' name='merek' value='{$data['merek']}' readonly><br><br>

        <label>Jenis:</label><br>
        <input type='text' name='jenis' value='{$data['jenis']}' readonly><br><br>

        <label>Harga:</label><br>
        <input type='text' name='harga' value='{$data['harga1']}' readonly><br><br>
        ";
    } else {
        echo "<p>Barang tidak ditemukan.</p>";
    }
}
?>
