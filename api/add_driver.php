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

if (!isset($input['number_id'], $input['password'], $input['fname'], $input['lname'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$number_id = $input['number_id'];
$password = $input['password'];
$fname = $input['fname'];
$lname = $input['lname'];
$photo = $input['photo'] ?? null;

// ค่า `bus_id` และ `Position` กำหนดเป็น NULL
$bus_id = null;
$position = null;

$sql = "INSERT INTO driver (number_id, password, fname, lname, photo, bus_id, Position) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $number_id, $password, $fname, $lname, $photo, $bus_id, $position);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Driver added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add driver']);
}

$stmt->close();
$conn->close();
?>
