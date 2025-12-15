-- Add winner columns to tournaments table
-- Run this migration to add support for storing tournament winners

ALTER TABLE `tournaments` 
ADD COLUMN `winner_user_id` int(11) DEFAULT NULL COMMENT 'Winner for individual tournaments' AFTER `completed_at`,
ADD COLUMN `winner_name` varchar(255) DEFAULT NULL COMMENT 'Winner name (cached)' AFTER `winner_user_id`,
ADD COLUMN `winner_team_id` int(11) DEFAULT NULL COMMENT 'Winner team for team-based tournaments' AFTER `winner_name`,
ADD COLUMN `winner_team_name` varchar(255) DEFAULT NULL COMMENT 'Winner team name (cached)' AFTER `winner_team_id`,
ADD KEY `idx_winner_user` (`winner_user_id`),
ADD KEY `idx_winner_team` (`winner_team_id`);

-- Add foreign keys (optional, depends on whether you want cascading deletes)
-- ALTER TABLE `tournaments` 
-- ADD CONSTRAINT `fk_tournament_winner_user` FOREIGN KEY (`winner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
-- ADD CONSTRAINT `fk_tournament_winner_team` FOREIGN KEY (`winner_team_id`) REFERENCES `tournament_teams` (`id`) ON DELETE SET NULL;
