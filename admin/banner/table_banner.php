<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require "../../php/config.php";

// Ambil semua banner dari tabel content_images
$sql = "SELECT * FROM content_images ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$banners = [];
$hasAbout = false;
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['type'] === 'about') {
        $hasAbout = true;
    }
    $banners[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kelola Banner - Herbal Nusantara</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo-nav.png" />
    <link href="https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet" />
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
                    <input type="search" placeholder="Cari banner..." />
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
                    <h1>Kelola Banner</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Konten</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Banner</a></li>
                    </ul>
                </div>
                <a href="tambah_banner.php" class="btn-download" style="margin-left: auto;">
                    <span class="text">Tambah Banner</span>
                </a>
            </div>

            <div class="note" style="margin-top: 10px; margin-bottom: 20px; padding: 10px; background: #fff3cd; color: #856404; border: 1px solid #ffeeba; border-radius: 5px;">
                <strong>Catatan:</strong> Banner bertipe <strong>about</strong> hanya bisa <strong>diubah/edit</strong> dan <strong>tidak bisa ditambahkan atau dihapus</strong>.
            </div>

            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Tipe</th>
                            <th>Waktu Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($banners) === 0): ?>
                            <tr>
                                <td colspan="5" class="no-product-row">Belum ada banner yang diupload.</td>
                            </tr>
                            <?php else:
                            $no = 1;
                            foreach ($banners as $banner):
                                $imagePath = "../../uploads/content_images/" . htmlspecialchars($banner['image_path']);
                                if (!file_exists($imagePath) || empty($banner['image_path'])) {
                                    $imagePath = "../../assets/img/no-image.png";
                                }
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><img src="<?= $imagePath ?>" alt="Banner" class="product-img"></td>
                                    <td><?= htmlspecialchars($banner['type']) ?></td>
                                    <td><?= htmlspecialchars(date('d-m-Y H:i', strtotime($banner['created_at']))) ?></td>
                                    <td class="action-column">
                                        <a href="edit_banner.php?id=<?= $banner['id'] ?>" class="btn-edit">Edit</a>
                                        <?php if ($banner['type'] !== 'about'): ?>
                                            <a href="proses_banner.php?action=hapus&id=<?= $banner['id'] ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus banner ini?');">Hapus</a>
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