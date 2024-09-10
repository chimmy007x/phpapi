<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$input = json_decode(file_get_contents('php://input'), true);
$number_id = $input['number_id'] ?? '';
$position = $input['position'] ?? [];

if (empty($number_id) || empty($position)) {
    echo json_encode(['success' => false, 'message' => 'Number ID and Position are required']);
    exit;
}

// Convert latitude and longitude to a geometry point
$latitude = $position['latitude'];
$longitude = $position['longitude'];
$point = "POINT($longitude $latitude)";

$sql = "UPDATE driver SET Position = ST_GeomFromText(?) WHERE number_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $point, $number_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Position updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update position']);
}

$stmt->close();
$conn->close();
?>
