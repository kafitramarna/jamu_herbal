<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require_once '../../php/config.php';

function uploadImage($fileInput, $prefixId, $uploadDir = '../../uploads/produk/')
{
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if ($fileInput['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $extension = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extension, $allowedExtensions)) {
        return null;
    }

    $randomCode = bin2hex(random_bytes(4)); // 8 karakter acak
    $fileName = $prefixId . '_' . $randomCode . '.' . $extension;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($fileInput['tmp_name'], $targetPath)) {
        // Kembali hanya nama file saja untuk disimpan di DB
        return $fileName;
    } else {
        return null;
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'tambah') {
    $name = $_POST['product_name'];
    $type = $_POST['product_type'];
    $description = $_POST['product_description'];

    $query = "INSERT INTO products (name, description, type, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'sss', $name, $description, $type);
    mysqli_stmt_execute($stmt);
    $productId = mysqli_insert_id($conn);

    $mainImagePath = null;
    if (!empty($_FILES['main_image']['name'])) {
        $mainImagePath = uploadImage($_FILES['main_image'], $productId);
    }

    if ($mainImagePath) {
        $updateQuery = "UPDATE products SET main_image = ? WHERE id = ?";
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, 'si', $mainImagePath, $productId);
        mysqli_stmt_execute($updateStmt);
    }

    foreach ($_POST['variant_name'] as $index => $variantName) {
        $price = $_POST['price'][$index];
        $stock = $_POST['stock'][$index];

        $variantImage = null;
        if (!empty($_FILES['variant_image']['name'][$index])) {
            $fileArray = [
                'name' => $_FILES['variant_image']['name'][$index],
                'type' => $_FILES['variant_image']['type'][$index],
                'tmp_name' => $_FILES['variant_image']['tmp_name'][$index],
                'error' => $_FILES['variant_image']['error'][$index],
                'size' => $_FILES['variant_image']['size'][$index],
            ];
            $variantImage = uploadImage($fileArray, $productId);
        }

        $variantQuery = "INSERT INTO product_variants (product_id, variant_name, price, stock, variant_image, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $variantStmt = mysqli_prepare($conn, $variantQuery);
        mysqli_stmt_bind_param($variantStmt, 'isdis', $productId, $variantName, $price, $stock, $variantImage);
        mysqli_stmt_execute($variantStmt);
    }

    header("Location: table_produk.php?success=tambah");
    exit;
} elseif ($action === 'edit') {
    $product_id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $type = $_POST['product_type'];
    $description = $_POST['product_description'];

    // Upload gambar utama jika ada perubahan
    if (!empty($_FILES['main_image']['name'])) {
        $main_image = $_FILES['main_image']['name'];
        $target_main = "uploads/" . basename($main_image);
        move_uploaded_file($_FILES['main_image']['tmp_name'], $target_main);

        // Update produk termasuk gambar
        $stmt = mysqli_prepare($conn, "UPDATE products SET name=?, description=?, type=?, main_image=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $description, $type, $main_image, $product_id);
    } else {
        // Update produk tanpa ubah gambar
        $stmt = mysqli_prepare($conn, "UPDATE products SET name=?, description=?, type=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssi", $name, $description, $type, $product_id);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // === VARIAN ===
    $existing_variant_ids = $_POST['variant_id'] ?? [];
    $variant_names = $_POST['variant_name'];
    $variant_prices = $_POST['price'];
    $variant_stocks = $_POST['stock'];
    $variant_images = $_FILES['variant_image'];

    // Ambil semua varian lama dari database
    $result = mysqli_query($conn, "SELECT id FROM product_variants WHERE product_id = $product_id");
    $old_variant_ids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $old_variant_ids[] = $row['id'];
    }

    // Buat array varian yang dipertahankan (id dari form)
    $kept_variant_ids = [];

    foreach ($variant_names as $index => $variant_name) {
        $price = $variant_prices[$index];
        $stock = $variant_stocks[$index];
        $variant_image = '';

        // Proses upload gambar varian jika ada
        if (!empty($variant_images['name'][$index])) {
            $variant_image = $variant_images['name'][$index];
            $target_variant = "uploads/" . basename($variant_image);
            move_uploaded_file($variant_images['tmp_name'][$index], $target_variant);
        }

        // Jika ini varian lama (ada ID)
        if (!empty($existing_variant_ids[$index])) {
            $variant_id = $existing_variant_ids[$index];
            $kept_variant_ids[] = $variant_id;

            if ($variant_image !== '') {
                // Update dengan gambar baru
                $stmt = mysqli_prepare($conn, "UPDATE product_variants SET variant_name=?, price=?, stock=?, variant_image=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "sdisi", $variant_name, $price, $stock, $variant_image, $variant_id);
            } else {
                // Update tanpa ubah gambar
                $stmt = mysqli_prepare($conn, "UPDATE product_variants SET variant_name=?, price=?, stock=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "sdii", $variant_name, $price, $stock, $variant_id);
            }
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            // Tambahkan varian baru
            $stmt = mysqli_prepare($conn, "INSERT INTO product_variants (product_id, variant_name, price, stock, variant_image) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isdis", $product_id, $variant_name, $price, $stock, $variant_image);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    // Hapus varian yang tidak lagi ada di form
    $to_delete_ids = array_diff($old_variant_ids, $kept_variant_ids);
    foreach ($to_delete_ids as $delete_id) {
        // Ambil nama file gambar
        $query = mysqli_query($conn, "SELECT variant_image FROM product_variants WHERE id = $delete_id");
        $row = mysqli_fetch_assoc($query);
        $image_to_delete = $row['variant_image'];

        // Hapus file gambar jika ada
        if (!empty($image_to_delete)) {
            $file_path = "uploads/" . $image_to_delete;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Hapus data dari database
        mysqli_query($conn, "DELETE FROM product_variants WHERE id = $delete_id");
    }

    // Cek apakah masih ada varian
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM product_variants WHERE product_id = $product_id");
    $row = mysqli_fetch_assoc($result);
    if ($row['total'] == 0) {
        // Hapus produk jika tidak ada varian lagi
        mysqli_query($conn, "DELETE FROM products WHERE id = $product_id");
    }

    header("Location: table_produk.php");
    exit();
} elseif ($action === 'hapus_varian') {
    $variantId = $_GET['id'];

    // Ambil gambar varian
    $getImageStmt = mysqli_prepare($conn, "SELECT variant_image, product_id FROM product_variants WHERE id = ?");
    mysqli_stmt_bind_param($getImageStmt, 'i', $variantId);
    mysqli_stmt_execute($getImageStmt);
    mysqli_stmt_bind_result($getImageStmt, $variantImage, $productId);
    mysqli_stmt_fetch($getImageStmt);
    mysqli_stmt_close($getImageStmt);

    // Hapus gambar varian dari folder jika ada
    if (!empty($variantImage)) {
        $variantImagePath = "../../uploads/produk/" . $variantImage;
        if (file_exists($variantImagePath)) {
            unlink($variantImagePath);
        }
    }

    // Hapus varian dari database
    $deleteStmt = mysqli_prepare($conn, "DELETE FROM product_variants WHERE id = ?");
    mysqli_stmt_bind_param($deleteStmt, 'i', $variantId);
    mysqli_stmt_execute($deleteStmt);

    // Cek apakah masih ada varian lain untuk produk ini
    $checkStmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM product_variants WHERE product_id = ?");
    mysqli_stmt_bind_param($checkStmt, 'i', $productId);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_bind_result($checkStmt, $variantCount);
    mysqli_stmt_fetch($checkStmt);
    mysqli_stmt_close($checkStmt);

    // Jika tidak ada varian tersisa, hapus juga produk dan gambarnya
    if ($variantCount == 0) {
        // Hapus gambar produk
        $getProductStmt = mysqli_prepare($conn, "SELECT main_image FROM products WHERE id = ?");
        mysqli_stmt_bind_param($getProductStmt, 'i', $productId);
        mysqli_stmt_execute($getProductStmt);
        mysqli_stmt_bind_result($getProductStmt, $mainImage);
        mysqli_stmt_fetch($getProductStmt);
        mysqli_stmt_close($getProductStmt);

        if (!empty($mainImage)) {
            $mainImagePath = "../../uploads/produk/" . $mainImage;
            if (file_exists($mainImagePath)) {
                unlink($mainImagePath);
            }
        }

        // Hapus produk
        $deleteProductStmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
        mysqli_stmt_bind_param($deleteProductStmt, 'i', $productId);
        mysqli_stmt_execute($deleteProductStmt);
    }

    header("Location: table_produk.php?success=hapus_varian");
    exit;
}
