<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}

require "../../php/config.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "ID banner tidak valid.";
    exit;
}

$sql = "SELECT * FROM content_images WHERE id = ? AND type = 'banner'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$banner = mysqli_fetch_assoc($result);
if (!$banner) {
    echo "Banner tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Banner - Herbal Nusantara</title>
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
            <input type="checkbox" id="switch-mode" hidden />
            <label for="switch-mode" class="switch-mode"></label>
        </nav>

        <main>
            <div class="head-title">
                <a href="table_banner.php" class="back-btn"><i class='bx bx-arrow-back'></i> Kembali</a>
                <h1 class="judul">Edit Banner</h1>
            </div>

            <div class="form-container">
                <form action="proses_banner.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $banner['id'] ?>">
                    <input type="hidden" name="type" value="banner">

                    <div class="form-group">
                        <label>Gambar Lama</label>
                        <?php if ($banner['image_path']) : ?>
                            <div><img src="../../uploads/content_images/<?= htmlspecialchars($banner['image_path']) ?>" alt="Banner Lama" class="preview-img" style="max-height: 150px;"></div>
                        <?php else : ?>
                            <p><em>Tidak ada gambar lama</em></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Ganti Gambar Baru</label>
                        <label class="custom-file-upload">
                            <input type="file" name="banner_image" id="banner_image" required>
                            Klik di sini untuk unggah gambar baru
                        </label>
                        <span id="file-name-preview" style="display:block; margin-top: 10px; color:#444;"></span>
                    </div>

                    <button type="submit" class="submit-btn">Simpan Perubahan</button>
                </form>
            </div>
        </main>
    </section>

    <script>
        const inputFile = document.getElementById('banner_image');
        const fileNamePreview = document.getElementById('file-name-preview');

        inputFile.addEventListener('change', function () {
            if (inputFile.files.length > 0) {
                fileNamePreview.textContent = "Gambar terpilih: " + inputFile.files[0].name;
            } else {
                fileNamePreview.textContent = "";
            }
        });
    </script>

    <script src="../../assets/js/script.js"></script>
</body>

</html>
