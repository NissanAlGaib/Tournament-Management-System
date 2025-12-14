<?php
/**
 * Tournament API Integration Example
 * 
 * This file demonstrates how to integrate the tournament management
 * database schema with the existing Tournament Management System.
 * 
 * Place this in: backend/api/tournament_api.php
 */

// This is an example/template file - not a complete implementation
// Developers should customize based on their specific needs

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../api/database.php';
require_once '../middleware/auth_middleware.php';

// Get authentication middleware
$auth = getAuthMiddleware();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $database = new Database();
    $db = $database->getConnection();

    switch ($action) {
        
        // ==================================================
        // CREATE TOURNAMENT (Organizer/Admin only)
        // ==================================================
        case 'create_tournament':
            // Require Organizer or Admin role
            $user = $auth->requireRole(['Organizer', 'Admin']);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $query = "INSERT INTO tournaments 
                (organizer_id, name, description, game_type, format, tournament_size, 
                 rules, registration_deadline, start_date, status, visibility) 
                VALUES 
                (:organizer_id, :name, :description, :game_type, :format, :tournament_size,
                 :rules, :registration_deadline, :start_date, :status, :visibility)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':organizer_id', $user->id);
            $stmt->bindParam(':name', htmlspecialchars($data['name']));
            $stmt->bindParam(':description', htmlspecialchars($data['description']));
            $stmt->bindParam(':game_type', htmlspecialchars($data['game_type']));
            $stmt->bindParam(':format', $data['format']);
            $stmt->bindParam(':tournament_size', $data['tournament_size']);
            $stmt->bindParam(':rules', htmlspecialchars($data['rules']));
            $stmt->bindParam(':registration_deadline', $data['registration_deadline']);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':visibility', $data['visibility']);
            
            if ($stmt->execute()) {
                $tournament_id = $db->lastInsertId();
                
                // Add prizes if provided
                if (!empty($data['prizes'])) {
                    $prizeQuery = "INSERT INTO tournament_prizes 
                        (tournament_id, placement, prize_type, prize_amount, currency, prize_description)
                        VALUES (:tournament_id, :placement, :prize_type, :prize_amount, :currency, :prize_description)";
                    $prizeStmt = $db->prepare($prizeQuery);
                    
                    foreach ($data['prizes'] as $prize) {
                        $prizeStmt->bindParam(':tournament_id', $tournament_id);
                        $prizeStmt->bindParam(':placement', $prize['placement']);
                        $prizeStmt->bindParam(':prize_type', $prize['type']);
                        $prizeStmt->bindParam(':prize_amount', $prize['amount']);
                        $prizeStmt->bindParam(':currency', $prize['currency']);
                        $prizeStmt->bindParam(':prize_description', htmlspecialchars($prize['description']));
                        $prizeStmt->execute();
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'tournament_id' => $tournament_id,
                    'message' => 'Tournament created successfully'
                ]);
            } else {
                throw new Exception('Failed to create tournament');
            }
            break;
        
        // ==================================================
        // GET ACTIVE TOURNAMENTS (Public)
        // ==================================================
        case 'get_active_tournaments':
            $query = "SELECT * FROM active_tournaments 
                      WHERE is_public = 1 
                      ORDER BY start_date ASC 
                      LIMIT 50";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'tournaments' => $tournaments
            ]);
            break;
        
        // ==================================================
        // GET TOURNAMENT DETAILS (Public)
        // ==================================================
        case 'get_tournament':
            $tournament_id = $_GET['id'] ?? null;
            
            if (!$tournament_id) {
                throw new Exception('Tournament ID required');
            }
            
            // Get tournament details
            $query = "SELECT t.*, u.username as organizer_name 
                      FROM tournaments t 
                      LEFT JOIN users u ON t.organizer_id = u.id 
                      WHERE t.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $tournament_id);
            $stmt->execute();
            $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tournament) {
                throw new Exception('Tournament not found');
            }
            
            // Get prizes
            $prizeQuery = "SELECT * FROM tournament_prizes WHERE tournament_id = :id ORDER BY placement";
            $prizeStmt = $db->prepare($prizeQuery);
            $prizeStmt->bindParam(':id', $tournament_id);
            $prizeStmt->execute();
            $tournament['prizes'] = $prizeStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get participants count
            $participantQuery = "SELECT COUNT(*) as count FROM tournament_participants 
                                WHERE tournament_id = :id AND registration_status = 'confirmed'";
            $participantStmt = $db->prepare($participantQuery);
            $participantStmt->bindParam(':id', $tournament_id);
            $participantStmt->execute();
            $tournament['participants_count'] = $participantStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode([
                'success' => true,
                'tournament' => $tournament
            ]);
            break;
        
        // ==================================================
        // REGISTER FOR TOURNAMENT (Authenticated users)
        // ==================================================
        case 'register_tournament':
            $user = $auth->authenticate();
            $data = json_decode(file_get_contents('php://input'), true);
            $tournament_id = $data['tournament_id'];
            
            // Check if tournament exists and is open
            $tournamentQuery = "SELECT * FROM tournaments WHERE id = :id AND status = 'open'";
            $tournamentStmt = $db->prepare($tournamentQuery);
            $tournamentStmt->bindParam(':id', $tournament_id);
            $tournamentStmt->execute();
            $tournament = $tournamentStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tournament) {
                throw new Exception('Tournament not found or not open for registration');
            }
            
            // Check if already registered
            $checkQuery = "SELECT * FROM tournament_participants 
                          WHERE tournament_id = :tournament_id AND user_id = :user_id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':tournament_id', $tournament_id);
            $checkStmt->bindParam(':user_id', $user->id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                throw new Exception('Already registered for this tournament');
            }
            
            // Register participant
            $query = "INSERT INTO tournament_participants 
                      (tournament_id, user_id, registration_status, payment_status) 
                      VALUES (:tournament_id, :user_id, 'confirmed', 'pending')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':tournament_id', $tournament_id);
            $stmt->bindParam(':user_id', $user->id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Successfully registered for tournament'
                ]);
            } else {
                throw new Exception('Failed to register');
            }
            break;
        
        // ==================================================
        // GET TOURNAMENT LEADERBOARD (Public)
        // ==================================================
        case 'get_leaderboard':
            $tournament_id = $_GET['tournament_id'] ?? null;
            
            if (!$tournament_id) {
                throw new Exception('Tournament ID required');
            }
            
            $query = "SELECT * FROM tournament_leaderboard 
                      WHERE tournament_id = :tournament_id 
                      ORDER BY current_rank ASC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':tournament_id', $tournament_id);
            $stmt->execute();
            $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'leaderboard' => $leaderboard
            ]);
            break;
        
        // ==================================================
        // UPDATE MATCH RESULT (Organizer/Admin only)
        // ==================================================
        case 'update_match_result':
            $user = $auth->requireRole(['Organizer', 'Admin']);
            $data = json_decode(file_get_contents('php://input'), true);
            
            $match_id = $data['match_id'];
            $winner_id = $data['winner_id'];
            $p1_score = $data['participant1_score'];
            $p2_score = $data['participant2_score'];
            
            // Use stored procedure to update match and standings
            $query = "CALL update_match_result(:match_id, :winner_id, :p1_score, :p2_score)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':match_id', $match_id);
            $stmt->bindParam(':winner_id', $winner_id);
            $stmt->bindParam(':p1_score', $p1_score);
            $stmt->bindParam(':p2_score', $p2_score);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Match result updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update match result');
            }
            break;
        
        // ==================================================
        // MY TOURNAMENTS (Authenticated users)
        // ==================================================
        case 'my_tournaments':
            $user = $auth->authenticate();
            
            // Get tournaments user is participating in
            $query = "SELECT t.*, tp.registration_status, tp.registered_at 
                      FROM tournaments t 
                      INNER JOIN tournament_participants tp ON t.id = tp.tournament_id 
                      WHERE tp.user_id = :user_id 
                      ORDER BY t.start_date DESC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user->id);
            $stmt->execute();
            $participating = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get tournaments user is organizing (if Organizer)
            $organizing = [];
            if (in_array('Organizer', array_column($user->roles, 'role_name')) || 
                in_array('Admin', array_column($user->roles, 'role_name'))) {
                $orgQuery = "SELECT * FROM tournaments 
                            WHERE organizer_id = :user_id 
                            ORDER BY start_date DESC";
                $orgStmt = $db->prepare($orgQuery);
                $orgStmt->bindParam(':user_id', $user->id);
                $orgStmt->execute();
                $organizing = $orgStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            echo json_encode([
                'success' => true,
                'participating' => $participating,
                'organizing' => $organizing
            ]);
            break;
        
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
