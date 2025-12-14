<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

include "./Database.php";
include "../classes/Auth.class.php";
include "../middleware/auth_middleware.php";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

$authentication = new Authentication($db);
$authMiddleware = getAuthMiddleware();
$method = $_SERVER["REQUEST_METHOD"];

// Require admin role for all endpoints
$user = $authMiddleware->requireRole('Admin');

switch ($method) {
    case "GET":
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        if ($action === 'pending-requests') {
            // Get all pending role requests
            $requests = $authentication->getPendingRoleRequests();
            
            echo json_encode([
                "success" => true,
                "requests" => $requests
            ]);
            
        } elseif ($action === 'all-users') {
            // Get all users with their roles
            try {
                $query = "SELECT u.id, u.username, u.email, u.created_at 
                          FROM users u 
                          ORDER BY u.created_at DESC";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Add roles to each user
                foreach ($users as &$userData) {
                    $userData['roles'] = $authentication->getUserRoles($userData['id']);
                }
                
                echo json_encode([
                    "success" => true,
                    "users" => $users
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to fetch users"
                ]);
            }
            
        } else {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Invalid action"
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

        if ($action === 'approve-request') {
            // Approve a role request
            if (!isset($data['request_id'])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Request ID is required"]);
                exit();
            }
            
            $requestId = (int)$data['request_id'];
            
            if ($authentication->processRoleRequest($requestId, 'approved', $user['user_id'])) {
                echo json_encode([
                    "success" => true,
                    "message" => "Role request approved successfully"
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to approve role request"
                ]);
            }
            
        } elseif ($action === 'reject-request') {
            // Reject a role request
            if (!isset($data['request_id'])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Request ID is required"]);
                exit();
            }
            
            $requestId = (int)$data['request_id'];
            
            if ($authentication->processRoleRequest($requestId, 'rejected', $user['user_id'])) {
                echo json_encode([
                    "success" => true,
                    "message" => "Role request rejected successfully"
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to reject role request"
                ]);
            }
            
        } elseif ($action === 'assign-role') {
            // Manually assign a role to a user
            if (!isset($data['user_id']) || !isset($data['role_id'])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "User ID and Role ID are required"]);
                exit();
            }
            
            $userId = (int)$data['user_id'];
            $roleId = (int)$data['role_id'];
            
            if ($authentication->assignRole($userId, $roleId)) {
                echo json_encode([
                    "success" => true,
                    "message" => "Role assigned successfully"
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to assign role"
                ]);
            }
            
        } elseif ($action === 'remove-role') {
            // Remove a role from a user
            if (!isset($data['user_id']) || !isset($data['role_id'])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "User ID and Role ID are required"]);
                exit();
            }
            
            $userId = (int)$data['user_id'];
            $roleId = (int)$data['role_id'];
            
            if ($authentication->removeRole($userId, $roleId)) {
                echo json_encode([
                    "success" => true,
                    "message" => "Role removed successfully"
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to remove role"
                ]);
            }
            
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid action"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Method not allowed"
        ]);
        break;
}
