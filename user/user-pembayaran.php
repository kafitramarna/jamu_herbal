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
                    <label for="kurir" class="section-title">Kurir</label>
                    <select id="kurir" class="form-select" required>
                        <option value="">-- Pilih Kurir --</option>
                        <option value="jne">JNE</option>
                        <option value="jnt">J&T</option>
                        <option value="pos">POS Indonesia</option>
                    </select>
                    <div id="loading-ongkir" class="form-text text-primary mt-1" style="display: none;">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div> Mengambil data ongkir...
                    </div>
                </div>

                <div class="mb-3">
                    <label for="layanan" class="section-title">Layanan Pengiriman</label>
                    <select id="layanan" class="form-select" required>
                        <option value="">-- Pilih Layanan --</option>
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
        const origin = 151; // ID kecamatan asal
        const destination = <?= (int)$user['district_id'] ?>;
        const weight = <?= (int)$total_berat ?>;
        const totalHarga = <?= (int)$total_harga ?>;
        const asuransi = <?= (int)$asuransi ?>;
        const biayaLayanan = <?= (int)$biaya_layanan ?>;

        document.getElementById("kurir")?.addEventListener("change", function() {
            const kurir = this.value;
            const loading = document.getElementById("loading-ongkir");
            const layananSelect = document.getElementById("layanan");
            layananSelect.innerHTML = '<option value="">-- Pilih Layanan --</option>';
            if (!kurir) return;

            loading.style.display = "inline-block";
            fetch("../php/cek_ongkir.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `origin=${origin}&destination=${destination}&weight=${weight}&courier=${kurir}`
                })
                .then(async response => {
                    console.log("Is Response:", response.constructor.name);
                    const raw = await response.text();
                    console.log("RAW RESPONSE:", raw);

                    let json;
                    try {
                        json = JSON.parse(raw);
                    } catch (e) {
                        alert("Response bukan JSON valid!");
                        return [];
                    }
                    return json;
                })
                .then(result => {
                    console.log("Parsed JSON:", result);

                    if (!result || !Array.isArray(result.data)) {
                        alert("Tidak ada layanan ongkir tersedia / format salah");
                        return;
                    }

                    result.data.forEach(service => {
                        const cost = service.cost;
                        const etd = service.etd;
                        const option = `<option value="${cost}">${service.service} - Rp${cost.toLocaleString('id-ID')} (ETD: ${etd} hari)</option>`;
                        layananSelect.innerHTML += option;
                    });
                })
                .catch(err => alert("Gagal ambil data ongkir: " + err))
                .finally(() => loading.style.display = "none");
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
                onPending: function() {
                    alert("Transaksi pending! Selesaikan pembayaran.");
                },
                onError: function() {
                    alert("Terjadi error saat pembayaran!");
                }
            });
        });
    </script>
</body>

</html>