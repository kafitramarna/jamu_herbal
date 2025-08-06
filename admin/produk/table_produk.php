<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require "../../php/config.php";

// Ambil semua produk dengan varian-nya
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
            v.variant_image,
            v.weight
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Produk List - Herbal Nusantara</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo-nav.png" />
    <!-- Boxicons -->
    <link href="https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet" />
    <!-- My CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="stylesheet" href="../../assets/css/produk.css" />
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
                    <h1>Produk</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Produk</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Table List</a></li>
                    </ul>
                </div>
            </div>
            <div class="head-title">
                <a href="tambah_produk.php" class="btn-download" style="margin-left: auto;">
                    <span class="text">Tambah Produk</span>
                </a>
            </div>

            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar Produk</th>
                            <th>Nama Produk</th>
                            <th>Jenis</th>
                            <th>Deskripsi</th>
                            <th>Nama Varian</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Berat (gram)</th>
                            <th>Gambar Varian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (count($products) === 0) {
                            echo "<tr><td colspan='11' class='no-product-row'>Tidak ada produk.</td></tr>";
                        } else {
                            foreach ($products as $id => $prod) {
                                // Jika tidak ada varian, tampilkan satu baris dummy
                                if (empty($prod['variants'])) {
                                    $mainImagePath = ($prod['main_image'] && file_exists("../../uploads/produk/" . $prod['main_image']))
                                        ? "../../uploads/produk/" . htmlspecialchars($prod['main_image'])
                                        : "../../assets/img/no-image.png";

                                    echo "<tr>";
                                    echo "<td>{$no}</td>";
                                    echo "<td><img src='{$mainImagePath}' alt='Produk' class='product-img'></td>";
                                    echo "<td>" . htmlspecialchars($prod['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($prod['type']) . "</td>";

                                    $descWords = explode(' ', $prod['description']);
                                    $shortDesc = count($descWords) > 15 ? implode(' ', array_slice($descWords, 0, 15)) . '...' : $prod['description'];
                                    echo "<td class='text-truncate'>" . htmlspecialchars($shortDesc) . "</td>";

                                    echo "<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>";
                                    echo "<td class='action-column'>
                <a href='edit_produk.php?id={$id}' class='btn-edit'>Edit</a>
                <a href='proses_produk.php?action=hapus&id={$id}' class='btn-delete' onclick=\"return confirm('Yakin ingin menghapus produk ini?');\">Hapus</a>
            </td>";
                                    echo "</tr>";
                                    $no++;
                                } else {
                                    foreach ($prod['variants'] as $variant) {
                                        $mainImagePath = ($prod['main_image'] && file_exists("../../uploads/produk/" . $prod['main_image']))
                                            ? "../../uploads/produk/" . htmlspecialchars($prod['main_image'])
                                            : "../../assets/img/no-image.png";

                                        $variantImagePath = ($variant['variant_image'] && file_exists("../../uploads/produk/" . $variant['variant_image']))
                                            ? "../../uploads/produk/" . htmlspecialchars($variant['variant_image'])
                                            : "../../assets/img/no-image.png";

                                        echo "<tr>";
                                        echo "<td>{$no}</td>";
                                        echo "<td><img src='{$mainImagePath}' alt='Produk' class='product-img'></td>";
                                        echo "<td>" . htmlspecialchars($prod['name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($prod['type']) . "</td>";

                                        $descWords = explode(' ', $prod['description']);
                                        $shortDesc = count($descWords) > 15 ? implode(' ', array_slice($descWords, 0, 15)) . '...' : $prod['description'];
                                        echo "<td class='text-truncate'>" . htmlspecialchars($shortDesc) . "</td>";

                                        echo "<td>" . htmlspecialchars($variant['variant_name']) . "</td>";
                                        echo "<td>Rp " . number_format($variant['price'], 0, ',', '.') . "</td>";
                                        echo "<td>" . htmlspecialchars($variant['stock']) . "</td>";
                                        echo "<td>" . htmlspecialchars($variant['weight']) . "</td>";
                                        echo "<td><img src='{$variantImagePath}' alt='Varian' class='variant-img'></td>";

                                        echo "<td class='action-column'>
                    <a href='edit_produk.php?id={$id}' class='btn-edit'>Edit</a>
                    <a href='proses_produk.php?action=hapus_varian&id={$variant['variant_id']}' class='btn-delete' onclick=\"return confirm('Yakin ingin menghapus varian ini?');\">Hapus Varian</a>
                </td>";
                                        echo "</tr>";
                                        $no++;
                                    }
                                }
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