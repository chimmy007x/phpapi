<?php
header('Content-Type: application/json');

// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// การตั้งค่าฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// สร้างคำสั่ง SQL สำหรับดึงข้อมูลจากตาราง driver
$sql = "SELECT number_id, fname, lname, photo, bus_id FROM driver";
$result = $conn->query($sql);

$drivers = [];

if ($result->num_rows > 0) {
    // เก็บผลลัพธ์จากการ query ลงใน array
    while($row = $result->fetch_assoc()) {
        $drivers[] = [
            'number_id' => $row['number_id'],
            'fname' => $row['fname'],
            'lname' => $row['lname'],
            'photo' => base64_encode($row['photo']),  // แปลงรูปภาพจาก BLOB เป็น base64
            'bus_id' => $row['bus_id']
        ];
    }
    echo json_encode(['success' => true, 'drivers' => $drivers]);
} else {
    echo json_encode(['success' => false, 'message' => 'No drivers found']);
}

// ปิดการเชื่อมต่อ
$conn->close();
?>
