<?php
session_start();

// Cek jika user sudah login, langsung arahkan ke halamannya
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

// Generate angka acak untuk Math CAPTCHA
$angka1 = rand(1, 10);
$angka2 = rand(1, 10);

// Simpan kunci jawabannya di Session (Sistem yang tahu jawabannya)
$_SESSION['captcha_jawaban'] = $angka1 + $angka2;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem PO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .captcha-box {
            background-color: #e9ecef;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
            letter-spacing: 2px;
            color: #333;
            user-select: none; /* Mencegah dicopy dengan mudah */
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card login-card">
                <div class="login-header">
                    <!-- -->
                    <i class="bi bi-shield-lock-fill display-4 mb-2"></i>
                    <h4 class="mb-0 fw-bold">Sistem PO</h4>
                    <p class="text-white-50 mb-0">Silakan login untuk melanjutkan</p>
                </div>
                <div class="card-body p-4">
                    
                    <form action="cek_login.php" method="POST">
                        <!-- -->
                        <div class="mb-3">
                            <label class="form-label text-muted">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" class="form-control" required autofocus autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>

                        <!-- -->
                        <div class="mb-4">
                            <label class="form-label text-muted">Verifikasi Keamanan</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="captcha-box flex-shrink-0 w-50">
                                    <?= $angka1 ?> + <?= $angka2 ?> = ?
                                </div>
                                <input type="number" name="captcha" class="form-control text-center fs-5" placeholder="Hasil" required autocomplete="off">
                            </div>
                            <div class="form-text text-muted mt-1 small"><i class="bi bi-info-circle"></i> Jawab soal matematika di atas.</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">Login</button>
                        </div>
                    </form>

                </div>
            </div>
            <div class="text-center mt-3 text-muted small">
                &copy; <?= date('Y') ?> Sistem Purchase Order
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>