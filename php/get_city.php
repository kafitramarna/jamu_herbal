<?php
// API Key Komerce
$key = "bMzQKOhycb0fd2992129021fIdsXns6S";

// Ambil province_id dari query parameter
$province_id = $_GET['province'] ?? null;

if (!$province_id) {
    echo json_encode(['error' => 'province_id tidak ditemukan']);
    exit;
}

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://rajaongkir.komerce.id/api/v1/destination/city/$province_id",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        "Accept: application/json",
        "key: $key"
    ),
));

$response = curl_exec($curl);
curl_close($curl);

// Ubah response supaya hanya kirim data array 'data' (sama kayak Laravel)
$data = json_decode($response, true);
echo json_encode($data['data'] ?? []);
