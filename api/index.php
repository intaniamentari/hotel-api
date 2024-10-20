<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include database jika menggunakan database
include 'db.php';

// Mendapatkan metode request (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// Mengambil data dari URL
$request = explode("/", trim($_SERVER['PATH_INFO'], "/"));

// Endpoint API, misalnya: /users atau /users/1
$resource = isset($request[0]) ? $request[0] : '';
$id = isset($request[1]) ? (int)$request[1] : null;

switch ($method) {
    case 'GET':
        if ($resource === 'users') {
            if ($id) {
                // Ambil user berdasarkan ID
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    echo json_encode(['status' => 'success', 'data' => $user]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'User not found']);
                }
            } else {
                // Ambil semua users
                $stmt = $pdo->query("SELECT * FROM users");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['status' => 'success', 'data' => $users]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid endpoint']);
        }
        break;

    case 'POST':
        if ($resource === 'users') {
            // Ambil data JSON yang dikirim
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['name']) && isset($data['email'])) {
                // Insert data user ke database
                $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
                $stmt->execute([$data['name'], $data['email']]);
                echo json_encode(['status' => 'success', 'message' => 'User created']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
            }
        }
        break;

    case 'PUT':
        if ($resource === 'users' && $id) {
            // Ambil data JSON yang dikirim
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['name']) && isset($data['email'])) {
                // Update user berdasarkan ID
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$data['name'], $data['email'], $id]);
                echo json_encode(['status' => 'success', 'message' => 'User updated']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
            }
        }
        break;

    case 'DELETE':
        if ($resource === 'users' && $id) {
            // Hapus user berdasarkan ID
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'User deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid endpoint']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}
?>
