<?php
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