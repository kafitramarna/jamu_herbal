<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require "../../php/config.php";

// Mengambil ID dari URL atau gunakan ID admin saat ini jika sedang mengedit profil sendiri
$userId = $_GET['id'] ?? $_SESSION['user']['id'];

// Cek apakah pengguna dengan ID tersebut ada di database
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

// Cek apakah ini adalah admin yang mengedit profilnya sendiri
$isSelf = ($_SESSION['user']['id'] == $userId);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= $isSelf ? "Edit Profil Saya" : "Edit Akun" ?> - Herbal Nusantara</title>
    <link rel="icon" href="../../assets/img/logo-nav.png" type="image/png">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/tambah_produk.css">
    <link href="https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet" />
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .password-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .password-section h3 {
            margin-bottom: 15px;
            color: #333;
        }
    </style>
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
                <h1 class="judul"><?= $isSelf ? "Edit Profil Saya" : "Edit Akun" ?></h1>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    switch ($_GET['error']) {
                        case 'password_match':
                            echo "Password dan konfirmasi password tidak sama.";
                            break;
                        case 'password_length':
                            echo "Password baru harus minimal 6 karakter.";
                            break;
                        case 'old_password':
                            echo "Password lama tidak sesuai.";
                            break;
                        default:
                            echo "Terjadi kesalahan. Silakan coba lagi.";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    switch ($_GET['success']) {
                        case 'profile':
                            echo "Profil berhasil diperbarui.";
                            break;
                        case 'password':
                            echo "Password berhasil diperbarui.";
                            break;
                        default:
                            echo "Perubahan berhasil disimpan.";
                    }
                    ?>
                </div>
            <?php endif; ?>

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
                        <?php if ($isSelf || $user['role'] === 'admin'): ?>
                            <input type="text" value="<?= htmlspecialchars($user['role']) ?>" disabled>
                            <input type="hidden" name="role" value="<?= htmlspecialchars($user['role']) ?>">
                        <?php else: ?>
                            <select name="role">
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>No. HP</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Provinsi</label>
                        <input type="text" name="province_name" value="<?= htmlspecialchars($user['province_name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Kota/Kabupaten</label>
                        <input type="text" name="city_name" value="<?= htmlspecialchars($user['city_name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Kode Pos</label>
                        <input type="text" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Alamat Lengkap</label>
                        <textarea name="full_address" rows="3"><?= htmlspecialchars($user['full_address'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Simpan Perubahan</button>
                </form>

                <?php if ($isSelf): ?>
                    <div class="password-section">
                        <h3>Ubah Password</h3>
                        <form action="proses_akun.php" method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

                            <div class="form-group">
                                <label>Password Lama</label>
                                <input type="password" name="old_password" required>
                            </div>

                            <div class="form-group">
                                <label>Password Baru</label>
                                <input type="password" name="new_password" required>
                                <small style="display: block; margin-top: 5px; color: #666;">Minimal 6 karakter</small>
                            </div>

                            <div class="form-group">
                                <label>Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" required>
                            </div>

                            <button type="submit" class="submit-btn">Ubah Password</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </section>

    <script src="../../assets/js/script.js"></script>
</body>

</html>