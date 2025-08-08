<?php

/**
 * Cek Ongkir API Integration
 * 
 * File ini digunakan untuk melakukan pengecekan biaya pengiriman 
 * menggunakan API Raja Ongkir untuk keperluan checkout.
 */

// Headers untuk Cross-Origin dan Content-Type
header('Content-Type: application/json');

// Ambil API key dari config file atau simpan di file ini (untuk production gunakan env var)
$apiKey = "bMzQKOhycb0fd2992129021fIdsXns6S";

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Ambil parameter dari POST request
$origin = isset($_POST['origin']) ? $_POST['origin'] : null;
$destination = isset($_POST['destination']) ? $_POST['destination'] : null;
$weight = isset($_POST['weight']) ? $_POST['weight'] : null;
$courier = isset($_POST['courier']) ? $_POST['courier'] : null;

// Validasi parameter yang diperlukan
if (!$origin || !$destination || !$weight || !$courier) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter tidak lengkap',
        'received' => [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier
        ]
    ]);
    exit;
}

// Pastikan weight minimal 1 gram
$weight = max(1, intval($weight));

// Siapkan data untuk API
$postData = http_build_query([
    'origin' => $origin,
    'destination' => $destination,
    'weight' => $weight,
    'courier' => $courier
]);

// Inisiasi cURL
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
        "key: $apiKey"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo json_encode([
        'status' => 'error',
        'message' => "cURL Error: $err"
    ]);
    exit;
}

// Decode response dari RajaOngkir
$responseData = json_decode($response, true);

// Log response untuk debug jika perlu
// file_put_contents('rajaongkir_response.log', date('Y-m-d H:i:s') . ' ' . $response . PHP_EOL, FILE_APPEND);

// Cek apakah response valid
if (!$responseData || !isset($responseData['meta']) || $responseData['meta']['status'] !== 'success') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid response from Raja Ongkir API',
        'raw_response' => $responseData
    ]);
    exit;
}

// Format data untuk kemudahan penggunaan di frontend
$formattedData = [];

if (isset($responseData['data']) && is_array($responseData['data'])) {
    foreach ($responseData['data'] as $service) {
        // Ekstrak informasi yang diperlukan untuk setiap layanan
        $formattedData[] = [
            'name' => $service['name'],
            'code' => $service['code'],
            'service' => $service['service'],
            'description' => $service['description'],
            'cost' => (int)$service['cost'],
            'etd' => $service['etd']
        ];
    }
}

// Return formatted data
echo json_encode([
    'status' => 'success',
    'message' => 'Berhasil mengambil data ongkir',
    'data' => $formattedData
]);
