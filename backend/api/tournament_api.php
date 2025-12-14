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

try {
    switch ($method) {
        case "GET":
            $action = isset($_GET['action']) ? $_GET['action'] : '';

            if ($action === 'tournaments') {
                // Get all active tournaments
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                
                $query = "SELECT t.*, u.username as organizer_name, 
                         COUNT(DISTINCT tp.id) as registered_participants
                         FROM tournaments t
                         LEFT JOIN users u ON t.organizer_id = u.id
                         LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id 
                         AND tp.registration_status = 'confirmed'
                         WHERE 1=1";
                
                if ($status) {
                    $query .= " AND t.status = :status";
                }
                
                $query .= " GROUP BY t.id ORDER BY t.created_at DESC LIMIT 50";
                
                $stmt = $db->prepare($query);
                if ($status) {
                    $stmt->bindParam(':status', $status);
                }
                $stmt->execute();
                $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    "success" => true,
                    "tournaments" => $tournaments
                ]);
                
            } elseif ($action === 'tournament' && isset($_GET['id'])) {
                // Get single tournament with details
                $id = $_GET['id'];
                
                $query = "SELECT t.*, u.username as organizer_name 
                         FROM tournaments t
                         LEFT JOIN users u ON t.organizer_id = u.id
                         WHERE t.id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$tournament) {
                    throw new Exception('Tournament not found');
                }
                
                // Get prizes
                $prizeQuery = "SELECT * FROM tournament_prizes 
                              WHERE tournament_id = :id ORDER BY placement";
                $prizeStmt = $db->prepare($prizeQuery);
                $prizeStmt->bindParam(':id', $id);
                $prizeStmt->execute();
                $tournament['prizes'] = $prizeStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get participant count
                $participantQuery = "SELECT COUNT(*) as count FROM tournament_participants 
                                    WHERE tournament_id = :id AND registration_status = 'confirmed'";
                $participantStmt = $db->prepare($participantQuery);
                $participantStmt->bindParam(':id', $id);
                $participantStmt->execute();
                $tournament['participants_count'] = $participantStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                echo json_encode([
                    "success" => true,
                    "tournament" => $tournament
                ]);
                
            } elseif ($action === 'my-tournaments') {
                // Get tournaments for current user
                $user = $authMiddleware->authenticate();
                
                // Tournaments user is participating in
                $participatingQuery = "SELECT t.*, tp.registration_status, tp.registered_at 
                                      FROM tournaments t 
                                      INNER JOIN tournament_participants tp ON t.id = tp.tournament_id 
                                      WHERE tp.user_id = :user_id 
                                      ORDER BY t.start_date DESC";
                $stmt = $db->prepare($participatingQuery);
                $stmt->bindParam(':user_id', $user->id);
                $stmt->execute();
                $participating = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Tournaments user is organizing
                $organizing = [];
                $roles = array_column($user->roles, 'role_name');
                if (in_array('Organizer', $roles) || in_array('Admin', $roles)) {
                    $organizingQuery = "SELECT t.*, 
                                       (SELECT COUNT(*) FROM tournament_participants tp 
                                        WHERE tp.tournament_id = t.id AND tp.registration_status = 'confirmed') as participant_count
                                       FROM tournaments t 
                                       WHERE t.organizer_id = :user_id 
                                       ORDER BY t.created_at DESC";
                    $orgStmt = $db->prepare($organizingQuery);
                    $orgStmt->bindParam(':user_id', $user->id);
                    $orgStmt->execute();
                    $organizing = $orgStmt->fetchAll(PDO::FETCH_ASSOC);
                }
                
                echo json_encode([
                    "success" => true,
                    "participating" => $participating,
                    "organizing" => $organizing
                ]);
                
            } elseif ($action === 'leaderboard' && isset($_GET['tournament_id'])) {
                // Get tournament leaderboard
                $tournamentId = $_GET['tournament_id'];
                
                $query = "SELECT ts.*, u.username, tp.user_id
                         FROM tournament_standings ts
                         JOIN tournament_participants tp ON ts.participant_id = tp.id
                         JOIN users u ON tp.user_id = u.id
                         WHERE ts.tournament_id = :tournament_id
                         ORDER BY ts.current_rank ASC, ts.points DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':tournament_id', $tournamentId);
                $stmt->execute();
                $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    "success" => true,
                    "leaderboard" => $leaderboard
                ]);
                
            } else {
                throw new Exception('Invalid action or missing parameters');
            }
            break;

        case "POST":
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!isset($data['action'])) {
                throw new Exception('Action parameter is required');
            }
            
            $action = $data['action'];
            
            if ($action === 'create') {
                // Create tournament (Organizer/Admin only)
                $user = $authMiddleware->requireRole(['Organizer', 'Admin']);
                
                // Validate required fields
                if (!isset($data['name']) || !isset($data['registration_deadline']) || !isset($data['start_date'])) {
                    throw new Exception('Missing required fields: name, registration_deadline, start_date');
                }
                
                $db->beginTransaction();
                
                try {
                    $query = "INSERT INTO tournaments 
                            (organizer_id, name, description, game_type, format, tournament_size, 
                             max_participants, rules, match_rules, scoring_system, entry_fee, 
                             is_public, is_featured, registration_start, registration_deadline, 
                             allow_late_registration, start_date, end_date, estimated_duration_hours, 
                             status, visibility)
                            VALUES 
                            (:organizer_id, :name, :description, :game_type, :format, :tournament_size,
                             :max_participants, :rules, :match_rules, :scoring_system, :entry_fee,
                             :is_public, :is_featured, :registration_start, :registration_deadline,
                             :allow_late_registration, :start_date, :end_date, :estimated_duration_hours,
                             :status, :visibility)";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':organizer_id', $user->id);
                    $stmt->bindParam(':name', $data['name']);
                    $stmt->bindParam(':description', $data['description'] ?? null);
                    $stmt->bindParam(':game_type', $data['game_type'] ?? null);
                    $stmt->bindParam(':format', $data['format'] ?? 'single_elimination');
                    $stmt->bindParam(':tournament_size', $data['tournament_size'] ?? 16);
                    $stmt->bindParam(':max_participants', $data['max_participants'] ?? null);
                    $stmt->bindParam(':rules', $data['rules'] ?? null);
                    $stmt->bindParam(':match_rules', $data['match_rules'] ?? null);
                    $stmt->bindParam(':scoring_system', $data['scoring_system'] ?? 'best_of_3');
                    $stmt->bindParam(':entry_fee', $data['entry_fee'] ?? 0.00);
                    $stmt->bindParam(':is_public', $data['is_public'] ?? 1);
                    $stmt->bindParam(':is_featured', $data['is_featured'] ?? 0);
                    $stmt->bindParam(':registration_start', $data['registration_start'] ?? null);
                    $stmt->bindParam(':registration_deadline', $data['registration_deadline']);
                    $stmt->bindParam(':allow_late_registration', $data['allow_late_registration'] ?? 0);
                    $stmt->bindParam(':start_date', $data['start_date']);
                    $stmt->bindParam(':end_date', $data['end_date'] ?? null);
                    $stmt->bindParam(':estimated_duration_hours', $data['estimated_duration_hours'] ?? null);
                    $stmt->bindParam(':status', $data['status'] ?? 'draft');
                    $stmt->bindParam(':visibility', $data['visibility'] ?? 'public');
                    
                    $stmt->execute();
                    $tournamentId = $db->lastInsertId();
                    
                    // Add prizes if provided
                    if (isset($data['prizes']) && is_array($data['prizes'])) {
                        $prizeQuery = "INSERT INTO tournament_prizes 
                                      (tournament_id, placement, prize_type, prize_amount, currency, prize_description)
                                      VALUES (:tournament_id, :placement, :prize_type, :prize_amount, :currency, :prize_description)";
                        $prizeStmt = $db->prepare($prizeQuery);
                        
                        foreach ($data['prizes'] as $prize) {
                            $prizeStmt->bindParam(':tournament_id', $tournamentId);
                            $prizeStmt->bindParam(':placement', $prize['placement']);
                            $prizeStmt->bindParam(':prize_type', $prize['type'] ?? 'cash');
                            $prizeStmt->bindParam(':prize_amount', $prize['amount'] ?? 0);
                            $prizeStmt->bindParam(':currency', $prize['currency'] ?? 'USD');
                            $prizeStmt->bindParam(':prize_description', $prize['description'] ?? null);
                            $prizeStmt->execute();
                        }
                    }
                    
                    $db->commit();
                    
                    echo json_encode([
                        "success" => true,
                        "message" => "Tournament created successfully",
                        "tournament_id" => $tournamentId
                    ]);
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
                
            } elseif ($action === 'register') {
                // Register for tournament
                $user = $authMiddleware->authenticate();
                
                if (!isset($data['tournament_id'])) {
                    throw new Exception('Tournament ID is required');
                }
                
                $tournamentId = $data['tournament_id'];
                
                // Check if tournament exists and is open
                $tournamentQuery = "SELECT * FROM tournaments WHERE id = :id";
                $tournamentStmt = $db->prepare($tournamentQuery);
                $tournamentStmt->bindParam(':id', $tournamentId);
                $tournamentStmt->execute();
                $tournament = $tournamentStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$tournament) {
                    throw new Exception('Tournament not found');
                }
                
                if ($tournament['status'] !== 'open') {
                    throw new Exception('Tournament is not open for registration');
                }
                
                // Check if already registered
                $checkQuery = "SELECT * FROM tournament_participants 
                              WHERE tournament_id = :tournament_id AND user_id = :user_id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':tournament_id', $tournamentId);
                $checkStmt->bindParam(':user_id', $user->id);
                $checkStmt->execute();
                
                if ($checkStmt->rowCount() > 0) {
                    throw new Exception('Already registered for this tournament');
                }
                
                // Check if tournament is full
                if ($tournament['max_participants'] && $tournament['current_participants'] >= $tournament['max_participants']) {
                    throw new Exception('Tournament is full');
                }
                
                // Register participant
                $registerQuery = "INSERT INTO tournament_participants 
                                 (tournament_id, user_id, registration_status, payment_status)
                                 VALUES (:tournament_id, :user_id, 'confirmed', 'pending')";
                $registerStmt = $db->prepare($registerQuery);
                $registerStmt->bindParam(':tournament_id', $tournamentId);
                $registerStmt->bindParam(':user_id', $user->id);
                $registerStmt->execute();
                
                echo json_encode([
                    "success" => true,
                    "message" => "Successfully registered for tournament"
                ]);
                
            } elseif ($action === 'update-status') {
                // Update tournament status (Organizer/Admin only)
                $user = $authMiddleware->requireRole(['Organizer', 'Admin']);
                
                if (!isset($data['tournament_id']) || !isset($data['status'])) {
                    throw new Exception('Tournament ID and status are required');
                }
                
                $tournamentId = $data['tournament_id'];
                $newStatus = $data['status'];
                
                // Verify ownership
                $checkQuery = "SELECT * FROM tournaments WHERE id = :id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':id', $tournamentId);
                $checkStmt->execute();
                $tournament = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$tournament) {
                    throw new Exception('Tournament not found');
                }
                
                $roles = array_column($user->roles, 'role_name');
                if ($tournament['organizer_id'] != $user->id && !in_array('Admin', $roles)) {
                    throw new Exception('You do not have permission to update this tournament');
                }
                
                // Update status
                $updateQuery = "UPDATE tournaments SET status = :status WHERE id = :id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':status', $newStatus);
                $updateStmt->bindParam(':id', $tournamentId);
                $updateStmt->execute();
                
                echo json_encode([
                    "success" => true,
                    "message" => "Tournament status updated successfully"
                ]);
                
            } else {
                throw new Exception('Invalid action');
            }
            break;

        case "PUT":
            $data = json_decode(file_get_contents("php://input"), true);
            $user = $authMiddleware->requireRole(['Organizer', 'Admin']);
            
            if (!isset($data['id'])) {
                throw new Exception('Tournament ID is required');
            }
            
            $tournamentId = $data['id'];
            
            // Verify ownership
            $checkQuery = "SELECT * FROM tournaments WHERE id = :id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $tournamentId);
            $checkStmt->execute();
            $tournament = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tournament) {
                throw new Exception('Tournament not found');
            }
            
            $roles = array_column($user->roles, 'role_name');
            if ($tournament['organizer_id'] != $user->id && !in_array('Admin', $roles)) {
                throw new Exception('You do not have permission to update this tournament');
            }
            
            // Update tournament
            $updateFields = [];
            $params = [':id' => $tournamentId];
            
            $allowedFields = ['name', 'description', 'game_type', 'format', 'tournament_size', 
                             'max_participants', 'rules', 'match_rules', 'scoring_system', 'entry_fee',
                             'is_public', 'is_featured', 'registration_deadline', 'start_date', 
                             'end_date', 'status', 'visibility'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            
            if (empty($updateFields)) {
                throw new Exception('No valid fields to update');
            }
            
            $updateQuery = "UPDATE tournaments SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute($params);
            
            echo json_encode([
                "success" => true,
                "message" => "Tournament updated successfully"
            ]);
            break;

        case "DELETE":
            $user = $authMiddleware->requireRole(['Organizer', 'Admin']);
            
            if (!isset($_GET['id'])) {
                throw new Exception('Tournament ID is required');
            }
            
            $tournamentId = $_GET['id'];
            
            // Verify ownership
            $checkQuery = "SELECT * FROM tournaments WHERE id = :id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $tournamentId);
            $checkStmt->execute();
            $tournament = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tournament) {
                throw new Exception('Tournament not found');
            }
            
            $roles = array_column($user->roles, 'role_name');
            if ($tournament['organizer_id'] != $user->id && !in_array('Admin', $roles)) {
                throw new Exception('You do not have permission to delete this tournament');
            }
            
            // Delete tournament (cascade will handle related records)
            $deleteQuery = "DELETE FROM tournaments WHERE id = :id";
            $deleteStmt = $db->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $tournamentId);
            $deleteStmt->execute();
            
            echo json_encode([
                "success" => true,
                "message" => "Tournament deleted successfully"
            ]);
            break;

        default:
            throw new Exception('Method not supported');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
