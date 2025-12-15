<?php
// Start session for authentication
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
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
    if ($method === "GET") {
        // Require authentication
        $user = $authMiddleware->requireAuth();
        $userId = $user['user_id'];

        // Get player statistics
        $action = isset($_GET['action']) ? $_GET['action'] : 'stats';

        if ($action === 'stats') {
            // Calculate player statistics
            $stats = [];

            // Get total matches played
            $matchQuery = "SELECT COUNT(DISTINCT m.id) as total_matches,
                          SUM(CASE WHEN m.winner_id = tp.id THEN 1 ELSE 0 END) as wins,
                          SUM(CASE WHEN m.match_status = 'completed' AND m.winner_id != tp.id AND m.winner_id IS NOT NULL THEN 1 ELSE 0 END) as losses
                          FROM tournament_participants tp
                          LEFT JOIN matches m ON (m.participant1_id = tp.id OR m.participant2_id = tp.id)
                          WHERE tp.user_id = :user_id 
                          AND m.match_status = 'completed'";

            $stmt = $db->prepare($matchQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $matchStats = $stmt->fetch(PDO::FETCH_ASSOC);

            $stats['total_matches'] = (int)$matchStats['total_matches'];
            $stats['wins'] = (int)$matchStats['wins'];
            $stats['losses'] = (int)$matchStats['losses'];
            $stats['win_rate'] = $stats['total_matches'] > 0
                ? round(($stats['wins'] / $stats['total_matches']) * 100)
                : 0;

            // Get championships won (tournaments where user is the winner)
            $championshipQuery = "SELECT COUNT(*) as championships
                                 FROM tournaments t
                                 WHERE t.winner_user_id = :user_id 
                                 AND t.status = 'completed'";

            $stmt = $db->prepare($championshipQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $championshipStats = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['championships'] = (int)$championshipStats['championships'];

            // Get tournaments participated in
            $tournamentQuery = "SELECT COUNT(DISTINCT tp.tournament_id) as total_tournaments,
                               SUM(CASE WHEN t.status = 'active' OR t.status = 'in_progress' THEN 1 ELSE 0 END) as active_tournaments
                               FROM tournament_participants tp
                               INNER JOIN tournaments t ON tp.tournament_id = t.id
                               WHERE tp.user_id = :user_id 
                               AND tp.registration_status = 'confirmed'";

            $stmt = $db->prepare($tournamentQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $tournamentStats = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_tournaments'] = (int)$tournamentStats['total_tournaments'];
            $stats['active_tournaments'] = (int)$tournamentStats['active_tournaments'];

            // Get player level (based on total matches and wins)
            $experiencePoints = ($stats['wins'] * 100) + ($stats['total_matches'] * 25);
            $stats['level'] = min(100, max(1, floor($experiencePoints / 500) + 1));
            $stats['experience_points'] = $experiencePoints;

            echo json_encode([
                "success" => true,
                "stats" => $stats
            ]);
        } elseif ($action === 'match_history') {
            // Get recent match history
            $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 20;

            $query = "SELECT 
                        m.id,
                        m.match_status,
                        m.end_time,
                        m.participant1_score,
                        m.participant2_score,
                        m.winner_id,
                        t.id as tournament_id,
                        t.name as tournament_name,
                        t.format as tournament_type,
                        tp_user.id as user_participant_id,
                        tp_opponent.id as opponent_participant_id,
                        u_opponent.username as opponent_name,
                        CASE 
                            WHEN m.winner_id = tp_user.id THEN 'win'
                            WHEN m.winner_id = tp_opponent.id THEN 'loss'
                            WHEN m.match_status = 'completed' THEN 'draw'
                            ELSE 'pending'
                        END as result,
                        CASE
                            WHEN m.participant1_id = tp_user.id THEN m.participant1_score
                            ELSE m.participant2_score
                        END as user_score,
                        CASE
                            WHEN m.participant1_id = tp_user.id THEN m.participant2_score
                            ELSE m.participant1_score
                        END as opponent_score,
                        CONCAT('Round ', m.round_number) as round_name
                      FROM matches m
                      INNER JOIN tournament_participants tp_user 
                        ON (m.participant1_id = tp_user.id OR m.participant2_id = tp_user.id)
                      INNER JOIN tournaments t ON m.tournament_id = t.id
                      LEFT JOIN tournament_participants tp_opponent 
                        ON (CASE 
                            WHEN m.participant1_id = tp_user.id THEN m.participant2_id 
                            ELSE m.participant1_id 
                           END = tp_opponent.id)
                      LEFT JOIN users u_opponent ON tp_opponent.user_id = u_opponent.id
                      WHERE tp_user.user_id = :user_id
                      AND m.match_status IN ('completed', 'in_progress')
                      ORDER BY m.end_time DESC, m.updated_at DESC
                      LIMIT :limit";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "matches" => $matches
            ]);
        } elseif ($action === 'achievements') {
            // Get player achievements
            $achievements = [];

            // Championship achievements (tournaments won by this user)
            $champQuery = "SELECT t.name as tournament_name, t.end_date, 1 as final_placement
                          FROM tournaments t
                          WHERE t.winner_user_id = :user_id 
                          AND t.status = 'completed'
                          ORDER BY t.end_date DESC
                          LIMIT 10";

            $stmt = $db->prepare($champQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $placements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($placements as $placement) {
                $badge = 'yellow-400';
                $title = 'Champion';
                if ($placement['final_placement'] == 1) {
                    $badge = 'yellow-400';
                    $title = 'Champion';
                } elseif ($placement['final_placement'] == 2) {
                    $badge = 'gray-400';
                    $title = 'Runner-up';
                } else {
                    $badge = 'orange-600';
                    $title = '3rd Place';
                }

                $achievements[] = [
                    'type' => 'placement',
                    'title' => $title,
                    'description' => $placement['tournament_name'],
                    'badge_color' => $badge,
                    'date' => $placement['end_date']
                ];
            }

            // Win streak achievement (if they have 5+ consecutive wins)
            $streakQuery = "SELECT COUNT(*) as wins FROM (
                              SELECT m.id, m.winner_id, tp.id as participant_id
                              FROM matches m
                              INNER JOIN tournament_participants tp ON (m.participant1_id = tp.id OR m.participant2_id = tp.id)
                              WHERE tp.user_id = :user_id AND m.match_status = 'completed'
                              ORDER BY m.end_time DESC
                              LIMIT 5
                           ) recent_matches
                           WHERE winner_id = participant_id";

            $stmt = $db->prepare($streakQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $streak = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($streak['wins'] >= 5) {
                $achievements[] = [
                    'type' => 'streak',
                    'title' => 'Winning Streak',
                    'description' => '5 consecutive wins',
                    'badge_color' => 'green-400',
                    'date' => date('Y-m-d')
                ];
            }

            // Check if player is in top rankings (only if they have participated in matches)
            $rankQuery = "SELECT COUNT(*) + 1 as rank
                         FROM (
                             SELECT tp.user_id, COUNT(CASE WHEN m.winner_id = tp.id THEN 1 END) as wins
                             FROM tournament_participants tp
                             INNER JOIN matches m ON (m.participant1_id = tp.id OR m.participant2_id = tp.id)
                             WHERE m.match_status = 'completed'
                             GROUP BY tp.user_id
                             HAVING wins > (
                                 SELECT COUNT(CASE WHEN m.winner_id = tp.id THEN 1 END)
                                 FROM tournament_participants tp
                                 INNER JOIN matches m ON (m.participant1_id = tp.id OR m.participant2_id = tp.id)
                                 WHERE tp.user_id = :user_id AND m.match_status = 'completed'
                             )
                         ) rankings";

            $stmt = $db->prepare($rankQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $ranking = $stmt->fetch(PDO::FETCH_ASSOC);

            // Only show ranking if user has actually participated in matches
            $hasMatchesQuery = "SELECT COUNT(*) as match_count 
                               FROM tournament_participants tp
                               INNER JOIN matches m ON (m.participant1_id = tp.id OR m.participant2_id = tp.id)
                               WHERE tp.user_id = :user_id AND m.match_status = 'completed'";

            $stmt = $db->prepare($hasMatchesQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $hasMatches = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ranking && $ranking['rank'] <= 10 && $hasMatches['match_count'] > 0) {
                $achievements[] = [
                    'type' => 'ranking',
                    'title' => 'Top Player',
                    'description' => 'Ranked #' . $ranking['rank'],
                    'badge_color' => 'cyan-400',
                    'date' => date('Y-m-d')
                ];
            }

            echo json_encode([
                "success" => true,
                "achievements" => $achievements
            ]);
        } elseif ($action === 'recent_activity') {
            // Get recent activity for dashboard
            $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 20) : 10;
            $activities = [];

            // Get recent tournament registrations
            $regQuery = "SELECT tp.registered_at as activity_date, t.name as tournament_name, 'registration' as activity_type
                        FROM tournament_participants tp
                        INNER JOIN tournaments t ON tp.tournament_id = t.id
                        WHERE tp.user_id = :user_id
                        ORDER BY tp.registered_at DESC
                        LIMIT 5";

            $stmt = $db->prepare($regQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($registrations as $reg) {
                $activities[] = [
                    'type' => 'registration',
                    'message' => 'Registered for "' . $reg['tournament_name'] . '"',
                    'date' => $reg['activity_date']
                ];
            }

            // Get recent match wins
            $winQuery = "SELECT m.end_time as activity_date, t.name as tournament_name, 'win' as activity_type
                        FROM matches m
                        INNER JOIN tournament_participants tp ON m.winner_id = tp.id
                        INNER JOIN tournaments t ON m.tournament_id = t.id
                        WHERE tp.user_id = :user_id AND m.match_status = 'completed'
                        ORDER BY m.end_time DESC
                        LIMIT 5";

            $stmt = $db->prepare($winQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $wins = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($wins as $win) {
                $activities[] = [
                    'type' => 'win',
                    'message' => 'Won match in "' . $win['tournament_name'] . '"',
                    'date' => $win['activity_date']
                ];
            }

            // Get tournaments won
            $champQuery = "SELECT t.end_date as activity_date, t.name as tournament_name, 'championship' as activity_type
                          FROM tournaments t
                          WHERE t.winner_user_id = :user_id
                          ORDER BY t.end_date DESC
                          LIMIT 3";

            $stmt = $db->prepare($champQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $championships = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($championships as $champ) {
                $activities[] = [
                    'type' => 'championship',
                    'message' => 'Won Championship in "' . $champ['tournament_name'] . '"',
                    'date' => $champ['activity_date']
                ];
            }

            // Sort all activities by date
            usort($activities, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            // Limit to requested amount
            $activities = array_slice($activities, 0, $limit);

            echo json_encode([
                "success" => true,
                "activities" => $activities
            ]);
        } else {
            throw new Exception('Invalid action');
        }
    } else {
        throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
