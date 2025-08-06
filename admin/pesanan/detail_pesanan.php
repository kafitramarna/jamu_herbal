<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require "../../php/config.php";

// Ambil ID pesanan dari URL
$orderIdDb = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($orderIdDb <= 0) {
    echo "ID pesanan tidak valid.";
    exit;
}

// Ambil detail pesanan utama
$sqlOrder = "SELECT 
                o.*, u.full_name, u.email, u.phone, u.full_address, u.city_name, u.province_name, u.postal_code
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE o.id = $orderIdDb";

$resultOrder = mysqli_query($conn, $sqlOrder);
$order = mysqli_fetch_assoc($resultOrder);

if (!$order) {
    echo "Pesanan tidak ditemukan.";
    exit;
}

// Ambil item pesanan
$sqlItems = "SELECT 
                oi.*, 
                p.name AS product_name, 
                v.variant_name 
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             LEFT JOIN product_variants v ON oi.variant_id = v.id
             WHERE oi.order_id = $orderIdDb";

$resultItems = mysqli_query($conn, $sqlItems);
$orderItems = [];
while ($row = mysqli_fetch_assoc($resultItems)) {
    $orderItems[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Pesanan - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/produk.css">
    <link rel="stylesheet" href="../../assets/css/detail_pesanan.css">
    <link href="https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>
    <?php include '../../components/sidebar.php'; ?>

    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
        </nav>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Detail Pesanan</h1>
                    <ul class="breadcrumb ">
                        <li><a href="index.php">Kelola Pesanan</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="table_pesanan.php">Daftar Pesanan</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Detail Pesanan</a></li>
                    </ul>
                </div>
            </div>

            <div class="card-container ">
                <div class="card">
                    <h3>📦 Informasi Pesanan</h3>
                    <p><strong>Order ID:</strong> <?= $order['order_id'] ?></p>
                    <p><strong>Tanggal:</strong> <?= $order['transaction_time'] ?></p>
                    <p><strong>Status:</strong> <span class="badge <?= strtolower($order['transaction_status']) ?>"><?= $order['transaction_status'] ?></span></p>
                    <p><strong>Metode Pembayaran:</strong> <?= $order['payment_type'] ?></p>
                    <p><strong>Kurir:</strong> <?= $order['layanan'] ?></p>
                    <p><strong>Ongkir:</strong> Rp <?= number_format($order['ongkir'], 0, ',', '.') ?></p>
                    <p><strong>Asuransi:</strong> Rp <?= number_format($order['asuransi'], 0, ',', '.') ?></p>
                    <p><strong>Total:</strong> <span class="total-amount">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></span></p>
                </div>

                <div class="card">
                    <h3>👤 Informasi Pembeli</h3>
                    <p><strong>Nama:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                    <p><strong>Telepon:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                    <p><strong>Alamat:</strong><br>
                        <?= htmlspecialchars($order['full_address']) ?><br>
                        <?= $order['city_name'] ?>, <?= $order['province_name'] ?>, <?= $order['postal_code'] ?>
                    </p>
                </div>
            </div>

            <div class="table-container">
                <h3>🛒 Item Pesanan</h3>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Varian</th>
                            <th>Qty</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($orderItems)) {
                            echo "<tr><td colspan='6'>Tidak ada item dalam pesanan ini.</td></tr>";
                        } else {
                            $no = 1;
                            foreach ($orderItems as $item) {
                                $subtotal = $item['price'] * $item['quantity'];
                                echo "<tr>
                                    <td>{$no}</td>
                                    <td>{$item['product_name']}</td>
                                    <td>{$item['variant_name']}</td>
                                    <td>{$item['quantity']}</td>
                                    <td>Rp " . number_format($item['price'], 0, ',', '.') . "</td>
                                    <td>Rp " . number_format($subtotal, 0, ',', '.') . "</td>
                                </tr>";
                                $no++;
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </section>

    <script src="../../assets/js/script.js"></script>

</body>

</html>

</body>

</html>