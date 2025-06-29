<?php
// Hardcoded Google Weather API URL (used only for testing)
$lat = $_GET['lat'] ?? null;
$lng = $_GET['lng'] ?? null;
$date = $_GET['date'] ?? null;

$url = 'https://weather.googleapis.com/v1/forecast/hours:lookup?key=AIzaSyAuy24KIJyJtG01xMGEFhwMJiRadDjFxeM&location.latitude=' . $lat . '&location.longitude=' . $lng . '&units_system=IMPERIAL&hours=240&pageSize=240';

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

