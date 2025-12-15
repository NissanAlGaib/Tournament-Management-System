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
            $activities = [];

            try {
                // Get recent role requests
                try {
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
                } catch (PDOException $e) {
                    // Role requests table might not exist or be empty
                    error_log("Activity log - role requests error: " . $e->getMessage());
                }

                // Get recent user registrations
                try {
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
                } catch (PDOException $e) {
                    error_log("Activity log - user registrations error: " . $e->getMessage());
                }

                // Get recent tournament creations
                try {
                    $tournamentQuery = "SELECT t.id, t.name, t.created_at, u.username
                                       FROM tournaments t
                                       JOIN users u ON t.organizer_id = u.id
                                       ORDER BY t.created_at DESC
                                       LIMIT 10";
                    $stmt = $db->prepare($tournamentQuery);
                    $stmt->execute();
                    $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($tournaments as $tournament) {
                        $activities[] = [
                            'type' => 'tournament_created',
                            'user' => $tournament['username'],
                            'action' => 'Created tournament "' . $tournament['name'] . '"',
                            'timestamp' => $tournament['created_at']
                        ];
                    }
                } catch (PDOException $e) {
                    error_log("Activity log - tournaments created error: " . $e->getMessage());
                }

                // Get recently completed tournaments
                try {
                    $completedQuery = "SELECT t.id, t.name, t.completed_at, t.winner_name, t.winner_team_name,
                                             t.is_team_based,
                                             u.username as organizer, w.username as winner_username
                                       FROM tournaments t
                                       JOIN users u ON t.organizer_id = u.id
                                       LEFT JOIN users w ON t.winner_user_id = w.id
                                       WHERE t.status = 'completed' AND t.completed_at IS NOT NULL
                                       ORDER BY t.completed_at DESC
                                       LIMIT 10";
                    $stmt = $db->prepare($completedQuery);
                    $stmt->execute();
                    $completed = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($completed as $tournament) {
                        // Determine winner - check team first if team-based, then individual
                        $winner = null;
                        if ($tournament['is_team_based'] && $tournament['winner_team_name']) {
                            $winner = $tournament['winner_team_name'];
                        } elseif ($tournament['winner_username']) {
                            $winner = $tournament['winner_username'];
                        } elseif ($tournament['winner_name']) {
                            $winner = $tournament['winner_name'];
                        }

                        // Create activity message
                        if ($winner) {
                            $action = 'Completed tournament "' . $tournament['name'] . '" - Winner: ' . $winner;
                        } else {
                            $action = 'Completed tournament "' . $tournament['name'] . '"';
                        }

                        $activities[] = [
                            'type' => 'tournament_completed',
                            'user' => $tournament['organizer'],
                            'action' => $action,
                            'timestamp' => $tournament['completed_at']
                        ];
                    }
                } catch (PDOException $e) {
                    error_log("Activity log - tournaments completed error: " . $e->getMessage());
                }

                // Get recently started tournaments
                try {
                    $startedQuery = "SELECT t.id, t.name, t.updated_at, u.username
                                    FROM tournaments t
                                    JOIN users u ON t.organizer_id = u.id
                                    WHERE t.status = 'ongoing'
                                    ORDER BY t.updated_at DESC
                                    LIMIT 10";
                    $stmt = $db->prepare($startedQuery);
                    $stmt->execute();
                    $started = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($started as $tournament) {
                        $activities[] = [
                            'type' => 'tournament_started',
                            'user' => $tournament['username'],
                            'action' => 'Started tournament "' . $tournament['name'] . '"',
                            'timestamp' => $tournament['updated_at']
                        ];
                    }
                } catch (PDOException $e) {
                    error_log("Activity log - tournaments started error: " . $e->getMessage());
                }

                // Get recent tournament registrations
                try {
                    $registrationQuery = "SELECT tp.registered_at, t.name as tournament_name,
                                                u.username
                                         FROM tournament_participants tp
                                         JOIN tournaments t ON tp.tournament_id = t.id
                                         JOIN users u ON tp.user_id = u.id
                                         WHERE tp.registration_status = 'confirmed'
                                         ORDER BY tp.registered_at DESC
                                         LIMIT 15";
                    $stmt = $db->prepare($registrationQuery);
                    $stmt->execute();
                    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($registrations as $reg) {
                        $activities[] = [
                            'type' => 'tournament_registration',
                            'user' => $reg['username'],
                            'action' => 'Joined tournament "' . $reg['tournament_name'] . '"',
                            'timestamp' => $reg['registered_at']
                        ];
                    }
                } catch (PDOException $e) {
                    error_log("Activity log - tournament registrations error: " . $e->getMessage());
                }

                // Sort all activities by timestamp
                if (count($activities) > 0) {
                    usort($activities, function ($a, $b) {
                        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
                    });

                    // Limit to 30 most recent
                    $activities = array_slice($activities, 0, 30);
                }

                echo json_encode([
                    "success" => true,
                    "activities" => $activities
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                error_log("Activity log general error: " . $e->getMessage());
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to fetch activity log: " . $e->getMessage()
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
