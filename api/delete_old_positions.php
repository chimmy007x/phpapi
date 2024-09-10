<?php
header('Content-Type: application/json');

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// ลบข้อมูลที่เก่ากว่า 10 นาทีจากเวลาปัจจุบัน
$sql = "DELETE FROM position WHERE date_time < (NOW() - INTERVAL 10 MINUTE)";
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Old positions deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting positions: ' . $conn->error]);
}

$conn->close();
?>
