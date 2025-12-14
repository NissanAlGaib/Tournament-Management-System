<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

include "./database.php";
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
                $user = $authMiddleware->requireAuth();

                // Tournaments user is participating in
                $participatingQuery = "SELECT t.*, tp.registration_status, tp.registered_at 
                                      FROM tournaments t 
                                      INNER JOIN tournament_participants tp ON t.id = tp.tournament_id 
                                      WHERE tp.user_id = :user_id 
                                      ORDER BY t.start_date DESC";
                $stmt = $db->prepare($participatingQuery);
                $userId = $user['user_id'];
                $stmt->bindParam(':user_id', $userId);
                $stmt->execute();
                $participating = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Tournaments user is organizing
                $organizing = [];
                $roles = array_column($user['roles'], 'role_name');
                if (in_array('Organizer', $roles) || in_array('Admin', $roles)) {
                    $organizingQuery = "SELECT t.*, 
                                       (SELECT COUNT(*) FROM tournament_participants tp 
                                        WHERE tp.tournament_id = t.id AND tp.registration_status = 'confirmed') as participant_count
                                       FROM tournaments t 
                                       WHERE t.organizer_id = :user_id 
                                       ORDER BY t.created_at DESC";
                    $orgStmt = $db->prepare($organizingQuery);
                    $userId = $user['user_id'];
                    $orgStmt->bindParam(':user_id', $userId);
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
                             is_public, is_featured, is_team_based, team_size, registration_start, registration_deadline, 
                             allow_late_registration, start_date, end_date, estimated_duration_hours, 
                             status, visibility)
                            VALUES 
                            (:organizer_id, :name, :description, :game_type, :format, :tournament_size,
                             :max_participants, :rules, :match_rules, :scoring_system, :entry_fee,
                             :is_public, :is_featured, :is_team_based, :team_size, :registration_start, :registration_deadline,
                             :allow_late_registration, :start_date, :end_date, :estimated_duration_hours,
                             :status, :visibility)";

                    $stmt = $db->prepare($query);
                    $organizerId = $user['user_id'];
                    $description = $data['description'] ?? null;
                    $gameType = $data['game_type'] ?? null;
                    $format = $data['format'] ?? 'single_elimination';
                    $tournamentSize = $data['tournament_size'] ?? 16;
                    $maxParticipants = $data['max_participants'] ?? null;
                    $rules = $data['rules'] ?? null;
                    $matchRules = $data['match_rules'] ?? null;
                    $scoringSystem = $data['scoring_system'] ?? 'best_of_3';
                    $entryFee = $data['entry_fee'] ?? 0.00;
                    $isPublic = $data['is_public'] ?? 1;
                    $isFeatured = $data['is_featured'] ?? 0;
                    $isTeamBased = $data['is_team_based'] ?? 0;
                    $teamSize = $data['team_size'] ?? null;
                    $registrationStart = $data['registration_start'] ?? null;
                    $allowLateReg = $data['allow_late_registration'] ?? 0;
                    $endDate = $data['end_date'] ?? null;
                    $estimatedDuration = $data['estimated_duration_hours'] ?? null;
                    $status = $data['status'] ?? 'draft';
                    $visibility = $data['visibility'] ?? 'public';

                    $stmt->bindParam(':organizer_id', $organizerId);
                    $stmt->bindParam(':name', $data['name']);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':game_type', $gameType);
                    $stmt->bindParam(':format', $format);
                    $stmt->bindParam(':tournament_size', $tournamentSize);
                    $stmt->bindParam(':max_participants', $maxParticipants);
                    $stmt->bindParam(':rules', $rules);
                    $stmt->bindParam(':match_rules', $matchRules);
                    $stmt->bindParam(':scoring_system', $scoringSystem);
                    $stmt->bindParam(':entry_fee', $entryFee);
                    $stmt->bindParam(':is_public', $isPublic);
                    $stmt->bindParam(':is_featured', $isFeatured);
                    $stmt->bindParam(':is_team_based', $isTeamBased);
                    $stmt->bindParam(':team_size', $teamSize);
                    $stmt->bindParam(':registration_start', $registrationStart);
                    $stmt->bindParam(':registration_deadline', $data['registration_deadline']);
                    $stmt->bindParam(':allow_late_registration', $allowLateReg);
                    $stmt->bindParam(':start_date', $data['start_date']);
                    $stmt->bindParam(':end_date', $endDate);
                    $stmt->bindParam(':estimated_duration_hours', $estimatedDuration);
                    $stmt->bindParam(':status', $status);
                    $stmt->bindParam(':visibility', $visibility);

                    $stmt->execute();
                    $tournamentId = $db->lastInsertId();

                    // Add prizes if provided
                    if (isset($data['prizes']) && is_array($data['prizes'])) {
                        $prizeQuery = "INSERT INTO tournament_prizes 
                                      (tournament_id, placement, prize_type, prize_amount, currency, prize_description)
                                      VALUES (:tournament_id, :placement, :prize_type, :prize_amount, :currency, :prize_description)";
                        $prizeStmt = $db->prepare($prizeQuery);

                        foreach ($data['prizes'] as $prize) {
                            $prizeType = $prize['type'] ?? 'cash';
                            $prizeAmount = $prize['amount'] ?? 0;
                            $prizeCurrency = $prize['currency'] ?? 'USD';
                            $prizeDescription = $prize['description'] ?? null;

                            $prizeStmt->bindParam(':tournament_id', $tournamentId);
                            $prizeStmt->bindParam(':placement', $prize['placement']);
                            $prizeStmt->bindParam(':prize_type', $prizeType);
                            $prizeStmt->bindParam(':prize_amount', $prizeAmount);
                            $prizeStmt->bindParam(':currency', $prizeCurrency);
                            $prizeStmt->bindParam(':prize_description', $prizeDescription);
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
                $user = $authMiddleware->requireAuth();

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

                // Prevent organizers from joining their own tournaments
                if ($tournament['organizer_id'] == $user['user_id']) {
                    throw new Exception('You cannot join your own tournament');
                }

                if ($tournament['status'] !== 'open') {
                    throw new Exception('Tournament is not open for registration');
                }

                // Check if already registered
                $checkQuery = "SELECT * FROM tournament_participants 
                              WHERE tournament_id = :tournament_id AND user_id = :user_id";
                $checkStmt = $db->prepare($checkQuery);
                $userId = $user['user_id'];
                $checkStmt->bindParam(':tournament_id', $tournamentId);
                $checkStmt->bindParam(':user_id', $userId);
                $checkStmt->execute();

                if ($checkStmt->rowCount() > 0) {
                    throw new Exception('Already registered for this tournament');
                }

                // Check if tournament is full
                if ($tournament['max_participants'] && $tournament['current_participants'] >= $tournament['max_participants']) {
                    throw new Exception('Tournament is full');
                }

                $db->beginTransaction();
                
                try {
                    $teamId = null;
                    
                    // Handle team-based registration
                    if (isset($data['create_team']) && $data['create_team']) {
                        // Create new team
                        if (!isset($data['team_name'])) {
                            throw new Exception('Team name is required');
                        }
                        
                        $teamQuery = "INSERT INTO tournament_teams 
                                     (tournament_id, team_name, team_tag, captain_user_id, team_status)
                                     VALUES (:tournament_id, :team_name, :team_tag, :captain_id, 'active')";
                        $teamStmt = $db->prepare($teamQuery);
                        $teamTag = $data['team_tag'] ?? null;
                        $teamStmt->bindParam(':tournament_id', $tournamentId);
                        $teamStmt->bindParam(':team_name', $data['team_name']);
                        $teamStmt->bindParam(':team_tag', $teamTag);
                        $teamStmt->bindParam(':captain_id', $userId);
                        $teamStmt->execute();
                        $teamId = $db->lastInsertId();
                        
                        // Add captain as team member
                        $memberQuery = "INSERT INTO tournament_team_members 
                                       (team_id, user_id, role) VALUES (:team_id, :user_id, 'captain')";
                        $memberStmt = $db->prepare($memberQuery);
                        $memberStmt->bindParam(':team_id', $teamId);
                        $memberStmt->bindParam(':user_id', $userId);
                        $memberStmt->execute();
                        
                        // Add team members if provided
                        if (isset($data['team_members']) && is_array($data['team_members'])) {
                            foreach ($data['team_members'] as $memberUsername) {
                                $memberUsername = trim($memberUsername);
                                if (empty($memberUsername)) continue;
                                
                                // Find user by username
                                $userQuery = "SELECT id FROM users WHERE username = :username";
                                $userStmt = $db->prepare($userQuery);
                                $userStmt->bindParam(':username', $memberUsername);
                                $userStmt->execute();
                                $memberUser = $userStmt->fetch(PDO::FETCH_ASSOC);
                                
                                if (!$memberUser) {
                                    throw new Exception("User '$memberUsername' not found");
                                }
                                
                                $memberUserId = $memberUser['id'];
                                
                                // Check if member already registered for tournament
                                $memberCheckQuery = "SELECT * FROM tournament_participants 
                                                    WHERE tournament_id = :tournament_id AND user_id = :user_id";
                                $memberCheckStmt = $db->prepare($memberCheckQuery);
                                $memberCheckStmt->bindParam(':tournament_id', $tournamentId);
                                $memberCheckStmt->bindParam(':user_id', $memberUserId);
                                $memberCheckStmt->execute();
                                
                                if ($memberCheckStmt->rowCount() > 0) {
                                    throw new Exception("User '$memberUsername' is already registered for this tournament");
                                }
                                
                                // Add member to team
                                $teamMemberQuery = "INSERT INTO tournament_team_members 
                                                   (team_id, user_id, role) VALUES (:team_id, :user_id, 'member')";
                                $teamMemberStmt = $db->prepare($teamMemberQuery);
                                $teamMemberStmt->bindParam(':team_id', $teamId);
                                $teamMemberStmt->bindParam(':user_id', $memberUserId);
                                $teamMemberStmt->execute();
                                
                                // Register team member for tournament
                                $memberRegQuery = "INSERT INTO tournament_participants 
                                                  (tournament_id, user_id, registration_status, payment_status)
                                                  VALUES (:tournament_id, :user_id, 'confirmed', 'pending')";
                                $memberRegStmt = $db->prepare($memberRegQuery);
                                $memberRegStmt->bindParam(':tournament_id', $tournamentId);
                                $memberRegStmt->bindParam(':user_id', $memberUserId);
                                $memberRegStmt->execute();
                            }
                        }
                    } elseif (isset($data['team_id'])) {
                        // Join existing team
                        $teamId = $data['team_id'];
                    }

                    // Register participant (captain)
                    $notes = $data['notes'] ?? null;
                    $registerQuery = "INSERT INTO tournament_participants 
                                     (tournament_id, user_id, registration_status, payment_status, registration_notes)
                                     VALUES (:tournament_id, :user_id, 'confirmed', 'pending', :notes)";
                    $registerStmt = $db->prepare($registerQuery);
                    $registerStmt->bindParam(':tournament_id', $tournamentId);
                    $registerStmt->bindParam(':user_id', $userId);
                    $registerStmt->bindParam(':notes', $notes);
                    $registerStmt->execute();
                    
                    $db->commit();

                    echo json_encode([
                        "success" => true,
                        "message" => "Successfully registered for tournament",
                        "team_id" => $teamId
                    ]);
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            } elseif ($action === 'invite_to_team') {
                // Invite player to team
                $user = $authMiddleware->requireAuth();
                
                if (!isset($data['team_id']) || !isset($data['username'])) {
                    throw new Exception('Team ID and username are required');
                }
                
                // Verify team exists and user is captain
                $teamQuery = "SELECT * FROM tournament_teams WHERE id = :team_id AND captain_user_id = :user_id";
                $teamStmt = $db->prepare($teamQuery);
                $teamStmt->bindParam(':team_id', $data['team_id']);
                $teamStmt->bindParam(':user_id', $user['user_id']);
                $teamStmt->execute();
                $team = $teamStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$team) {
                    throw new Exception('Team not found or you are not the captain');
                }
                
                // Find user by username
                $userQuery = "SELECT id FROM users WHERE username = :username";
                $userStmt = $db->prepare($userQuery);
                $userStmt->bindParam(':username', $data['username']);
                $userStmt->execute();
                $invitedUser = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$invitedUser) {
                    throw new Exception('User not found');
                }
                
                // Check if user is already in team
                $checkQuery = "SELECT * FROM tournament_team_members WHERE team_id = :team_id AND user_id = :user_id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':team_id', $data['team_id']);
                $checkStmt->bindParam(':user_id', $invitedUser['id']);
                $checkStmt->execute();
                
                if ($checkStmt->rowCount() > 0) {
                    throw new Exception('User is already in this team');
                }
                
                // Add to team
                $role = $data['role'] ?? 'member';
                $addQuery = "INSERT INTO tournament_team_members (team_id, user_id, role) 
                            VALUES (:team_id, :user_id, :role)";
                $addStmt = $db->prepare($addQuery);
                $addStmt->bindParam(':team_id', $data['team_id']);
                $addStmt->bindParam(':user_id', $invitedUser['id']);
                $addStmt->bindParam(':role', $role);
                $addStmt->execute();
                
                // Register user for tournament if not already registered
                $checkRegQuery = "SELECT * FROM tournament_participants 
                                 WHERE tournament_id = :tournament_id AND user_id = :user_id";
                $checkRegStmt = $db->prepare($checkRegQuery);
                $checkRegStmt->bindParam(':tournament_id', $team['tournament_id']);
                $checkRegStmt->bindParam(':user_id', $invitedUser['id']);
                $checkRegStmt->execute();
                
                if ($checkRegStmt->rowCount() == 0) {
                    $regQuery = "INSERT INTO tournament_participants 
                                (tournament_id, user_id, registration_status, payment_status)
                                VALUES (:tournament_id, :user_id, 'confirmed', 'pending')";
                    $regStmt = $db->prepare($regQuery);
                    $regStmt->bindParam(':tournament_id', $team['tournament_id']);
                    $regStmt->bindParam(':user_id', $invitedUser['id']);
                    $regStmt->execute();
                }
                
                echo json_encode([
                    "success" => true,
                    "message" => "Player added to team successfully"
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

                $roles = array_column($user['roles'], 'role_name');
                if ($tournament['organizer_id'] != $user['user_id'] && !in_array('Admin', $roles)) {
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

            $roles = array_column($user['roles'], 'role_name');
            if ($tournament['organizer_id'] != $user['user_id'] && !in_array('Admin', $roles)) {
                throw new Exception('You do not have permission to update this tournament');
            }

            // Update tournament
            $updateFields = [];
            $params = [':id' => $tournamentId];

            $allowedFields = [
                'name',
                'description',
                'game_type',
                'format',
                'tournament_size',
                'max_participants',
                'rules',
                'match_rules',
                'scoring_system',
                'entry_fee',
                'is_public',
                'is_featured',
                'registration_deadline',
                'start_date',
                'end_date',
                'status',
                'visibility'
            ];

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

            $roles = array_column($user['roles'], 'role_name');
            if ($tournament['organizer_id'] != $user['user_id'] && !in_array('Admin', $roles)) {
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
