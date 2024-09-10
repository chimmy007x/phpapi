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

$nontri_id = $_GET['nontri_id'] ?? '';

if (empty($nontri_id)) {
    echo json_encode(['success' => false, 'message' => 'Nontri ID is required']);
    exit;
}

$sql = "SELECT * FROM students_personnel WHERE nontri_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nontri_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'fname' => $user['fname'],
        'lname' => $user['lname'],
        'status' => $user['status']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'No data found']);
}

$stmt->close();
$conn->close();
?>
