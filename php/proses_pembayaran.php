<?php
require 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$user_id = $data['user_id'];
$cart_id = $data['cart_id'];
$order_id = $data['order_id'];
$total_amount = $data['gross_amount'];
$payment_type = $data['payment_type'];
$transaction_time = $data['transaction_time'];
$transaction_status = $data['transaction_status'];
$ongkir = $data['ongkir'];
$asuransi = 1000;
$layanan = 2000;

// Simpan ke orders
$insert_order = $conn->query("INSERT INTO orders 
    (user_id, order_id, ongkir, asuransi, layanan, total_amount, payment_type, transaction_time, transaction_status, created_at)
    VALUES 
    ($user_id, '$order_id', $ongkir, $asuransi, $layanan, $total_amount, '$payment_type', '$transaction_time', '$transaction_status', NOW())");

if (!$insert_order) {
    error_log("Insert order failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Insert order failed']);
    exit;
}

$order_db_id = $conn->insert_id;

// Ambil item dari keranjang
$items_query = $conn->query("
    SELECT ci.product_variant_id, ci.qty, ci.price
    FROM cart_items ci
    WHERE ci.cart_id = $cart_id
");

if (!$items_query) {
    error_log("Select cart_items failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Load cart items failed']);
    exit;
}

while ($item = $items_query->fetch_assoc()) {
    $variant_id = $item['product_variant_id'];
    $qty = $item['qty'];
    $price = $item['price'];

    // Ambil product_id dari variant
    $product_id_query = $conn->query("SELECT product_id FROM product_variants WHERE id = $variant_id");
    $product = $product_id_query->fetch_assoc();
    $product_id = $product['product_id'];

    // Simpan ke order_items
    $insert_item = $conn->query("INSERT INTO order_items 
        (order_id, product_id, variant_id, quantity, price, created_at)
        VALUES ($order_db_id, $product_id, $variant_id, $qty, $price, NOW())");

    if (!$insert_item) {
        error_log("Insert order_items failed: " . $conn->error);
    }

    // Kurangi stok
    $update_stock = $conn->query("UPDATE product_variants SET stock = GREATEST(stock - $qty, 0) WHERE id = $variant_id");

    if (!$update_stock) {
        error_log("Update stock failed: " . $conn->error);
    }
}

// Update status cart
$update_cart = $conn->query("UPDATE carts SET status = 'paid' WHERE id = $cart_id");

if (!$update_cart) {
    error_log("Update cart status failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Update cart status failed']);
    exit;
}

// Done
echo json_encode(['success' => true]);
