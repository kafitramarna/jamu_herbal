<footer class="bg-white text-dark pt-4 border-top border-primary-subtle shadow-sm">
    <div class="container">
        <div class="row justify-content-between align-items-start">
            <!-- Kolom 1: Logo dan Deskripsi -->
            <div class="col-md-6 mb-4">
                <div class="d-flex align-items-center">
                    <img src="<?php
                                if (strpos($_SERVER['PHP_SELF'], '/login/') !== false || strpos($_SERVER['PHP_SELF'], '/user/') !== false) {
                                    echo '../assets/img/logo-nav.png';
                                } else {
                                    echo 'assets/img/logo-nav.png';
                                }
                                ?>" width="45" class="me-2">
                    <h5 class="mb-0">
                        <span class="fw-bold text-primary">Herbal</span>
                        <span class="fw-bold" style="color: #0F55B2;">Nusantara</span>
                    </h5>
                </div>
                <p class="text-muted mt-2 small">Mitra kesehatan herbal terpercaya Anda. Menghadirkan solusi alami berkualitas tinggi untuk gaya hidup sehat.</p>
            </div>

            <!-- Kolom 2: Media Sosial -->
            <div class="col-md-6 mb-4 text-md-end">
                <h6 class="text-uppercase fw-semibold mb-3">Ikuti Kami</h6>
                <div>
                    <a href="#" class="btn btn-outline-primary btn-sm rounded-circle me-1"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-primary btn-sm rounded-circle me-1"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="btn btn-outline-primary btn-sm rounded-circle me-1"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="btn btn-outline-primary btn-sm rounded-circle me-1"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-outline-primary btn-sm rounded-circle"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-primary text-white text-center py-2 mt-2">
        <p class="mb-0 small">&copy; 2025 Herbal Nusantara – All Rights Reserved</p>
    </div>
</footer>