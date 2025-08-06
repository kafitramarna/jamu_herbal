<?php
// Mulai session
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}


require "../../php/config.php";

// Ambil semua produk dengan variannya
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
        ORDER BY p.id, v.id";

$result = mysqli_query($conn, $sql);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['product_id'];
    if (!isset($products[$id])) {
        $products[$id] = [
            'name' => $row['product_name'],
            'description' => $row['description'],
            'main_image' => $row['main_image'],
            'type' => $row['type'],
            'variants' => []
        ];
    }

    if ($row['variant_id']) {
        $products[$id]['variants'][] = [
            'variant_id' => $row['variant_id'],
            'variant_name' => $row['variant_name'],
            'price' => $row['price'],
            'stock' => $row['stock'],
            'weight' => $row['weight'],
            'variant_image' => $row['variant_image']
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/tambah_produk.css">

    <title>Produk List - Herbal Nusantara</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo-nav.png">

</head>

<body>
    <?php include '../../components/sidebar.php'; ?>

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <form action="#">
                <div class="form-input"></div>
            </form>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="switch-mode" class="switch-mode"></label>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <a href="table_produk.php" class="back-btn"><i class='bx bx-arrow-back'></i> Kembali</a>
                <h1 class="judul">Tambah Produk</h1>
            </div>

            <div class="form-container">
                <form action="proses_produk.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="tambah">
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" name="product_name" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis</label>
                        <input type="text" name="product_type" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="product_description" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Gambar Utama</label>
                        <label class="custom-file-upload">
                            <input type="file" name="main_image" required>
                            Klik di sini untuk unggah gambar utama
                        </label>
                    </div>

                    <h3 class="variant-title">Varian Produk</h3>
                    <div id="variant-container">
                        <div class="variant-item">
                            <input type="text" name="variant_name[]" placeholder="Nama Varian" required>
                            <input type="number" name="price[]" placeholder="Harga" required>
                            <input type="number" name="stock[]" placeholder="Stok" required>
                            <input type="number" name="weight[]" placeholder="Berat (gram)" required>
                            <label class="custom-file-upload-variant">
                                <input type="file" name="variant_image[]">
                                Klik di sini untuk unggah gambar varian
                            </label>
                            <button type="button" class="remove-variant" onclick="removeVariant(this)">–</button>
                        </div>
                    </div>

                    <button type="button" class="add-variant" onclick="addVariant()">+ Tambah Varian</button>

                    <br><br>
                    <button type="submit" class="submit-btn">Simpan Produk</button>
                </form>
            </div>
        </main>

        <!-- MAIN -->

    </section>
    <!-- CONTENT -->

    <script src="../../assets/js/script.js"></script>

    <script>
        function addVariant() {
            const container = document.getElementById('variant-container');
            const variants = container.querySelectorAll('.variant-item');

            const newVariant = document.createElement('div');
            newVariant.className = 'variant-item';
            newVariant.innerHTML = `
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
            container.appendChild(newVariant);
        }

        function removeVariant(button) {
            const container = document.getElementById('variant-container');
            const variants = container.querySelectorAll('.variant-item');
            if (variants.length > 1) {
                button.parentElement.remove();
            }
        }
    </script>


</body>

</html>