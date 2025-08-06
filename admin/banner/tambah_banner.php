<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tambah Banner - Herbal Nusantara</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo-nav.png" />
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="stylesheet" href="../../assets/css/tambah_produk.css" />
    <link href="https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>
    <?php include '../../components/sidebar.php'; ?>

    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <form action="#">
                <div class="form-input"></div>
            </form>
            <input type="checkbox" id="switch-mode" hidden />
            <label for="switch-mode" class="switch-mode"></label>
        </nav>

        <main>
            <div class="head-title">
                <a href="table_banner.php" class="back-btn"><i class='bx bx-arrow-back'></i> Kembali</a>
                <h1 class="judul">Tambah Banner</h1>
            </div>

            <div class="form-container">
                <form action="proses_banner.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="tambah">
                    <input type="hidden" name="type" value="banner">

                    <div class="form-group">
                        <label>Gambar Banner</label>
                        <label class="custom-file-upload">
                            <input type="file" name="banner_image" id="banner_image" required>
                            Klik di sini untuk unggah gambar banner
                        </label>
                        <span id="file-name-preview" style="display:block; margin-top: 10px; color:#444;"></span>
                    </div>

                    <button type="submit" class="submit-btn">Simpan Banner</button>
                </form>
            </div>
        </main>
    </section>

    <script src="../../assets/js/script.js"></script>
    <script>
        const inputFile = document.getElementById('banner_image');
        const fileNamePreview = document.getElementById('file-name-preview');

        inputFile.addEventListener('change', function() {
            if (inputFile.files.length > 0) {
                fileNamePreview.textContent = "Gambar terpilih: " + inputFile.files[0].name;
            } else {
                fileNamePreview.textContent = "";
            }
        });
    </script>
</body>

</html>