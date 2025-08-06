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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Produk tidak ditemukan.";
    exit;
}

$product_id = (int)$_GET['id'];
$sqlProduct = "SELECT * FROM products WHERE id = $product_id LIMIT 1";
$resultProduct = mysqli_query($conn, $sqlProduct);
if (!$resultProduct || mysqli_num_rows($resultProduct) == 0) {
    echo "Produk tidak ditemukan.";
    exit;
}
$product = mysqli_fetch_assoc($resultProduct);

$sqlVariants = "SELECT * FROM product_variants WHERE product_id = $product_id ORDER BY variant_name ASC";
$resultVariants = mysqli_query($conn, $sqlVariants);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detail Produk - <?= htmlspecialchars($product['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/index.css" />
    <link rel="icon" href="../assets/img/logo-nav.png" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .rating i {
            color: gold;
        }

        .comment-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <?php include '../components/user-navbar.php'; ?>

    <?php if (isset($_SESSION['Berhasil'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['Berhasil']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['Berhasil']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['Gagal'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['Gagal']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['Gagal']); ?>
    <?php endif; ?>

    <div class="container py-5">
        <div class="text-center mb-4">
            <!-- Gambar utama lebih besar & di tengah -->
            <img src="../uploads/produk/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid rounded shadow" style="max-height: 400px;" />
        </div>

        <div class="text-center mb-4">
            <h2 class="fw-bold"><?= htmlspecialchars($product['name']) ?></h2>

            <!-- Rating ala-ala -->
            <div class="rating mb-2">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
                <i class="far fa-star"></i>
                <small class="text-muted">(4.5 dari 5)</small>
            </div>
            <p><strong>Jenis:</strong> <?= htmlspecialchars($product['type']) ?></p>
        </div>

        <!-- Varian Produk -->
        <div class="mb-5">
            <h4>Varian Produk</h4>
            <?php if (mysqli_num_rows($resultVariants) > 0): ?>
                <div class="list-group">
                    <?php while ($variant = mysqli_fetch_assoc($resultVariants)): ?>
                        <div class="list-group-item d-flex align-items-center">
                            <div>
                                <?php if ($variant['variant_image'] && file_exists("../uploads/produk/" . $variant['variant_image'])): ?>
                                    <img src="../uploads/produk/<?= htmlspecialchars($variant['variant_image']) ?>" alt="<?= htmlspecialchars($variant['variant_name']) ?>" style="width: 80px; height: 80px; object-fit: cover;" class="me-3 rounded" />
                                <?php else: ?>
                                    <div style="width:80px; height:80px; background:#eee;" class="me-3 rounded d-flex justify-content-center align-items-center text-muted">
                                        No Image
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?= htmlspecialchars($variant['variant_name']) ?></h5>
                                <p class="mb-1">Harga: Rp <?= number_format($variant['price'], 0, ',', '.') ?></p>
                                <p class="mb-0">Stok: <?= (int)$variant['stock'] ?></p>
                            </div>
                            <div>
                                <form action="tambah-keranjang.php" method="POST" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="product_variant_id" value="<?= $variant['id'] ?>">
                                    <div class="input-group input-group-sm" style="width: 120px;">
                                        <button class="btn btn-outline-secondary btn-sm qty-minus" type="button">-</button>
                                        <input type="text" name="qty" value="1" class="form-control text-center qty-input" data-max="<?= (int)$variant['stock'] ?>" data-min="1">
                                        <button class="btn btn-outline-secondary btn-sm qty-plus" type="button">+</button>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Tambah ke Keranjang</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Belum ada varian untuk produk ini.</p>
            <?php endif; ?>
        </div>

        <!-- Deskripsi Produk -->
        <div class="mb-5">
            <h4>Deskripsi Produk</h4>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>

        <!-- Komentar Dummy -->
        <div class="mb-5">
            <h4>Ulasan Pelanggan</h4>
            <div class="comment-box">
                <strong>Rani A.</strong> <small class="text-muted">⭐️⭐️⭐️⭐️⭐️</small>
                <p>Produk herbalnya ampuh banget, pengiriman juga cepat. Recommended!</p>
            </div>
            <div class="comment-box">
                <strong>Yoga P.</strong> <small class="text-muted">⭐️⭐️⭐️⭐️</small>
                <p>Rasanya enak, cocok buat herbal harian. Tapi packaging agak penyok kemarin 😅</p>
            </div>
            <div class="comment-box">
                <strong>Linda M.</strong> <small class="text-muted">⭐️⭐️⭐️⭐️⭐️</small>
                <p>Sudah langganan dari dulu, kualitasnya terjaga. Sukses terus!</p>
            </div>
        </div>
        <div class="mt-4 text-start">
            <a href="user-produk.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Produk
            </a>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/navbar.js"></script>
    <script src="../assets/js/caraousel.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const qtyInputs = document.querySelectorAll('.qty-input');

            qtyInputs.forEach(input => {
                const min = parseInt(input.dataset.min) || 1;
                const max = parseInt(input.dataset.max);

                input.addEventListener('input', () => {
                    // Bersihkan karakter non-angka
                    input.value = input.value.replace(/[^0-9]/g, '');

                    let value = parseInt(input.value);

                    // Cek validasi nilai input
                    if (isNaN(value) || value < min) {
                        input.value = min;
                    } else if (value > max) {
                        input.value = max;
                    }
                });

                // Prevent paste huruf-huruf aneh
                input.addEventListener('paste', (e) => {
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    if (!/^\d+$/.test(paste)) {
                        e.preventDefault();
                    }
                });

                // Prevent ketik huruf
                input.addEventListener('keypress', (e) => {
                    if (!/[0-9]/.test(e.key)) {
                        e.preventDefault();
                    }
                });
            });

            document.querySelectorAll('.qty-minus').forEach(button => {
                button.addEventListener('click', () => {
                    const input = button.parentElement.querySelector('.qty-input');
                    const min = parseInt(input.dataset.min) || 1;
                    let value = parseInt(input.value) || min;
                    if (value > min) input.value = value - 1;
                });
            });

            document.querySelectorAll('.qty-plus').forEach(button => {
                button.addEventListener('click', () => {
                    const input = button.parentElement.querySelector('.qty-input');
                    const max = parseInt(input.dataset.max);
                    let value = parseInt(input.value) || 1;
                    if (value < max) input.value = value + 1;
                });
            });
        });
    </script>
</body>

</html>