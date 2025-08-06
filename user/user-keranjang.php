<?php
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['Gagal'] = 'Silakan login terlebih dahulu.';
    header("Location: ../login/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

if ($role !== 'user') {
    $_SESSION['Gagal'] = 'Akses hanya untuk user.';
    header("Location: ../login/login.php");
    exit;
}

// Ambil data keranjang user
$query = "
SELECT carts.id AS cart_id, cart_items.id AS item_id, products.name AS product_name, 
    products.main_image, product_variants.variant_name, 
    product_variants.price, cart_items.qty AS quantity
    FROM carts
    JOIN cart_items ON carts.id = cart_items.cart_id
    JOIN product_variants ON cart_items.product_variant_id = product_variants.id
    JOIN products ON product_variants.product_id = products.id
    WHERE carts.user_id = $user_id AND carts.status = 'pending'
";

$cartItems = query($query);

// Fungsi hitung total
function hitungTotal($items)
{
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}
$totalBayar = hitungTotal($cartItems);

// Hapus item dari keranjang
if (isset($_GET['hapus'])) {
    $hapusItemId = intval($_GET['hapus']);
    // hapus dari tabel cart_items, bukan carts!
    mysqli_query($conn, "DELETE FROM cart_items WHERE id = $hapusItemId");
    header("Location: user-keranjang.php");
    exit;
}

// Ambil data user dari database
$userData = query("SELECT phone, province_id, province_name, city_id, city_name, postal_code, full_address FROM users WHERE id = $user_id")[0];

// Cek apakah data alamat kosong
$isAlamatLengkap = !empty($userData['phone']) &&
    !empty($userData['province_id']) &&
    !empty($userData['province_name']) &&
    !empty($userData['city_id']) &&
    !empty($userData['city_name']) &&
    !empty($userData['postal_code']) &&
    !empty($userData['full_address']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Keranjang Saya - Herbal Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../assets/img/logo-nav.png">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/prdk.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../components/user-navbar.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center fw-bold">Keranjang Belanja Anda</h2>
        <p class="text-muted text-center">Cek kembali produk sebelum checkout</p>

        <?php if (count($cartItems) === 0): ?>
            <div class="alert alert-info text-center mt-5 py-5" style="margin-bottom: 120px;">
                Keranjang kamu masih kosong, yuk belanja dulu 😁
            </div>
        <?php else: ?>
            <div class="table-responsive mt-4">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Varian</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <img src="../uploads/produk/<?= $item['main_image']; ?>" width="50" height="50" class="rounded me-2">
                                    <?= htmlspecialchars($item['product_name']); ?>
                                </td>
                                <td><?= htmlspecialchars($item['variant_name']); ?></td>
                                <td>Rp <?= number_format($item['price'], 0, ',', '.'); ?></td>
                                <td><?= $item['quantity']; ?></td>
                                <td>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                <td>
                                    <a href="user-keranjang.php?hapus=<?= $item['item_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus item ini dari keranjang?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Total</td>
                            <td colspan="2" class="text-primary">Rp <?= number_format($totalBayar, 0, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-end mt-3 mb-5">
                <?php if ($isAlamatLengkap): ?>
                    <a href="user-pembayaran.php" class="btn btn-success">
                        <i class="fas fa-shopping-cart"></i> Checkout Sekarang
                    </a>
                <?php else: ?>
                    <button class="btn btn-warning" onclick="redirectToProfil()">
                        <i class="fas fa-exclamation-triangle"></i> Lengkapi Profil Dulu
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <a href="https://wa.me/628970014820" class="btn btn-success position-fixed bottom-0 end-0 m-3" target="_blank">
        <i class="fab fa-whatsapp"></i> Chat
    </a>

    <?php include '../components/footer.php'; ?>
    <script src="../assets/js/navbar.js"></script>
    <script src="https://kit.fontawesome.com/yourkitid.js" crossorigin="anonymous"></script>
    <script>
        function redirectToProfil() {
            alert("Lengkapi data alamat terlebih dahulu sebelum checkout, bro 🚀");
            window.location.href = "user-profil.php";
        }
    </script>
</body>

</html>