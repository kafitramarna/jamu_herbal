<?php
// API Key Komerce
$key = "bMzQKOhycb0fd2992129021fIdsXns6S";

// Ambil city_id dari query parameter
$city_id = $_GET['city'] ?? null;

if (!$city_id) {
    echo json_encode(['error' => 'city_id tidak ditemukan']);
    exit;
}

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://rajaongkir.komerce.id/api/v1/destination/district/$city_id",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        "Accept: application/json",
        "key: $key"
    ),
));

$response = curl_exec($curl);
curl_close($curl);

// Ambil bagian 'data' saja biar sama kayak Laravel
$data = json_decode($response, true);
echo json_encode($data['data'] ?? []);
