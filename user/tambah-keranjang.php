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

$variant_id = isset($_POST['product_variant_id']) ? intval($_POST['product_variant_id']) : 0;
$qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
$product_id = 0;

if ($variant_id <= 0 || $qty <= 0) {
    $_SESSION['Gagal'] = 'Data tidak valid.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'user-produk.php'));
    exit;
}

// Ambil data varian
$query = "SELECT * FROM product_variants WHERE id = $variant_id";
$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) === 0) {
    $_SESSION['Gagal'] = 'Produk tidak ditemukan.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'user-produk.php'));
    exit;
}

$variant = mysqli_fetch_assoc($result);
$product_id = $variant['product_id'];

// Cek stok
if ($qty > $variant['stock']) {
    $_SESSION['Gagal'] = 'Jumlah melebihi stok tersedia.';
    header("Location: detail-produk.php?id=$product_id");
    exit;
}

$price = $variant['price']; // harga satuan

// Cek apakah user sudah punya keranjang pending
$cart_res = mysqli_query($conn, "SELECT id FROM carts WHERE user_id = $user_id AND status = 'pending'");
if ($cart_res && mysqli_num_rows($cart_res) > 0) {
    $cart = mysqli_fetch_assoc($cart_res);
    $cart_id = $cart['id'];
} else {
    mysqli_query($conn, "INSERT INTO carts (user_id, total_price, status) VALUES ($user_id, 0, 'pending')");
    $cart_id = mysqli_insert_id($conn);
}

// Cek apakah varian sudah ada di keranjang
$item_check = mysqli_query($conn, "SELECT id, qty FROM cart_items WHERE cart_id = $cart_id AND product_variant_id = $variant_id");
if ($item_check && mysqli_num_rows($item_check) > 0) {
    $item = mysqli_fetch_assoc($item_check);
    $new_qty = $item['qty'] + $qty;

    if ($new_qty > $variant['stock']) {
        $_SESSION['Gagal'] = 'Jumlah melebihi stok tersedia.';
        header("Location: detail-produk.php?id=$product_id");
        exit;
    }

    mysqli_query($conn, "UPDATE cart_items SET qty = $new_qty WHERE id = {$item['id']}");
} else {
    mysqli_query($conn, "INSERT INTO cart_items (cart_id, product_variant_id, qty, price) VALUES ($cart_id, $variant_id, $qty, $price)");
}

// Update total harga keranjang: (qty * price)
$update_total_query = "SELECT SUM(price * qty) AS total FROM cart_items WHERE cart_id = $cart_id";
$total_res = mysqli_query($conn, $update_total_query);
if ($total_res) {
    $total_row = mysqli_fetch_assoc($total_res);
    $total_price = $total_row['total'];
    mysqli_query($conn, "UPDATE carts SET total_price = $total_price WHERE id = $cart_id");
}

$_SESSION['Berhasil'] = 'Produk berhasil ditambahkan ke keranjang.';
header("Location: detail-produk.php?id=$product_id");
exit;
?>
