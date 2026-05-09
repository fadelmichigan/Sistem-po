<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan ini adalah admin yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

// 1. Cek apakah form disubmit (method POST) dan ada data barang
if ($_SERVER['REQUEST_MEATHOD'] == 'POST' && !empty($_POST['id_barang'])) {

    try {
        // 2. Ambil data induk dari form
        // Konversi string kosong dari <select> menjadi NULL untuk database
        $id_distributor = !empty($_POST['id_distributor']) ? $_POST['id_distributor'] : null;
        $tanggal_masuk = $_POST['tanggal_masuk'];
        $catatan = $_POST['catatan'];
        $dicatat_oleh = $_SESSION['username'] ?? 'Admin'; // Ambil nama admin yang login

        // Ambil array data barang
        $id_barang_arr = $_POST['id_barang'];
        $jumlah_arr = $_POST['jumlah'];

        // 3. Mulai Transaksi Database
        $pdo->beginTransaction();

        // 4. Simpan data ke tabel 'stok_masuk' (Tabel Induk)
        $sql_induk = "INSERT INTO stok_masuk (id_distributor, tanggal_masuk, dicatat_oleh, catatan) 
                      VALUES (?, ?, ?, ?)";
        $stmt_induk = $pdo->prepare($sql_induk);
        $stmt_induk->execute([$id_distributor, $tanggal_masuk, $dicatat_oleh, $catatan]);

        // 5. Dapatkan ID dari 'stok_masuk' yang baru saja di-insert
        $id_stok_masuk = $pdo->lastInsertId();

        // 6. Siapkan query untuk menyimpan detail dan update stok
        $sql_detail = "INSERT INTO stok_masuk_detail (id_stok_masuk, id_barang, jumlah) 
                       VALUES (?, ?, ?)";
        $stmt_detail = $pdo->prepare($sql_detail);

        $sql_stok = "UPDATE barang SET stok = stok + ? WHERE id = ?";
        $stmt_stok = $pdo->prepare($sql_stok);

        // 7. Loop semua barang yang diterima
        foreach ($id_barang_arr as $index => $id_barang) {
            $jumlah = (int)$jumlah_arr[$index];

            // Hanya proses jika jumlah lebih dari 0 dan barang dipilih
            if ($jumlah > 0 && !empty($id_barang)) {
                // a. Simpan ke stok_masuk_detail
                $stmt_detail->execute([$id_stok_masuk, $id_barang, $jumlah]);
                
                // b. Tambah stok di tabel 'barang'
                $stmt_stok->execute([$jumlah, $id_barang]);
            }
        }

        // 8. Jika semua query berhasil, commit (simpan permanen)
        $pdo->commit();

        // 9. Redirect ke halaman STOK GUDANG agar admin bisa lihat stoknya bertambah
        // Kita tambahkan notifikasi sukses di langkah berikutnya
        header("Location: stok_gudang.php?status=stok_masuk_sukses");
        exit;

    } catch (Exception $e) {
        // 10. Jika ada satu saja query yang gagal, batalkan semua (rollback)
        $pdo->rollBack();
        
        // Tampilkan pesan error
        die("Gagal menyimpan stok masuk: " . $e->getMessage());
    }

} else {
    // Jika data tidak lengkap atau diakses langsung, kembalikan ke form
    header("Location: stok_masuk.php?status=gagal");
    exit;
}
?>