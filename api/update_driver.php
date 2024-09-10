<?php
header('Content-Type: application/json');

// เชื่อมต่อกับฐานข้อมูล
$servername = "localhost";
$username = "root"; // ชื่อผู้ใช้ฐานข้อมูล
$password = ""; // รหัสผ่านของฐานข้อมูล
$dbname = "talaicsc"; // ชื่อฐานข้อมูล

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบว่าการเชื่อมต่อสำเร็จหรือไม่
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// รับข้อมูล JSON ที่ส่งมาจาก Flutter
$input = json_decode(file_get_contents('php://input'), true);

// ตรวจสอบว่ามีการส่ง number_id มาหรือไม่
if (!isset($input['number_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required field: number_id']);
    exit;
}

$number_id = $input['number_id'];

// เตรียมคำสั่ง SQL สำหรับการอัปเดตเฉพาะฟิลด์ที่ส่งมา
$fields = [];
$params = [];
$types = '';

// ตรวจสอบว่าแต่ละฟิลด์ถูกส่งมาหรือไม่ และเพิ่มเข้าไปในคำสั่ง SQL
if (isset($input['password'])) {
    $fields[] = 'password = ?';
    $params[] = $input['password'];
    $types .= 's';
}

if (isset($input['fname'])) {
    $fields[] = 'fname = ?';
    $params[] = $input['fname'];
    $types .= 's';
}

if (isset($input['lname'])) {
    $fields[] = 'lname = ?';
    $params[] = $input['lname'];
    $types .= 's';
}

if (isset($input['photo'])) {
    $fields[] = 'photo = ?';
    $params[] = base64_decode($input['photo']); // แปลง base64 ให้เป็น binary
    $types .= 's';
}

// ตรวจสอบว่ามีฟิลด์ที่ต้องการอัปเดตหรือไม่
if (count($fields) > 0) {
    // เพิ่มเงื่อนไขในการเลือกหมายเลขบัตรประชาชน
    $params[] = $number_id;
    $types .= 's';
    
    $sql = "UPDATE driver SET " . implode(', ', $fields) . " WHERE number_id = ?";
    $stmt = $conn->prepare($sql);

    // ผูกค่ากับตัวแปรใน SQL
    $stmt->bind_param($types, ...$params);

    // ดำเนินการอัปเดตข้อมูลในฐานข้อมูล
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Driver information updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update driver information: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No fields to update']);
}

$conn->close();
?>
