<?php
require "php/config.php";

// Ambil data about
$about = null;
$sqlAbout = "SELECT * FROM content_images WHERE type = 'about' ORDER BY created_at DESC LIMIT 1";
$resultAbout = mysqli_query($conn, $sqlAbout);
if ($resultAbout && mysqli_num_rows($resultAbout) > 0) {
    $about = mysqli_fetch_assoc($resultAbout);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Herbal Nusantara</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/img/logo-nav.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include 'components/navbar.php'; ?>

    <!-- Tentang Kami -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <?php if ($about): ?>
                        <img src="uploads/content_images/<?= htmlspecialchars($about['image_path']); ?>" alt="Tentang Herbal Nusantara" class="img-fluid rounded shadow">
                    <?php else: ?>
                        <img src="assets/img/default-about.jpg" alt="Tentang Herbal Nusantara" class="img-fluid rounded shadow">
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h2 class="fw-bold mb-4">Tentang Herbal Nusantara</h2>
                    <p class="text-muted">
                        Herbal Nusantara adalah toko jamu dan produk herbal yang berdedikasi dalam menghadirkan solusi kesehatan alami bagi masyarakat Indonesia. Kami menggabungkan warisan leluhur dengan inovasi modern dalam setiap produk kami.
                    </p>
                    <p class="text-muted">
                        Dengan bahan alami pilihan dan proses produksi yang higienis, kami memastikan setiap produk memiliki kualitas terbaik. Herbal Nusantara percaya bahwa kunci hidup sehat berasal dari alam.
                    </p>
                    <p class="text-muted">
                        Kami juga berkomitmen dalam menjaga kearifan lokal, memberdayakan petani lokal, serta terus mengedukasi masyarakat akan pentingnya gaya hidup sehat berbasis herbal.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Lokasi & Kontak Kami -->
    <section class="bg-light py-5">
        <div class="container">
            <h3 class="text-center fw-bold mb-4">Lokasi & Kontak Kami</h3>
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <iframe class="w-100 rounded shadow-sm"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d253840.4300341496!2d106.66470168270964!3d-6.22972804212283!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e9354f4211%3A0x301576d14feb9d0!2sJakarta!5e0!3m2!1sen!2sid!4v1700000000000"
                        height="300" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="col-md-6">
                    <h5 class="fw-bold mb-3">Alamat</h5>
                    <p class="text-muted mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Jl. Sehat No. 123, Jakarta Selatan, Indonesia</p>

                    <h5 class="fw-bold mt-4 mb-3">Kontak</h5>
                    <p class="text-muted mb-2"><i class="fas fa-phone-alt me-2 text-primary"></i>+62 897 0014 820</p>
                    <p class="text-muted mb-2"><i class="fas fa-envelope me-2 text-primary"></i>herbalnusantara@gmail.com</p>
                    <p class="text-muted"><i class="fab fa-whatsapp me-2 text-primary"></i><a href="https://wa.me/628970014820" class="text-decoration-none text-muted">Hubungi via WhatsApp</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- WhatsApp Button -->
    <a href="https://wa.me/628970014820" class="btn btn-success position-fixed bottom-0 end-0 m-3" target="_blank">
        <i class="fab fa-whatsapp"></i> Chat
    </a>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/navbar.js"></script>

</body>

</html>