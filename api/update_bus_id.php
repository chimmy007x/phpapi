<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$input = json_decode(file_get_contents('php://input'), true);
$number_id = $input['number_id'] ?? '';
$bus_id = $input['bus_id'] ?? '';

if (empty($number_id) || empty($bus_id)) {
    echo json_encode(['success' => false, 'message' => 'Number ID and Bus ID are required']);
    exit;
}

// ตรวจสอบว่า bus_id ถูกใช้ไปแล้วหรือยัง
$check_sql = "SELECT COUNT(*) as count FROM driver WHERE bus_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $bus_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$count = $check_result->fetch_assoc()['count'];

if ($count > 0) {
    echo json_encode(['success' => false, 'message' => 'กรุณาใส่หมายเลขรถให้ถูกต้อง']);
    exit;
}

$sql = "UPDATE driver SET bus_id = ? WHERE number_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $bus_id, $number_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'คุณได้บันทึกหมายเลขรถแล้ว']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update Bus ID']);
}

$stmt->close();
$conn->close();
?>
