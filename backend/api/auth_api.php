<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

include "./Database.php";
include "../classes/Auth.class.php";
include "../classes/JWT.class.php";
include "../classes/Session.class.php";
include "../middleware/auth_middleware.php";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["message" => "Database connection failed"]);
    exit();
}

$authentication = new Authentication($db);
$jwt = new JWT();
$session = new Session($db);
$authMiddleware = getAuthMiddleware();
$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
    case "GET":
        // Get current user info
        $user = $authMiddleware->getCurrentUser();
        if ($user) {
            echo json_encode([
                "success" => true,
                "user" => $user
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Not authenticated"
            ]);
        }
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
                // Get user with roles
                $user = $authentication->getUserById($authentication->id);
                
                // Generate JWT token
                $tokenPayload = [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'roles' => $user['roles']
                ];
                $token = $jwt->generate($tokenPayload);
                
                // Store session
                $session->create($user['id'], $token);
                
                http_response_code(201);
                echo json_encode([
                    "success" => true,
                    "message" => "User registered successfully",
                    "token" => $token,
                    "user" => $user
                ]);
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
                // Get user with roles
                $user = $authentication->getUserById($authentication->id);
                
                // Generate JWT token
                $tokenPayload = [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'roles' => $user['roles']
                ];
                $token = $jwt->generate($tokenPayload);
                
                // Store session
                $session->create($user['id'], $token);
                
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Login successful",
                    "token" => $token,
                    "user" => $user
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["success" => false, "message" => "Invalid username or password"]);
            }
            
        } elseif ($action === 'logout') {
            if ($authMiddleware->logout()) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Logout successful"
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Logout failed"
                ]);
            }
            
        } elseif ($action === 'check-role') {
            // Check if user has a specific role
            $user = $authMiddleware->requireAuth();
            
            if (!isset($data['role'])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Role parameter is required"]);
                exit();
            }
            
            $hasRole = $authMiddleware->hasRole($user, $data['role']);
            
            echo json_encode([
                "success" => true,
                "hasRole" => $hasRole,
                "role" => $data['role']
            ]);
            
        } elseif ($action === 'get-roles') {
            // Get all available roles
            $roles = $authentication->getAllRoles();
            
            echo json_encode([
                "success" => true,
                "roles" => $roles
            ]);
            
        } elseif ($action === 'request-organizer-role') {
            // Request organizer role upgrade
            $user = $authMiddleware->requireAuth();
            
            $reason = isset($data['reason']) ? $data['reason'] : null;
            
            // Organizer role ID is 3
            if ($authentication->requestRoleUpgrade($user['user_id'], 3, $reason)) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Organizer role request submitted successfully. An admin will review your request."
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to submit role request. You may already have a pending request."
                ]);
            }
            
        } elseif ($action === 'verify-token') {
            // Verify if current token is valid
            $user = $authMiddleware->getCurrentUser();
            
            if ($user) {
                echo json_encode([
                    "success" => true,
                    "valid" => true,
                    "user" => $user
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    "success" => false,
                    "valid" => false,
                    "message" => "Invalid or expired token"
                ]);
            }
            
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid action"]);
        }
        break;
}