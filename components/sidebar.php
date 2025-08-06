<?php
// Ambil nama file dari URL
$current_page = basename($_SERVER['PHP_SELF']);

$notif_query = "SELECT COUNT(*) AS total FROM orders WHERE transaction_status = 'settlement'";
$notif_result = mysqli_query($conn, $notif_query);
$notif_data = mysqli_fetch_assoc($notif_result);
$jumlah_pesanan_settlement = $notif_data['total'] ?? 0;
?>


<!-- SIDEBAR -->
<section id="sidebar">
    <a href="/admin/dashboard.php" class="brand d-flex align-items-center" style="margin-left: 20px;">
        <img src="/assets/img/logo-nav.png" alt="Logo Herbal Nusantara" class="me-2" style="width: 40px; height: 40px;">
        <span class="fw-bold text-primary" style="margin-right: 5px; margin-left: 5px;">Herbal</span>
        <span class="fw-bold" style="color: #0F55B2;">Nusantara</span>
    </a>
    <ul class="side-menu top">
        <li class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <a href="/admin/dashboard.php">
                <i class='bx bxs-dashboard'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <li class="<?= in_array($current_page, ['table_produk.php', 'tambah_produk.php', 'edit_produk.php']) ? 'active' : '' ?>">
            <a href="/admin/produk/table_produk.php">
                <i class='bx bxs-shopping-bag-alt'></i>
                <span class="text">Kelola Produk</span>
            </a>
        </li>
        <li class="<?= in_array($current_page, ['table_banner.php', 'tambah_banner.php', 'edit_banner.php']) ? 'active' : '' ?>">
            <a href="/admin/banner/table_banner.php">
                <i class='bx bxs-photo-album'></i>
                <span class="text">Kelola Banner</span>
            </a>
        </li>
        <li class="<?= in_array($current_page, ['table_pesanan.php', 'detail_pesanan.php']) ? 'active' : '' ?>">
            <a href="/admin/pesanan/table_pesanan.php" style="position: relative;">
                <i class='bx bxs-package'></i>
                <span class="text">Kelola Pesanan</span>
                <?php if ($jumlah_pesanan_settlement > 0): ?>
                    <span class="notif-badge"><?= $jumlah_pesanan_settlement ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="<?= $current_page == 'rating.php' ? 'active' : '' ?>">
            <a href="/admin/rating.php">
                <i class='bx bxs-message-dots'></i>
                <span class="text">Kelola Komentar</span>
            </a>
        </li>
        <li class="<?= in_array($current_page, ['table_akun.php', 'edit_akun.php']) ? 'active' : '' ?>">
            <a href="/admin/akun/table_akun.php">
                <i class='bx bxs-group'></i>
                <span class="text">Kelola Akun</span>
            </a>
        </li>
    </ul>
    <ul class="side-menu">
        <li>
            <a href="/login/logout.php" class="logout">
                <i class='bx bxs-log-out-circle'></i>
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</section>