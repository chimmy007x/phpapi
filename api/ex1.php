<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "talaicsc";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$input = json_decode(file_get_contents('php://input'), true);
$nontri_id = $input['nontri_id'];
$password = $input['password'];

if (empty($nontri_id) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Nontri ID and Password are required']);
    exit;
}

// Check in students_personnel table
$sql = "SELECT * FROM students_personnel WHERE nontri_id = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $nontri_id, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => true, 'page' => 'UserPage']);
} else {
    // Check in officer table
    $sql = "SELECT * FROM officer WHERE nontri_id = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nontri_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => true, 'page' => 'OfficerPage']);
    } else {
        // Check in driver table
        $sql = "SELECT * FROM driver WHERE number_id = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nontri_id, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => true, 'page' => 'BusPage']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    }
}

$stmt->close();
$conn->close();
?>
