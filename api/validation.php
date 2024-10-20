<?php

include 'db.php';

function ValidationStoreRoom($data)
{
    if (_notEmpty($data)) {
        $validation_error = [
            'All data must be filled'
        ];

        return $validation_error;
    }

    $validation_type = _inputDataType($data);
    if($validation_type !== []) {
        return $validation_type;
    }

    return;
}

function ValidationUpdateRoom($data)
{

}

function _notEmpty($data)
{
    if(
        !isset($data['room_number']) && empty($data['room_number']) &&
        !isset($data['room_type']) && empty($data['room_type']) &&
        !isset($data['price_per_night']) && empty($data['price_per_night']) &&
        !isset($data['availability']) && empty($data['availability'])
    ) {
        return [
            'All data must be filled'
        ];
    }

    return;
}

function _inputDataType($data)
{
    global $pdo;
    $error_list = [];

    if(!is_string($data['room_number'])){
        $error_list['room_number'] = 'Data must be string format';
    } else {
        // Cek apakah room_number sudah ada di database
        $room_number = $data['room_number'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = :room_number");
        $stmt->execute(['room_number' => $room_number]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error_list['room_number'] = 'Room number must be unique';
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