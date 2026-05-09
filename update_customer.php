<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil semua data dari form
    $id_perusahaan = $_POST['id_perusahaan'];
    $id_user = $_POST['id_user'];
    
    // Data profil
    $nama_perusahaan = $_POST['nama_perusahaan'];
    $email = $_POST['email'];
    $no_telepon = $_POST['no_telepon'];
    $npwp = $_POST['npwp'];
    
    // Data login
    $username = $_POST['username'];
    $password_plain = $_POST['password']; // Password baru (jika diisi)

    // Validasi
    if (empty($id_perusahaan) || empty($id_user) || empty($nama_perusahaan) || empty($username)) {
        die("Data tidak lengkap. Gagal mengupdate.");
    }

    // Mulai Transaksi
    $pdo->beginTransaction();

    try {
        // === QUERY 1: Update tabel 'perusahaan' ===
        $sql_p = "UPDATE perusahaan 
                  SET nama_perusahaan = ?, email = ?, no_telepon = ?, npwp = ? 
                  WHERE id = ?";
        $stmt_p = $pdo->prepare($sql_p);
        $stmt_p->execute([$nama_perusahaan, $email, $no_telepon, $npwp, $id_perusahaan]);

        // === QUERY 2: Update tabel 'users' ===
        
        // Cek apakah password diisi atau tidak
        if (!empty($password_plain)) {
            // Jika password diisi, HASH dan update
            $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
            $sql_u = "UPDATE users SET username = ?, password = ? WHERE id = ?";
            $stmt_u = $pdo->prepare($sql_u);
            $stmt_u->execute([$username, $password_hashed, $id_user]);
        } else {
            // Jika password kosong, JANGAN update password, hanya username
            $sql_u = "UPDATE users SET username = ? WHERE id = ?";
            $stmt_u = $pdo->prepare($sql_u);
            $stmt_u->execute([$username, $id_user]);
        }

        // Jika kedua query berhasil, simpan permanen
        $pdo->commit();

        // Redirect kembali ke halaman customer
        header("Location: customer.php?status=update_sukses");
        exit;

    } catch (PDOException $e) {
        // Jika salah satu gagal, batalkan semua
        $pdo->rollBack();
        
        if ($e->errorInfo[1] == 1062) { // Error 'Duplicate entry'
             die("Gagal mengupdate data: Username '$username' sudah terpakai. Silakan gunakan username lain.");
        } else {
             die("Gagal mengupdate data: ". $e->getMessage());
        }
    }

} else {
    header("Location: customer.php");
    exit;
}
?>