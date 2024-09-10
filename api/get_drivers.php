<?php
header('Content-Type: application/json');

// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// การตั้งค่าฐานข้อมูล

$servername = "bg9pkbtnzixeo5bxltsz-mysql.services.clever-cloud.com"; // Host จาก Clever Cloud
$username = "uekuyjck8be0fvyl"; // User จาก Clever Cloud
$password = "NylI2V6zJJrezJ2c71pd"; // ใส่รหัสผ่านจาก Clever Cloud ที่แสดงในช่อง Password
$dbname = "bg9pkbtnzixeo5bxltsz"; // Database Name จาก Clever Cloud

// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "talaicsc";

// $servername = "sql200.infinityfree.com";  // MySQL Host Name ที่คุณได้จาก InfinityFree
// $username = "if0_37282459";             // MySQL User Name ที่คุณได้จาก InfinityFree
// $password = "8Dbp17sNMq1F";      // รหัสผ่าน vPanel ที่คุณใช้บน InfinityFree
// $dbname = "if0_37282459_db_project";

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname, 3306);


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
