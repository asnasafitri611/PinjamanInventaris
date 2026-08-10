<?php
// includes/header.php
if (!isset($pageTitle)) $pageTitle = 'Sistem Inventaris';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Inventaris System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-boxes-stacked"></i>
                    <span>Inventaris</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-label">Menu Utama</span>
                    <a href="<?php echo BASE_URL; ?>dashboard.php" class="nav-item <?php echo $activePage == 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i><span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-section">
                    <span class="nav-label">Data Inventaris</span>
                    <a href="<?php echo BASE_URL; ?>pages/inventaris/index.php" class="nav-item <?php echo $activePage == 'inventaris' ? 'active' : ''; ?>">
                        <i class="fas fa-box-open"></i><span>Entry Inventaris</span>
                    </a>
                </div>
                <div class="nav-section">
                    <span class="nav-label">Laporan</span>
                    <a href="<?php echo BASE_URL; ?>pages/laporan/all.php" class="nav-item <?php echo $activePage == 'laporan_all' ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i><span>Semua Inventaris</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/laporan/idle.php" class="nav-item <?php echo $activePage == 'laporan_idle' ? 'active' : ''; ?>">
                        <i class="fas fa-check-circle"></i><span>Status Idle</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/laporan/rent.php" class="nav-item <?php echo $activePage == 'laporan_rent' ? 'active' : ''; ?>">
                        <i class="fas fa-hand-holding"></i><span>Status Rent</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/laporan/repair.php" class="nav-item <?php echo $activePage == 'laporan_repair' ? 'active' : ''; ?>">
                        <i class="fas fa-wrench"></i><span>Status Repair</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/laporan/broken.php" class="nav-item <?php echo $activePage == 'laporan_broken' ? 'active' : ''; ?>">
                        <i class="fas fa-triangle-exclamation"></i><span>Status Broken</span>
                    </a>
                </div>
                <div class="nav-section">
                    <span class="nav-label">Transaksi</span>
                    <a href="<?php echo BASE_URL; ?>pages/transaksi/rent.php" class="nav-item <?php echo $activePage == 'rent' ? 'active' : ''; ?>">
                        <i class="fas fa-file-signature"></i><span>Peminjaman</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/transaksi/return.php" class="nav-item <?php echo $activePage == 'return' ? 'active' : ''; ?>">
                        <i class="fas fa-rotate-left"></i><span>Pengembalian</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/transaksi/request.php" class="nav-item <?php echo $activePage == 'request' ? 'active' : ''; ?>">
                        <i class="fas fa-clipboard-list"></i><span>Request Pinjam</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/transaksi/service.php" class="nav-item <?php echo $activePage == 'service' ? 'active' : ''; ?>">
                        <i class="fas fa-screwdriver-wrench"></i><span>Service / Keluar</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/transaksi/cancel.php" class="nav-item <?php echo $activePage == 'cancel' ? 'active' : ''; ?>">
                        <i class="fas fa-ban"></i><span>Pembatalan</span>
                    </a>
                </div>
                <?php if (isAdmin()): ?>
                <div class="nav-section">
                    <span class="nav-label">Master Data</span>
                    <a href="<?php echo BASE_URL; ?>pages/master/employee.php" class="nav-item <?php echo $activePage == 'employee' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i><span>Karyawan</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/master/user.php" class="nav-item <?php echo $activePage == 'user' ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield"></i><span>User</span>
                    </a>
                </div>
                <?php endif; ?>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1 class="page-title"><?php echo $pageTitle; ?></h1>
                </div>
                <div class="header-right">
                    <div class="user-menu">
                        <div class="user-info">
                            <span class="user-name"><?php echo getUserName(); ?></span>
                            <span class="user-role"><?php echo isAdmin() ? 'Administrator' : 'Karyawan'; ?></span>
                        </div>
                        <div class="user-avatar"><i class="fas fa-user-circle"></i></div>
                        <div class="dropdown">
                            <a href="<?php echo BASE_URL; ?>logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            <div class="content-wrapper">
                <?php echo showFlash(); ?>