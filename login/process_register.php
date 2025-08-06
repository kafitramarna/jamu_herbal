<?php
session_start();
require_once '../php/config.php'; // Pastikan jalur ini sesuai struktur kamu

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $full_name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = 'user';

    // Validasi input
    if (empty($full_name) || empty($email) || empty($password)) {
        header("Location: register.php?error=" . urlencode("Semua kolom wajib diisi!"));
        exit;
    }

    // Hash password
    $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

    // Cek apakah email sudah terdaftar
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: register.php?error=" . urlencode("Email sudah terdaftar!"));
        exit;
    }
    $stmt->close();

    // Simpan user baru
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $email, $passwordHashed, $role);

    if ($stmt->execute()) {
        header("Location: register.php?success=" . urlencode("Pendaftaran berhasil! Silakan login."));
    } else {
        header("Location: register.php?error=" . urlencode("Terjadi kesalahan saat menyimpan data."));
    }

    $stmt->close();
    $conn->close();
    exit;
} else {
    header("Location: register.php");
    exit;
}
