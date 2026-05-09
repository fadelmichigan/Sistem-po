<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil data profil
    $nama_perusahaan = $_POST['nama_perusahaan'];
    $email = $_POST['email'];
    $no_telepon = $_POST['no_telepon'];
    $npwp = $_POST['npwp'];
    
    // Ambil data login
    $username = $_POST['username'];
    $password_plain = $_POST['password'];
    $role = 'user'; // Otomatis set role sebagai 'user'

    // Validasi data
    if (empty($nama_perusahaan) || empty($email) || empty($username) || empty($password_plain)) {
        die("Data tidak lengkap. Harap isi semua field yang wajib.");
    }

    // Hash password untuk keamanan
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

    // Mulai Transaksi
    $pdo->beginTransaction();

    try {
        // === QUERY 1: Simpan ke tabel 'users' ===
        // (Asumsi kolom Anda: username, password, role)
        $sql_user = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt_user = $pdo->prepare($sql_user);
        $stmt_user->execute([$username, $password_hashed, $role]);
        
        // Ambil ID dari user yang baru saja dibuat
        $id_user_baru = $pdo->lastInsertId();

        // === QUERY 2: Simpan ke tabel 'perusahaan' ===
        // (Sekarang kita masukkan 'id_user' sebagai link)
        $sql_perusahaan = "INSERT INTO perusahaan (nama_perusahaan, email, no_telepon, npwp, id_user) 
                           VALUES (?, ?, ?, ?, ?)";
        $stmt_perusahaan = $pdo->prepare($sql_perusahaan);
        $stmt_perusahaan->execute([
            $nama_perusahaan,
            $email,
            $no_telepon,
            $npwp,
            $id_user_baru // Ini adalah 'link' nya
        ]);

        // Jika kedua query berhasil, simpan permanen
        $pdo->commit();

        // Redirect kembali ke halaman customer
        header("Location: customer.php?status=sukses");
        exit;

    } catch (PDOException $e) {
        // Jika salah satu gagal, batalkan semua
        $pdo->rollBack();
        
        // Tampilkan error (bisa dicek jika username sudah ada)
        if ($e->errorInfo[1] == 1062) { // Error 'Duplicate entry'
             die("Gagal menyimpan data: Username '$username' sudah terpakai. Silakan gunakan username lain.");
        } else {
             die("Gagal menyimpan data: ". $e->getMessage());
        }
    }

} else {
    header("Location: customer.php");
    exit;
}
?>