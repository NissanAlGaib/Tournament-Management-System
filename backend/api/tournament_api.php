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

                // Tournaments user is participating in with team info
                $participatingQuery = "SELECT t.*, tp.registration_status, tp.registered_at,
                                      COUNT(DISTINCT tp2.id) as registered_participants,
                                      tt.team_name, tt.team_tag
                                      FROM tournaments t 
                                      INNER JOIN tournament_participants tp ON t.id = tp.tournament_id 
                                      LEFT JOIN tournament_participants tp2 ON t.id = tp2.tournament_id AND tp2.registration_status = 'confirmed'
                                      LEFT JOIN tournament_team_members ttm ON tp.user_id = ttm.user_id
                                      LEFT JOIN tournament_teams tt ON ttm.team_id = tt.id AND tt.tournament_id = t.id
                                      WHERE tp.user_id = :user_id 
                                      GROUP BY t.id, tp.id, tt.id
                                      ORDER BY t.start_date DESC";
                $stmt = $db->prepare($participatingQuery);
                $userId = $user['user_id'];
                $stmt->bindParam(':user_id', $userId);
                $stmt->execute();
                $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    "success" => true,
                    "tournaments" => $tournaments
                ]);
            } elseif ($action === 'my-teams') {
                // Get teams where user is captain
                $user = $authMiddleware->requireAuth();

                $query = "SELECT tt.*, t.name as tournament_name, t.status as tournament_status
                         FROM tournament_teams tt
                         INNER JOIN tournaments t ON tt.tournament_id = t.id
                         WHERE tt.captain_user_id = :user_id
                         ORDER BY tt.created_at DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user['user_id']);
                $stmt->execute();
                $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    "success" => true,
                    "teams" => $teams
                ]);
            } elseif ($action === 'team-members' && isset($_GET['team_id'])) {
                // Get team members
                $user = $authMiddleware->requireAuth();

                $query = "SELECT ttm.*, u.username
                         FROM tournament_team_members ttm
                         INNER JOIN users u ON ttm.user_id = u.id
                         WHERE ttm.team_id = :team_id
                         ORDER BY 
                           CASE ttm.role 
                             WHEN 'captain' THEN 1
                             WHEN 'co_captain' THEN 2
                             ELSE 3
                           END, ttm.joined_at";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':team_id', $_GET['team_id']);
                $stmt->execute();
                $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    "success" => true,
                    "members" => $members
                ]);
            } elseif ($action === 'notifications') {
                // Get user notifications
                $user = $authMiddleware->requireAuth();

                $query = "SELECT tn.*, t.name as tournament_name
                         FROM tournament_notifications tn
                         INNER JOIN tournaments t ON tn.tournament_id = t.id
                         WHERE (tn.target_audience = 'all' OR 
                               (tn.target_audience = 'participants' AND EXISTS (
                                   SELECT 1 FROM tournament_participants tp 
                                   WHERE tp.tournament_id = tn.tournament_id AND tp.user_id = :user_id
                               )) OR
                               (tn.target_audience = 'specific_user' AND tn.target_user_id = :user_id))
                         ORDER BY tn.created_at DESC
                         LIMIT 50";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user['user_id']);
                $stmt->execute();
                $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    "success" => true,
                    "data" => $notifications
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
            } elseif ($action === 'organized-tournaments') {
                // Get tournaments organized by current user
                $user = $authMiddleware->requireRole(['Organizer', 'Admin']);

                $query = "SELECT t.*, 
                         COUNT(DISTINCT CASE WHEN tp.registration_status = 'confirmed' THEN tp.id END) as confirmed_count,
                         COUNT(DISTINCT CASE WHEN tp.registration_status = 'pending' THEN tp.id END) as pending_count,
                         COUNT(DISTINCT CASE WHEN tp.registration_status = 'rejected' THEN tp.id END) as rejected_count
                         FROM tournaments t
                         LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id
                         WHERE t.organizer_id = :user_id
                         GROUP BY t.id
                         ORDER BY t.created_at DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user['user_id']);
                $stmt->execute();
                $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    "success" => true,
                    "tournaments" => $tournaments
                ]);
            } elseif ($action === 'tournament-participants' && isset($_GET['tournament_id'])) {
                // Get participants for a tournament (Organizer/Admin only)
                $user = $authMiddleware->requireRole(['Organizer', 'Admin']);
                $tournamentId = $_GET['tournament_id'];

                // Verify ownership or admin
                $checkQuery = "SELECT organizer_id FROM tournaments WHERE id = :id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':id', $tournamentId);
                $checkStmt->execute();
                $tournament = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$tournament) {
                    throw new Exception('Tournament not found');
                }

                $roles = array_column($user['roles'], 'role_name');
                if ($tournament['organizer_id'] != $user['user_id'] && !in_array('Admin', $roles)) {
                    throw new Exception('You do not have permission to view participants for this tournament');
                }

                // Get participants with user details
                $query = "SELECT tp.*, u.username, u.email,
                         tt.team_name, tt.id as team_id
                         FROM tournament_participants tp
                         INNER JOIN users u ON tp.user_id = u.id
                         LEFT JOIN tournament_team_members ttm ON tp.user_id = ttm.user_id
                         LEFT JOIN tournament_teams tt ON ttm.team_id = tt.id AND tt.tournament_id = tp.tournament_id
                         WHERE tp.tournament_id = :tournament_id
                         ORDER BY tp.registration_status, tp.registered_at DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':tournament_id', $tournamentId);
                $stmt->execute();
                $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    "success" => true,
                    "participants" => $participants
                ]);
            } elseif ($action === 'tournament-teams' && isset($_GET['tournament_id'])) {
                // Get teams for a tournament (Organizer/Admin only)
                $user = $authMiddleware->requireRole(['Organizer', 'Admin']);
                $tournamentId = $_GET['tournament_id'];

                // Verify ownership or admin
                $checkQuery = "SELECT organizer_id FROM tournaments WHERE id = :id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':id', $tournamentId);
                $checkStmt->execute();
                $tournament = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$tournament) {
                    throw new Exception('Tournament not found');
                }

                $roles = array_column($user['roles'], 'role_name');
                if ($tournament['organizer_id'] != $user['user_id'] && !in_array('Admin', $roles)) {
                    throw new Exception('You do not have permission to view teams for this tournament');
                }

                // Get teams with member counts
                $query = "SELECT tt.*, u.username as captain_name,
                         COUNT(DISTINCT ttm.id) as member_count
                         FROM tournament_teams tt
                         INNER JOIN users u ON tt.captain_user_id = u.id
                         LEFT JOIN tournament_team_members ttm ON tt.id = ttm.team_id
                         WHERE tt.tournament_id = :tournament_id
                         GROUP BY tt.id
                         ORDER BY tt.created_at DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':tournament_id', $tournamentId);
                $stmt->execute();
                $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    "success" => true,
                    "teams" => $teams
                ]);
            } elseif ($action === 'tournament-bracket' && isset($_GET['tournament_id'])) {
                // Get bracket for a tournament (Organizer/Admin only)
                $user = $authMiddleware->requireRole(['Organizer', 'Admin']);
                $tournamentId = $_GET['tournament_id'];

                // Verify ownership or admin
                $checkQuery = "SELECT organizer_id, format, tournament_size FROM tournaments WHERE id = :id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':id', $tournamentId);
                $checkStmt->execute();
                $tournament = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$tournament) {
                    throw new Exception('Tournament not found');
                }

                $roles = array_column($user['roles'], 'role_name');
                if ($tournament['organizer_id'] != $user['user_id'] && !in_array('Admin', $roles)) {
                    throw new Exception('You do not have permission to view bracket for this tournament');
                }

                // Get all matches with participant details
                $query = "SELECT m.*, 
                         p1.user_id as participant1_user_id, u1.username as participant1_name,
                         p2.user_id as participant2_user_id, u2.username as participant2_name,
                         pw.user_id as winner_user_id, uw.username as winner_name,
                         tt1.team_name as participant1_team_name, tt1.team_tag as participant1_team_tag,
                         tt2.team_name as participant2_team_name, tt2.team_tag as participant2_team_tag
                         FROM matches m
                         LEFT JOIN tournament_participants p1 ON m.participant1_id = p1.id
                         LEFT JOIN users u1 ON p1.user_id = u1.id
                         LEFT JOIN tournament_participants p2 ON m.participant2_id = p2.id
                         LEFT JOIN users u2 ON p2.user_id = u2.id
                         LEFT JOIN tournament_participants pw ON m.winner_id = pw.id
                         LEFT JOIN users uw ON pw.user_id = uw.id
                         LEFT JOIN tournament_team_members ttm1 ON p1.user_id = ttm1.user_id
                         LEFT JOIN tournament_teams tt1 ON ttm1.team_id = tt1.id AND tt1.tournament_id = m.tournament_id
                         LEFT JOIN tournament_team_members ttm2 ON p2.user_id = ttm2.user_id
                         LEFT JOIN tournament_teams tt2 ON ttm2.team_id = tt2.id AND tt2.tournament_id = m.tournament_id
                         WHERE m.tournament_id = :tournament_id
                         ORDER BY m.round_number, m.match_number";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':tournament_id', $tournamentId);
                $stmt->execute();
                $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    "success" => true,
                    "tournament" => $tournament,
                    "matches" => $matches
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
            } elseif ($action === 'withdraw') {
                // Withdraw from tournament
                $user = $authMiddleware->requireAuth();

                if (!isset($data['tournament_id'])) {
                    throw new Exception('Tournament ID is required');
                }

                $tournamentId = $data['tournament_id'];

                // Check if user is registered
                $checkQuery = "SELECT tp.*, t.status FROM tournament_participants tp
                              INNER JOIN tournaments t ON tp.tournament_id = t.id
                              WHERE tp.tournament_id = :tournament_id AND tp.user_id = :user_id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':tournament_id', $tournamentId);
                $checkStmt->bindParam(':user_id', $user['user_id']);
                $checkStmt->execute();
                $participant = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$participant) {
                    throw new Exception('You are not registered for this tournament');
                }

                // Cannot withdraw from ongoing or completed tournaments
                if ($participant['status'] === 'ongoing' || $participant['status'] === 'completed') {
                    throw new Exception('Cannot withdraw from ongoing or completed tournaments');
                }

                // Update participant status
                $withdrawQuery = "UPDATE tournament_participants 
                                 SET registration_status = 'withdrawn'
                                 WHERE tournament_id = :tournament_id AND user_id = :user_id";
                $withdrawStmt = $db->prepare($withdrawQuery);
                $withdrawStmt->bindParam(':tournament_id', $tournamentId);
                $withdrawStmt->bindParam(':user_id', $user['user_id']);
                $withdrawStmt->execute();

                // Decrement participant count
                $updateCountQuery = "UPDATE tournaments 
                                    SET current_participants = current_participants - 1
                                    WHERE id = :tournament_id AND current_participants > 0";
                $updateCountStmt = $db->prepare($updateCountQuery);
                $updateCountStmt->bindParam(':tournament_id', $tournamentId);
                $updateCountStmt->execute();

                echo json_encode([
                    "success" => true,
                    "message" => "Successfully withdrawn from tournament"
                ]);
            } elseif ($action === 'add-team-member') {
                // Add member to team (captain only)
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
                $newMember = $userStmt->fetch(PDO::FETCH_ASSOC);

                if (!$newMember) {
                    throw new Exception('User not found');
                }

                // Check if user is already in team
                $checkQuery = "SELECT * FROM tournament_team_members WHERE team_id = :team_id AND user_id = :user_id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':team_id', $data['team_id']);
                $checkStmt->bindParam(':user_id', $newMember['id']);
                $checkStmt->execute();

                if ($checkStmt->rowCount() > 0) {
                    throw new Exception('User is already in this team');
                }

                $db->beginTransaction();

                try {
                    // Add to team
                    $addQuery = "INSERT INTO tournament_team_members (team_id, user_id, role) 
                                VALUES (:team_id, :user_id, 'member')";
                    $addStmt = $db->prepare($addQuery);
                    $addStmt->bindParam(':team_id', $data['team_id']);
                    $addStmt->bindParam(':user_id', $newMember['id']);
                    $addStmt->execute();

                    // Register user for tournament if not already registered
                    $checkRegQuery = "SELECT * FROM tournament_participants 
                                     WHERE tournament_id = :tournament_id AND user_id = :user_id";
                    $checkRegStmt = $db->prepare($checkRegQuery);
                    $checkRegStmt->bindParam(':tournament_id', $team['tournament_id']);
                    $checkRegStmt->bindParam(':user_id', $newMember['id']);
                    $checkRegStmt->execute();

                    if ($checkRegStmt->rowCount() == 0) {
                        $regQuery = "INSERT INTO tournament_participants 
                                    (tournament_id, user_id, registration_status, payment_status)
                                    VALUES (:tournament_id, :user_id, 'confirmed', 'pending')";
                        $regStmt = $db->prepare($regQuery);
                        $regStmt->bindParam(':tournament_id', $team['tournament_id']);
                        $regStmt->bindParam(':user_id', $newMember['id']);
                        $regStmt->execute();
                    }

                    $db->commit();

                    echo json_encode([
                        "success" => true,
                        "message" => "Member added successfully"
                    ]);
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            } elseif ($action === 'remove-team-member') {
                // Remove member from team (captain only)
                $user = $authMiddleware->requireAuth();

                if (!isset($data['team_id']) || !isset($data['member_id'])) {
                    throw new Exception('Team ID and member ID are required');
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

                // Cannot remove captain
                $memberQuery = "SELECT * FROM tournament_team_members WHERE id = :member_id AND team_id = :team_id";
                $memberStmt = $db->prepare($memberQuery);
                $memberStmt->bindParam(':member_id', $data['member_id']);
                $memberStmt->bindParam(':team_id', $data['team_id']);
                $memberStmt->execute();
                $member = $memberStmt->fetch(PDO::FETCH_ASSOC);

                if (!$member) {
                    throw new Exception('Member not found in this team');
                }

                if ($member['role'] === 'captain') {
                    throw new Exception('Cannot remove captain from team');
                }

                // Remove from team
                $removeQuery = "DELETE FROM tournament_team_members WHERE id = :member_id";
                $removeStmt = $db->prepare($removeQuery);
                $removeStmt->bindParam(':member_id', $data['member_id']);
                $removeStmt->execute();

                echo json_encode([
                    "success" => true,
                    "message" => "Member removed successfully"
                ]);
            } elseif ($action === 'mark-notification-read') {
                // Mark notification as read
                $user = $authMiddleware->requireAuth();

                if (!isset($data['notification_id'])) {
                    throw new Exception('Notification ID is required');
                }

                $updateQuery = "UPDATE tournament_notifications 
                               SET is_read = 1
                               WHERE id = :notification_id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':notification_id', $data['notification_id']);
                $updateStmt->execute();

                echo json_encode([
                    "success" => true,
                    "message" => "Notification marked as read"
                ]);
            } elseif ($action === 'mark_notification_read') {
                // Mark notification as read (alternative action name)
                $user = $authMiddleware->requireAuth();

                if (!isset($data['notification_id'])) {
                    throw new Exception('Notification ID is required');
                }

                $updateQuery = "UPDATE tournament_notifications 
                               SET is_read = 1
                               WHERE id = :notification_id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':notification_id', $data['notification_id']);
                $updateStmt->execute();

                echo json_encode([
                    "success" => true,
                    "message" => "Notification marked as read"
                ]);
            } elseif ($action === 'mark_all_notifications_read') {
                // Mark all notifications as read for current user
                $user = $authMiddleware->requireAuth();

                // Update all unread notifications for this user
                $updateQuery = "UPDATE tournament_notifications 
                               SET is_read = 1
                               WHERE is_read = 0 
                               AND (target_audience = 'all' OR 
                                   (target_audience = 'participants' AND EXISTS (
                                       SELECT 1 FROM tournament_participants tp 
                                       WHERE tp.tournament_id = tournament_notifications.tournament_id 
                                       AND tp.user_id = :user_id
                                   )) OR
                                   (target_audience = 'specific_user' AND target_user_id = :user_id))";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':user_id', $user['user_id']);
                $updateStmt->execute();

                echo json_encode([
                    "success" => true,
                    "message" => "All notifications marked as read"
                ]);
            } elseif ($action === 'approve-participant') {
                // Approve participant registration (Organizer/Admin only)
                $user = $authMiddleware->requireRole(['Organizer', 'Admin']);

                if (!isset($data['participant_id'])) {
                    throw new Exception('Participant ID is required');
                }

                // Get participant and tournament info
                $checkQuery = "SELECT tp.*, t.organizer_id, t.max_participants, t.current_participants
                              FROM tournament_participants tp
                              INNER JOIN tournaments t ON tp.tournament_id = t.id
                              WHERE tp.id = :participant_id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':participant_id', $data['participant_id']);
                $checkStmt->execute();
                $participant = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$participant) {
                    throw new Exception('Participant not found');
                }

                // Verify ownership or admin
                $roles = array_column($user['roles'], 'role_name');
                if ($participant['organizer_id'] != $user['user_id'] && !in_array('Admin', $roles)) {
                    throw new Exception('You do not have permission to manage this tournament');
                }

                // Check if tournament is full
                if ($participant['max_participants'] && $participant['current_participants'] >= $participant['max_participants']) {
                    throw new Exception('Tournament is full');
                }

                // Update participant status to confirmed
                $updateQuery = "UPDATE tournament_participants 
                               SET registration_status = 'confirmed'
                               WHERE id = :participant_id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':participant_id', $data['participant_id']);
                $updateStmt->execute();

                echo json_encode([
                    "success" => true,
                    "message" => "Participant approved successfully"
                ]);
            } elseif ($action === 'reject-participant') {
                // Reject participant registration (Organizer/Admin only)
                $user = $authMiddleware->requireRole(['Organizer', 'Admin']);

                if (!isset($data['participant_id'])) {
                    throw new Exception('Participant ID is required');
                }

                // Get participant and tournament info
                $checkQuery = "SELECT tp.*, t.organizer_id
                              FROM tournament_participants tp
                              INNER JOIN tournaments t ON tp.tournament_id = t.id
                              WHERE tp.id = :participant_id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':participant_id', $data['participant_id']);
                $checkStmt->execute();
                $participant = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$participant) {
                    throw new Exception('Participant not found');
                }

                // Verify ownership or admin
                $roles = array_column($user['roles'], 'role_name');
                if ($participant['organizer_id'] != $user['user_id'] && !in_array('Admin', $roles)) {
                    throw new Exception('You do not have permission to manage this tournament');
                }

                // Update participant status to rejected
                $updateQuery = "UPDATE tournament_participants 
                               SET registration_status = 'rejected'
                               WHERE id = :participant_id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':participant_id', $data['participant_id']);
                $updateStmt->execute();

                echo json_encode([
                    "success" => true,
                    "message" => "Participant rejected successfully"
                ]);
            } elseif ($action === 'generate-bracket') {
                // Generate bracket for tournament (Organizer/Admin only)
                $user = $authMiddleware->requireRole(['Organizer', 'Admin']);

                if (!isset($data['tournament_id'])) {
                    throw new Exception('Tournament ID is required');
                }

                $tournamentId = $data['tournament_id'];

                // Verify ownership and get tournament details
                $checkQuery = "SELECT t.*, COUNT(DISTINCT tp.id) as confirmed_count
                              FROM tournaments t
                              LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id 
                              AND tp.registration_status = 'confirmed'
                              WHERE t.id = :id
                              GROUP BY t.id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':id', $tournamentId);
                $checkStmt->execute();
                $tournament = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$tournament) {
                    throw new Exception('Tournament not found');
                }

                $roles = array_column($user['roles'], 'role_name');
                if ($tournament['organizer_id'] != $user['user_id'] && !in_array('Admin', $roles)) {
                    throw new Exception('You do not have permission to manage this tournament');
                }

                // Check if bracket already exists
                $existingQuery = "SELECT COUNT(*) as count FROM matches WHERE tournament_id = :id";
                $existingStmt = $db->prepare($existingQuery);
                $existingStmt->bindParam(':id', $tournamentId);
                $existingStmt->execute();
                if ($existingStmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                    throw new Exception('Bracket already exists for this tournament');
                }

                // Get confirmed participants
                $participantsQuery = "SELECT * FROM tournament_participants 
                                     WHERE tournament_id = :id AND registration_status = 'confirmed'
                                     ORDER BY registered_at";
                $participantsStmt = $db->prepare($participantsQuery);
                $participantsStmt->bindParam(':id', $tournamentId);
                $participantsStmt->execute();
                $participants = $participantsStmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($participants) < 2) {
                    throw new Exception('Need at least 2 participants to generate bracket');
                }

                $db->beginTransaction();

                try {
                    // Calculate rounds needed
                    $participantCount = count($participants);
                    $rounds = ceil(log($participantCount, 2));
                    $bracketSize = pow(2, $rounds);
                    
                    // Generate first round matches
                    $matchNumber = 1;
                    $currentRound = 1;
                    $byeMatches = []; // Track bye matches to advance winners
                    
                    // Pair up participants for first round
                    for ($i = 0; $i < $bracketSize; $i += 2) {
                        $participant1 = isset($participants[$i]) ? $participants[$i]['id'] : null;
                        $participant2 = isset($participants[$i + 1]) ? $participants[$i + 1]['id'] : null;
                        
                        // Skip if both slots are empty
                        if (!$participant1 && !$participant2) {
                            continue;
                        }
                        
                        $insertQuery = "INSERT INTO matches 
                                       (tournament_id, round_number, match_number, participant1_id, participant2_id, match_status, winner_id)
                                       VALUES (:tournament_id, :round_number, :match_number, :participant1_id, :participant2_id, :match_status, :winner_id)";
                        $insertStmt = $db->prepare($insertQuery);
                        $insertStmt->bindParam(':tournament_id', $tournamentId);
                        $insertStmt->bindParam(':round_number', $currentRound);
                        $insertStmt->bindParam(':match_number', $matchNumber);
                        $insertStmt->bindParam(':participant1_id', $participant1);
                        $insertStmt->bindParam(':participant2_id', $participant2);
                        
                        // If only one participant, mark as bye and set them as winner
                        if (!$participant1 || !$participant2) {
                            $status = 'bye';
                            $winnerId = $participant1 ?: $participant2;
                        } else {
                            $status = 'scheduled';
                            $winnerId = null;
                        }
                        $insertStmt->bindParam(':match_status', $status);
                        $insertStmt->bindParam(':winner_id', $winnerId);
                        $insertStmt->execute();
                        $matchId = $db->lastInsertId();
                        
                        // If bye match, advance winner to next round
                        if ($status === 'bye' && $winnerId) {
                            $nextRound = $currentRound + 1;
                            $nextMatchNum = ceil($matchNumber / 2);
                            
                            // We'll set this after creating all first round matches
                            $byeMatches[] = [
                                'winner_id' => $winnerId,
                                'next_round' => $nextRound,
                                'next_match_num' => $nextMatchNum,
                                'current_match_num' => $matchNumber
                            ];
                        }
                        
                        $matchNumber++;
                    }
                    
                    // Generate placeholder matches for subsequent rounds
                    $matchesInPreviousRound = $matchNumber - 1;
                    for ($round = 2; $round <= $rounds; $round++) {
                        $matchesInRound = ceil($matchesInPreviousRound / 2);
                        for ($i = 1; $i <= $matchesInRound; $i++) {
                            $insertQuery = "INSERT INTO matches 
                                           (tournament_id, round_number, match_number, match_status)
                                           VALUES (:tournament_id, :round_number, :match_number, 'scheduled')";
                            $insertStmt = $db->prepare($insertQuery);
                            $insertStmt->bindParam(':tournament_id', $tournamentId);
                            $insertStmt->bindParam(':round_number', $round);
                            $insertStmt->bindParam(':match_number', $i);
                            $insertStmt->execute();
                        }
                        $matchesInPreviousRound = $matchesInRound;
                    }
                    
                    // Process bye matches - advance winners to next round
                    foreach ($byeMatches as $bye) {
                        $updateQuery = "UPDATE matches 
                                       SET " . ($bye['current_match_num'] % 2 == 1 ? "participant1_id" : "participant2_id") . " = :winner_id
                                       WHERE tournament_id = :tournament_id 
                                       AND round_number = :round_number 
                                       AND match_number = :match_number";
                        $updateStmt = $db->prepare($updateQuery);
                        $updateStmt->bindParam(':winner_id', $bye['winner_id']);
                        $updateStmt->bindParam(':tournament_id', $tournamentId);
                        $updateStmt->bindParam(':round_number', $bye['next_round']);
                        $updateStmt->bindParam(':match_number', $bye['next_match_num']);
                        $updateStmt->execute();
                    }

                    $db->commit();

                    echo json_encode([
                        "success" => true,
                        "message" => "Bracket generated successfully",
                        "rounds" => $rounds
                    ]);
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            } elseif ($action === 'set-match-winner') {
                // Set match winner and advance to next round (Organizer/Admin only)
                $user = $authMiddleware->requireRole(['Organizer', 'Admin']);

                if (!isset($data['match_id']) || !isset($data['winner_id'])) {
                    throw new Exception('Match ID and winner ID are required');
                }

                // Get match and tournament info
                $checkQuery = "SELECT m.*, t.organizer_id, t.format
                              FROM matches m
                              INNER JOIN tournaments t ON m.tournament_id = t.id
                              WHERE m.id = :match_id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':match_id', $data['match_id']);
                $checkStmt->execute();
                $match = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$match) {
                    throw new Exception('Match not found');
                }

                // Verify ownership or admin
                $roles = array_column($user['roles'], 'role_name');
                if ($match['organizer_id'] != $user['user_id'] && !in_array('Admin', $roles)) {
                    throw new Exception('You do not have permission to manage this tournament');
                }

                // Verify winner is a valid participant in this match
                if ($data['winner_id'] != $match['participant1_id'] && $data['winner_id'] != $match['participant2_id']) {
                    throw new Exception('Winner must be one of the match participants');
                }

                $db->beginTransaction();

                try {
                    // Update match with winner
                    $updateQuery = "UPDATE matches 
                                   SET winner_id = :winner_id, 
                                       match_status = 'completed',
                                       end_time = NOW()
                                   WHERE id = :match_id";
                    $updateStmt = $db->prepare($updateQuery);
                    $updateStmt->bindParam(':winner_id', $data['winner_id']);
                    $updateStmt->bindParam(':match_id', $data['match_id']);
                    $updateStmt->execute();

                    // Find and update next round match
                    $currentRound = $match['round_number'];
                    $currentMatchNum = $match['match_number'];
                    $nextRound = $currentRound + 1;
                    $nextMatchNum = ceil($currentMatchNum / 2);

                    // Check if next round match exists
                    $nextMatchQuery = "SELECT id, participant1_id, participant2_id 
                                      FROM matches 
                                      WHERE tournament_id = :tournament_id 
                                      AND round_number = :round_number 
                                      AND match_number = :match_number";
                    $nextMatchStmt = $db->prepare($nextMatchQuery);
                    $nextMatchStmt->bindParam(':tournament_id', $match['tournament_id']);
                    $nextMatchStmt->bindParam(':round_number', $nextRound);
                    $nextMatchStmt->bindParam(':match_number', $nextMatchNum);
                    $nextMatchStmt->execute();
                    $nextMatch = $nextMatchStmt->fetch(PDO::FETCH_ASSOC);

                    if ($nextMatch) {
                        // Determine which slot in next match
                        // Odd match numbers go to participant1, even to participant2
                        // But if slot is already occupied, use the other slot
                        if ($currentMatchNum % 2 == 1) {
                            // Odd match number - winner should go to participant1_id
                            if (!$nextMatch['participant1_id']) {
                                $updateNextQuery = "UPDATE matches SET participant1_id = :winner_id WHERE id = :next_match_id";
                            } else {
                                // Slot occupied, use participant2
                                $updateNextQuery = "UPDATE matches SET participant2_id = :winner_id WHERE id = :next_match_id";
                            }
                        } else {
                            // Even match number - winner should go to participant2_id
                            if (!$nextMatch['participant2_id']) {
                                $updateNextQuery = "UPDATE matches SET participant2_id = :winner_id WHERE id = :next_match_id";
                            } else {
                                // Slot occupied, use participant1
                                $updateNextQuery = "UPDATE matches SET participant1_id = :winner_id WHERE id = :next_match_id";
                            }
                        }
                        $updateNextStmt = $db->prepare($updateNextQuery);
                        $updateNextStmt->bindParam(':winner_id', $data['winner_id']);
                        $updateNextStmt->bindParam(':next_match_id', $nextMatch['id']);
                        $updateNextStmt->execute();
                    }

                    $db->commit();

                    echo json_encode([
                        "success" => true,
                        "message" => "Match winner set successfully"
                    ]);
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
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
