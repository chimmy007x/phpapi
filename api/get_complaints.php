<?php
header('Content-Type: application/json');

// การเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// รับพารามิเตอร์ start_date และ end_date จาก URL
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// ตรวจสอบรูปแบบวันที่และใช้ DATE() เพื่อดึงข้อมูลเฉพาะวันที่ (ไม่รวมเวลา)
if ($startDate && $endDate) {
    $sql = "SELECT * FROM complaint WHERE DATE(date_time) BETWEEN '$startDate' AND '$endDate' ORDER BY date_time DESC";
} else {
    $sql = "SELECT * FROM complaint ORDER BY date_time DESC";
}

$result = $conn->query($sql);

$complaints = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $complaints[] = $row;
    }
    echo json_encode(['success' => true, 'complaints' => $complaints]);
} else {
    echo json_encode(['success' => false, 'message' => 'No complaints found']);
}

$conn->close();
?>
