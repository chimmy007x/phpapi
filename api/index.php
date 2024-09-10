<?php
header("Content-Type: application/json");

include '../config/config.php';

$request_method = $_SERVER["REQUEST_METHOD"];

switch($request_method) {
    case 'GET':
        // Retrive students personnel
        get_students_personnel();
        break;
    case 'POST':
        // Login
        login();
        break;
    default:
        // Invalid request method
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}

function get_students_personnel() {
    global $conn;
    $query = "SELECT * FROM students_personnel";
    $result = $conn->query($query);
    $students = array();

    while($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    echo json_encode($students);
}

function login() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data["nontri_id"]) && isset($data["password"])) {
        $nontri_id = $data["nontri_id"];
        $password = $data["password"];

        $query = "SELECT * FROM students_personnel WHERE nontri_id='$nontri_id' AND password='$password'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo json_encode(array("success" => true, "user" => $user));
        } else {
            echo json_encode(array("success" => false, "message" => "Invalid credentials"));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "nontri_id or password is missing"));
    }
}
?>
