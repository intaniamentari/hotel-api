<?php

include 'db.php';

function ValidationRoom($data, $update = false, $id = null)
{
    if (_notEmpty($data)) {
        $validation_error = [
            'All data must be filled'
        ];

        return $validation_error;
    }

    $validation_type = _inputDataType($data, $update, $id);
    if($validation_type !== []) {
        return $validation_type;
    }

    return;
}

function _notEmpty($data)
{
    if(
        !isset($data['room_number']) && empty($data['room_number']) ||
        !isset($data['room_type']) && empty($data['room_type']) ||
        !isset($data['price_per_night']) && empty($data['price_per_night']) ||
        !isset($data['availability']) && empty($data['availability'])
    ) {
        return [
            'All data must be filled'
        ];
    }

    return;
}

function _inputDataType($data, $update = false, $id)
{
    global $pdo;
    $error_list = [];

    if(!is_string($data['room_number'])){
        $error_list['room_number'] = 'Data must be string format';
    } else {
        // check room_number in method update
        $room_number = $data['room_number'];
        if($update) {
            $data_id = $id;           

            // Query check duplicate room_number except data itself
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = :room_number AND id != :id");
            $stmt->execute(['room_number' => $room_number, 'id' => $data_id]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $error_list['room_number'] = 'Room number must be unique';
            }
        
        // check data room_number in method create
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = :room_number");
            $stmt->execute(['room_number' => $room_number]);
            $count = $stmt->fetchColumn();
    
            if ($count > 0) {
                $error_list['room_number'] = 'Room number must be unique';
            }
        }
    }

    if(!is_string($data['room_type'])){
        $error_list['room_type'] = 'Data must be string format';
    }

    if (!is_numeric($data['price_per_night']) || filter_var($data['price_per_night'], FILTER_VALIDATE_FLOAT) === false) {
        $error_list['price_per_night'] = 'Data must be a decimal format';
    }

    if(!is_bool($data['availability'])){
        $error_list['availability'] = 'Data must be boolean format';
    }

    return $error_list;
}