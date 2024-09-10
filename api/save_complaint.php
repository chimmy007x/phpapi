<?php
header('Content-Type: application/json');

// เปิดการแสดงข้อผิดพลาดทั้งหมด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// เขียน log ลงไฟล์ error_log
error_log("PHP script started");

$servername = "bg9pkbtnzixeo5bxltsz-mysql.services.clever-cloud.com"; // Host จาก Clever Cloud
$username = "uekuyjck8be0fvyl"; // User จาก Clever Cloud
$password = "NylI2V6zJJrezJ2c71pd"; // ใส่รหัสผ่านจาก Clever Cloud ที่แสดงในช่อง Password
$dbname = "bg9pkbtnzixeo5bxltsz"; // Database Name จาก Clever Cloud

// การตั้งค่าฐานข้อมูล
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "talaicsc";

// $servername = "sql200.infinityfree.com";  // MySQL Host Name ที่คุณได้จาก InfinityFree
// $username = "if0_37282459";             // MySQL User Name ที่คุณได้จาก InfinityFree
// $password = "8Dbp17sNMq1F";      // รหัสผ่าน vPanel ที่คุณใช้บน InfinityFree
// $dbname = "if0_37282459_db_project";

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
} else {
    error_log("Database connection successful");
}

// รับข้อมูล JSON จากคำขอ
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    error_log("Invalid input data");
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

$description = $input['description'] ?? '';
$nontri_id = $input['nontri_id'] ?? '';

// ตรวจสอบว่ามีการส่งข้อมูลที่จำเป็นครบถ้วนหรือไม่
if (empty($description) || empty($nontri_id)) {
    error_log("Description or Nontri ID is empty");
    echo json_encode(['success' => false, 'message' => 'Description and Nontri ID are required']);
    exit;
}

// สร้างรหัสการร้องเรียนใหม่ (complaint_id)
$complaint_id = 'CMP_' . bin2hex(random_bytes(8));
$date_time = date('Y-m-d H:i:s');

// เตรียมคำสั่ง SQL สำหรับการบันทึกข้อมูลการร้องเรียน
$sql = "INSERT INTO complaint (complaint_id, complaints_data, date_time, nontri_id) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare statement failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssss", $complaint_id, $description, $date_time, $nontri_id);

// ตรวจสอบการประมวลผลคำสั่ง SQL
if ($stmt->execute()) {
    error_log("Complaint submitted successfully: ID - $complaint_id, Description - $description, Nontri ID - $nontri_id");
    echo json_encode(['success' => true, 'message' => 'Complaint submitted successfully']);
} else {
    error_log("Failed to submit complaint: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Failed to submit complaint: ' . $stmt->error]);
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>
