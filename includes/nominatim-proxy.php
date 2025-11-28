<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$query = $_GET['q'] ?? '';
$limit = $_GET['limit'] ?? 5;

if (strlen($query) < 3) {
    echo json_encode([]);
    exit;
}

$url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($query) . "&limit=" . intval($limit);

// Obligatoire : User-Agent personnalisé
$options = [
    "http" => [
        "header" => "User-Agent: StudyGoApp/1.0 (contact@studygo.fr)\r\n"
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

echo $response;
