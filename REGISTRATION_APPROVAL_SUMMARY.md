# Registration Approval Implementation Summary

## Quick Start

### 1. Install PHPMailer
```bash
cd "c:\xampp\htdocs\GitHub Repos\Tournament-Management-System"
composer install
```

### 2. Run Database Migration
```bash
mysql -u root -p tournament_management < backend/database/add_registration_fields.sql
```

### 3. Configure Email (backend/config/email_config.php)
```php
'smtp' => [
    'host' => 'smtp.gmail.com',
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',  // Use Gmail App Password
],
```

## What Changed

### Backend Changes

**New Files:**
- `backend/classes/EmailNotification.class.php` - Email sending class
- `backend/config/email_config.php` - SMTP configuration
- `backend/templates/emails/registration_submitted.php` - Email template
- `backend/templates/emails/registration_approved.php` - Email template
- `backend/templates/emails/registration_rejected.php` - Email template
- `backend/database/add_registration_fields.sql` - Database migration
- `composer.json` - PHPMailer dependency

**Modified Files:**
- `backend/api/tournament_api.php`:
  - Line ~8: Added EmailNotification class include
  - Line ~620: Changed registration status from 'confirmed' to 'pending'
  - Line ~636: Added new registration fields (phone, experience, role, additional_info)
  - Line ~645: Send registration confirmation email
  - Line ~1040: Send approval email + in-app notification
  - Line ~1080: Send rejection email + in-app notification with reason

### Frontend Changes

**Modified Files:**
- `frontend/app/views/pages/home/tournament-details.php`:
  - Line ~85-120: Added new form fields (phone, experience level, role, additional info)
  - Line ~128: Added pending approval notice
  - Line ~680: Include new fields in form submission
  - Line ~725: Updated success message to mention pending approval

- `frontend/app/views/pages/home/manage-tournaments.php`:
  - Line ~217-265: Enhanced participant display with all registration fields
  - Line ~327: Added rejection reason prompt
  - Line ~345: Send reason to API

### Database Changes

**New Columns in `tournament_participants`:**
- `phone_number` (VARCHAR) - Optional contact number
- `experience_level` (ENUM) - beginner|intermediate|advanced|professional
- `player_role` (VARCHAR) - Preferred role/position
- `additional_info` (TEXT) - Extra player information

## Workflow

### Player Registration
1. Player fills registration form with additional details
2. Status set to **'pending'**
3. Email sent: "Registration Submitted"
4. In-app notification created
5. Organizer sees application in participant list

### Organizer Approval
1. Organizer clicks "Approve" button
2. Status changed to **'confirmed'**
3. Email sent: "Registration Approved"
4. In-app notification created
5. Participant count incremented

### Organizer Rejection
1. Organizer clicks "Reject" button
2. Prompted to enter reason (optional)
3. Status changed to **'rejected'**
4. Email sent: "Registration Not Approved" (with reason if provided)
5. In-app notification created

## API Changes

### Register Endpoint (POST)
**New Parameters:**
```json
{
  "action": "register",
  "tournament_id": 123,
  "notes": "Note to organizer",
  "phone_number": "+1234567890",
  "experience_level": "intermediate",
  "player_role": "DPS",
  "additional_info": "Available weekends only"
}
```

**Response:**
- Status now defaults to 'pending' instead of 'confirmed'
- Email notification sent automatically

### Approve Participant (POST)
**Endpoint:** `tournament_api.php?action=approve-participant`
```json
{
  "action": "approve-participant",
  "participant_id": 456
}
```

**New Behavior:**
- Sends approval email to participant
- Creates in-app notification
- Includes tournament start date in email if available

### Reject Participant (POST)
**Endpoint:** `tournament_api.php?action=reject-participant`
```json
{
  "action": "reject-participant",
  "participant_id": 456,
  "reason": "Tournament is full for your skill level"
}
```

**New Behavior:**
- Sends rejection email with optional reason
- Creates in-app notification with reason
- Reason displayed in email if provided

## Configuration

### SMTP Settings
File: `backend/config/email_config.php`

**Gmail Example:**
```php
'smtp' => [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'youremail@gmail.com',
    'password' => 'xxxx-xxxx-xxxx-xxxx',  // App Password
    'encryption' => 'tls',
],
```

**Required for Gmail:**
1. Enable 2-Factor Authentication
2. Generate App Password (Google Account → Security → App Passwords)
3. Use app password (not your regular password)

### Application Settings
```php
'from' => [
    'email' => 'noreply@tournament-management.com',
    'name' => 'Tournament Management System',
],

'app_url' => 'http://localhost/Tournament-Management-System',

'settings' => [
    'is_html' => true,
    'charset' => 'UTF-8',
    'debug' => false,  // Set true for debugging
],
```

## Testing Checklist

- [ ] Database migration successful
- [ ] PHPMailer installed via Composer
- [ ] SMTP credentials configured
- [ ] Test registration creates pending status
- [ ] Registration email received
- [ ] In-app notification created on registration
- [ ] Organizer sees all participant info in management panel
- [ ] Approve button sends email and updates status
- [ ] Reject button prompts for reason
- [ ] Rejection email includes reason
- [ ] All new form fields save correctly

## Error Handling

### Email Failures
- Emails are sent in try-catch blocks
- Registration/approval/rejection succeeds even if email fails
- Errors logged to PHP error log
- Check: `C:\xampp\apache\logs\error.log`

### Debug Mode
Enable in `email_config.php`:
```php
'settings' => [
    'debug' => true,  // Shows SMTP conversation
],
```

## Key Benefits

✅ **Better Player Information**: Collect contact details, experience, role preferences
✅ **Controlled Admissions**: Organizers review before confirming
✅ **Professional Communication**: Automated email notifications
✅ **Dual Notifications**: Both email and in-app notifications
✅ **Rejection Feedback**: Organizers can explain rejection reasons
✅ **No Breaking Changes**: Existing tournaments unaffected

## Dependencies

- **PHPMailer 6.9+**: Email sending library
- **PHP 7.4+**: Required for PHPMailer
- **MySQL**: Database with updated schema
- **SMTP Server**: Gmail, SendGrid, or similar

## Security Notes

⚠️ **Important:**
- Never commit SMTP credentials to version control
- Use environment variables in production
- Consider rate limiting to prevent spam
- Validate all user inputs
- Sanitize data displayed in emails

## Support Resources

- PHPMailer Docs: https://github.com/PHPMailer/PHPMailer/wiki
- Gmail App Passwords: https://support.google.com/accounts/answer/185833
- SMTP Troubleshooting: Check error logs and enable debug mode
