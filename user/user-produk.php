<?php
session_start();

// Cek apakah admin login → tendang ke dashboard admin
if (isset($_SESSION['admin'])) {
    header("Location: ../admin/index.php");
    exit;
}

// Cek apakah user belum login → tendang ke login
if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    header("Location: ../login/login.php");
    exit;
}

require "../php/config.php";

// Ambil semua produk
$products = query("SELECT * FROM products");

// Jika ada pencarian
if (isset($_POST['search'])) {
    $keyword = $_POST['cari_data'];
    $products = query("SELECT * FROM products WHERE name LIKE '%$keyword%' OR type LIKE '%$keyword%'");
}

// Fungsi untuk menghitung total stok semua varian produk
function getTotalStock($conn, $product_id)
{
    $result = mysqli_query($conn, "SELECT SUM(stock) AS total_stock FROM product_variants WHERE product_id = $product_id");
    $row = mysqli_fetch_assoc($result);
    return (int)($row['total_stock'] ?? 0);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Herbal Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../assets/img/logo-nav.png">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/prdk.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../components/user-navbar.php'; ?>

    <!-- Heading -->
    <div class="container pt-5 text-center">
        <h2 class="fw-bold">Katalog Produk Herbal Nusantara</h2>
        <p class="text-muted">Temukan produk herbal terbaik untuk kesehatan Anda</p>
    </div>

    <!-- Search -->
    <div class="container mt-3 mb-3">
        <form class="d-flex justify-content-center" method="POST" action="">
            <input class="form-control w-50" type="search" placeholder="Cari produk..." name="cari_data" aria-label="Search">
            <button class="btn btn-outline-primary ms-2" type="submit" name="search">Search</button>
        </form>
    </div>

    <!-- Info pencarian -->
    <?php if (isset($_POST['search'])): ?>
        <div class="container text-center mt-2">
            <p class="text-muted fst-italic">Menampilkan hasil untuk: <strong><?= htmlspecialchars($_POST['cari_data']); ?></strong></p>
        </div>
    <?php endif; ?>

    <!-- Produk -->
    <div class="container mt-4 mb-5">
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($products as $product) :
                $variantResult = mysqli_query($conn, "SELECT * FROM product_variants WHERE product_id = {$product['id']} ORDER BY id ASC LIMIT 1");
                $variant = mysqli_fetch_assoc($variantResult);
                $totalStock = getTotalStock($conn, $product['id']);
            ?>
                <div class="col">
                    <div class="card shadow-sm h-100">
                        <img src="../uploads/produk/<?= htmlspecialchars($product['main_image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']); ?>" style="object-fit: cover; height: 200px;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']); ?></h5>
                            <?php if ($variant): ?>
                                <p class="card-text">
                                    <i class="fas fa-capsules me-1"></i>
                                    <?= htmlspecialchars($variant['variant_name']); ?>
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-boxes-stacked me-1"></i>
                                    Stok:
                                    <?= $totalStock <= 5 ? "<span class='text-low-stock'>$totalStock (Hampir habis!)</span>" : $totalStock; ?>
                                </p>
                                <p class="card-text text-primary fw-semibold">
                                    <i class="fas fa-tags me-1"></i>
                                    Rp <?= number_format($variant['price'], 0, ',', '.'); ?>
                                </p>
                            <?php else: ?>
                                <p class="text-muted fst-italic">Tidak ada varian</p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-center bg-light">
                            <a href="detail-produk.php?id=<?= $product['id']; ?>" class="btn btn-primary w-100">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- WhatsApp Button -->
    <a href="https://wa.me/628970014820" class="btn btn-success position-fixed bottom-0 end-0 m-3" target="_blank">
        <i class="fab fa-whatsapp"></i> Chat
    </a>

    <?php include '../components/footer.php'; ?>
    <script src="../assets/js/navbar.js"></script>

</body>

</html>
