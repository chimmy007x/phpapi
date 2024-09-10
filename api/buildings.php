<?php
header('Content-Type: application/json');

// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "talaicsc";

$servername = "sql200.infinityfree.com";  // MySQL Host Name ที่คุณได้จาก InfinityFree
$username = "if0_37282459";             // MySQL User Name ที่คุณได้จาก InfinityFree
$password = "8Dbp17sNMq1F";      // รหัสผ่าน vPanel ที่คุณใช้บน InfinityFree
$dbname = "if0_37282459_db_project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT building_id, bname, ST_AsText(location) as location FROM building WHERE location IS NOT NULL"; // เงื่อนไขเพื่อดึงเฉพาะข้อมูลตำแหน่งที่มี
$result = $conn->query($sql);

$buildings = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $buildings[] = $row;
    }
    echo json_encode(['success' => true, 'buildings' => $buildings]);
} else {
    echo json_encode(['success' => false, 'message' => 'No buildings found']);
}

$conn->close();
?>
