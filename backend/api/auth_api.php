<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

include "./Database.php";
include "../classes/Auth.class.php";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["message" => "Database connection failed"]);
    exit();
}

$authentication = new Authentication($db);
$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
    case "GET":
        echo json_encode(["message" => "Auth API is working"]);
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['action'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Action parameter is required"]);
            exit();
        }

        $action = $data['action'];

        if ($action === 'register') {
            if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Incomplete data. Username, email, and password are required."]);
                exit();
            }

            $authentication->username = $data['username'];
            $authentication->email = $data['email'];
            $authentication->password = $data['password'];

            if ($authentication->registerUser()) {
                http_response_code(201);
                echo json_encode(["success" => true, "message" => "User registered successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "User registration failed"]);
            }
        } elseif ($action === 'login') {
            if (!isset($data['username']) || !isset($data['password'])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Username and password are required."]);
                exit();
            }

            $authentication->username = $data['username'];
            $authentication->password = $data['password'];

            if ($authentication->loginUser()) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Login successful",
                    "user" => [
                        "id" => $authentication->id,
                        "username" => $authentication->username,
                        "email" => $authentication->email
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["success" => false, "message" => "Invalid username or password"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid action"]);
        }
        break;
}