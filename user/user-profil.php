<?php
session_start();
require "../php/config.php";

// Cek apakah admin login → tendang ke dashboard admin
if (isset($_SESSION['admin'])) {
    header("Location: ../admin/index.php");
    exit;
}

// Cek apakah user belum login → tendang ke login
if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    header("Location: ../login/login.php");
    exit;
}

$user_id = $_SESSION['user']['id']; // Ambil ID user dari session

// Ambil data user dari database
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (isset($_POST['update'])) {
        $nama = $conn->real_escape_string($_POST['nama']);
        $email = $conn->real_escape_string($_POST['email']);
        $no_hp = $conn->real_escape_string($_POST['no_hp']);
        $province_id = $conn->real_escape_string($_POST['province_id']);
        $province_name = $conn->real_escape_string($_POST['province_name']);
        $city_id = $conn->real_escape_string($_POST['city_id']);
        $city_name = $conn->real_escape_string($_POST['city_name']);
        $district_id = $conn->real_escape_string($_POST['district_id']);
        $district_name = $conn->real_escape_string($_POST['district_name']);
        $postal_code = $conn->real_escape_string($_POST['postal_code']);
        $full_address = $conn->real_escape_string($_POST['full_address']);
        $password = $_POST['password'];

        // Kalau user isi password baru, update juga password-nya
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET 
            full_name='$nama', email='$email', phone='$no_hp',
            province_id='$province_id', province_name='$province_name',
            city_id='$city_id', city_name='$city_name',
            district_id='$district_id', district_name='$district_name',
            postal_code='$postal_code', full_address='$full_address',
            password='$hashed_password'
            WHERE id=$user_id";
        } else {
            $update_sql = "UPDATE users SET 
            full_name='$nama', email='$email', phone='$no_hp',
            province_id='$province_id', province_name='$province_name',
            city_id='$city_id', city_name='$city_name',
            district_id='$district_id', district_name='$district_name',
            postal_code='$postal_code', full_address='$full_address'
            WHERE id=$user_id";
        }

        if ($conn->query($update_sql) === TRUE) {
            $_SESSION['msg'] = "Profil berhasil diperbarui!";
            // Refresh data user dari DB
            $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
            $user = $result->fetch_assoc();
        } else {
            $_SESSION['msg'] = "Gagal memperbarui profil: " . $conn->error;
        }
    }

    // Proses hapus akun
    if (isset($_POST['delete'])) {
        $delete_sql = "DELETE FROM users WHERE id=$user_id";

        if ($conn->query($delete_sql) === TRUE) {
            session_destroy();
            header("Location: ../login/login.php");
            exit;
        } else {
            $_SESSION['msg'] = "Gagal menghapus akun: " . $conn->error;
        }
    }
} else {
    session_destroy();
    header("Location: ../login/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profil Saya - Herbal Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" href="../assets/img/logo-nav.png" />
    <link rel="stylesheet" href="../assets/css/index.css" />
    <link rel="stylesheet" href="../assets/css/prfl.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
</head>

<body>
    <?php include '../components/user-navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="profile-card p-4 shadow rounded-4">
                    <h4 class="mb-4 text-center">Profil Saya</h4>

                    <?php if (isset($_SESSION['msg'])): ?>
                        <div class="alert alert-info"><?php echo $_SESSION['msg'];
                                                        unset($_SESSION['msg']); ?></div>
                    <?php endif; ?>

                    <form method="POST" id="profileForm">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($user['full_name']) ?>" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor HP</label>
                            <input type="text" class="form-control" name="no_hp" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required />
                        </div>

                        <div class="mb-3">
                            <label>Provinsi</label>
                            <select id="province" name="province_id" class="form-select" required data-selected="<?= htmlspecialchars($user['province_id'] ?? '') ?>">
                                <option value="">-- Pilih Provinsi --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Kota / Kabupaten</label>
                            <select id="city" name="city_id" class="form-select" required data-selected="<?= htmlspecialchars($user['city_id'] ?? '') ?>">
                                <option value="">-- Pilih Kota --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Kecamatan</label>
                            <select id="district" name="district_id" class="form-select" required data-selected="<?= htmlspecialchars($user['district_id'] ?? '') ?>">
                                <option value="">-- Pilih Kecamatan --</option>
                            </select>
                        </div>

                        <input type="hidden" name="province_name" id="province_name" value="<?= htmlspecialchars($user['province_name'] ?? '') ?>" />
                        <input type="hidden" name="city_name" id="city_name" value="<?= htmlspecialchars($user['city_name'] ?? '') ?>" />
                        <input type="hidden" name="district_name" id="district_name" value="<?= htmlspecialchars($user['district_name'] ?? '') ?>" />

                        <div class="mb-3">
                            <label>Kode Pos</label>
                            <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>" required />
                        </div>

                        <div class="mb-3">
                            <label>Alamat Lengkap</label>
                            <textarea name="full_address" class="form-control" required><?= htmlspecialchars($user['full_address'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password Baru (kosongkan jika tidak ingin ganti)</label>
                            <input type="password" class="form-control" name="password" placeholder="••••••••" />
                        </div>

                        <button type="submit" name="update" class="btn btn-primary w-100">Simpan Perubahan</button>
                    </form>

                    <form method="POST" class="mt-3">
                        <button type="submit" name="delete" class="btn btn-danger w-100" onclick="return confirm('Yakin ingin hapus akun ini?')">Hapus Akun</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <a href="https://wa.me/628970014820" class="btn btn-success position-fixed bottom-0 end-0 m-3" target="_blank">
        <i class="fab fa-whatsapp"></i> Chat
    </a>

    <?php include '../components/footer.php'; ?>
    <script src="../assets/js/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const provinceSelect = document.getElementById('province');
            const citySelect = document.getElementById('city');
            const districtSelect = document.getElementById('district');

            function loadProvinces(selectedProvinceId = null) {
                fetch('../php/api_ongkir.php')
                    .then(res => res.json())
                    .then(data => {
                        console.log("Data Provinsi:", data); // debug
                        const provinces = data || [];
                        provinceSelect.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
                        provinces.forEach(prov => {
                            const option = document.createElement('option');
                            option.value = prov.id; // 🔥 pakai id
                            option.textContent = prov.name; // 🔥 pakai name
                            if (selectedProvinceId && selectedProvinceId == prov.id) {
                                option.selected = true;
                            }
                            provinceSelect.appendChild(option);
                        });
                    })
                    .catch(err => console.error("Gagal load provinsi:", err));
            }

            function loadCities(provinceId, selectedCityId = null) {
                if (!provinceId) {
                    citySelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
                    return;
                }
                fetch(`../php/get_city.php?province=${provinceId}`)
                    .then(res => res.json())
                    .then(data => {
                        console.log("Data Kota:", data); // debug
                        const cities = data || [];
                        citySelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
                        cities.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.id; // ✅ pakai id
                            option.textContent = city.name; // ✅ pakai name
                            if (selectedCityId && selectedCityId == city.id) {
                                option.selected = true;
                            }
                            citySelect.appendChild(option);
                        });
                    })
                    .catch(err => console.error("Gagal load kota:", err));
            }

            function loadDistricts(cityId, selectedDistrictId = null) {
                if (!cityId) {
                    districtSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                    return;
                }
                fetch(`../php/get_district.php?city=${cityId}`)
                    .then(res => res.json())
                    .then(data => {
                        console.log("Data Kecamatan:", data);
                        const districts = data || [];
                        districtSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                        districts.forEach(d => {
                            const option = document.createElement('option');
                            option.value = d.id; // ✅ pakai id
                            option.textContent = d.name; // ✅ pakai name
                            if (selectedDistrictId && selectedDistrictId == d.id) {
                                option.selected = true;
                            }
                            districtSelect.appendChild(option);
                        });
                    })
                    .catch(err => console.error("Gagal load kecamatan:", err));
            }

            const selectedProvince = provinceSelect.dataset.selected;
            const selectedCity = citySelect.dataset.selected;
            const selectedDistrict = districtSelect.dataset.selected;

            loadProvinces(selectedProvince);

            if (selectedProvince) {
                loadCities(selectedProvince, selectedCity);
            }
            if (selectedCity) {
                loadDistricts(selectedCity, selectedDistrict);
            }

            provinceSelect.addEventListener('change', () => {
                loadCities(provinceSelect.value);
                const selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
                document.getElementById('province_name').value = selectedOption.textContent || '';
            });

            citySelect.addEventListener('change', () => {
                loadDistricts(citySelect.value);
                const selectedOption = citySelect.options[citySelect.selectedIndex];
                document.getElementById('city_name').value = selectedOption.textContent || '';
            });

            districtSelect.addEventListener('change', () => {
                const selectedOption = districtSelect.options[districtSelect.selectedIndex];
                document.getElementById('district_name').value = selectedOption.textContent || '';
            });
        });
    </script>
</body>

</html>