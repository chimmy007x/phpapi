<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$input = json_decode(file_get_contents('php://input'), true);
$position_id = $input['position_id'] ?? '';
$nontri_id = $input['nontri_id'] ?? '';  // รับค่าจาก nontri_id
$latitude = $input['latitude'] ?? 0;
$longitude = $input['longitude'] ?? 0;

if (empty($position_id) || empty($nontri_id) || $latitude == 0 || $longitude == 0) {
    echo json_encode(['success' => false, 'message' => 'Position ID, Nontri ID, Latitude, and Longitude are required']);
    exit;
}

// ตรวจสอบว่า nontri_id มีอยู่ในตาราง students_personnel
$checkNontriSql = "SELECT COUNT(*) FROM students_personnel WHERE nontri_id = ?";
$checkStmt = $conn->prepare($checkNontriSql);
$checkStmt->bind_param("s", $nontri_id);
$checkStmt->execute();
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Nontri ID']);
    exit;
}

$location = "POINT($longitude $latitude)";

$sql = "INSERT INTO position (position_id, nontri_id, location) VALUES (?, ?, ST_GeomFromText(?))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $position_id, $nontri_id, $location);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Position saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save position: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
