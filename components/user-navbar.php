<?php
// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$isHome = $currentPage === 'user-index.php';
$isTentang = $currentPage === 'user-tentang.php';
$isProduk = $currentPage === 'user-produk.php' || $currentPage === 'detail-produk.php';
$isPesanan = $currentPage === 'user-pesanan.php';

// Ambil nama user dari session
$userName = isset($_SESSION['user']['nama_lengkap']) ? $_SESSION['user']['nama_lengkap'] : 'User';

$cartCount = 0;

if (isset($_SESSION['user'])) {
    $userId = $_SESSION['user']['id'];

    require_once '../php/config.php';

    $queryCart = "
        SELECT COUNT(ci.id) AS item_count
        FROM carts c
        JOIN cart_items ci ON c.id = ci.cart_id
        WHERE c.user_id = $userId AND c.status = 'pending'
    ";
    $resultCart = mysqli_query($conn, $queryCart);

    if ($resultCart && mysqli_num_rows($resultCart) > 0) {
        $row = mysqli_fetch_assoc($resultCart);
        $cartCount = (int)$row['item_count'];
    }
}

?>

<nav id="mainNavbar" class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="user-index.php">
            <img src="../assets/img/logo-nav.png" alt="Herbal Nusantara Logo" class="me-2" height="40">
            <span>
                <span class="fw-bold text-primary">Herbal</span>
                <span class="fw-bold" style="color: #0F55B2;">Nusantara</span>
            </span>
        </a>

        <!-- Toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu tengah -->
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?= $isHome ? 'active' : '' ?>" href="<?= $isHome ? '#' : 'user-index.php'; ?>">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isTentang ? 'active' : '' ?>" href="<?= $isTentang ? '#' : 'user-tentang.php'; ?>">Tentang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isProduk ? 'active' : '' ?>" href="user-produk.php">Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isPesanan ? 'active' : '' ?>" href="user-pesanan.php">Pesanan Saya</a>
                </li>
            </ul>
        </div>

        <!-- Menu kanan: Dropdown user -->
        <div class="d-flex align-items-center">
            <a href="user-keranjang.php" class="btn btn-outline-primary me-3 d-flex align-items-center position-relative" style="text-decoration: none;">
                <i class="fas fa-shopping-cart me-2"></i> Keranjang
                <?php if ($cartCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $cartCount ?>
                    </span>
                <?php endif; ?>
            </a>

            <div class="dropdown">
                <a class="nav-link dropdown-toggle fw-semibold text-dark" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Hi, <?= htmlspecialchars($userName) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="user-profil.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="../login/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>