<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

include "./Database.php";
include "../classes/Auth.class.php";
include "../classes/Session.class.php";
include "../middleware/auth_middleware.php";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

$authentication = new Authentication($db);
$sessionManager = new Session($db);
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
            
        } elseif ($action === 'dashboard-stats') {
            // Get dashboard statistics
            try {
                // Get tournament count
                $tournamentQuery = "SELECT COUNT(*) as count FROM tournaments";
                $stmt = $db->prepare($tournamentQuery);
                $stmt->execute();
                $tournamentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Get active sessions count
                $sessionQuery = "SELECT COUNT(*) as count FROM sessions WHERE expires_at > NOW()";
                $stmt = $db->prepare($sessionQuery);
                $stmt->execute();
                $activeSessionsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                echo json_encode([
                    "success" => true,
                    "stats" => [
                        "tournament_count" => (int)$tournamentCount,
                        "active_sessions" => (int)$activeSessionsCount
                    ]
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to fetch dashboard statistics"
                ]);
            }
            
        } elseif ($action === 'active-sessions') {
            // Get all active sessions with user info
            try {
                $query = "SELECT s.id, s.user_id, s.ip_address, s.user_agent, s.created_at, s.last_activity,
                                 u.username, u.email
                          FROM sessions s
                          JOIN users u ON s.user_id = u.id
                          WHERE s.expires_at > NOW()
                          ORDER BY s.last_activity DESC";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    "success" => true,
                    "sessions" => $sessions
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to fetch active sessions"
                ]);
            }
            
        } elseif ($action === 'activity-log') {
            // Get recent activity from various sources
            try {
                $activities = [];
                
                // Get recent role requests
                $roleQuery = "SELECT rr.id, rr.user_id, rr.role_id, rr.status, rr.created_at,
                                     u.username, r.role_name
                              FROM role_requests rr
                              JOIN users u ON rr.user_id = u.id
                              JOIN roles r ON rr.role_id = r.id
                              ORDER BY rr.created_at DESC
                              LIMIT 10";
                $stmt = $db->prepare($roleQuery);
                $stmt->execute();
                $roleRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($roleRequests as $request) {
                    $action = $request['status'] === 'pending' ? 'requested' : $request['status'];
                    $activities[] = [
                        'type' => 'role',
                        'user' => $request['username'],
                        'action' => ucfirst($action) . ' ' . $request['role_name'] . ' role',
                        'timestamp' => $request['created_at']
                    ];
                }
                
                // Get recent user registrations
                $userQuery = "SELECT id, username, created_at
                              FROM users
                              ORDER BY created_at DESC
                              LIMIT 10";
                $stmt = $db->prepare($userQuery);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($users as $user) {
                    $activities[] = [
                        'type' => 'user',
                        'user' => $user['username'],
                        'action' => 'Registered new account',
                        'timestamp' => $user['created_at']
                    ];
                }
                
                // Get recent tournament creations
                $tournamentQuery = "SELECT t.id, t.name, t.created_at, u.username
                                   FROM tournaments t
                                   JOIN users u ON t.created_by = u.id
                                   ORDER BY t.created_at DESC
                                   LIMIT 10";
                $stmt = $db->prepare($tournamentQuery);
                $stmt->execute();
                $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($tournaments as $tournament) {
                    $activities[] = [
                        'type' => 'tournament',
                        'user' => $tournament['username'],
                        'action' => 'Created new tournament "' . $tournament['name'] . '"',
                        'timestamp' => $tournament['created_at']
                    ];
                }
                
                // Sort all activities by timestamp
                usort($activities, function($a, $b) {
                    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
                });
                
                // Limit to 20 most recent
                $activities = array_slice($activities, 0, 20);
                
                echo json_encode([
                    "success" => true,
                    "activities" => $activities
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to fetch activity log"
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
