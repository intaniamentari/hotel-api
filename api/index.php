<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include database jika menggunakan database
include 'db.php';

// Mendapatkan metode request (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// Fungsi untuk merespon data JSON
function response($status, $message, $data = []) {
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ]);
}

// Mengambil data dari URL
$request = isset($_SERVER['PATH_INFO']) ? explode("/", trim($_SERVER['PATH_INFO'], "/")) : [];

return $request[0];

// API Handler untuk berbagai metode
switch($method) {
    case 'GET':
        response(200, "GET request successful", ["name" => "John Doe", "age" => 28]);
        break;
    
    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        response(200, "POST request successful", ["received_data" => $input]);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents("php://input"), true);
        response(200, "PUT request successful", ["updated_data" => $input]);
        break;

    case 'DELETE':
        response(200, "DELETE request successful");
        break;

    default:
        response(405, "Method not allowed");
        break;
}
?>
