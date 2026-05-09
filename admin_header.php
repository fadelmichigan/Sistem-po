<?php
// File: admin_header.php
// Berisi bagian atas HTML, CSS, dan Sidebar
// Digunakan oleh semua halaman admin

// Cek jika variabel $active_page belum di-set di halaman pemanggil
if (!isset($active_page)) {
    $active_page = ''; // Set default kosong
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Panel - PO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: row;
            background-color: #f8f9fa;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background-color: #fff;
            border-right: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
            padding: 1rem;
            position: fixed;
            height: 100%;
            overflow-y: auto; /* Agar sidebar bisa di-scroll jika menunya banyak */
        }
        .sidebar-header {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1rem;
            text-align: center;
        }
        .sidebar-nav {
            list-style: none;
            padding-left: 0;
            flex-grow: 1;
        }
        .sidebar-nav .nav-item {
            margin-bottom: 0.25rem;
        }
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            color: #343a40;
            text-decoration: none;
            font-size: 1.05rem;
        }
        .sidebar-nav .nav-link i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }
        .sidebar-nav .nav-link:hover {
            background-color: #e9ecef;
        }
        /* Class 'active' akan ditambahkan oleh PHP */
        .sidebar-nav .nav-link.active {
            background-color: #0d6efd;
            color: #fff;
            font-weight: 500;
        }
        .sidebar-footer {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
            margin-top: 1rem;
        }

        /* Main Content */
        .main-content {
            flex-grow: 1;
            padding: 2rem;
            /* Offset untuk sidebar */
            margin-left: 260px; 
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        /* CSS untuk stok rendah (dari stok_gudang.php) */
        .stok-rendah {
            background-color: #fff3cd;
            font-weight: bold;
            color: #664d03;
        }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
    <div class="sidebar-header">
        Admin Panel
    </div>
    <ul class="sidebar-nav nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= ($active_page == 'admin') ? 'active' : '' ?>" href="admin.php">
                <i class="bi bi-grid-fill"></i>
                Dashboard (PO)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($active_page == 'customer') ? 'active' : '' ?>" href="customer.php">
                <i class="bi bi-people-fill"></i>
                Customer
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($active_page == 'barang') ? 'active' : '' ?>" href="barang.php">
                <i class="bi bi-box-seam-fill"></i>
                Barang
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($active_page == 'distributor') ? 'active' : '' ?>" href="distributor.php">
                <i class="bi bi-truck"></i>
                Distributor
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($active_page == 'penerimaan') ? 'active' : '' ?>" href="penerimaan.php">
                <i class="bi bi-box-arrow-in-down"></i>
                Penerimaan (Customer)
            </a>
        </li>
        
        <!-- =================================== -->
        <!-- LINK BARU DITAMBAHKAN DI SINI -->
        <li class="nav-item">
            <a class="nav-link <?= ($active_page == 'stok_masuk') ? 'active' : '' ?>" href="stok_masuk.php">
                <i class="bi bi-box-arrow-in-right"></i>
                Stok Masuk (Distributor)
            </a>
        </li>
        <!-- =================================== -->
        
        <li class="nav-item">
            <a class="nav-link <?= ($active_page == 'stok_gudang') ? 'active' : '' ?>" href="stok_gudang.php">
                <i class="bi bi-houses-fill"></i>
                Stok Gudang
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($active_page == 'pengiriman') ? 'active' : '' ?>" href="pengiriman.php">
                <i class="bi bi-box-arrow-up-right"></i>
                Pengiriman
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($active_page == 'laporan') ? 'active' : '' ?>" href="laporan.php">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                Laporan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($active_page == 'invoice_list') ? 'active' : '' ?>" href="invoice_list.php">
                <i class="bi bi-receipt-cutoff"></i>
                Invoice
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-danger w-100">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>
</div>

<!-- ===== MAIN CONTENT (DIBUKA DI SINI) ===== -->
<div class="main-content">