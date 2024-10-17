<?php
header('Content-Type: application/json');

// $servername = "bg9pkbtnzixeo5bxltsz-mysql.services.clever-cloud.com"; // Host จาก Clever Cloud
// $username = "uekuyjck8be0fvyl"; // User จาก Clever Cloud
// $password = "NylI2V6zJJrezJ2c71pd"; // ใส่รหัสผ่านจาก Clever Cloud ที่แสดงในช่อง Password
// $dbname = "bg9pkbtnzixeo5bxltsz"; // Database Name จาก Clever Cloud

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ฟังก์ชันสำหรับย่อรูปภาพ
function resizeImage($rawImageData, $width, $height) {
    $image = @imagecreatefromstring($rawImageData);
    
    // ตรวจสอบว่าภาพถูกต้องหรือไม่
    if ($image === false) {
        error_log('Invalid image data');
        return false;
    }

    $originalWidth = imagesx($image);
    $originalHeight = imagesy($image);
    $resizedImage = imagecreatetruecolor($width, $height);
    
    // ย่อรูปภาพ
    imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);
    
    // เก็บภาพที่ถูกย่อเป็นข้อมูล base64
    ob_start();
    imagejpeg($resizedImage);
    $resizedImageData = ob_get_clean();
    
    return base64_encode($resizedImageData);
}

$sql = "SELECT building_id, bname, ST_AsText(location) as location, Bphoto FROM building WHERE location IS NOT NULL";
$result = $conn->query($sql);

$buildings = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if (!is_null($row['Bphoto'])) {
            // ย่อรูปภาพเป็นขนาด 500x500
            $resizedPhoto = resizeImage($row['Bphoto'], 500, 500);
            if ($resizedPhoto !== false) {
                $row['Bphoto'] = 'data:image/jpeg;base64,' . $resizedPhoto;
            } else {
                $row['Bphoto'] = null; // หากภาพไม่ถูกต้อง ให้ตั้งเป็น null
            }
        } else {
            $row['Bphoto'] = null;
        }
        $buildings[] = $row;
    }
    echo json_encode(['success' => true, 'buildings' => $buildings]);
} else {
    echo json_encode(['success' => false, 'message' => 'No buildings found']);
}

$conn->close();
?>
