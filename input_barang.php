<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Master Barang</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        form { width: 400px; }
        label { display: block; margin-top: 10px; }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            box-sizing: border-box;
        }
        button {
            margin-top: 15px;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover { background: #45a049; }
    </style>
</head>
<body>

<h2>Form Input Master Barang</h2>

<form action="simpan_barang.php" method="POST" enctype="multipart/form-data">
    <label>Kode Produk</label>
    <input type="text" name="kodeproduk" required>

    <label>Nama Produk</label>
    <input type="text" name="namaproduk" required>

    <label>Merek</label>
    <input type="text" name="merek" required>

    <label>Jenis</label>
    <input type="text" name="jenis" required>

    <label>Satuan</label>
    <input type="text" name="satuan" required>

    <label>Stok</label>
    <input type="number" name="stok" required>

    <label>Gambar</label>
    <input type="file" name="gambar" accept="image/*">

    <label>Deskripsi</label>
    <textarea name="deskripsi"></textarea>

    <label>Harga 1</label>
    <input type="number" name="harga1" required>

    <label>Harga 2</label>
    <input type="number" name="harga2">

    <label>Harga 3</label>
    <input type="number" name="harga3">

    <label>Harga 4</label>
    <input type="number" name="harga4">

    <button type="submit">Simpan Barang</button>
</form>

</body>
</html>
