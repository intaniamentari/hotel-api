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

function responseError($status, $error = []) {
    echo json_encode([
        "status" => $status,
        "error" => $error
    ]);
}

function simpleResponse($status, $message) {
    echo json_encode([
        "status" => $status,
        "message" => $message
    ]);
}

// Cek endpoint
if (count($request) > 0 && $request[0] == 'rooms') {
    switch ($method) {
        case 'GET':
            if (isset($request[1])) {
                // GET /rooms/{id} -> Dapatkan detail kamar berdasarkan ID
                $room_id = (int) $request[1]; // Pastikan ID adalah integer
        
                try {
                    // Siapkan pernyataan untuk mengambil detail kamar berdasarkan ID
                    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id AND deleted_at  IS NULL");
                    $stmt->execute(['id' => $room_id]);
                    $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
                    if ($room) {
                        // Jika kamar ditemukan, kembalikan detail kamar
                        response(200, "Get data room successful", $room);
                    } else {
                        // Jika tidak ada kamar ditemukan dengan ID tersebut
                        responseError(404, "Room not found");
                    }
                } catch (PDOException $e) {
                    // Tangani kesalahan jika gagal mendapatkan data dari database
                    return responseError(500, ["Database error: " . $e->getMessage()]);
                }
            } else {
                // GET /rooms -> Dapatkan semua kamar
                try {
                    // Siapkan pernyataan untuk mengambil semua kamar
                    $stmt = $pdo->query("SELECT * FROM rooms WHERE deleted_at IS NULL");
                    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
                    response(200, "Get all data rooms", $rooms);
                } catch (PDOException $e) {
                    // Tangani kesalahan jika gagal mendapatkan data dari database
                    return responseError(500, ["Database error: " . $e->getMessage()]);
                }
            }
            break;
        

        case 'POST':
            // POST /rooms -> Menambahkan kamar baru
            $input = json_decode(file_get_contents("php://input"), true);
            $validation_error = ValidationRoom($input);

            if($validation_error) {
                return responseError(400, $validation_error);
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
                    'availability' => $input['availability'] === true ? 1 : 0,
                    'created_at' => $current_timestamp,
                    'updated_at' => $current_timestamp
                ]);

                // Jika eksekusi berhasil, kembalikan respons
                response(201, "Room successfully added", $input);
            } catch (PDOException $e) {
                // Tangani kesalahan jika gagal menambahkan ke database
                return responseError(500, ["Database error: " . $e->getMessage()]);
            }
            break;

        case 'PUT':        
            try {
                // PUT /rooms/{id} -> Memperbarui kamar
                $input = json_decode(file_get_contents("php://input"), true);
                // Mendapatkan ID kamar dari URL
                $room_id = (int) $request[1]; // Asumsikan ID kamar ada di $request[1]

                $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id AND deleted_at IS NULL");
                $stmt->execute(['id' => $room_id]);

                if($stmt->rowCount() > 0){
                    // Validasi input
                    $validation_error = ValidationRoom($input, true, $room_id);
                
                    if ($validation_error) {
                        return responseError(400, $validation_error);
                    }
                } else {
                    return responseError(404, "Room not found");
                }

                // Siapkan pernyataan untuk memperbarui data kamar
                $stmt = $pdo->prepare("UPDATE rooms SET 
                    room_number = :room_number,
                    room_type = :room_type,
                    price_per_night = :price_per_night,
                    availability = :availability,
                    updated_at = NOW() 
                    WHERE id = :id AND deleted_at IS NULL");
        
                // Eksekusi pernyataan dengan data input
                $stmt->execute([
                    'room_number' => $input['room_number'],
                    'room_type' => $input['room_type'],
                    'price_per_night' => $input['price_per_night'],
                    'availability' => $input['availability'] === true ? '1' : '0', // Ubah boolean menjadi string
                    'id' => $room_id // ID kamar yang ingin diperbarui
                ]);

                $getDataUpdated = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
                $getDataUpdated->execute(['id' => $room_id]);
                $roomDataUpdated = $getDataUpdated->fetch(PDO::FETCH_ASSOC);
        
                response(200, "Room updated", $roomDataUpdated);
            } catch (PDOException $e) {
                return responseError(500, ["Database error: " . $e->getMessage()]);
            }
            break;            

        case 'DELETE':
            // DELETE /rooms/{id} -> Menghapus kamar berdasarkan ID
            if (isset($request[1])) {
                $room_id = (int) $request[1]; // Ambil ID dari URL
                
                try {
                    $stmt = $pdo->prepare("UPDATE rooms SET 
                        deleted_at = NOW() 
                        WHERE id = :id");

                    $stmt->execute(['id' => $room_id]);
                    
                    // Cek apakah ada baris yang terpengaruh (dihapus)
                    if ($stmt->rowCount() > 0) {
                        simpleResponse(200, "Room deleted successfully");
                    } else {
                        // Jika tidak ada baris yang dihapus (misalnya ID tidak ditemukan)
                        responseError(404, "Room not found");
                    }
                } catch (PDOException $e) {
                    return responseError(500, ["Database error: " . $e->getMessage()]);
                }
            } else {
                responseError(400, "Room ID is required");
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
