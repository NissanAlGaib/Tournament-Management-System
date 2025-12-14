# Tournament Management Features - Organizer Guide

## Overview
This guide explains how organizers can manage their tournaments, including viewing and managing participant registrations.

## Features

### 1. Manage Tournaments Dashboard
Organizers can access a dedicated dashboard to manage all their tournaments.

**Access:** Click "Manage Tournaments" in the sidebar navigation (visible only to Organizers and Admins)

**Features:**
- View all tournaments you've created
- See participant statistics at a glance:
  - Confirmed participants
  - Pending registrations
  - Rejected applications
- Quick access to participant management

### 2. Participant Management

#### View Participants
Click "Manage Participants" on any tournament card to view all registered participants.

**Information Displayed:**
- Username and email
- Team affiliation (for team-based tournaments)
- Registration status (Pending, Confirmed, Rejected, Withdrawn)
- Registration notes submitted by the participant

#### Approve Participants
For participants with "Pending" status:
1. Click the "Approve" button next to the participant
2. Confirm the action
3. The participant status will be updated to "Confirmed"
4. The participant count will be updated automatically

**Note:** Tournament capacity is enforced - you cannot approve participants if the tournament is full.

#### Reject Participants
For participants with "Pending" status:
1. Click the "Reject" button next to the participant
2. Confirm the action
3. The participant status will be updated to "Rejected"

### 3. Team Management (Team-Based Tournaments)

For team-based tournaments, click "View Teams" to see:
- Team names and tags
- Team captain
- Number of team members
- Team status

## API Endpoints

### Get Organized Tournaments
```
GET /backend/api/tournament_api.php?action=organized-tournaments
Headers: Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "tournaments": [
    {
      "id": 1,
      "name": "Tournament Name",
      "confirmed_count": 10,
      "pending_count": 3,
      "rejected_count": 1,
      ...
    }
  ]
}
```

### Get Tournament Participants
```
GET /backend/api/tournament_api.php?action=tournament-participants&tournament_id={id}
Headers: Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "participants": [
    {
      "id": 1,
      "username": "player1",
      "email": "player1@example.com",
      "registration_status": "pending",
      "registration_notes": "Notes...",
      "team_name": "Team Name" // if team-based
    }
  ]
}
```

### Approve Participant
```
POST /backend/api/tournament_api.php
Headers: 
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "action": "approve-participant",
  "participant_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Participant approved successfully"
}
```

### Reject Participant
```
POST /backend/api/tournament_api.php
Headers: 
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "action": "reject-participant",
  "participant_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Participant rejected successfully"
}
```

### Get Tournament Teams
```
GET /backend/api/tournament_api.php?action=tournament-teams&tournament_id={id}
Headers: Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "teams": [
    {
      "id": 1,
      "team_name": "Team Name",
      "team_tag": "TAG",
      "captain_name": "captain",
      "member_count": 5,
      "team_status": "active"
    }
  ]
}
```

## Security

### Authorization
All tournament management endpoints require:
- Valid JWT authentication token
- Organizer or Admin role
- Tournament ownership verification (organizer must own the tournament)

### Ownership Verification
Before any management action:
1. User's authentication is verified
2. User's role is checked (Organizer or Admin)
3. Tournament ownership is verified (organizer_id matches user_id OR user is Admin)

### SQL Injection Protection
All database queries use prepared statements with parameter binding.

### XSS Protection
All user-provided data is escaped before rendering in HTML.

## Frontend Implementation

### JavaScript API Client
The `TournamentAPI` object in `tournament.js` provides methods:

```javascript
// Get organized tournaments
const result = await TournamentAPI.getOrganizedTournaments();

// Get participants
const result = await TournamentAPI.getTournamentParticipants(tournamentId);

// Get teams
const result = await TournamentAPI.getTournamentTeams(tournamentId);

// Approve participant
const result = await TournamentAPI.approveParticipant(participantId);

// Reject participant
const result = await TournamentAPI.rejectParticipant(participantId);
```

### User Notifications
Modern toast notifications replace traditional alerts:
- Success notifications (green)
- Error notifications (red)
- Auto-dismiss after 3 seconds

## Database Schema

### Relevant Tables

**tournament_participants:**
- `id` - Participant record ID
- `tournament_id` - Tournament reference
- `user_id` - User reference
- `registration_status` - pending, confirmed, rejected, withdrawn, waitlist
- `registration_notes` - Optional notes from participant
- `registered_at` - Registration timestamp

**tournaments:**
- `organizer_id` - References the user who created the tournament
- `max_participants` - Maximum number of participants
- `current_participants` - Current confirmed count (auto-updated via triggers)

## Troubleshooting

### Cannot Approve Participant
**Error:** "Tournament is full"
**Solution:** Increase `max_participants` or reject other pending participants first

### Cannot See Participants
**Error:** "You do not have permission"
**Solution:** Verify you are the tournament organizer or have Admin role

### Participant Not Listed
**Possible Causes:**
- Participant hasn't registered yet
- Participant withdrew their registration
- Check the registration status filter

## Future Enhancements

Potential features for future versions:
- Bulk approve/reject actions
- Participant messaging system
- Registration status change notifications
- Participant search and filtering
- Export participant list
- Registration deadline management
- Waitlist management
