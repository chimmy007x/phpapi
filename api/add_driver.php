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
