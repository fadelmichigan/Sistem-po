<?php
session_start();
include 'koneksi.php';

// Proteksi
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pemesanan = (int)$_POST['id_pemesanan'];
    $id_user_login = $_SESSION['user_id'];

    // Validasi kepemilikan pesanan
    $stmt = $pdo->prepare("SELECT p.id_user FROM pemesanan po JOIN perusahaan p ON po.id_perusahaan = p.id WHERE po.id = ?");
    $stmt->execute([$id_pemesanan]);
    $pesanan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pesanan || $pesanan['id_user'] != $id_user_login) {
        die("Pesanan tidak valid.");
    }

    // Proses File Upload
    if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['bukti_bayar']['tmp_name'];
        $fileName = $_FILES['bukti_bayar']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Boleh upload gambar atau PDF
        $allowedExts = array('jpg', 'jpeg', 'png', 'pdf');
        
        if (in_array($fileExtension, $allowedExts)) {
            $newFileName = 'bukti_PO' . $id_pemesanan . '_' . time() . '.' . $fileExtension;
            $uploadFileDir = './uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                // Update database
                $sql = "UPDATE pemesanan SET bukti_bayar = ? WHERE id = ?";
                $stmt_up = $pdo->prepare($sql);
                $stmt_up->execute([$newFileName, $id_pemesanan]);

                header("Location: detail_pesanan.php?id=" . $id_pemesanan . "&status=upload_sukses");
                exit;
            } else {
                header("Location: detail_pesanan.php?id=" . $id_pemesanan . "&status=gagal&msg=Folder error");
                exit;
            }
        } else {
            header("Location: detail_pesanan.php?id=" . $id_pemesanan . "&status=gagal&msg=Format tidak didukung");
            exit;
        }
    } else {
        header("Location: detail_pesanan.php?id=" . $id_pemesanan . "&status=gagal&msg=File tidak terdeteksi");
        exit;
    }
} else {
    header("Location: index.php");
}
?>