<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require "../../php/config.php";

// Ambil data produk berdasarkan ID
$productId = $_GET['id'];
$sql = "SELECT 
            p.id AS product_id, 
            p.name AS product_name, 
            p.description, 
            p.main_image, 
            p.type,
            v.id AS variant_id, 
            v.variant_name, 
            v.price, 
            v.stock, 
            v.weight,
            v.variant_image 
        FROM products p
        LEFT JOIN product_variants v ON p.id = v.product_id
        WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$product = null;
$variants = [];

while ($row = mysqli_fetch_assoc($result)) {
    if (!$product) {
        $product = [
            'name' => $row['product_name'],
            'description' => $row['description'],
            'main_image' => $row['main_image'],
            'type' => $row['type']
        ];
    }

    if ($row['variant_id']) {
        $variants[] = [
            'id' => $row['variant_id'],
            'variant_name' => $row['variant_name'],
            'price' => $row['price'],
            'stock' => $row['stock'],
            'weight' => $row['weight'],
            'variant_image' => $row['variant_image']
        ];
    }
}

if (!$product) {
    echo "Produk tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Edit Produk - Herbal Nusantara</title>
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="stylesheet" href="../../assets/css/edit_produk.css" />
    <link rel="icon" type="image/png" href="../../assets/img/logo-nav.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php include '../../components/sidebar.php'; ?>

    <section id="content">
        <main>
            <div class="head-title">
                <a href="table_produk.php" class="back-btn"><i class='bx bx-arrow-back'></i> Kembali</a>
                <h1 class="judul">Edit Produk</h1>
            </div>

            <div class="form-container">
                <form action="proses_produk.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="product_id" value="<?= $productId ?>">

                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" name="product_name" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Jenis</label>
                        <input type="text" name="product_type" value="<?= htmlspecialchars($product['type']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="product_description" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Gambar Utama</label>
                        <?php if ($product['main_image']) : ?>
                            <div><img src="../../uploads/produk/<?= htmlspecialchars($product['main_image']) ?>" alt="Main Image" class="preview-img" style="max-height: 120px;"></div>
                        <?php endif; ?>

                        <label class="custom-file-upload">
                            <input type="file" name="main_image">
                            Klik di sini untuk unggah gambar utama
                        </label>
                    </div>

                    <div id="variant-container">
                        <?php foreach ($variants as $v) : ?>
                            <div class="variant-item variant-box">
                                <input type="hidden" name="variant_id[]" value="<?= $v['id'] ?>">

                                <div class="form-group-1">
                                    <label>Nama Varian</label>
                                    <input type="text" name="variant_name[]" placeholder="Nama Varian" value="<?= htmlspecialchars($v['variant_name']) ?>" required>
                                </div>
                                <div class="form-group-1">
                                    <label>Harga</label>
                                    <input type="number" name="price[]" placeholder="Harga" value="<?= $v['price'] ?>" required>
                                </div>
                                <div class="form-group-1">
                                    <label>Stok</label>
                                    <input type="number" name="stock[]" placeholder="Stok" value="<?= $v['stock'] ?>" required>
                                </div>
                                <div class="form-group-1">
                                    <label>Berat (gram)</label>
                                    <input type="number" name="weight[]" placeholder="Berat" value="<?= $v['weight'] ?? '' ?>" required>
                                </div>

                                <div class="form-group-1">
                                    <label>Gambar Varian Lama</label>
                                    <?php if ($v['variant_image']) : ?>
                                        <div class="variant-image-section">
                                            <div class="image-preview-wrapper">
                                                <img src="../../uploads/produk/<?= htmlspecialchars($v['variant_image']) ?>" alt="Varian Image" class="preview-img">
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <p><em>Tidak ada gambar lama</em></p>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group-1">
                                    <label>Unggah Gambar Baru</label>
                                    <label class="custom-file-upload-variant">
                                        <input type="file" name="variant_image[]">
                                        Klik di sini untuk unggah gambar varian baru
                                    </label>
                                </div>

                                <button type="button" class="remove-variant" onclick="removeVariant(this)">Hapus Varian</button>
                            </div>
                        <?php endforeach; ?>
                    </div>


                    <button type="button" class="add-variant" onclick="addVariant()">+ Tambah Varian</button>

                    <br><br>
                    <button type="submit" class="submit-btn">Simpan Perubahan</button>
                </form>
            </div>
        </main>
    </section>


    <script>
        function addVariant() {
            const container = document.getElementById('variant-container');
            const variant = document.createElement('div');
            variant.className = 'variant-item';
            variant.innerHTML = `
                <input type="text" name="variant_name[]" placeholder="Nama Varian" required>
                <input type="number" name="price[]" placeholder="Harga" required>
                <input type="number" name="stock[]" placeholder="Stok" required>
                <input type="number" name="weight[]" placeholder="Berat (gram)" required>
                <input type="file" name="variant_image[]" required>
                <label class="custom-file-upload-variant">
                    <input type="file" name="variant_image[]" required>
                    Klik di sini untuk unggah gambar varian
                </label>
                <button type="button" class="remove-variant" onclick="removeVariant(this)">–</button>
            `;
            container.appendChild(variant);
        }
    </script>


    <script>
        function removeVariant(button) {
            const container = document.getElementById('variant-container');
            const items = container.querySelectorAll('.variant-item');
            if (items.length > 1) {
                button.parentElement.remove();
            }
        }
    </script>
    <script>
        function removeVariant(button) {
            const variantItem = button.closest('.variant-item');
            const nameInput = variantItem.querySelector('input[name="variant_name[]"]');
            const variantName = nameInput ? nameInput.value : 'varian ini';

            const confirmed = confirm(`Apakah kamu yakin ingin menghapus "${variantName}"?`);
            if (confirmed) {
                variantItem.remove();
            }
        }
    </script>

</body>

</html>