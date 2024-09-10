<?php
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// รับข้อมูลจาก request
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['number_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing number_id']);
    exit;
}

$number_id = $input['number_id'];

$sql = "DELETE FROM driver WHERE number_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $number_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Driver deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete driver']);
}

$stmt->close();
$conn->close();
?>
