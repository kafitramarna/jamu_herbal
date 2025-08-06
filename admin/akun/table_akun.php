<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require "../../php/config.php";

// Ambil semua data user
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Kelola Akun - Herbal Nusantara</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo-nav.png" />
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="stylesheet" href="../../assets/css/produk.css" />
</head>

<body>
    <?php include '../../components/sidebar.php'; ?>

    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Cari akun..." />
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
                <div class="left">
                    <h1>Kelola Akun</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Pengguna</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Akun</a></li>
                    </ul>
                </div>
            </div>

            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>No. HP</th>
                            <th>Alamat</th>
                            <th>Waktu Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) === 0): ?>
                            <tr>
                                <td colspan="8" class="no-product-row">Belum ada akun pengguna.</td>
                            </tr>
                            <?php else:
                            $no = 1;
                            $currentAdminId = $_SESSION['user']['id'];

                            foreach ($users as $user):
                                $isSelf = $currentAdminId == $user['id'];
                                $isAdmin = $user['role'] === 'admin';

                                $phone = !empty($user['phone']) ? htmlspecialchars($user['phone']) : '-';

                                // Alamat lengkap bisa null
                                $fullAlamat = trim("{$user['full_address']} {$user['city_name']} {$user['province_name']} {$user['postal_code']}");
                                $alamatArray = explode(' ', $fullAlamat);
                                $alamatPendek = implode(' ', array_slice($alamatArray, 0, 10));
                                if (count($alamatArray) > 10) {
                                    $alamatPendek .= '...';
                                }
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['role']) ?></td>
                                    <td><?= $phone ?></td>
                                    <td><?= htmlspecialchars($alamatPendek) ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td class="action-column">
                                        <?php if (!$isAdmin): ?>
                                            <a href="edit_akun.php?id=<?= $user['id'] ?>" class="btn-edit">Edit</a>
                                            <a href="proses_user.php?action=hapus&id=<?= $user['id'] ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus akun ini?');">Hapus</a>
                                        <?php elseif ($isAdmin && !$isSelf): ?>
                                            <a href="edit_akun.php?id=<?= $user['id'] ?>" class="btn-edit">Edit</a>
                                            <span style="color: gray; font-size: 0.9em;">Tidak bisa hapus admin</span>
                                        <?php else: ?>
                                            <span style="color: gray; font-size: 0.9em;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </section>

    <script src="../../assets/js/script.js"></script>
</body>

</html>