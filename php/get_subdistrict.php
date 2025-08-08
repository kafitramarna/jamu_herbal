<?php
// API Key Komerce
$key = "bMzQKOhycb0fd2992129021fIdsXns6S";

// Ambil district_id dari query parameter
$district_id = $_GET['district'] ?? null;

if (!$district_id) {
    echo json_encode(['error' => 'district_id tidak ditemukan']);
    exit;
}

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://rajaongkir.komerce.id/api/v1/destination/sub-district/$district_id",
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
