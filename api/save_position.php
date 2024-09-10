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
$position_id = $input['position_id'] ?? '';
$latitude = $input['latitude'] ?? 0;
$longitude = $input['longitude'] ?? 0;
$nontri_id = $input['nontri_id'] ?? '';  // เพิ่มตัวแปรนี้เพื่อบันทึก nontri_id

if (empty($position_id) || $latitude == 0 || $longitude == 0 || empty($nontri_id)) {
    echo json_encode(['success' => false, 'message' => 'Position ID, Latitude, Longitude, and Nontri ID are required']);
    exit;
}

// บันทึกตำแหน่งในตาราง position
$location = "POINT($longitude $latitude)";
$sql = "INSERT INTO position (position_id, location) VALUES (?, ST_GeomFromText(?))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $position_id, $location);

if ($stmt->execute()) {
    // ถ้าบันทึกตำแหน่งสำเร็จ ให้บันทึกการเรียกรถลงในตาราง request ด้วย
    $quantity_request = 1;  // จำนวนการกดเรียก
    $sql_request = "INSERT INTO request (ouantity_request, nontri_id) VALUES (?, ?)";
    $stmt_request = $conn->prepare($sql_request);
    $stmt_request->bind_param("is", $quantity_request, $nontri_id);  // i สำหรับ integer, s สำหรับ string
    
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
