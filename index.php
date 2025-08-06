<?php

require "php/config.php";

// Ambil data banner
$banners = [];
$sql = "SELECT * FROM content_images WHERE type = 'banner' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $banners[] = $row;
    }
}

// Ambil data about (hanya satu)
$about = null;
$sqlAbout = "SELECT * FROM content_images WHERE type = 'about' ORDER BY created_at DESC LIMIT 1";
$resultAbout = mysqli_query($conn, $sqlAbout);
if ($resultAbout && mysqli_num_rows($resultAbout) > 0) {
    $about = mysqli_fetch_assoc($resultAbout);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Herbal Nusantara</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="icon" type="image/png" href="assets/img/logo-nav.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include 'components/navbar.php'; ?>

    <!-- Hero Section -->
    <div id="banner" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="false">
        <div class="carousel-inner">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>">
                        <img src="uploads/content_images/<?= htmlspecialchars($banner['image_path']); ?>" class="d-block w-100" alt="Banner <?= $banner['id']; ?>">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carousel-item active">
                    <img src="assets/img/default-banner.jpg" class="d-block w-100" alt="Default Banner">
                </div>
            <?php endif; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#banner" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#banner" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- About Section -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <?php if ($about): ?>
                        <img src="uploads/content_images/<?= htmlspecialchars($about['image_path']); ?>" alt="About Image" class="img-fluid rounded shadow">
                    <?php else: ?>
                        <img src="assets/img/default-about.jpg" alt="About Image" class="img-fluid rounded shadow">
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h2 class="fw-bold mb-4">Tentang Kami</h2>
                    <p class="text-muted">
                        Toko Jamu Herbal Nusantara berkomitmen untuk menyediakan produk jamu berkualitas tinggi yang diracik dari bahan alami pilihan. Kami terus berinovasi untuk menghadirkan ramuan tradisional yang sesuai dengan kebutuhan masyarakat modern.
                    </p>
                    <p class="text-muted">
                        Kami percaya bahwa tradisi jamu adalah warisan budaya Indonesia yang berharga dan harus dijaga. Dengan pengalaman yang luas di bidang herbal, kami berupaya memberikan produk yang aman, berkualitas, dan bermanfaat bagi kesehatan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Produk Unggulan -->
    <section id="featured-products" class="py-5 bg-light">
        <div class="container">
            <h2 class="fw-bold text-center mb-4">Produk Unggulan</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="assets/img/dummy-1.jpg" class="card-img-top" alt="Produk 1">
                        <div class="card-body">
                            <h5 class="card-title">Jamu</h5>
                            <p class="card-text text-muted">Jamu tradisional untuk membuat badan lebih sehat.</p>
                            <a href="produk.php" class="btn btn-primary">Lihat Produk</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Kenapa Memilih Kami -->
    <section id="why-us" class="py-5">
        <div class="container">
            <h2 class="fw-bold text-center mb-4">Kenapa Memilih Kami?</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-3">
                    <i class="fas fa-leaf fa-2x text-success mb-2"></i>
                    <h5>100% Herbal</h5>
                    <p class="text-muted">Produk diracik dari bahan alami tanpa bahan kimia.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <i class="fas fa-shipping-fast fa-2x text-primary mb-2"></i>
                    <h5>Pengiriman Cepat</h5>
                    <p class="text-muted">Pesanan diproses dalam 1x24 jam.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <i class="fas fa-award fa-2x text-warning mb-2"></i>
                    <h5>Terpercaya</h5>
                    <p class="text-muted">Banyak pelanggan puas dengan layanan kami.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimoni Section -->
    <section id="testimonials" class="py-5 bg-light">
        <div class="container">
            <h2 class="fw-bold text-center mb-4">Apa Kata Mereka?</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <p class="text-muted">“Jamu dari Herbal Nusantara sangat ampuh. Rasa alami dan bikin badan segar.”</p>
                            <h6 class="fw-bold mb-0">– Budi</h6>
                        </div>
                    </div>
                </div>
                <!-- Niatnya mau bisa geser kiri kanan -->
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section id="cta" class="py-5 text-white" style="background-color: #0F55B2;">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Siap untuk Hidup Lebih Sehat?</h2>
            <p class="mb-4">Mulai perjalanan sehatmu dengan jamu tradisional berkualitas dari Herbal Nusantara.</p>
            <a href="produk.php" class="btn btn-light btn-lg">Lihat Produk</a>
        </div>
    </section>

    <!-- WhatsApp Button -->
    <a href="https://wa.me/628970014820" class="btn btn-success position-fixed bottom-0 end-0 m-3" target="_blank">
        <i class="fab fa-whatsapp"></i> Chat
    </a>

    <?php include 'components/footer.php'; ?>

    <script src="assets/js/navbar.js"></script>
    <script src="assets/js/caraousel.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>