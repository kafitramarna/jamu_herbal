<?php
header("Content-Type: application/json");

// Ambil data POST
$origin = $_POST['origin'] ?? '';
$destination = $_POST['destination'] ?? '';
$weight = (int)($_POST['weight'] ?? 1000);
$courier = $_POST['courier'] ?? '';

// Simulasi biaya ongkir berdasarkan kurir (dummy)
$biaya = 0;
if ($courier == 'jne') {
    $biaya = 12000 + ($weight / 1000 * 2000);
} elseif ($courier == 'jnt') {
    $biaya = 15000 + ($weight / 1000 * 2500);
} elseif ($courier == 'pos') {
    $biaya = 10000 + ($weight / 1000 * 1500);
}

// Dummy response seperti format asli
$response = [
    "status" => true,
    "message" => "Dummy ongkir sukses",
    "data" => [
        [
            "service" => strtoupper($courier) . " Regular",
            "cost" => $biaya,
            "etd" => "2-3"
        ],
        [
            "service" => strtoupper($courier) . " Express",
            "cost" => $biaya + 5000,
            "etd" => "1-2"
        ]
    ]
];

echo json_encode($response);
exit;
