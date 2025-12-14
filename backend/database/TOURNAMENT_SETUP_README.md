# Tournament Management System - Database Setup Guide

## Overview
This guide explains how to integrate the tournament management database schema into your existing Tournament Management System.

## File Information
- **File**: `tournament_management.sql`
- **Purpose**: Complete database schema for tournament features
- **Compatible with**: MySQL 5.7+, MariaDB 10.2+

## What's Included

### Core Tables

#### 1. **tournaments**
Main tournament table with comprehensive configuration options:
- Tournament configuration (format, size, rules)
- Registration requirements and deadlines
- Status management (draft, open, ongoing, completed, cancelled)
- Prize pool information
- Visibility settings (public, private, invite-only)

#### 2. **tournament_requirements**
Define specific requirements for tournament registration:
- Skill level, rank, age, country requirements
- Mandatory vs optional requirements
- Custom requirement types

#### 3. **tournament_participants**
Track registered players:
- Registration status (pending, confirmed, waitlist, rejected)
- Payment status
- Check-in status
- Seed numbers for bracket placement

#### 4. **tournament_brackets**
Tournament structure organization:
- Winners/losers brackets
- Round organization
- Support for different tournament formats

#### 5. **matches**
Individual match tracking:
- Match scheduling
- Score tracking
- Result verification
- Match progression (next match routing)

#### 6. **match_results**
Detailed game-by-game results:
- Best-of-X match support
- Individual game scores
- Duration tracking

#### 7. **tournament_prizes**
Prize pool and reward management:
- Multiple prize types (cash, trophy, medal, points, items)
- Placement-based prizes
- Award tracking

#### 8. **tournament_standings**
Real-time rankings and statistics:
- Win/loss records
- Points system
- Score differential
- Final placements

#### 9. **tournament_notifications**
Communication system:
- Announcements
- Schedule changes
- Result notifications
- Target-specific messaging

#### 10. **tournament_teams** (Optional)
Team tournament support:
- Team creation and management
- Team member tracking
- Captain assignment

#### 11. **tournament_activity_log**
Audit trail:
- All tournament actions logged
- User tracking
- IP address logging
- Metadata storage

### Additional Features

#### Views
- `active_tournaments` - Quick access to active tournaments
- `tournament_leaderboard` - Real-time leaderboard data
- `upcoming_matches` - Scheduled matches
- `prize_distribution` - Prize allocation overview

#### Stored Procedures
- `update_tournament_participant_count()` - Automatically update participant counts
- `update_match_result()` - Update match results and standings in one transaction

#### Triggers
- Auto-update participant counts on registration
- Activity logging on tournament status changes
- Automatic standings updates

## Installation Instructions

### Method 1: MySQL Command Line

```bash
# Navigate to the database directory
cd backend/database

# Import the SQL file
mysql -u root -p tournament_db < tournament_management.sql
```

### Method 2: phpMyAdmin

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select your `tournament_db` database
3. Click on the "Import" tab
4. Click "Choose File" and select `tournament_management.sql`
5. Click "Go" at the bottom of the page
6. Wait for the success message

### Method 3: MySQL Workbench

1. Open MySQL Workbench
2. Connect to your database server
3. Go to File > Run SQL Script
4. Select `tournament_management.sql`
5. Choose `tournament_db` as the default schema
6. Click "Run"

## Prerequisites

Before running this script, ensure:

1. ✅ `tournament_db` database exists
2. ✅ `users` table exists (created by setup_roles.sql)
3. ✅ `setup_roles.sql` has been executed (for roles, users, and user_roles tables)

## Verification

After installation, verify the tables were created:

```sql
-- Show all tournament-related tables
SHOW TABLES LIKE 'tournament%';
SHOW TABLES LIKE 'match%';

-- Check a specific table structure
DESCRIBE tournaments;

-- Verify views were created
SHOW FULL TABLES WHERE Table_type = 'VIEW';

-- Check stored procedures
SHOW PROCEDURE STATUS WHERE Db = 'tournament_db';

-- Check triggers
SHOW TRIGGERS FROM tournament_db;
```

Expected output:
- 11 new tables (tournaments, tournament_participants, matches, etc.)
- 4 views (active_tournaments, tournament_leaderboard, etc.)
- 2 stored procedures
- 3 triggers

## Usage Examples

### Create a Tournament

```sql
INSERT INTO tournaments 
  (organizer_id, name, description, format, tournament_size, registration_deadline, start_date, status)
VALUES 
  (1, 'Summer Championship 2024', 'Epic summer tournament', 'single_elimination', 64,
   '2024-07-01 23:59:59', '2024-07-15 10:00:00', 'open');
```

### Add Tournament Prizes

```sql
INSERT INTO tournament_prizes 
  (tournament_id, placement, prize_type, prize_amount, currency, prize_description)
VALUES 
  (1, 1, 'cash', 5000.00, 'USD', 'First Place - Champion'),
  (1, 2, 'cash', 2500.00, 'USD', 'Second Place'),
  (1, 3, 'cash', 1000.00, 'USD', 'Third Place');
```

### Register a Participant

```sql
INSERT INTO tournament_participants 
  (tournament_id, user_id, registration_status)
VALUES 
  (1, 5, 'confirmed');
```

### View Active Tournaments

```sql
SELECT * FROM active_tournaments 
WHERE status = 'open' 
ORDER BY start_date;
```

### Check Tournament Leaderboard

```sql
SELECT * FROM tournament_leaderboard 
WHERE tournament_id = 1 
ORDER BY current_rank;
```

## Integration with Existing Code

### PHP API Integration

Create a new API file: `backend/api/tournament_api.php`

```php
<?php
require_once '../classes/Database.php';
require_once '../middleware/auth_middleware.php';

$auth = getAuthMiddleware();
$user = $auth->requireRole(['Organizer', 'Admin']);

$database = new Database();
$db = $database->getConnection();

// Example: Create tournament
if ($_POST['action'] === 'create_tournament') {
    $query = "INSERT INTO tournaments (organizer_id, name, description, ...) VALUES (?, ?, ?, ...)";
    $stmt = $db->prepare($query);
    // ... bind parameters and execute
}
?>
```

### Frontend Integration

Update the tournaments page to fetch from the database:

```javascript
// frontend/src/js/tournaments.js
async function loadTournaments() {
    const response = await fetch('/backend/api/tournament_api.php?action=get_tournaments', {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    });
    const tournaments = await response.json();
    // Render tournaments
}
```

## Database Relationships

```
users (existing)
  ├── tournaments (organizer_id)
  ├── tournament_participants (user_id)
  └── tournament_teams (captain_user_id)

tournaments
  ├── tournament_requirements
  ├── tournament_participants
  ├── tournament_brackets
  ├── matches
  ├── tournament_prizes
  ├── tournament_standings
  ├── tournament_notifications
  ├── tournament_teams
  └── tournament_activity_log

tournament_participants
  ├── matches (participant1_id, participant2_id, winner_id)
  ├── tournament_standings
  └── tournament_prizes (awarded_to_participant_id)

matches
  └── match_results
```

## Security Considerations

1. **Organizer Verification**: Always verify that the user has the 'Organizer' role before allowing tournament creation
2. **Input Validation**: Sanitize all user inputs before database insertion
3. **SQL Injection**: Use prepared statements (already implemented in your codebase)
4. **Authorization**: Check ownership before allowing tournament modifications

## Maintenance

### Regular Tasks

```sql
-- Clean up old completed tournaments (older than 1 year)
DELETE FROM tournaments 
WHERE status = 'completed' 
AND completed_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Archive old sessions
DELETE FROM sessions 
WHERE expires_at < NOW();

-- Update rankings
CALL update_tournament_participant_count(1);
```

### Backup

```bash
# Backup tournament data
mysqldump -u root -p tournament_db \
  tournaments tournament_participants matches tournament_prizes \
  > backup_tournaments_$(date +%Y%m%d).sql
```

## Troubleshooting

### Issue: Foreign Key Constraint Fails

**Solution**: Ensure `users` table exists before running this script.

```sql
-- Check if users table exists
SHOW TABLES LIKE 'users';

-- If not, run setup_roles.sql first or create users table
```

### Issue: Views Already Exist

**Solution**: Drop existing views before re-running.

```sql
DROP VIEW IF EXISTS active_tournaments;
DROP VIEW IF EXISTS tournament_leaderboard;
DROP VIEW IF EXISTS upcoming_matches;
DROP VIEW IF EXISTS prize_distribution;
```

### Issue: Procedure Already Exists

**Solution**: Drop procedures before re-running.

```sql
DROP PROCEDURE IF EXISTS update_tournament_participant_count;
DROP PROCEDURE IF EXISTS update_match_result;
```

## Next Steps

After setting up the database:

1. ✅ Create PHP API endpoints for tournament management
2. ✅ Update frontend pages to display real tournament data
3. ✅ Implement tournament creation form for organizers
4. ✅ Add match result submission functionality
5. ✅ Create tournament bracket visualization
6. ✅ Implement real-time standings updates

## Support

For issues or questions:
- Check the main README.md in the project root
- Review SETUP_GUIDE.md for general setup
- Consult ROLE_REFERENCE.md for role-based access control

## License

This database schema is part of the Tournament Management System project and follows the same license.
