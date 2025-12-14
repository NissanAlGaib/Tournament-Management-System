<?php
class Tournament
{
    private $conn;
    private $table_name = "tournaments";

    public $id;
    public $organizer_id;
    public $name;
    public $description;
    public $game_type;
    public $format;
    public $tournament_size;
    public $max_participants;
    public $rules;
    public $status;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Get all tournaments with optional filters
     */
    public function getAll($filters = [])
    {
        try {
            $query = "SELECT t.*, u.username as organizer_name,
                     COUNT(DISTINCT tp.id) as registered_participants
                     FROM " . $this->table_name . " t
                     LEFT JOIN users u ON t.organizer_id = u.id
                     LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id 
                     AND tp.registration_status = 'confirmed'
                     WHERE 1=1";
            
            $params = [];
            
            if (isset($filters['status'])) {
                $query .= " AND t.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if (isset($filters['is_public'])) {
                $query .= " AND t.is_public = :is_public";
                $params[':is_public'] = $filters['is_public'];
            }
            
            if (isset($filters['organizer_id'])) {
                $query .= " AND t.organizer_id = :organizer_id";
                $params[':organizer_id'] = $filters['organizer_id'];
            }
            
            $query .= " GROUP BY t.id ORDER BY t.created_at DESC";
            
            if (isset($filters['limit'])) {
                $query .= " LIMIT :limit";
            }
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if (isset($filters['limit'])) {
                $stmt->bindValue(':limit', (int)$filters['limit'], PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get tournaments error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get tournament by ID with full details
     */
    public function getById($id)
    {
        try {
            $query = "SELECT t.*, u.username as organizer_name
                     FROM " . $this->table_name . " t
                     LEFT JOIN users u ON t.organizer_id = u.id
                     WHERE t.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($tournament) {
                // Get prizes
                $tournament['prizes'] = $this->getPrizes($id);
                
                // Get participant count
                $tournament['participants_count'] = $this->getParticipantCount($id);
            }
            
            return $tournament;
        } catch (PDOException $e) {
            error_log("Get tournament error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new tournament
     */
    public function create($data)
    {
        try {
            $this->conn->beginTransaction();
            
            $query = "INSERT INTO " . $this->table_name . "
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
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters with defaults
            $stmt->bindParam(':organizer_id', $data['organizer_id']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindValue(':description', $data['description'] ?? null);
            $stmt->bindValue(':game_type', $data['game_type'] ?? null);
            $stmt->bindValue(':format', $data['format'] ?? 'single_elimination');
            $stmt->bindValue(':tournament_size', $data['tournament_size'] ?? 16);
            $stmt->bindValue(':max_participants', $data['max_participants'] ?? null);
            $stmt->bindValue(':rules', $data['rules'] ?? null);
            $stmt->bindValue(':match_rules', $data['match_rules'] ?? null);
            $stmt->bindValue(':scoring_system', $data['scoring_system'] ?? 'best_of_3');
            $stmt->bindValue(':entry_fee', $data['entry_fee'] ?? 0.00);
            $stmt->bindValue(':is_public', $data['is_public'] ?? 1);
            $stmt->bindValue(':is_featured', $data['is_featured'] ?? 0);
            $stmt->bindValue(':registration_start', $data['registration_start'] ?? null);
            $stmt->bindParam(':registration_deadline', $data['registration_deadline']);
            $stmt->bindValue(':allow_late_registration', $data['allow_late_registration'] ?? 0);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindValue(':end_date', $data['end_date'] ?? null);
            $stmt->bindValue(':estimated_duration_hours', $data['estimated_duration_hours'] ?? null);
            $stmt->bindValue(':status', $data['status'] ?? 'draft');
            $stmt->bindValue(':visibility', $data['visibility'] ?? 'public');
            
            $stmt->execute();
            $tournamentId = $this->conn->lastInsertId();
            
            // Add prizes if provided
            if (isset($data['prizes']) && is_array($data['prizes'])) {
                foreach ($data['prizes'] as $prize) {
                    $this->addPrize($tournamentId, $prize);
                }
            }
            
            $this->conn->commit();
            return $tournamentId;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Create tournament error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update tournament
     */
    public function update($id, $data)
    {
        try {
            $updateFields = [];
            $params = [':id' => $id];
            
            $allowedFields = ['name', 'description', 'game_type', 'format', 'tournament_size',
                             'max_participants', 'rules', 'match_rules', 'scoring_system', 'entry_fee',
                             'is_public', 'is_featured', 'registration_deadline', 'start_date',
                             'end_date', 'status', 'visibility', 'allow_late_registration'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            
            if (empty($updateFields)) {
                return false;
            }
            
            $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Update tournament error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete tournament
     */
    public function delete($id)
    {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete tournament error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get tournament prizes
     */
    public function getPrizes($tournamentId)
    {
        try {
            $query = "SELECT * FROM tournament_prizes WHERE tournament_id = :tournament_id ORDER BY placement";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tournament_id', $tournamentId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get prizes error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add prize to tournament
     */
    public function addPrize($tournamentId, $prize)
    {
        try {
            $query = "INSERT INTO tournament_prizes
                    (tournament_id, placement, prize_type, prize_amount, currency, prize_description)
                    VALUES (:tournament_id, :placement, :prize_type, :prize_amount, :currency, :prize_description)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tournament_id', $tournamentId);
            $stmt->bindParam(':placement', $prize['placement']);
            $stmt->bindValue(':prize_type', $prize['type'] ?? 'cash');
            $stmt->bindValue(':prize_amount', $prize['amount'] ?? 0);
            $stmt->bindValue(':currency', $prize['currency'] ?? 'USD');
            $stmt->bindValue(':prize_description', $prize['description'] ?? null);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Add prize error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get participant count
     */
    public function getParticipantCount($tournamentId)
    {
        try {
            $query = "SELECT COUNT(*) as count FROM tournament_participants
                     WHERE tournament_id = :tournament_id AND registration_status = 'confirmed'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tournament_id', $tournamentId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Get participant count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Register participant
     */
    public function registerParticipant($tournamentId, $userId)
    {
        try {
            // Check if already registered
            $checkQuery = "SELECT * FROM tournament_participants
                          WHERE tournament_id = :tournament_id AND user_id = :user_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':tournament_id', $tournamentId);
            $checkStmt->bindParam(':user_id', $userId);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                throw new Exception('Already registered');
            }
            
            // Register
            $query = "INSERT INTO tournament_participants
                     (tournament_id, user_id, registration_status, payment_status)
                     VALUES (:tournament_id, :user_id, 'confirmed', 'pending')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tournament_id', $tournamentId);
            $stmt->bindParam(':user_id', $userId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Register participant error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard($tournamentId)
    {
        try {
            $query = "SELECT ts.*, u.username, tp.user_id
                     FROM tournament_standings ts
                     JOIN tournament_participants tp ON ts.participant_id = tp.id
                     JOIN users u ON tp.user_id = u.id
                     WHERE ts.tournament_id = :tournament_id
                     ORDER BY ts.current_rank ASC, ts.points DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tournament_id', $tournamentId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get leaderboard error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user is organizer of tournament
     */
    public function isOrganizer($tournamentId, $userId)
    {
        try {
            $query = "SELECT organizer_id FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $tournamentId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['organizer_id'] == $userId;
        } catch (PDOException $e) {
            error_log("Check organizer error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get tournaments organized by user
     */
    public function getByOrganizer($userId)
    {
        return $this->getAll(['organizer_id' => $userId]);
    }

    /**
     * Get tournaments user is participating in
     */
    public function getByParticipant($userId)
    {
        try {
            $query = "SELECT t.*, tp.registration_status, tp.registered_at,
                     COUNT(DISTINCT tp2.id) as registered_participants
                     FROM tournaments t
                     INNER JOIN tournament_participants tp ON t.id = tp.tournament_id
                     LEFT JOIN tournament_participants tp2 ON t.id = tp2.tournament_id 
                     AND tp2.registration_status = 'confirmed'
                     WHERE tp.user_id = :user_id
                     GROUP BY t.id
                     ORDER BY t.start_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get participant tournaments error: " . $e->getMessage());
            return [];
        }
    }
}
?>
