-- Tournament Management System - Tournament Features
-- This script creates all tables needed for tournament management
-- Including: tournaments, registrations, matches, prizes, and brackets

-- ==================================================
-- TOURNAMENTS TABLE
-- Core tournament information with configuration
-- ==================================================
CREATE TABLE IF NOT EXISTS `tournaments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organizer_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `game_type` varchar(100) DEFAULT NULL,
  
  -- Tournament configuration
  `format` enum('single_elimination', 'double_elimination', 'round_robin', 'swiss', 'custom') NOT NULL DEFAULT 'single_elimination',
  `tournament_size` int(11) NOT NULL DEFAULT 16,
  `min_participants` int(11) DEFAULT 2,
  `max_participants` int(11) DEFAULT NULL,
  `current_participants` int(11) DEFAULT 0,
  
  -- Rules and settings
  `rules` text DEFAULT NULL,
  `match_rules` text DEFAULT NULL,
  `scoring_system` varchar(100) DEFAULT 'best_of_3',
  `entry_fee` decimal(10,2) DEFAULT 0.00,
  `is_public` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  
  -- Registration management
  `registration_start` datetime DEFAULT NULL,
  `registration_deadline` datetime NOT NULL,
  `allow_late_registration` tinyint(1) DEFAULT 0,
  
  -- Tournament schedule
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `estimated_duration_hours` int(11) DEFAULT NULL,
  
  -- Status management
  `status` enum('draft', 'open', 'registration_closed', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
  `visibility` enum('public', 'private', 'invite_only') DEFAULT 'public',
  
  -- Metadata
  `banner_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  KEY `idx_organizer` (`organizer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_registration_deadline` (`registration_deadline`),
  KEY `idx_start_date` (`start_date`),
  CONSTRAINT `fk_tournament_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- TOURNAMENT REQUIREMENTS
-- Define specific requirements for tournament registration
-- ==================================================
CREATE TABLE IF NOT EXISTS `tournament_requirements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `requirement_type` enum('skill_level', 'rank', 'age', 'country', 'experience', 'custom') NOT NULL,
  `requirement_value` varchar(255) NOT NULL,
  `is_mandatory` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_tournament` (`tournament_id`),
  CONSTRAINT `fk_requirement_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- TOURNAMENT PARTICIPANTS
-- Track registered players and their status
-- ==================================================
CREATE TABLE IF NOT EXISTS `tournament_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `seed_number` int(11) DEFAULT NULL,
  `registration_status` enum('pending', 'confirmed', 'waitlist', 'rejected', 'withdrawn') DEFAULT 'pending',
  `payment_status` enum('pending', 'paid', 'refunded', 'waived') DEFAULT 'pending',
  `check_in_status` enum('not_checked_in', 'checked_in', 'no_show') DEFAULT 'not_checked_in',
  `check_in_time` datetime DEFAULT NULL,
  `registration_notes` text DEFAULT NULL,
  `registered_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tournament_user` (`tournament_id`, `user_id`),
  KEY `idx_tournament` (`tournament_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_registration_status` (`registration_status`),
  CONSTRAINT `fk_participant_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_participant_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- TOURNAMENT BRACKETS
-- Tournament structure and bracket organization
-- ==================================================
CREATE TABLE IF NOT EXISTS `tournament_brackets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `bracket_name` varchar(100) DEFAULT 'Main Bracket',
  `bracket_type` enum('winners', 'losers', 'finals', 'group_stage', 'custom') DEFAULT 'winners',
  `round_number` int(11) NOT NULL,
  `position` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_tournament` (`tournament_id`),
  KEY `idx_bracket_round` (`tournament_id`, `round_number`),
  CONSTRAINT `fk_bracket_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- MATCHES TABLE
-- Individual match tracking and results
-- ==================================================
CREATE TABLE IF NOT EXISTS `matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `bracket_id` int(11) DEFAULT NULL,
  `round_number` int(11) NOT NULL,
  `match_number` int(11) NOT NULL,
  
  -- Participants
  `participant1_id` int(11) DEFAULT NULL,
  `participant2_id` int(11) DEFAULT NULL,
  `winner_id` int(11) DEFAULT NULL,
  
  -- Match details
  `match_status` enum('scheduled', 'in_progress', 'completed', 'disputed', 'cancelled', 'bye') DEFAULT 'scheduled',
  `scheduled_time` datetime DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  
  -- Results tracking
  `participant1_score` int(11) DEFAULT 0,
  `participant2_score` int(11) DEFAULT 0,
  `match_details` text DEFAULT NULL,
  `result_verified` tinyint(1) DEFAULT 0,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  
  -- Next match progression
  `next_match_id` int(11) DEFAULT NULL,
  `loser_next_match_id` int(11) DEFAULT NULL,
  
  -- Metadata
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_tournament` (`tournament_id`),
  KEY `idx_bracket` (`bracket_id`),
  KEY `idx_participant1` (`participant1_id`),
  KEY `idx_participant2` (`participant2_id`),
  KEY `idx_winner` (`winner_id`),
  KEY `idx_match_status` (`match_status`),
  CONSTRAINT `fk_match_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_match_bracket` FOREIGN KEY (`bracket_id`) REFERENCES `tournament_brackets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_match_participant1` FOREIGN KEY (`participant1_id`) REFERENCES `tournament_participants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_match_participant2` FOREIGN KEY (`participant2_id`) REFERENCES `tournament_participants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_match_winner` FOREIGN KEY (`winner_id`) REFERENCES `tournament_participants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_match_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- MATCH RESULTS DETAIL
-- Detailed game-by-game results for best-of-X matches
-- ==================================================
CREATE TABLE IF NOT EXISTS `match_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `game_number` int(11) NOT NULL,
  `participant1_score` int(11) DEFAULT 0,
  `participant2_score` int(11) DEFAULT 0,
  `winner_id` int(11) DEFAULT NULL,
  `game_data` text DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_match` (`match_id`),
  CONSTRAINT `fk_result_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_result_winner` FOREIGN KEY (`winner_id`) REFERENCES `tournament_participants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- TOURNAMENT PRIZES
-- Prize pool and reward distribution
-- ==================================================
CREATE TABLE IF NOT EXISTS `tournament_prizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `placement` int(11) NOT NULL,
  `prize_type` enum('cash', 'trophy', 'medal', 'points', 'item', 'custom') DEFAULT 'cash',
  `prize_amount` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'USD',
  `prize_description` varchar(255) DEFAULT NULL,
  `is_awarded` tinyint(1) DEFAULT 0,
  `awarded_to_participant_id` int(11) DEFAULT NULL,
  `awarded_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_tournament` (`tournament_id`),
  KEY `idx_placement` (`tournament_id`, `placement`),
  KEY `idx_awarded_to` (`awarded_to_participant_id`),
  CONSTRAINT `fk_prize_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_prize_participant` FOREIGN KEY (`awarded_to_participant_id`) REFERENCES `tournament_participants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- TOURNAMENT STANDINGS
-- Track current standings and rankings
-- ==================================================
CREATE TABLE IF NOT EXISTS `tournament_standings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `current_rank` int(11) DEFAULT NULL,
  `wins` int(11) DEFAULT 0,
  `losses` int(11) DEFAULT 0,
  `draws` int(11) DEFAULT 0,
  `points` int(11) DEFAULT 0,
  `score_differential` int(11) DEFAULT 0,
  `matches_played` int(11) DEFAULT 0,
  `is_eliminated` tinyint(1) DEFAULT 0,
  `final_placement` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tournament_participant` (`tournament_id`, `participant_id`),
  KEY `idx_tournament` (`tournament_id`),
  KEY `idx_participant` (`participant_id`),
  KEY `idx_rank` (`tournament_id`, `current_rank`),
  CONSTRAINT `fk_standing_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_standing_participant` FOREIGN KEY (`participant_id`) REFERENCES `tournament_participants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- TOURNAMENT NOTIFICATIONS
-- Track notifications and announcements
-- ==================================================
CREATE TABLE IF NOT EXISTS `tournament_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `notification_type` enum('announcement', 'schedule_change', 'result', 'registration', 'general') DEFAULT 'general',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  `target_audience` enum('all', 'participants', 'organizers', 'specific_user') DEFAULT 'all',
  `target_user_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_tournament` (`tournament_id`),
  KEY `idx_target_user` (`target_user_id`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_notification_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notification_target_user` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notification_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- TOURNAMENT TEAMS (optional for team tournaments)
-- ==================================================
CREATE TABLE IF NOT EXISTS `tournament_teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `team_name` varchar(255) NOT NULL,
  `team_tag` varchar(50) DEFAULT NULL,
  `captain_user_id` int(11) NOT NULL,
  `team_status` enum('active', 'disbanded', 'disqualified') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_tournament` (`tournament_id`),
  KEY `idx_captain` (`captain_user_id`),
  CONSTRAINT `fk_team_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_team_captain` FOREIGN KEY (`captain_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- TOURNAMENT TEAM MEMBERS
-- ==================================================
CREATE TABLE IF NOT EXISTS `tournament_team_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(50) DEFAULT 'member',
  `joined_at` datetime DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_user` (`team_id`, `user_id`),
  KEY `idx_team` (`team_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_team_member_team` FOREIGN KEY (`team_id`) REFERENCES `tournament_teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_team_member_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- TOURNAMENT ACTIVITY LOG
-- Audit trail for tournament actions
-- ==================================================
CREATE TABLE IF NOT EXISTS `tournament_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `action_description` text NOT NULL,
  `metadata` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_tournament` (`tournament_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_activity_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- ==================================================

-- Additional composite indexes for common queries
CREATE INDEX idx_tournament_status_dates ON tournaments(status, start_date, registration_deadline);
CREATE INDEX idx_tournament_public_featured ON tournaments(is_public, is_featured, status);
CREATE INDEX idx_participant_tournament_status ON tournament_participants(tournament_id, registration_status);
CREATE INDEX idx_match_tournament_round ON matches(tournament_id, round_number, match_status);

-- ==================================================
-- VIEWS FOR COMMON QUERIES
-- ==================================================

-- View: Active Tournaments
CREATE OR REPLACE VIEW active_tournaments AS
SELECT 
  t.*,
  u.username as organizer_name,
  COUNT(DISTINCT tp.id) as registered_participants
FROM tournaments t
LEFT JOIN users u ON t.organizer_id = u.id
LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id AND tp.registration_status = 'confirmed'
WHERE t.status IN ('open', 'ongoing')
GROUP BY t.id;

-- View: Tournament Leaderboard
CREATE OR REPLACE VIEW tournament_leaderboard AS
SELECT 
  ts.tournament_id,
  ts.participant_id,
  u.username,
  ts.current_rank,
  ts.wins,
  ts.losses,
  ts.points,
  ts.final_placement
FROM tournament_standings ts
JOIN tournament_participants tp ON ts.participant_id = tp.id
JOIN users u ON tp.user_id = u.id
ORDER BY ts.tournament_id, ts.current_rank;

-- View: Upcoming Matches
CREATE OR REPLACE VIEW upcoming_matches AS
SELECT 
  m.*,
  t.name as tournament_name,
  u1.username as participant1_name,
  u2.username as participant2_name
FROM matches m
JOIN tournaments t ON m.tournament_id = t.id
LEFT JOIN tournament_participants tp1 ON m.participant1_id = tp1.id
LEFT JOIN tournament_participants tp2 ON m.participant2_id = tp2.id
LEFT JOIN users u1 ON tp1.user_id = u1.id
LEFT JOIN users u2 ON tp2.user_id = u2.id
WHERE m.match_status IN ('scheduled', 'in_progress')
ORDER BY m.scheduled_time;

-- View: Prize Distribution
CREATE OR REPLACE VIEW prize_distribution AS
SELECT 
  tp.tournament_id,
  t.name as tournament_name,
  tp.placement,
  tp.prize_type,
  tp.prize_amount,
  tp.currency,
  tp.prize_description,
  tp.is_awarded,
  u.username as winner_name
FROM tournament_prizes tp
JOIN tournaments t ON tp.tournament_id = t.id
LEFT JOIN tournament_participants tpart ON tp.awarded_to_participant_id = tpart.id
LEFT JOIN users u ON tpart.user_id = u.id
ORDER BY tp.tournament_id, tp.placement;

-- ==================================================
-- STORED PROCEDURES
-- ==================================================

-- Procedure: Update Tournament Participant Count
DELIMITER //
CREATE PROCEDURE update_tournament_participant_count(IN tourney_id INT)
BEGIN
  UPDATE tournaments 
  SET current_participants = (
    SELECT COUNT(*) 
    FROM tournament_participants 
    WHERE tournament_id = tourney_id 
    AND registration_status = 'confirmed'
  )
  WHERE id = tourney_id;
END//
DELIMITER ;

-- Procedure: Update Match Result and Standings
DELIMITER //
CREATE PROCEDURE update_match_result(
  IN match_id_param INT,
  IN winner_id_param INT,
  IN p1_score INT,
  IN p2_score INT
)
BEGIN
  DECLARE tournament_id_var INT;
  DECLARE participant1_id_var INT;
  DECLARE participant2_id_var INT;
  
  -- Update match result
  UPDATE matches 
  SET winner_id = winner_id_param,
      participant1_score = p1_score,
      participant2_score = p2_score,
      match_status = 'completed',
      end_time = NOW()
  WHERE id = match_id_param;
  
  -- Get tournament and participant info
  SELECT tournament_id, participant1_id, participant2_id 
  INTO tournament_id_var, participant1_id_var, participant2_id_var
  FROM matches 
  WHERE id = match_id_param;
  
  -- Update standings for winner
  INSERT INTO tournament_standings 
    (tournament_id, participant_id, wins, matches_played) 
  VALUES 
    (tournament_id_var, winner_id_param, 1, 1)
  ON DUPLICATE KEY UPDATE 
    wins = wins + 1,
    matches_played = matches_played + 1,
    points = points + 3;
  
  -- Update standings for loser
  INSERT INTO tournament_standings 
    (tournament_id, participant_id, losses, matches_played) 
  VALUES 
    (tournament_id_var, 
     IF(winner_id_param = participant1_id_var, participant2_id_var, participant1_id_var), 
     1, 1)
  ON DUPLICATE KEY UPDATE 
    losses = losses + 1,
    matches_played = matches_played + 1;
END//
DELIMITER ;

-- ==================================================
-- TRIGGERS
-- ==================================================

-- Trigger: Update participant count after registration
DELIMITER //
CREATE TRIGGER after_participant_insert
AFTER INSERT ON tournament_participants
FOR EACH ROW
BEGIN
  IF NEW.registration_status = 'confirmed' THEN
    UPDATE tournaments 
    SET current_participants = current_participants + 1
    WHERE id = NEW.tournament_id;
  END IF;
END//
DELIMITER ;

-- Trigger: Update participant count after status change
DELIMITER //
CREATE TRIGGER after_participant_update
AFTER UPDATE ON tournament_participants
FOR EACH ROW
BEGIN
  IF OLD.registration_status != NEW.registration_status THEN
    IF NEW.registration_status = 'confirmed' AND OLD.registration_status != 'confirmed' THEN
      UPDATE tournaments 
      SET current_participants = current_participants + 1
      WHERE id = NEW.tournament_id;
    ELSEIF OLD.registration_status = 'confirmed' AND NEW.registration_status != 'confirmed' THEN
      UPDATE tournaments 
      SET current_participants = current_participants - 1
      WHERE id = NEW.tournament_id;
    END IF;
  END IF;
END//
DELIMITER ;

-- Trigger: Log tournament activity
DELIMITER //
CREATE TRIGGER after_tournament_update
AFTER UPDATE ON tournaments
FOR EACH ROW
BEGIN
  IF OLD.status != NEW.status THEN
    INSERT INTO tournament_activity_log 
      (tournament_id, action_type, action_description, metadata)
    VALUES 
      (NEW.id, 'status_change', 
       CONCAT('Tournament status changed from ', OLD.status, ' to ', NEW.status),
       JSON_OBJECT('old_status', OLD.status, 'new_status', NEW.status));
  END IF;
END//
DELIMITER ;

DELIMITER ;

-- ==================================================
-- SAMPLE DATA (Optional - comment out in production)
-- ==================================================

-- Note: Sample data commented out to prevent accidental insertion
-- Uncomment for testing purposes

/*
-- Sample Tournaments
INSERT INTO tournaments 
  (organizer_id, name, description, format, tournament_size, rules, registration_deadline, start_date, status, visibility)
VALUES 
  (1, 'Summer Championship 2024', 'The ultimate summer gaming championship', 'single_elimination', 64, 
   'Standard tournament rules apply. Best of 3 matches.', 
   '2024-07-01 23:59:59', '2024-07-15 10:00:00', 'open', 'public'),
  (1, 'Winter League', 'Compete in the winter league tournament', 'round_robin', 16,
   'Round robin format with top 4 advancing to playoffs.',
   '2024-12-01 23:59:59', '2024-12-15 14:00:00', 'open', 'public');

-- Sample Tournament Prizes
INSERT INTO tournament_prizes 
  (tournament_id, placement, prize_type, prize_amount, currency, prize_description)
VALUES 
  (1, 1, 'cash', 5000.00, 'USD', 'First Place - Champion Trophy'),
  (1, 2, 'cash', 2500.00, 'USD', 'Second Place - Runner-up Trophy'),
  (1, 3, 'cash', 1000.00, 'USD', 'Third Place - Bronze Medal');
*/

-- ==================================================
-- GRANT PERMISSIONS (Adjust based on your setup)
-- ==================================================

-- Grant necessary permissions to your application user
-- GRANT SELECT, INSERT, UPDATE, DELETE ON tournament_db.tournaments TO 'app_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON tournament_db.tournament_participants TO 'app_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON tournament_db.matches TO 'app_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON tournament_db.tournament_prizes TO 'app_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON tournament_db.tournament_standings TO 'app_user'@'localhost';
-- GRANT EXECUTE ON PROCEDURE tournament_db.update_tournament_participant_count TO 'app_user'@'localhost';
-- GRANT EXECUTE ON PROCEDURE tournament_db.update_match_result TO 'app_user'@'localhost';

-- ==================================================
-- COMPLETION MESSAGE
-- ==================================================

SELECT 'Tournament Management System database setup completed successfully!' as status;
