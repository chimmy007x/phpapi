<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$input = json_decode(file_get_contents('php://input'), true);
$nontri_id = $input['nontri_id'] ?? '';
$password = $input['password'] ?? '';

if (empty($nontri_id) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Nontri ID and Password are required']);
    exit;
}

$sql = "SELECT * FROM students_personnel WHERE nontri_id = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $nontri_id, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // ตรวจสอบว่าตาราง students_personnel มีฟิลด์ number_id หรือไม่
    $userData = [
        'fname' => $user['fname'],
        'lname' => $user['lname'],
        'status' => $user['status'],
        'nontri_id' => $user['nontri_id'],
        // หากไม่มีฟิลด์ number_id ในตารางนี้ ต้องไม่ใส่ค่านี้
        // 'number_id' => $user['number_id'], 
    ];

    echo json_encode([
        'success' => true,
        'page' => 'UserPage',
        'user' => $userData
    ]);
} else {
    // ตรวจสอบในตาราง officer
    $sql = "SELECT * FROM officer WHERE nontri_id = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nontri_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => true, 'page' => 'OfficerPage']);
    } else {
        // ตรวจสอบในตาราง driver
        $sql = "SELECT * FROM driver WHERE number_id = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nontri_id, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $driver = $result->fetch_assoc(); // ดึงข้อมูล driver

            echo json_encode([
                'success' => true,
                'page' => 'BusPage',
                'user' => [
                    'number_id' => $driver['number_id'], // ส่งข้อมูล number_id สำหรับคนขับ
                    'fname' => $driver['fname'], // คุณสามารถเพิ่มข้อมูลเพิ่มเติมได้ที่นี่หากต้องการ
                    'lname' => $driver['lname']
                ],
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
        }
    }
}


$stmt->close();
$conn->close();
?>
