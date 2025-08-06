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
    $phone = $_POST['phone'] ?? null;
    $province = $_POST['province_name'] ?? null;
    $city = $_POST['city_name'] ?? null;
    $postal = $_POST['postal_code'] ?? null;
    $address = $_POST['full_address'] ?? null;

    // Cek apakah email sudah digunakan orang lain
    $checkEmail = mysqli_prepare($conn, "SELECT id FROM users WHERE email=? AND id!=?");
    mysqli_stmt_bind_param($checkEmail, "si", $email, $userId);
    mysqli_stmt_execute($checkEmail);
    mysqli_stmt_store_result($checkEmail);

    if (mysqli_stmt_num_rows($checkEmail) > 0) {
        header("Location: edit_akun.php?id={$userId}&error=email_exists");
        exit;
    }

    mysqli_stmt_close($checkEmail);

    // Update data pengguna
    $stmt = mysqli_prepare($conn, "UPDATE users SET full_name=?, email=?, role=?, phone=?, province_name=?, city_name=?, postal_code=?, full_address=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssssssi", $fullName, $email, $role, $phone, $province, $city, $postal, $address, $userId);

    if (mysqli_stmt_execute($stmt)) {
        // Jika yang diedit adalah akun admin saat ini, perbarui data di session
        if ($userId == $_SESSION['user']['id']) {
            $_SESSION['user']['full_name'] = $fullName;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;
            header("Location: edit_akun.php?id={$userId}&success=profile");
        } else {
            header("Location: table_akun.php?success=edit");
        }
    } else {
        header("Location: edit_akun.php?id={$userId}&error=general");
    }

    mysqli_stmt_close($stmt);
    exit;
} elseif ($action === 'change_password') {
    $userId = $_POST['user_id'];
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validasi password baru
    if ($newPassword !== $confirmPassword) {
        header("Location: edit_akun.php?id={$userId}&error=password_match");
        exit;
    }

    if (strlen($newPassword) < 6) {
        header("Location: edit_akun.php?id={$userId}&error=password_length");
        exit;
    }

    // Verifikasi password lama
    $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!password_verify($oldPassword, $user['password'])) {
        header("Location: edit_akun.php?id={$userId}&error=old_password");
        exit;
    }

    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
    mysqli_stmt_bind_param($updateStmt, "si", $hashedPassword, $userId);

    if (mysqli_stmt_execute($updateStmt)) {
        header("Location: edit_akun.php?id={$userId}&success=password");
    } else {
        header("Location: edit_akun.php?id={$userId}&error=general");
    }

    mysqli_stmt_close($updateStmt);
    exit;
} elseif ($action === 'hapus') {
    $userId = $_GET['id'] ?? null;

    if (!$userId) {
        echo "ID tidak valid.";
        exit;
    }

    // Cek apakah user eksis dulu
    $cek = mysqli_prepare($conn, "SELECT id, role FROM users WHERE id=?");
    mysqli_stmt_bind_param($cek, 'i', $userId);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) === 0) {
        echo "User tidak ditemukan.";
        exit;
    }

    // Cek jika yang dihapus adalah admin
    mysqli_stmt_bind_result($cek, $id, $role);
    mysqli_stmt_fetch($cek);
    mysqli_stmt_close($cek);

    // Jangan izinkan penghapusan akun admin atau akun sendiri
    if ($role === 'admin' || $userId == $_SESSION['user']['id']) {
        header("Location: table_akun.php?error=cannot_delete");
        exit;
    }

    // Hapus user
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: table_akun.php?success=hapus");
    exit;
}
