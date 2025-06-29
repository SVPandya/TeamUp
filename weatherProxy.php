<?php
// Hardcoded Google Weather API URL (used only for testing)
$lat = $_GET['lat'] ?? null;
$lng = $_GET['lng'] ?? null;
$date = $_GET['date'] ?? null;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['GOOGLE_API_KEY'];

$url = 'https://weather.googleapis.com/v1/forecast/days:lookup?key=' . $apiKey .'&location.latitude=' . $lat . '&location.longitude=' . $lng . '&units_system=IMPERIAL';

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute and fetch the response
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Set response type
header('Content-Type: application/json');

// Output for debugging
echo json_encode([  
    'status' => $http_code,
    'response' => json_decode($response, true)
]);
?>

