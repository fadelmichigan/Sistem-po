<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Ambil data dari form POST
    $id_perusahaan = $_POST['id_perusahaan'];
    $id_user = $_POST['id_user'];
    $nama_perusahaan = $_POST['nama_perusahaan'];
    $email = $_POST['email'];
    $no_telepon = $_POST['no_telepon'];
    $npwp = $_POST['npwp'];
    
    $username = $_POST['username'];
    $password_plain = $_POST['password']; // Bisa kosong jika tidak diubah
    
    $logo_lama = $_POST['logo_lama']; // Nama file logo yang saat ini dipakai

    if (empty($id_perusahaan) || empty($nama_perusahaan)) {
        die("Data penting tidak lengkap.");
    }

    // 2. --- PROSES UPLOAD LOGO BARU ---
    $logo_final = $logo_lama; // Secara default, gunakan nama logo lama
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['logo']['tmp_name'];
        $fileName = $_FILES['logo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Buat nama file unik
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './uploads/';
            
            // Pastikan folder uploads ada
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            // Pindahkan file ke server (folder uploads)
            if(move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                $logo_final = $newFileName; // Gunakan nama logo baru untuk database
                
                // PENTING: Hapus file logo lama dari server untuk menghemat ruang
                if (!empty($logo_lama) && file_exists($uploadFileDir . $logo_lama)) {
                    unlink($uploadFileDir . $logo_lama);
                }
            } else {
                die("Gagal memindahkan file yang diupload. Cek perizinan folder.");
            }
        } else {
            die("Format file gambar tidak valid. Gunakan JPG, PNG, atau GIF.");
        }
    }
    // --------------------------------

    // 3. Mulai Transaksi Database
    $pdo->beginTransaction();

    try {
        // A. Update tabel Perusahaan (termasuk kolom logo)
        $sql_p = "UPDATE perusahaan 
                  SET nama_perusahaan = ?, email = ?, no_telepon = ?, npwp = ?, logo = ? 
                  WHERE id = ?";
        $stmt_p = $pdo->prepare($sql_p);
        $stmt_p->execute([$nama_perusahaan, $email, $no_telepon, $npwp, $logo_final, $id_perusahaan]);

        // B. Update tabel Users (Cek apakah password diganti atau tidak)
        if (!empty($password_plain)) {
            // Jika kolom password diisi, hash password baru dan update
            $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
            $sql_u = "UPDATE users SET username = ?, password = ? WHERE id = ?";
            $stmt_u = $pdo->prepare($sql_u);
            $stmt_u->execute([$username, $password_hashed, $id_user]);
        } else {
            // Jika kolom password kosong, update username saja (password lama tetap aman)
            $sql_u = "UPDATE users SET username = ? WHERE id = ?";
            $stmt_u = $pdo->prepare($sql_u);
            $stmt_u->execute([$username, $id_user]);
        }

        // C. Simpan permanen
        $pdo->commit();
        
        // Redirect kembali ke halaman manajemen customer dengan notifikasi sukses
        header("Location: customer.php?status=update_sukses");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        
        // Jika proses database gagal, hapus gambar baru yang terlanjur diupload
        if ($logo_final != $logo_lama && file_exists('./uploads/' . $logo_final)) {
            unlink('./uploads/' . $logo_final);
        }
        
        if ($e->errorInfo[1] == 1062) { 
             die("Username '$username' sudah terpakai oleh akun lain.");
        } else {
             die("Gagal memperbarui data: " . $e->getMessage());
        }
    }

} else {
    // Jika file diakses langsung tanpa lewat form POST
    header("Location: customer.php");
    exit;
}
?>