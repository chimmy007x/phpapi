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
$driver_id = $input['driver_id'] ?? '';

if (empty($driver_id)) {
    echo json_encode(['success' => false, 'message' => 'Driver ID is required']);
    exit;
}

$sql = "UPDATE driver SET bus_id = NULL, Position = NULL WHERE number_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $driver_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Bus ID and Position cleared successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to clear Bus ID and Position']);
}

$stmt->close();
$conn->close();
?>
