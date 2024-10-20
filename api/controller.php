<?php

function IndexRooms($pdo)
{
    try {
        // Query get all data rooms
        $stmt = $pdo->query("SELECT * FROM rooms WHERE deleted_at IS NULL");
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        response(200, "Get all data rooms", $rooms);
    } catch (PDOException $e) {
        return responseError(500, ["Database error: " . $e->getMessage()]);
    }
}

function ShowRoom($pdo, $request)
{
    try {
        $room_id = (int) $request[1];
        // Query get data by id
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id AND deleted_at  IS NULL");
        $stmt->execute(['id' => $room_id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        // Data exist
        if ($room) {
            response(200, "Get data room successful", $room);
        } else {
            responseError(404, "Room not found");
        }
    } catch (PDOException $e) {
        return responseError(500, ["Database error: " . $e->getMessage()]);
    }
}

function StoreRoom($pdo)
{
    try {
        $input = json_decode(file_get_contents("php://input"), true);
        $validation_error = ValidationRoom($input);

        if($validation_error) {
            return responseError(400, $validation_error);
        }
        // Query create data rooms
        $stmt = $pdo->prepare(
            "INSERT INTO rooms (
                room_number, room_type, price_per_night, availability, created_at, updated_at
            ) VALUES (
                :room_number, :room_type, :price_per_night, :availability, :created_at, :updated_at
            )");
        $stmt->execute([
            'room_number' => $input['room_number'],
            'room_type' => $input['room_type'],
            'price_per_night' => $input['price_per_night'],
            'availability' => $input['availability'] === true ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        response(201, "Room successfully added", $input);
    } catch (PDOException $e) {
        return responseError(500, ["Database error: " . $e->getMessage()]);
    }
}

function UpdateRoom($pdo, $request)
{
    try {
        $input = json_decode(file_get_contents("php://input"), true);
        $room_id = (int) $request[1];

        // Data exist
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $room_id]);
        if($stmt->rowCount() > 0){
            $validation_error = ValidationRoom($input, true, $room_id);
        
            if ($validation_error) {
                return responseError(400, $validation_error);
            }
        } else {
            return responseError(404, "Room not found");
        }

        // Query update data rooms
        $stmt = $pdo->prepare("UPDATE rooms SET 
            room_number = :room_number,
            room_type = :room_type,
            price_per_night = :price_per_night,
            availability = :availability,
            updated_at = NOW() 
            WHERE id = :id AND deleted_at IS NULL");

        $stmt->execute([
            'room_number' => $input['room_number'],
            'room_type' => $input['room_type'],
            'price_per_night' => $input['price_per_night'],
            'availability' => $input['availability'] === true ? '1' : '0',
            'id' => $room_id 
        ]);

        // create data response
        $getDataUpdated = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
        $getDataUpdated->execute(['id' => $room_id]);
        $roomDataUpdated = $getDataUpdated->fetch(PDO::FETCH_ASSOC);

        response(200, "Room updated successful", $roomDataUpdated);
    } catch (PDOException $e) {
        return responseError(500, ["Database error: " . $e->getMessage()]);
    }
}

function DeleteRoom($pdo, $request)
{
    if (isset($request[1])) {
        $room_id = (int) $request[1];
        
        try {
            $stmt = $pdo->prepare("UPDATE rooms SET 
                deleted_at = NOW() 
                WHERE id = :id AND deleted_at IS NULL");

            $stmt->execute(['id' => $room_id]);
            
            if ($stmt->rowCount() > 0) {
                simpleResponse(200, "Room deleted successfully");
            } else {
                responseError(404, "Room not found");
            }
        } catch (PDOException $e) {
            return responseError(500, ["Database error: " . $e->getMessage()]);
        }
    } else {
        responseError(400, "Room ID is required");
    }
}