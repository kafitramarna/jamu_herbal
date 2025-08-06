<?php
// Cek dan mulai session kalau belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kalau sudah login, arahkan langsung ke dashboard masing-masing
if (isset($_SESSION['user'])) {
    switch ($_SESSION['user']['role']) {
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
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login - Herbal Nusantara</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="icon" type="image/png" href="../assets/img/logo-nav.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

</head>

<body class="d-flex flex-column min-vh-100">

    <?php include '../components/navbar.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card login-card">
                    <div class="login-header">
                        <h4 class="fw-bold">Selamat Datang Kembali</h4>
                        <p class="mb-0">Silakan login untuk melanjutkan</p>
                    </div>
                    <div class="login-form">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>
                        <form action="process_login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control" name="email" id="email" autocomplete="off" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Kata Sandi</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Masuk</button>
                        </form>
                    </div>
                    <div class="login-footer">
                        Belum punya akun? <a href="register.php">Daftar di sini</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>