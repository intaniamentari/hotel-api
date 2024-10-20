<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php';
include 'validation.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_SERVER['PATH_INFO']) ? explode("/", trim($_SERVER['PATH_INFO'], "/")) : [];

// Fungsi untuk merespon data JSON
function response($status, $message, $data = []) {
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ]);
}

function validationError($status, $error = []) {
    echo json_encode([
        "status" => $status,
        "error" => $error
    ]);
}

// Cek endpoint
if (count($request) > 0 && $request[0] == 'rooms') {
    switch ($method) {
        case 'GET':
            if (isset($request[1])) {
                // GET /rooms/{id} -> Dapatkan detail kamar berdasarkan ID
                $room_id = $request[1];
                // Logika untuk mendapatkan kamar berdasarkan ID dari database atau data statis
                response(200, "Room details fetched", ["id" => $room_id, "name" => "Room $room_id", "status" => "available"]);
            } else {
                // GET /rooms -> Dapatkan semua kamar
                // Logika untuk mendapatkan semua kamar dari database atau data statis
                $rooms = [
                    ["id" => 1, "name" => "Room 1", "status" => "available"],
                    ["id" => 2, "name" => "Room 2", "status" => "occupied"]
                ];
                response(200, "Rooms fetched", $rooms);
            }
            break;

        case 'POST':
            // POST /rooms -> Menambahkan kamar baru
            $input = json_decode(file_get_contents("php://input"), true);
            $validation_error = ValidationStoreRoom($input);

            if($validation_error) {
                return validationError(400, $validation_error);
            }

            // Logika untuk menambahkan kamar ke database
            try {
                // Siapkan pernyataan SQL
                $stmt = $pdo->prepare("INSERT INTO rooms (room_number, room_type, price_per_night, availability, created_at, updated_at) VALUES (:room_number, :room_type, :price_per_night, :availability, :created_at, :updated_at)");

                $current_timestamp = date('Y-m-d H:i:s');

                // Eksekusi pernyataan dengan data dari $input
                $stmt->execute([
                    'room_number' => $input['room_number'],
                    'room_type' => $input['room_type'],
                    'price_per_night' => $input['price_per_night'],
                    'availability' => $input['availability'],
                    'created_at' => $current_timestamp,
                    'updated_at' => $current_timestamp
                ]);

                // Jika eksekusi berhasil, kembalikan respons
                response(201, "Room successfully added", $input);
            } catch (PDOException $e) {
                // Tangani kesalahan jika gagal menambahkan ke database
                return validationError(500, ["Database error: " . $e->getMessage()]);
            }
            break;

        case 'PUT':
            // PUT /rooms -> Memperbarui kamar
            $input = json_decode(file_get_contents("php://input"), true);
            // Logika untuk memperbarui kamar di database
            response(200, "Room updated", $input);
            break;

        case 'DELETE':
            if (isset($request[1])) {
                // DELETE /rooms/{id} -> Menghapus kamar berdasarkan ID
                $room_id = $request[1];
                // Logika untuk menghapus kamar dari database
                response(200, "Room deleted", ["id" => $room_id]);
            } else {
                response(400, "Room ID not provided");
            }
            break;

        default:
            response(405, "Method not allowed");
            break;
    }
} else {
    response(404, "Endpoint not found");
}
?>
