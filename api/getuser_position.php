<?php
header('Content-Type: application/json');

// เปิดการแสดงผลข้อผิดพลาดสำหรับการดีบัก (สามารถปิดได้ในโปรดักชัน)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "bg9pkbtnzixeo5bxltsz-mysql.services.clever-cloud.com"; // Host จาก Clever Cloud
$username = "uekuyjck8be0fvyl"; // User จาก Clever Cloud
$password = "NylI2V6zJJrezJ2c71pd"; // ใส่รหัสผ่านจาก Clever Cloud ที่แสดงในช่อง Password
$dbname = "bg9pkbtnzixeo5bxltsz"; // Database Name จาก Clever Cloud

// ข้อมูลเชื่อมต่อฐานข้อมูล
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "talaicsc";

// $servername = "sql200.infinityfree.com";  // MySQL Host Name ที่คุณได้จาก InfinityFree
// $username = "if0_37282459";             // MySQL User Name ที่คุณได้จาก InfinityFree
// $password = "8Dbp17sNMq1F";      // รหัสผ่าน vPanel ที่คุณใช้บน InfinityFree
// $dbname = "if0_37282459_db_project";

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
