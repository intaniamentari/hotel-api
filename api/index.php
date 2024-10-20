<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php';
include 'validation.php';
include 'response.php';
include 'controller.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_SERVER['PATH_INFO']) ? explode("/", trim($_SERVER['PATH_INFO'], "/")) : [];

// Endpoint rooms
if (count($request) > 0 && $request[0] == 'rooms') {
    switch ($method) {
        case 'GET':
            /**
             * API Detail Room
             * Method GET
             * Endpoint: {baseURL}/api/rooms/{id}
             * 
             */
            if (isset($request[1])) {
                return ShowRoom($pdo, $request);
        
            /**
             * API Get Data Rooms
             * Method GET
             * Endpoint: {baseURL}/api/rooms
             * 
             */
            } else {
                return IndexRooms($pdo);
            }
            break;  

        /**
         * API Store Data Room
         * Method POST
         * Endpoint: {baseURL}/api/rooms
         * 
         */
        case 'POST':
            return StoreRoom($pdo);
            break;

        /**
         * API Update Data Room
         * Method PUT
         * Endpoint: {baseURL}/api/rooms/{id}
         * 
         */
        case 'PUT':        
            return UpdateRoom($pdo, $request);
            break;            

        /**
         * API Delete Data Room
         * Method DELETE
         * Endpoint: {baseURL}/api/rooms/{id}
         * 
         */
        case 'DELETE':
            return DeleteRoom($pdo, $request);
            break;

        default:
            responseError(405, "Method not allowed");
            break;
    }
} else {
    responseError(404, "Endpoint not found");
}
?>
