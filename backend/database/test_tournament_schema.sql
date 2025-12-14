-- Tournament Management System - Test Queries
-- This file contains test queries to verify the schema is working correctly
-- Run these after importing tournament_management.sql

-- ==================================================
-- VERIFICATION QUERIES
-- ==================================================

-- Show all tournament-related tables
SHOW TABLES LIKE 'tournament%';
SHOW TABLES LIKE 'match%';

-- Verify table structures
DESCRIBE tournaments;
DESCRIBE tournament_participants;
DESCRIBE matches;
DESCRIBE tournament_prizes;

-- ==================================================
-- SAMPLE DATA FOR TESTING (Optional)
-- ==================================================

-- Note: Assumes you have at least one user with ID 1
-- If not, create a user first or adjust the user IDs

-- Create a sample tournament
INSERT INTO tournaments 
  (organizer_id, name, description, game_type, format, tournament_size, 
   rules, registration_deadline, start_date, status, visibility)
VALUES 
  (1, 'Test Championship 2024', 'A test tournament for verification', 'Gaming', 
   'single_elimination', 16, 'Standard rules apply', 
   DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 14 DAY), 
   'open', 'public');

-- Get the tournament ID (should be 1 if this is first tournament)
SET @tournament_id = LAST_INSERT_ID();

-- Add prizes to the tournament
INSERT INTO tournament_prizes 
  (tournament_id, placement, prize_type, prize_amount, currency, prize_description)
VALUES 
  (@tournament_id, 1, 'cash', 1000.00, 'USD', 'First Place Winner'),
  (@tournament_id, 2, 'cash', 500.00, 'USD', 'Second Place'),
  (@tournament_id, 3, 'cash', 250.00, 'USD', 'Third Place');

-- Add tournament requirements
INSERT INTO tournament_requirements
  (tournament_id, requirement_type, requirement_value, is_mandatory, description)
VALUES
  (@tournament_id, 'skill_level', 'intermediate', 1, 'Must be intermediate level or higher');

-- ==================================================
-- TEST QUERIES
-- ==================================================

-- 1. Test active tournaments view
SELECT 'Testing active_tournaments view...' as test;
SELECT * FROM active_tournaments WHERE status = 'open';

-- 2. Test tournament with prizes
SELECT 'Testing tournament with prizes...' as test;
SELECT t.name, tp.placement, tp.prize_amount, tp.currency, tp.prize_description
FROM tournaments t
LEFT JOIN tournament_prizes tp ON t.id = tp.tournament_id
WHERE t.id = @tournament_id
ORDER BY tp.placement;

-- 3. Test participant registration (requires user_id 2 to exist)
-- Uncomment if you have a user with ID 2
-- INSERT INTO tournament_participants (tournament_id, user_id, registration_status)
-- VALUES (@tournament_id, 2, 'confirmed');

-- 4. Test participant count
SELECT 'Testing participant count...' as test;
SELECT id, name, current_participants, max_participants, status
FROM tournaments
WHERE id = @tournament_id;

-- 5. Test tournament standings view
SELECT 'Testing tournament standings...' as test;
SELECT * FROM tournament_standings WHERE tournament_id = @tournament_id;

-- 6. Test stored procedure (update participant count)
SELECT 'Testing stored procedure...' as test;
CALL update_tournament_participant_count(@tournament_id);

-- 7. Verify triggers are working
SELECT 'Testing triggers...' as test;
SELECT * FROM tournament_activity_log WHERE tournament_id = @tournament_id;

-- ==================================================
-- QUERY PERFORMANCE TESTS
-- ==================================================

-- Test indexes are being used
EXPLAIN SELECT * FROM tournaments WHERE status = 'open' AND is_public = 1;
EXPLAIN SELECT * FROM tournament_participants WHERE tournament_id = @tournament_id;
EXPLAIN SELECT * FROM matches WHERE tournament_id = @tournament_id AND match_status = 'scheduled';

-- ==================================================
-- CLEANUP (Optional - uncomment to remove test data)
-- ==================================================

/*
-- Delete test tournament and all related data (cascades automatically)
DELETE FROM tournaments WHERE id = @tournament_id;

-- Verify deletion
SELECT COUNT(*) as remaining_tournaments FROM tournaments;
SELECT COUNT(*) as remaining_prizes FROM tournament_prizes;
SELECT COUNT(*) as remaining_participants FROM tournament_participants;
*/

-- ==================================================
-- SUMMARY
-- ==================================================

SELECT 'Schema verification complete!' as status;
SELECT 
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE 'tournament%') as tournament_tables,
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE 'match%') as match_tables,
  (SELECT COUNT(*) FROM information_schema.views WHERE table_schema = DATABASE()) as views,
  (SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = DATABASE() AND routine_type = 'PROCEDURE') as procedures,
  (SELECT COUNT(*) FROM information_schema.triggers WHERE trigger_schema = DATABASE()) as triggers;
