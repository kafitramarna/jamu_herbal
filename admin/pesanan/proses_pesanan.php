<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require "../../php/config.php";

// Cek apakah data dikirim lewat POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];

    if (!empty($order_id) && !empty($new_status)) {
        // Cek apakah statusnya "shipping" → perlu update shipped_at juga
        if ($new_status === 'shipping') {
            $query = "UPDATE orders SET transaction_status = ?, shipped_at = NOW() WHERE id = ?";
        } else {
            $query = "UPDATE orders SET transaction_status = ? WHERE id = ?";
        }

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['Sukses'] = "Status pesanan berhasil diperbarui menjadi '$new_status'.";
        } else {
            $_SESSION['Gagal'] = "Gagal mengubah status pesanan.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['Gagal'] = "Data tidak lengkap.";
    }

    header("Location: table_pesanan.php");
    exit;
} else {
    header("Location: table_pesanan.php");
    exit;
}
