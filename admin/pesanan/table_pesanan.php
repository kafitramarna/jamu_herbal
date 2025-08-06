<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require "../../php/config.php";

// Query ambil semua pesanan
$sql = "SELECT 
    o.id AS order_id_db,
    o.order_id,
    o.user_id,
    u.full_name,
    o.total_amount,
    o.ongkir,
    o.asuransi,
    o.layanan,
    o.payment_type,
    o.transaction_time,
    o.transaction_status,
    o.created_at
FROM orders o
JOIN users u ON o.user_id = u.id
ORDER BY 
    CASE 
        WHEN o.transaction_status = 'settlement' THEN 0 
        ELSE 1 
    END,
    o.created_at DESC";

$result = mysqli_query($conn, $sql);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../assets/css/produk.css">
</head>

<body>
    <?php include '../../components/sidebar.php'; ?>

    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search..." />
                    <button type="submit" class="search-btn">
                        <i class='bx bx-search'></i>
                    </button>
                </div>
            </form>
            <input type="checkbox" id="switch-mode" hidden />
            <label for="switch-mode" class="switch-mode"></label>
        </nav>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Daftar Pesanan</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Kelola Pesanan</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Daftar Pesanan</a></li>
                    </ul>
                </div>
            </div>

            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Order ID</th>
                            <th>Nama Pembeli</th>
                            <th>Total</th>
                            <th>Ongkir</th>
                            <th>Asuransi</th>
                            <th>Layanan</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($orders)) {
                            echo "<tr><td colspan='10' class='no-product-row'>Tidak ada pesanan.</td></tr>";
                        } else {
                            $no = 1;
                            foreach ($orders as $order) :
                        ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $order['order_id']; ?></td>
                                    <td><?= $order['full_name']; ?></td>
                                    <td>Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                    <td>Rp <?= number_format($order['ongkir'], 0, ',', '.'); ?></td>
                                    <td>Rp <?= number_format($order['asuransi'], 0, ',', '.'); ?></td>
                                    <td>Rp <?= number_format($order['layanan'], 0, ',', '.'); ?></td>
                                    <td><?= $order['transaction_time']; ?></td>
                                    <td><?= $order['transaction_status']; ?></td>
                                    <td class="action-column">
                                        <a href="detail_pesanan.php?id=<?= $order['order_id_db']; ?>" class="btn-edit">Detail</a>

                                        <?php if ($order['transaction_status'] === 'settlement') : ?>
                                            <form action="proses_pesanan.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?= $order['order_id_db']; ?>">
                                                <input type="hidden" name="new_status" value="shipping">
                                                <button type="submit" class="btn-kirim" onclick="return confirm('Yakin pesanan ini sudah dikirim ke kurir?')">Barang Dikirim</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php
                            endforeach;
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