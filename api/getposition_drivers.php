<?php
header('Content-Type: application/json');

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// ดึงข้อมูลตำแหน่งคนขับรถ
$sql = "SELECT number_id, fname, lname, bus_id, ST_AsText(Position) AS position FROM driver";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $drivers = [];
    while($row = $result->fetch_assoc()) {
        $drivers[] = [
            'number_id' => $row['number_id'],
            'fname' => $row['fname'],
            'lname' => $row['lname'],
            'bus_id' => $row['bus_id'],
            'position' => $row['position']
        ];
    }
    echo json_encode(['success' => true, 'drivers' => $drivers]);
} else {
    echo json_encode(['success' => false, 'message' => 'No drivers found']);
}

$conn->close();
?>
