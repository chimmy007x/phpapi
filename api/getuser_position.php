<?php
header('Content-Type: application/json');

// เปิดการแสดงผลข้อผิดพลาดสำหรับการดีบัก (สามารถปิดได้ในโปรดักชัน)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ข้อมูลเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

// สร้างการเชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// SQL query เพื่อดึงข้อมูลตำแหน่งจากตาราง position
$sql = "SELECT position_id, ST_AsText(location) AS location FROM position";
$result = $conn->query($sql);

// ตรวจสอบว่ามีข้อมูลหรือไม่
if ($result->num_rows > 0) {
    $positions = [];

    // ลูปผ่านข้อมูลและจัดเก็บในอาเรย์
    while ($row = $result->fetch_assoc()) {
        $positions[] = [
            'position_id' => $row['position_id'],
            'location' => $row['location'],
        ];
    }

    // ส่งผลลัพธ์ในรูปแบบ JSON
    echo json_encode(['success' => true, 'positions' => $positions]);
} else {
    // กรณีไม่มีข้อมูล
    echo json_encode(['success' => false, 'message' => 'No positions found']);
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
