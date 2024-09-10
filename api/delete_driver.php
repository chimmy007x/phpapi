<?php
header('Content-Type: application/json');

$servername = "bg9pkbtnzixeo5bxltsz-mysql.services.clever-cloud.com"; // Host จาก Clever Cloud
$username = "uekuyjck8be0fvyl"; // User จาก Clever Cloud
$password = "NylI2V6zJJrezJ2c71pd"; // ใส่รหัสผ่านจาก Clever Cloud ที่แสดงในช่อง Password
$dbname = "bg9pkbtnzixeo5bxltsz"; // Database Name จาก Clever Cloud

// เชื่อมต่อฐานข้อมูล
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "talaicsc";

// $servername = "sql200.infinityfree.com";  // MySQL Host Name ที่คุณได้จาก InfinityFree
// $username = "if0_37282459";             // MySQL User Name ที่คุณได้จาก InfinityFree
// $password = "8Dbp17sNMq1F";      // รหัสผ่าน vPanel ที่คุณใช้บน InfinityFree
// $dbname = "if0_37282459_db_project";

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
