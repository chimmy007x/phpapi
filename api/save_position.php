<?php
header('Content-Type: application/json');

// แสดง error สำหรับการ debug (ปิดเมื่อพร้อมใช้งานจริง)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// รับข้อมูลจาก request
$input = json_decode(file_get_contents('php://input'), true);
$latitude = $input['latitude'] ?? 0;
$longitude = $input['longitude'] ?? '';
$nontri_id = $input['nontri_id'] ?? '';

if ($latitude == 0 || $longitude == 0 || empty($nontri_id)) {
    echo json_encode(['success' => false, 'message' => 'Latitude, Longitude, and Nontri ID are required']);
    exit;
}

// ตรวจสอบตำแหน่งที่บันทึกอยู่แล้วในระยะ 10 เมตร
$location = "POINT($longitude $latitude)";
$sql_check_position = "
    SELECT position_id, ST_Distance_Sphere(location, ST_GeomFromText(?)) AS distance
    FROM position
    WHERE ST_Distance_Sphere(location, ST_GeomFromText(?)) < 10
    ORDER BY date_time DESC
    LIMIT 1";
$stmt_check_position = $conn->prepare($sql_check_position);
$stmt_check_position->bind_param("ss", $location, $location);
$stmt_check_position->execute();
$result_check_position = $stmt_check_position->get_result();

if ($result_check_position->num_rows > 0) {
    // ถ้ามีตำแหน่งอยู่ในระยะ 10 เมตร
    echo json_encode(['success' => false, 'message' => 'ตำแหน่งนี้ถูกบันทึกไปแล้วหรือในระยะใกล้เคียง']);
    exit;
}

// ตรวจสอบเวลาของการเรียกรถล่าสุดจากตาราง position สำหรับ nontri_id ที่ระบุ
$sql_last_request = "SELECT date_time FROM position WHERE nontri_id = ? ORDER BY date_time DESC LIMIT 1";
$stmt_last_request = $conn->prepare($sql_last_request);
$stmt_last_request->bind_param("s", $nontri_id); // ตรวจสอบเฉพาะ nontri_id ที่ระบุ
$stmt_last_request->execute();
$result_last_request = $stmt_last_request->get_result();

if ($result_last_request->num_rows > 0) {
    $row = $result_last_request->fetch_assoc();
    $last_request_time = new DateTime($row['date_time']);
    $current_time = new DateTime();
    $interval = $last_request_time->diff($current_time);

    // ตรวจสอบว่าเรียกใช้ซ้ำภายใน 10 นาทีหรือไม่
    if ($interval->i < 10 && $interval->h == 0) {
        echo json_encode(['success' => false, 'message' => 'You can only request a ride every 10 minutes.']);
        exit;
    }
}

// สร้าง position_id แบบไม่ซ้ำกัน
$position_id = uniqid('pos_', true);  // สร้าง position_id โดยใช้ uniqid()

// บันทึกตำแหน่งในตาราง position
$sql = "INSERT INTO position (position_id, nontri_id, date_time, location) VALUES (?, ?, NOW(), ST_GeomFromText(?))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $position_id, $nontri_id, $location);

if ($stmt->execute()) {
    // ถ้าบันทึกตำแหน่งสำเร็จ ให้บันทึกการเรียกรถลงในตาราง request ด้วย
    $quantity_request = 1;  // จำนวนการกดเรียก
    $sql_request = "INSERT INTO request (ouantity_request, nontri_id, date_time) VALUES (?, ?, NOW())";
    $stmt_request = $conn->prepare($sql_request);
    $stmt_request->bind_param("is", $quantity_request, $nontri_id);
    
    if ($stmt_request->execute()) {
        echo json_encode(['success' => true, 'message' => 'Position and request saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save request: ' . $stmt_request->error]);
    }
    
    $stmt_request->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save position: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
