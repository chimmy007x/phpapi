<?php
header('Content-Type: application/json');

// $servername = "bg9pkbtnzixeo5bxltsz-mysql.services.clever-cloud.com"; // Host จาก Clever Cloud
// $username = "uekuyjck8be0fvyl"; // User จาก Clever Cloud
// $password = "NylI2V6zJJrezJ2c71pd"; // ใส่รหัสผ่านจาก Clever Cloud ที่แสดงในช่อง Password
// $dbname = "bg9pkbtnzixeo5bxltsz"; // Database Name จาก Clever Cloud


// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

// $servername = "sql200.infinityfree.com";  // MySQL Host Name ที่คุณได้จาก InfinityFree
// $username = "if0_37282459";             // MySQL User Name ที่คุณได้จาก InfinityFree
// $password = "8Dbp17sNMq1F";      // รหัสผ่าน vPanel ที่คุณใช้บน InfinityFree
// $dbname = "if0_37282459_db_project";

// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// ตรวจสอบช่วงวันที่จาก URL
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$sql = "SELECT DATE(date_time) as request_date, 
        COUNT(*) as total_requests, 
        MIN(TIME(date_time)) as start_time, 
        MAX(TIME(date_time)) as end_time,
        COUNT(DISTINCT nontri_id) as unique_nontri_ids  -- นับจำนวน nontri_id ที่ไม่ซ้ำ
        FROM request 
        WHERE 1=1";

if ($start_date && $end_date) {
    $sql .= " AND DATE(date_time) BETWEEN '$start_date' AND '$end_date'";
}

$sql .= " GROUP BY DATE(date_time) ORDER BY request_date ASC";

$result = $conn->query($sql);

$requests = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // คำนวณระยะเวลาการใช้งาน (ในชั่วโมง) โดยการลบเวลาสิ้นสุดกับเวลาเริ่มต้น
        $start_time = new DateTime($row['start_time']);
        $end_time = new DateTime($row['end_time']);
        $interval = $start_time->diff($end_time);
        $duration = $interval->format('%h ชั่วโมง %i นาที'); // หรือคำนวณเป็นชั่วโมงหรือรูปแบบอื่น ๆ ตามที่คุณต้องการ

        $requests[] = [
            'request_date' => $row['request_date'],
            'total_requests' => (int)$row['total_requests'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'duration' => $duration,
            'unique_nontri_ids' => (int)$row['unique_nontri_ids'] // จำนวน nontri_id ที่ไม่ซ้ำ
        ];
    }
    echo json_encode(['success' => true, 'requests' => $requests]);
} else {
    echo json_encode(['success' => false, 'message' => 'No requests found']);
}

$conn->close();
?>
