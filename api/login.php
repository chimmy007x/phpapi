<?php 
header('Content-Type: application/json');

// ปิดการแสดงผลข้อผิดพลาดแบบ HTML
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// รับข้อมูล JSON จาก request body
$input = json_decode(file_get_contents('php://input'), true);
$nontri_id = $input['nontri_id'] ?? '';
$password = $input['password'] ?? '';

// ตรวจสอบว่าได้กรอก Nontri ID และ Password หรือไม่
if (empty($nontri_id) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Nontri ID and Password are required']);
    exit;
}

// เรียก API เพื่อตรวจสอบข้อมูล
$api_url = "https://inv.csc.ku.ac.th/cscapi/ldap/";
$data = json_encode([
    'keyapp' => '5d41689157676df689c0081789a8d73f3ef859d93773559f8e570fe1b27217a0',
    'dataset' => 'ldap',
    'userid' => $nontri_id,  
    'pwd' => $password       
]);

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => $data,
    ],
];

$context  = stream_context_create($options);
$response = file_get_contents($api_url, false, $context);

// ตรวจสอบว่าได้รับข้อมูลจาก API และเป็น JSON
if ($response === FALSE) {
    echo json_encode(['success' => false, 'message' => 'Unable to connect to external API']);
    exit;
}

$response_data = json_decode($response, true);

// ตรวจสอบว่า API ตอบกลับมาด้วยสถานะที่ถูกต้อง
if (isset($response_data['status_code']) && $response_data['status_code'] == "1") {
    // ข้อมูลถูกต้อง ให้เช็คว่า nontri_id มีอยู่ในฐานข้อมูลหรือไม่
    $sql = "SELECT * FROM students_personnel WHERE nontri_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nontri_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ผู้ใช้มีอยู่ในฐานข้อมูลแล้ว ให้ดำเนินการเข้าสู่ระบบแทน
        $user = $result->fetch_assoc(); // ดึงข้อมูลผู้ใช้

        // ตรวจสอบรหัสผ่านด้วย password_verify() (ถ้ารหัสผ่านมีอยู่ในฟิลด์)
        // $input_password ควรเป็นรหัสผ่านที่ผู้ใช้ป้อนเข้ามา
        if (password_verify($input_password, $user['password'])) {
            echo json_encode([
                'success' => true,
                'page' => 'UserPage',
                'user' => [
                    'fname' => $user['fname'],
                    'lname' => $user['lname'],
                    'nontri_id' => $user['nontri_id'],
                    'status' => $user['status']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        // ข้อมูลไม่อยู่ในฐานข้อมูล ให้บันทึกข้อมูลลงในตาราง
        $uid = $response_data['data']['uid'];
        $thainame = $response_data['data']['thainame'];
        $name_parts = explode(' ', $thainame); // แยก fname และ lname
        $fname = $name_parts[0];
        $lname = isset($name_parts[1]) ? $name_parts[1] : ''; // ตรวจสอบหากไม่มี lname
        $status = $response_data['data']['position'] === null ? 'นิสิต' : 'อาจารย์';
        $building_id = null;
        $position_id = null;

        // เข้ารหัสรหัสผ่านก่อนบันทึกลงฐานข้อมูล
        $password = password_hash($input_password, PASSWORD_DEFAULT);

        // บันทึกข้อมูลลงในตาราง students_personnel
        $sql = "INSERT INTO students_personnel (nontri_id, password, fname, lname, status, building_id, Position_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $uid, $password, $fname, $lname, $status, $building_id, $position_id);

        if ($stmt->execute()) {
            // บันทึกข้อมูลสำเร็จ ให้พาผู้ใช้ไปยังหน้า UserPage
            echo json_encode([
                'success' => true,
                'page' => 'UserPage',
                'user' => [
                    'fname' => $fname,
                    'lname' => $lname,
                    'nontri_id' => $uid,
                    'status' => $status
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save data']);
        }
    }
 
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
            echo json_encode(['success' => false, 'message' => 'Invalid Nontri ID or Password']);
        }
    }
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>
