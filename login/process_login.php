<?php
require_once '../php/config.php'; // Sudah termasuk session_start()

// Cek apakah form disubmit
if (isset($_POST['email'], $_POST['password'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Cek apakah email terdaftar di tabel users
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Simpan data user ke session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nama_lengkap' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            // Redirect sesuai role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../admin/dashboard.php");
                    break;
                case 'user':
                    header("Location: ../user/user-index.php");
                    break;
                default:
                    header("Location: ../index.php");
                    break;
            }
            exit;
        } else {
            // Password salah
            header("Location: login.php?error=Email atau kata sandi salah");
            exit;
        }
    } else {
        // Email tidak ditemukan
        header("Location: login.php?error=Email atau kata sandi salah");
        exit;
    }

    $stmt->close();
} else {
    // Form tidak lengkap
    header("Location: login.php?error=Semua kolom harus diisi");
    exit;
}

$conn->close();
