<?php
require 'config.php';
require '../vendor/autoload.php';

\Midtrans\Config::$serverKey = 'SB-Mid-server-AO6ScMa7PYA2ek7WtifyiNd5';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$data = json_decode(file_get_contents("php://input"), true);

$order_id = 'INV-' . time();
$gross_amount = $data['total']; // total tagihan dari JS

// Siapkan item detail dari JavaScript
$item_details = $data['item_details'];
$item_details[] = [
    'id' => 'ASURANSI',
    'price' => 1000,
    'quantity' => 1,
    'name' => 'Asuransi Pengiriman'
];
$item_details[] = [
    'id' => 'LAYANAN',
    'price' => 2000,
    'quantity' => 1,
    'name' => 'Biaya Layanan'
];
$item_details[] = [
    'id' => 'ONGKIR',
    'price' => (int)$data['ongkir'],
    'quantity' => 1,
    'name' => 'Ongkir'
];

$transaction = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => $gross_amount
    ],
    'item_details' => $item_details,
    'customer_details' => $data['customer']
];

$snapToken = \Midtrans\Snap::getSnapToken($transaction);

echo json_encode(['token' => $snapToken, 'order_id' => $order_id]);
