<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$isHome = $currentPage === 'index.php';
$isTentang = $currentPage === 'tentang.php';
$isProduk = $currentPage === 'produk.php';
$isLogin = $currentPage === 'login.php';
$isRegister = $currentPage === 'register.php';

$isInLoginFolder = strpos($_SERVER['PHP_SELF'], '/login/') !== false;
$prefix = $isInLoginFolder ? '../' : '';
$loginLink = ($isLogin || $isRegister) ? '#' : 'login/login.php';
?>

<nav id="mainNavbar" class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= $prefix ?>index.php">
            <img src="<?= $prefix ?>assets/img/logo-nav.png" alt="Herbal Nusantara Logo" class="me-2">
            <span>
                <span class="fw-bold text-primary">Herbal</span>
                <span class="fw-bold" style="color: #0F55B2;">Nusantara</span>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?= $isHome ? 'active' : '' ?>" href="<?= $isHome ? '#' : $prefix . 'index.php'; ?>">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isTentang ? 'active' : '' ?>" href="<?= $isTentang ? '#' : $prefix . 'tentang.php'; ?>">Tentang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isProduk ? 'active' : '' ?>" href="<?= $isProduk ? '#' : $prefix . 'produk.php'; ?>">Produk</a>
                </li>
            </ul>
        </div>
        <div class="d-flex">
            <a class="btn btn-primary" href="<?= $loginLink; ?>">LOGIN</a>
        </div>
    </div>
</nav>