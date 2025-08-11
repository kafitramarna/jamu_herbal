<?php
session_start();
require "../php/config.php";
require '../vendor/autoload.php';

// Cek login
if (!isset($_SESSION['user']['id'])) {
    header("Location: ../login/login.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

// Ambil data user (prepared statement)
$stmt = $conn->prepare("SELECT full_name, full_address, phone, district_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User tidak ditemukan.");
}

// Ambil cart pending
$stmt = $conn->prepare("SELECT id, total_price FROM carts WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();
$stmt->close();

$cart_id = $cart['id'] ?? 0;
$items = [];
$total_items = 0;
$total_berat = 0;

if ($cart_id) {
    $stmt = $conn->prepare("
        SELECT ci.qty, ci.price, pv.variant_name, pv.variant_image, pv.weight, p.name AS product_name 
        FROM cart_items ci
        JOIN product_variants pv ON ci.product_variant_id = pv.id
        JOIN products p ON pv.product_id = p.id
        WHERE ci.cart_id = ?
    ");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
        $total_items += (int)$row['qty'];
        $total_berat += (int)$row['qty'] * (int)$row['weight'];
    }
    $stmt->close();
}

// Biaya tetap
$asuransi = 1000;
$biaya_layanan = 2000;
$total_harga = $cart['total_price'] ?? 0;

// CSRF token sederhana
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Checkout - Herbal Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../assets/img/logo-nav.png">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/pembayaran.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .shipping-logo {
            width: 20px;
            height: 20px;
            margin-right: 5px;
            vertical-align: text-bottom;
        }

        .loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .loading-spinner .spinner-border {
            margin-right: 10px;
        }

        optgroup {
            font-weight: bold;
            color: #333;
        }

        #layanan option {
            padding: 5px 0;
        }

        /* Style untuk dropdown layanan pengiriman */
        .custom-select-wrapper {
            position: relative;
        }

        .service-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .service-details {
            color: #6c757d;
            font-size: 0.85em;
        }
    </style>
</head>

<body>
    <?php include '../components/user-navbar.php'; ?>
    <div class="container my-5">
        <h4 class="mb-4">Checkout</h4>

        <div class="card p-4 shadow-sm mb-4">
            <div class="section-title">Alamat Pengiriman</div>
            <div><i class="fas fa-map-marker-alt me-2"></i> <?= htmlspecialchars($user['full_name']) ?> - <?= htmlspecialchars($user['phone']) ?></div>
            <div class="text-muted"><?= htmlspecialchars($user['full_address']) ?></div>
        </div>

        <div class="card p-4 shadow-sm mb-4">
            <div class="section-title">Produk Dipesan</div>
            <?php if (empty($items)): ?>
                <div class="alert alert-warning">Keranjang Anda kosong.</div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-3">
                        <div class="d-flex">
                            <img src="../uploads/produk/<?= htmlspecialchars($item['variant_image']) ?>" class="product-img me-3" style="width: 80px; height: 80px; object-fit: cover;">
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($item['variant_name']) ?></div>
                                <div class="text-muted small">QTY: <?= (int)$item['qty'] ?> x Rp<?= number_format($item['price'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="text-end text-muted small">
                            <div>Berat/item:</div>
                            <div><strong><?= (int)$item['weight'] ?> gram</strong></div>
                            <div>Total:</div>
                            <div><strong><?= (int)$item['qty'] * (int)$item['weight'] ?> gram</strong></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="mt-3">
                    <div class="d-flex justify-content-between fw-bold">
                        <div>Total Berat Semua Produk:</div>
                        <div><?= number_format($total_berat, 0, ',', '.') ?> gram</div>
                    </div>
                    <div class="d-flex justify-content-between fw-bold mt-2">
                        <div>Total Harga Semua Produk:</div>
                        <div>Rp<?= number_format($total_harga, 0, ',', '.') ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($items)): ?>
            <div class="card p-4 shadow-sm mb-4">
                <div class="mb-3">
                    <label for="layanan" class="section-title">Layanan Pengiriman</label>
                    <div id="shipping-loading" class="loading-spinner">
                        <div class="spinner-border text-primary" role="status"></div>
                        <span>Mengambil layanan pengiriman yang tersedia...</span>
                    </div>
                    <select id="layanan" class="form-select" required style="display: none;">
                        <option value="">-- Pilih Layanan Pengiriman --</option>
                    </select>
                </div>
            </div>

            <div class="card p-4 shadow-sm mb-4">
                <div class="section-title">Rincian Pembayaran</div>
                <div class="d-flex justify-content-between">
                    <span>Subtotal Pesanan</span>
                    <span>Rp<?= number_format($total_harga, 0, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Biaya Pengiriman</span>
                    <span id="ongkir-display" class="text-success">Rp0</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Asuransi Pengiriman</span>
                    <span>Rp<?= number_format($asuransi, 0, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Biaya Layanan</span>
                    <span>Rp<?= number_format($biaya_layanan, 0, ',', '.') ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Total Pembayaran</span>
                    <span id="total-display">Rp<?= number_format($total_harga + $asuransi + $biaya_layanan, 0, ',', '.') ?></span>
                </div>
            </div>

            <form id="checkout-form">
                <input type="hidden" name="cart_id" value="<?= (int)$cart_id ?>">
                <input type="hidden" name="total_tagihan" id="input-total-tagihan" value="<?= $total_harga + $asuransi + $biaya_layanan ?>">
                <input type="hidden" name="ongkir" id="input-ongkir" value="0">
                <input type="hidden" name="user_id" value="<?= (int)$user_id ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <button type="submit" class="btn btn-checkout w-100 py-3" id="pay-button">Bayar Sekarang</button>
            </form>
        <?php endif; ?>
    </div>

    <a href="https://wa.me/628970014820" class="btn btn-success position-fixed bottom-0 end-0 m-3" target="_blank">
        <i class="fab fa-whatsapp"></i> Chat
    </a>

    <?php include '../components/footer.php'; ?>
    <script src="../assets/js/navbar.js"></script>
    <script>
        const origin = 1324; // ID kecamatan asal
        const destination = <?= (int)$user['district_id'] ?>;
        const weight = <?= (int)$total_berat ?>;
        const totalHarga = <?= (int)$total_harga ?>;
        const asuransi = <?= (int)$asuransi ?>;
        const biayaLayanan = <?= (int)$biaya_layanan ?>;

        // Langsung load semua layanan pengiriman saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const layananSelect = document.getElementById("layanan");
            const loadingElement = document.getElementById("shipping-loading");

            // Daftar semua kurir yang didukung
            const couriers = 'jne:jnt:pos:sicepat:ninja:sap:ide:lion:rex';

            // Fetch layanan pengiriman
            fetch("../php/cek_ongkir.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `origin=${origin}&destination=${destination}&weight=${weight}&courier=${couriers}`
                })
                .then(response => response.json())
                .then(result => {
                    if (!result || !Array.isArray(result.data) || result.data.length === 0) {
                        loadingElement.innerHTML = '<div class="alert alert-warning">Tidak ada layanan pengiriman yang tersedia untuk lokasi Anda.</div>';
                        return;
                    }

                    // Grup layanan berdasarkan kurir
                    const courierGroups = {};

                    result.data.forEach(service => {
                        const courierName = service.name;
                        if (!courierGroups[courierName]) {
                            courierGroups[courierName] = [];
                        }
                        courierGroups[courierName].push(service);
                    });

                    // Sembunyikan loading, tampilkan dropdown
                    loadingElement.style.display = 'none';
                    layananSelect.style.display = 'block';

                    // Urutkan kurir berdasarkan nama
                    const sortedCouriers = Object.keys(courierGroups).sort();

                    // Populasi dropdown dengan optgroup untuk setiap kurir
                    sortedCouriers.forEach(courierName => {
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = courierName;

                        // Urutkan layanan berdasarkan harga (termurah dulu)
                        const sortedServices = courierGroups[courierName].sort((a, b) => a.cost - b.cost);

                        sortedServices.forEach(service => {
                            const option = document.createElement('option');
                            option.value = service.cost;

                            // Format estimasi pengiriman
                            let etdText = service.etd ? `(${service.etd} hari)` : '';

                            option.textContent = `${service.service} - Rp${service.cost.toLocaleString('id-ID')} ${etdText}`;
                            option.dataset.description = service.description || '';
                            option.dataset.courier = service.code;
                            option.dataset.service = service.service;

                            optgroup.appendChild(option);
                        });

                        layananSelect.appendChild(optgroup);
                    });
                })
                .catch(err => {
                    console.error("Error loading shipping options:", err);
                    loadingElement.innerHTML = '<div class="alert alert-danger">Gagal memuat layanan pengiriman. Silakan muat ulang halaman.</div>';
                });
        });

        document.getElementById("layanan")?.addEventListener("change", function() {
            const ongkir = parseInt(this.value || 0);
            const totalTagihan = totalHarga + asuransi + biayaLayanan + ongkir;
            document.getElementById("ongkir-display").textContent = `Rp${ongkir.toLocaleString('id-ID')}`;
            document.getElementById("total-display").textContent = `Rp${totalTagihan.toLocaleString('id-ID')}`;
            document.getElementById("input-ongkir").value = ongkir;
            document.getElementById("input-total-tagihan").value = totalTagihan;
        });
    </script>

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-x1e--msEyhvMkGot"></script>
    <script>
        document.getElementById("checkout-form")?.addEventListener("submit", async function(e) {
            e.preventDefault();

            const layanan = document.getElementById("layanan").value;
            if (!layanan) {
                alert("Silakan pilih layanan pengiriman terlebih dahulu!");
                return;
            }

            const ongkir = parseInt(document.getElementById('input-ongkir').value);
            const totalTagihan = parseInt(document.getElementById('input-total-tagihan').value);

            const itemDetails = <?= json_encode(array_map(function ($item, $i) {
                                    return [
                                        'id' => 'VAR' . ($i + 1),
                                        'price' => (int) $item['price'],
                                        'quantity' => (int) $item['qty'],
                                        'name' => $item['product_name'] . ' - ' . $item['variant_name']
                                    ];
                                }, $items, array_keys($items))) ?>;

            const customer = {
                first_name: <?= json_encode($user['full_name']) ?>,
                email: <?= json_encode($_SESSION['user']['email'] ?? '') ?>,
                phone: <?= json_encode($user['phone']) ?>
            };

            const res = await fetch("../php/generate_snap_token.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    total: totalTagihan,
                    ongkir: ongkir,
                    item_details: itemDetails,
                    customer: customer,
                    csrf_token: <?= json_encode($csrf_token) ?>
                })
            });

            const result = await res.json();
            if (!result.token) {
                alert("Gagal mendapatkan token pembayaran");
                return;
            }
            const snapToken = result.token;
            const orderId = result.order_id;

            snap.pay(snapToken, {
                onSuccess: function(result) {
                    fetch("../php/proses_pembayaran.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            payment_type: result.payment_type,
                            transaction_time: result.transaction_time,
                            transaction_status: result.transaction_status,
                            gross_amount: result.gross_amount,
                            user_id: <?= (int)$user_id ?>,
                            cart_id: <?= (int)$cart_id ?>,
                            ongkir: ongkir,
                            csrf_token: <?= json_encode($csrf_token) ?>
                        })
                    }).then(res => res.json()).then(res => {
                        if (res.success) {
                            window.location.href = "user-produk.php";
                        } else {
                            alert("Gagal simpan transaksi!");
                        }
                    });
                },
                onPending: function(result) {
                    fetch("../php/proses_pembayaran.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            payment_type: result.payment_type,
                            transaction_time: result.transaction_time,
                            transaction_status: "pending",
                            gross_amount: result.gross_amount,
                            user_id: <?= (int)$user_id ?>,
                            cart_id: <?= (int)$cart_id ?>,
                            ongkir: ongkir,
                            csrf_token: <?= json_encode($csrf_token) ?>
                        })
                    }).then(res => res.json()).then(res => {
                        if (res.success) {
                            alert("Transaksi pending! Selesaikan pembayaran Anda.");
                            window.location.href = "user-produk.php";
                        } else {
                            alert("Gagal simpan transaksi!");
                        }
                    });
                },
                onError: function() {
                    alert("Terjadi error saat pembayaran!");
                }
            });
        });
    </script>
</body>

</html>