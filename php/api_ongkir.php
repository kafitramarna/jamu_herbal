<?php
header('Content-Type: application/json');

$key = "nka4M4Xm12a7ea8339e3dd9dJEDHbcJx"; 

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://rajaongkir.komerce.id/api/v1/destination/province",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "key: $key"
    ],
]);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);

// Ambil hanya 'data' biar sama kayak Laravel
echo json_encode($data['data'] ?? []);
