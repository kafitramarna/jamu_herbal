<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require_once '../../php/config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'edit') {
    $userId = $_POST['user_id'];
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $province = $_POST['province_name'];
    $city = $_POST['city_name'];
    $postal = $_POST['postal_code'];
    $address = $_POST['full_address'];

    $stmt = mysqli_prepare($conn, "UPDATE users SET full_name=?, email=?, role=?, phone=?, province_name=?, city_name=?, postal_code=?, full_address=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssssssi", $fullName, $email, $role, $phone, $province, $city, $postal, $address, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: table_akun.php?success=edit");
    exit;
} elseif ($action === 'hapus') {
    $userId = $_GET['id'] ?? null;

    if (!$userId) {
        echo "ID tidak valid.";
        exit;
    }

    // Cek apakah user eksis dulu
    $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE id=?");
    mysqli_stmt_bind_param($cek, 'i', $userId);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);
    if (mysqli_stmt_num_rows($cek) === 0) {
        echo "User tidak ditemukan.";
        exit;
    }
    mysqli_stmt_close($cek);

    // Hapus user
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: table_akun.php?success=hapus");
    exit;
}
