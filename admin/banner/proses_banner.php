<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require_once '../../php/config.php';

function uploadImage($fileInput, $prefixId = 'banner', $uploadDir = '../../uploads/content_images/')
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
        return $fileName;
    } else {
        return null;
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'tambah') {
    $type = 'banner';

    if (!empty($_FILES['banner_image']['name'])) {
        $uploadedFile = uploadImage($_FILES['banner_image']);

        if ($uploadedFile) {
            $stmt = mysqli_prepare($conn, "INSERT INTO content_images (type, image_path, created_at) VALUES (?, ?, NOW())");
            mysqli_stmt_bind_param($stmt, 'ss', $type, $uploadedFile);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            header("Location: table_banner.php?success=tambah");
            exit;
        }
    }

    header("Location: table_banner.php?error=gagal_upload");
    exit;
} elseif ($action === 'hapus') {
    $id = $_GET['id'] ?? null;

    if ($id) {
        $stmt = mysqli_prepare($conn, "SELECT image_path FROM content_images WHERE id = ? AND type = 'banner'");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $imagePath);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (!empty($imagePath)) {
            $filePath = '../../uploads/content_image/' . $imagePath;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $deleteStmt = mysqli_prepare($conn, "DELETE FROM content_images WHERE id = ? AND type = 'banner'");
        mysqli_stmt_bind_param($deleteStmt, 'i', $id);
        mysqli_stmt_execute($deleteStmt);
        mysqli_stmt_close($deleteStmt);

        header("Location: table_banner.php?success=hapus");
        exit;
    }

    header("Location: table_banner.php?error=id_tidak_ditemukan");
    exit;
}
