<?php
session_start();
include 'koneksi.php'; // Menyediakan $pdo

// Proteksi: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Ambil data teks dari form
    $nama_perusahaan = $_POST['nama_perusahaan'];
    $email = $_POST['email'];
    $no_telepon = $_POST['no_telepon'];
    $npwp = $_POST['npwp'];
    
    $username = $_POST['username'];
    $password_plain = $_POST['password'];
    $role = 'user'; // Otomatis sebagai user/customer

    // Validasi data wajib
    if (empty($nama_perusahaan) || empty($email) || empty($username) || empty($password_plain)) {
        die("Data tidak lengkap. Nama Perusahaan, Email, Username, dan Password wajib diisi.");
    }

    // 2. --- PROSES UPLOAD LOGO ---
    $logo_name = null; // Default null jika admin tidak mengupload logo
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['logo']['tmp_name'];
        $fileName = $_FILES['logo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Ekstensi yang diizinkan
        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Generate nama file unik agar tidak tertimpa
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './uploads/';
            
            // Buat folder 'uploads' secara otomatis jika belum ada
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;

            // Pindahkan file dari temporary ke folder uploads
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $logo_name = $newFileName; // Simpan nama file untuk dimasukkan ke database
            } else {
                die("Gagal mengupload file logo. Pastikan folder 'uploads/' memiliki izin tulis (writable).");
            }
        } else {
            die("Format file tidak didukung. Hanya gunakan gambar berformat JPG, JPEG, PNG, atau GIF.");
        }
    }
    // --------------------------

    // Hash password demi keamanan
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

    // 3. Mulai Transaksi Database
    $pdo->beginTransaction();

    try {
        // A. Simpan data login ke tabel 'users'
        $sql_user = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt_user = $pdo->prepare($sql_user);
        $stmt_user->execute([$username, $password_hashed, $role]);
        
        // Ambil ID user yang baru saja dibuat
        $id_user_baru = $pdo->lastInsertId();

        // B. Simpan profil ke tabel 'perusahaan' (termasuk nama file logo)
        $sql_perusahaan = "INSERT INTO perusahaan (nama_perusahaan, email, no_telepon, npwp, id_user, logo) 
                           VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_perusahaan = $pdo->prepare($sql_perusahaan);
        $stmt_perusahaan->execute([
            $nama_perusahaan, 
            $email, 
            $no_telepon, 
            $npwp, 
            $id_user_baru, 
            $logo_name // Nama file logo akan disimpan di sini, jika kosong akan bernilai NULL
        ]);

        // C. Jika semua berhasil, simpan permanen
        $pdo->commit();
        
        // Redirect kembali ke halaman daftar customer dengan pesan sukses
        header("Location: customer.php?status=sukses");
        exit;

    } catch (PDOException $e) {
        // Jika terjadi error pada database, batalkan semua insert
        $pdo->rollBack();
        
        // Hapus file logo yang sudah terlanjur diupload (mencegah file sampah menumpuk)
        if ($logo_name && file_exists('./uploads/' . $logo_name)) {
            unlink('./uploads/' . $logo_name);
        }
        
        // Cek jika error disebabkan oleh username yang bentrok/duplikat
        if ($e->errorInfo[1] == 1062) { 
             die("Gagal: Username '$username' sudah terpakai. Silakan gunakan username lain.");
        } else {
             die("Gagal menyimpan ke database: ". $e->getMessage());
        }
    }

} else {
    // Jika diakses langsung tanpa lewat form
    header("Location: customer.php");
    exit;
}
?>