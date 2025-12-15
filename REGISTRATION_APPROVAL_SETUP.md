# Registration Approval System - Setup Guide

## Overview
This implementation adds a pending registration approval workflow where:
- Players submit registration applications with additional information
- Registrations default to "pending" status and require organizer approval
- Email notifications are sent when applications are approved or rejected
- In-app notifications complement email notifications

## Database Setup

### Step 1: Run Database Migration

Execute the SQL migration to add new fields for player registration:

```bash
mysql -u your_username -p tournament_management < backend/database/add_registration_fields.sql
```

Or manually run the SQL file in phpMyAdmin or your preferred MySQL client.

**New columns added to `tournament_participants` table:**
- `phone_number` (VARCHAR) - Player contact number
- `experience_level` (ENUM) - beginner, intermediate, advanced, professional
- `player_role` (VARCHAR) - Preferred position/role
- `additional_info` (TEXT) - Any extra information from the player

## PHPMailer Installation

### Step 2: Install PHPMailer via Composer

Navigate to the project root directory and run:

```bash
cd "c:\xampp\htdocs\GitHub Repos\Tournament-Management-System"
composer install
```

If you don't have Composer installed, download it from: https://getcomposer.org/download/

Alternatively, download PHPMailer manually:
1. Download from: https://github.com/PHPMailer/PHPMailer/releases
2. Extract to `vendor/phpmailer/phpmailer/`
3. Create autoload: `vendor/autoload.php`

## Email Configuration

### Step 3: Configure SMTP Settings

Edit `backend/config/email_config.php` and update the following:

```php
'smtp' => [
    'host' => 'smtp.gmail.com',        // Your SMTP server
    'port' => 587,                      // SMTP port
    'username' => 'your-email@gmail.com',  // SMTP username
    'password' => 'your-app-password',     // SMTP password or app password
    'encryption' => 'tls',              // tls or ssl
],

'from' => [
    'email' => 'noreply@tournament-management.com',
    'name' => 'Tournament Management System',
],

'app_url' => 'http://localhost/Tournament-Management-System',
```

### Gmail Configuration (Recommended for Testing)

If using Gmail:

1. **Enable 2-Factor Authentication** on your Google account
2. **Generate an App Password:**
   - Go to Google Account Settings → Security
   - Under "2-Step Verification", select "App passwords"
   - Generate a password for "Mail"
   - Use this password in `email_config.php`

3. Update settings:
   ```php
   'smtp' => [
       'host' => 'smtp.gmail.com',
       'port' => 587,
       'username' => 'youremail@gmail.com',
       'password' => 'your-16-digit-app-password',
       'encryption' => 'tls',
   ],
   ```

### Other SMTP Providers

**SendGrid:**
```php
'host' => 'smtp.sendgrid.net',
'port' => 587,
'username' => 'apikey',
'password' => 'your-sendgrid-api-key',
```

**Mailgun:**
```php
'host' => 'smtp.mailgun.org',
'port' => 587,
'username' => 'your-mailgun-smtp-username',
'password' => 'your-mailgun-smtp-password',
```

**Office 365:**
```php
'host' => 'smtp.office365.com',
'port' => 587,
'username' => 'youremail@yourdomain.com',
'password' => 'your-password',
```

## Testing the Implementation

### Step 4: Test Registration Flow

1. **Player Registration:**
   - Navigate to a tournament details page
   - Click "Join Tournament"
   - Fill out the registration form with:
     - Phone number
     - Experience level
     - Player role (if applicable)
     - Additional information
     - Notes to organizer
   - Submit the form
   - Verify:
     - Success message mentions "pending approval"
     - Registration email sent to player
     - In-app notification created

2. **Organizer Approval:**
   - Login as tournament organizer
   - Navigate to "Manage Tournaments"
   - Click "View Participants" on a tournament
   - Verify pending participants are visible with all submitted information
   - Click "Approve" on a pending registration
   - Verify:
     - Approval email sent to player
     - In-app notification created
     - Participant status updated to "confirmed"

3. **Organizer Rejection:**
   - Click "Reject" on a pending registration
   - Enter a reason (optional)
   - Verify:
     - Rejection email sent to player with reason
     - In-app notification created
     - Participant status updated to "rejected"

## Features Implemented

### 1. Additional Registration Fields
Players now provide:
- **Phone Number**: Contact information for urgent updates
- **Experience Level**: Skill categorization (Beginner → Professional)
- **Player Role**: Preferred position/role in team-based tournaments
- **Additional Info**: Open text for any relevant details

### 2. Pending Status Workflow
- All new registrations default to `pending` status
- Organizers must manually approve or reject applications
- Tournament participant count only increases when status is `confirmed`

### 3. Email Notifications
Three email templates created:
- **Registration Submitted**: Sent immediately upon registration
- **Registration Approved**: Sent when organizer approves
- **Registration Rejected**: Sent when organizer rejects (includes reason)

### 4. In-App Notifications
Complementary in-app notifications created for:
- Registration submission confirmation
- Approval notification
- Rejection notification

### 5. Enhanced UI
- Registration form displays all new fields
- Clear "pending approval" messaging
- Participant management shows all submitted information
- Rejection includes reason prompt

## Troubleshooting

### Emails Not Sending

1. **Check SMTP credentials** in `email_config.php`
2. **Enable debug mode:**
   ```php
   'settings' => [
       'debug' => true,
   ],
   ```
3. **Check PHP error logs:**
   - XAMPP: `C:\xampp\apache\logs\error.log`
   - Check browser console for fetch errors

4. **Test SMTP connection:**
   ```php
   // Create test script: test-email.php
   require_once 'vendor/autoload.php';
   include 'backend/classes/EmailNotification.class.php';
   include 'backend/api/database.php';
   
   $database = new Database();
   $db = $database->getConnection();
   $email = new EmailNotification($db);
   
   $result = $email->sendRegistrationSubmitted(
       'test@example.com',
       'Test User',
       'Test Tournament',
       1
   );
   
   echo $result ? "Email sent!" : "Failed to send email";
   ```

### Database Errors

If you get column not found errors:
1. Verify migration was run successfully
2. Check column names match exactly
3. Run: `DESCRIBE tournament_participants;` to verify schema

### Permission Issues

Ensure the organizer/admin checking:
```php
// In tournament_api.php approve/reject actions
$roles = array_column($user['roles'], 'role_name');
if ($participant['organizer_id'] != $user['user_id'] && !in_array('Admin', $roles))
```

## Production Considerations

### Security
1. **Move SMTP credentials to environment variables:**
   ```php
   'username' => $_ENV['SMTP_USERNAME'],
   'password' => $_ENV['SMTP_PASSWORD'],
   ```

2. **Use .env file** (with vlucas/phpdotenv package)

3. **Add rate limiting** to prevent email spam

### Performance
1. **Queue emails** for async sending (use message queue like RabbitMQ/Redis)
2. **Batch notifications** for bulk operations
3. **Cache email templates**

### Monitoring
1. Log all email sending attempts
2. Track delivery rates
3. Monitor SMTP quota usage

## File Structure

```
Tournament-Management-System/
├── backend/
│   ├── api/
│   │   └── tournament_api.php          # Updated with approval workflow
│   ├── classes/
│   │   └── EmailNotification.class.php # New email handler class
│   ├── config/
│   │   └── email_config.php            # New SMTP configuration
│   ├── database/
│   │   └── add_registration_fields.sql # New migration file
│   └── templates/
│       └── emails/
│           ├── registration_submitted.php
│           ├── registration_approved.php
│           └── registration_rejected.php
├── frontend/
│   └── app/
│       └── views/
│           └── pages/
│               └── home/
│                   ├── tournament-details.php      # Updated registration form
│                   └── manage-tournaments.php      # Updated participant management
├── vendor/                              # Composer dependencies
├── composer.json                        # PHPMailer dependency
└── REGISTRATION_APPROVAL_SETUP.md      # This file
```

## Support

For issues or questions:
1. Check PHPMailer documentation: https://github.com/PHPMailer/PHPMailer
2. Verify database schema matches migration
3. Test SMTP connection independently
4. Check error logs for detailed information

## Future Enhancements

Potential improvements:
- Bulk approve/reject operations
- Email template customization via admin panel
- SMS notifications integration
- Automated approval based on criteria
- Registration waitlist management
- Payment integration with approval workflow
