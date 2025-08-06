<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require "../../php/config.php";

$userId = $_GET['id'] ?? null;
if (!$userId) {
    echo "ID pengguna tidak ditemukan.";
    exit;
}

// Ambil data user berdasarkan ID
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "Data pengguna tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Akun - Herbal Nusantara</title>
    <link rel="icon" href="../../assets/img/logo-nav.png" type="image/png">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/tambah_produk.css">
    <link href="https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet" />
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
                <a href="table_akun.php" class="back-btn"><i class='bx bx-arrow-back'></i> Kembali</a>
                <h1 class="judul">Edit Akun</h1>
            </div>

            <div class="form-container">
                <form action="proses_akun.php" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" value="<?= htmlspecialchars($user['role']) ?>" disabled>
                        <input type="hidden" name="role" value="<?= htmlspecialchars($user['role']) ?>">
                    </div>

                    <div class="form-group">
                        <label>No. HP</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Provinsi</label>
                        <input type="text" name="province_name" value="<?= htmlspecialchars($user['province_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Kota/Kabupaten</label>
                        <input type="text" name="city_name" value="<?= htmlspecialchars($user['city_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Kode Pos</label>
                        <input type="text" name="postal_code" value="<?= htmlspecialchars($user['postal_code']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Alamat Lengkap</label>
                        <textarea name="full_address" rows="3" required><?= htmlspecialchars($user['full_address']) ?></textarea>
                    </div>

                    <br>
                    <button type="submit" class="submit-btn">Simpan Perubahan</button>
                </form>
            </div>
        </main>
    </section>

    <script src="../../assets/js/script.js"></script>
</body>

</html>