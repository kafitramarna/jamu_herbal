<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    header("Location: ../login/login.php");
    exit;
}

require "../php/config.php";
$userId = $_SESSION['user']['id'];

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['order_id'], $_POST['action']) && $_POST['action'] === 'completed') {
        $orderId = (int) $_POST['order_id'];
        mysqli_query($conn, "UPDATE orders SET transaction_status = 'completed' WHERE id = $orderId AND user_id = $userId");
    } elseif (isset($_POST['order_id'], $_POST['action']) && $_POST['action'] === 'rating') {
        $orderId = (int) $_POST['order_id'];
        $rating = (int) $_POST['rating'];
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);
        mysqli_query($conn, "INSERT INTO order_ratings (order_id, rating, comment, created_at) VALUES ($orderId, $rating, '$comment', NOW())");
    }
}

// Ambil pesanan
$sql = "SELECT * FROM orders WHERE user_id = $userId ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Saya - Herbal Nusantara</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../assets/img/logo-nav.png">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/prdk.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .order-product-list { list-style: none; padding-left: 0; margin-top: 10px; }
        .order-product-list li { padding: 4px 0; font-size: 0.95rem; color: #333; }
        .order-wrapper { margin-top: 80px; margin-bottom: 80px; }
        @media (min-width: 768px) {
            .order-wrapper { margin-top: 150px; margin-bottom: 150px; }
        }
    </style>
</head>
<body>
<?php include '../components/user-navbar.php'; ?>

<div class="container order-wrapper">
    <h2 class="fw-bold text-center mb-4">Pesanan Saya</h2>
    <?php if (empty($orders)): ?>
        <div class="alert alert-info text-center">Belum ada pesanan yang dibuat.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Order ID</th>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($order['order_id']); ?></td>
                        <td><?= htmlspecialchars($order['transaction_time']); ?></td>
                        <td>
                            <ul class="order-product-list">
                            <?php
                            $itemsResult = mysqli_query($conn, "
                                SELECT oi.quantity, p.name AS product_name, pv.variant_name
                                FROM order_items oi
                                JOIN products p ON oi.product_id = p.id
                                JOIN product_variants pv ON oi.variant_id = pv.id
                                WHERE oi.order_id = {$order['id']}
                            ");
                            while ($item = mysqli_fetch_assoc($itemsResult)) {
                                echo "<li>{$item['product_name']} ({$item['variant_name']}) × {$item['quantity']}</li>";
                            }
                            ?>
                            </ul>
                        </td>
                        <td>Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?></td>
                        <td>
                            <?php
                            $status = $order['transaction_status'];
                            $badge = 'secondary';
                            $label = strtoupper($status);

                            if ($status === 'settlement') {
                                $label = 'Sedang dikemas oleh tim kami';
                                $badge = 'info';
                            } elseif ($status === 'shipping') {
                                $shippedAt = $order['shipped_at'] ?? $order['transaction_time'];
                                $shippedTimestamp = strtotime($shippedAt);
                                $selisihMenit = (time() - $shippedTimestamp) / 60;

                                if ($selisihMenit < 1) {
                                    $label = 'Barang sudah dikirim ke kurir';
                                    $badge = 'info';
                                } elseif ($selisihMenit < 2) {
                                    $label = 'Barang sedang dalam perjalanan';
                                    $badge = 'primary';
                                } else {
                                    $label = 'Barang telah sampai di tujuan';
                                    $badge = 'success';
                                }
                            } elseif ($status === 'completed') {
                                $label = 'Pesanan telah diterima';
                                $badge = 'success';
                            } elseif ($status === 'pending') {
                                $label = 'Menunggu pembayaran';
                                $badge = 'warning';
                            } elseif ($status === 'expire') {
                                $label = 'Pembayaran gagal / kadaluarsa';
                                $badge = 'danger';
                            }

                            echo "<span class='badge bg-$badge'>$label</span>";
                            ?>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a href="detail-pesanan.php?id=<?= $order['id']; ?>" class="btn btn-info btn-sm">Lihat Detail</a>
                                <?php
                                if ($status === 'shipping' && $selisihMenit >= 2): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                        <input type="hidden" name="action" value="completed">
                                        <button type="submit" class="btn btn-success btn-sm">Pesanan Diterima</button>
                                    </form>
                                <?php elseif ($status === 'completed'): ?>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#ratingModal<?= $order['id']; ?>">Beri Rating</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- MODAL DI LUAR TABEL -->
        <?php foreach ($orders as $order): ?>
            <?php if ($order['transaction_status'] === 'completed'): ?>
                <div class="modal fade" id="ratingModal<?= $order['id']; ?>" tabindex="-1" aria-labelledby="ratingModalLabel<?= $order['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="ratingModalLabel<?= $order['id']; ?>">Beri Rating Pesanan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                <input type="hidden" name="action" value="rating">
                                <div class="mb-3">
                                    <label class="form-label">Rating (1-5)</label>
                                    <select name="rating" class="form-select" required>
                                        <option value="">Pilih rating</option>
                                        <option value="1">1 - Sangat Buruk</option>
                                        <option value="2">2 - Buruk</option>
                                        <option value="3">3 - Biasa</option>
                                        <option value="4">4 - Bagus</option>
                                        <option value="5">5 - Sangat Bagus</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Komentar</label>
                                    <textarea name="comment" class="form-control" rows="3" placeholder="Tulis komentar Anda..." required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Kirim</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../components/footer.php'; ?>
<script src="../assets/js/navbar.js"></script>
</body>
</html>
