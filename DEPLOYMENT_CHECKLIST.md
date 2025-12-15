# Registration Approval System - Deployment Checklist

## Pre-Deployment

### 1. Dependencies
- [ ] Composer is installed on server
- [ ] PHP 7.4+ is available
- [ ] MySQL/MariaDB is running
- [ ] PHPMailer installed via `composer install`

### 2. Database
- [ ] Backup current database before migration
- [ ] Run migration: `backend/database/add_registration_fields.sql`
- [ ] Verify new columns exist: 
  ```sql
  DESCRIBE tournament_participants;
  ```
- [ ] Check for errors in migration output

### 3. Email Configuration
- [ ] Copy `email_config.example.php` to `email_config.php`
- [ ] Update SMTP host, port, encryption
- [ ] Set SMTP username and password
- [ ] Configure "from" email and name
- [ ] Set correct `app_url` for your environment
- [ ] For Gmail: Generate App Password (not regular password)
- [ ] Test email sending with `test-email-notifications.php`

### 4. File Permissions
- [ ] Ensure `vendor/` directory is readable
- [ ] Verify `backend/config/email_config.php` has proper permissions
- [ ] Check `backend/templates/emails/` is readable
- [ ] Ensure error logs are writable

### 5. Security
- [ ] Verify `email_config.php` is in `.gitignore`
- [ ] Do NOT commit SMTP credentials to repository
- [ ] Consider using environment variables for production
- [ ] Enable HTTPS in production
- [ ] Set `debug => false` in email_config.php for production

## Testing

### 6. Registration Flow Testing
- [ ] Create test tournament
- [ ] Register as test player
- [ ] Fill all new fields (phone, experience, role, additional info)
- [ ] Submit registration
- [ ] Verify registration status is "pending"
- [ ] Check registration confirmation email received
- [ ] Verify in-app notification created

### 7. Approval Flow Testing
- [ ] Login as organizer
- [ ] Navigate to "Manage Tournaments"
- [ ] Click "View Participants"
- [ ] Verify all participant information displays correctly:
  - [ ] Username and email
  - [ ] Phone number
  - [ ] Experience level
  - [ ] Player role
  - [ ] Additional info
  - [ ] Notes to organizer
- [ ] Click "Approve" on pending participant
- [ ] Verify approval email sent
- [ ] Check status changed to "confirmed"
- [ ] Verify in-app notification created
- [ ] Check participant count incremented

### 8. Rejection Flow Testing
- [ ] Register another test player
- [ ] Click "Reject" on pending participant
- [ ] Enter rejection reason
- [ ] Verify rejection email sent with reason
- [ ] Check status changed to "rejected"
- [ ] Verify in-app notification created with reason
- [ ] Confirm participant count NOT incremented

### 9. Email Testing
- [ ] Test all three email types via `test-email-notifications.php`:
  - [ ] Registration Submitted
  - [ ] Registration Approved
  - [ ] Registration Rejected
- [ ] Check emails render correctly on:
  - [ ] Desktop email client
  - [ ] Mobile email client
  - [ ] Web mail (Gmail, Outlook, etc.)
- [ ] Verify links in emails work correctly
- [ ] Check spam folder if emails not arriving

### 10. Error Handling
- [ ] Test with invalid email addresses
- [ ] Test with SMTP server down (verify graceful failure)
- [ ] Test without required fields
- [ ] Verify error messages are user-friendly
- [ ] Check error logging works

## Post-Deployment

### 11. Monitoring
- [ ] Monitor email sending success rate
- [ ] Check PHP error logs regularly
- [ ] Watch for SMTP quota limits
- [ ] Track registration approval metrics
- [ ] Monitor database performance

### 12. Documentation
- [ ] Share `REGISTRATION_APPROVAL_IMPLEMENTATION.md` with team
- [ ] Document SMTP provider details
- [ ] Create runbook for common issues
- [ ] Train organizers on new approval workflow

### 13. Backup Plan
- [ ] Document rollback procedure
- [ ] Keep database backup before deployment
- [ ] Have old registration flow documented
- [ ] Test restore procedure

## Production Optimization

### 14. Performance
- [ ] Consider email queue for async sending
- [ ] Implement rate limiting on registration
- [ ] Cache email templates if high volume
- [ ] Monitor database query performance

### 15. Advanced Security
- [ ] Move credentials to environment variables
- [ ] Implement CSRF tokens on forms
- [ ] Add rate limiting on approve/reject actions
- [ ] Enable SQL injection prevention (already using prepared statements)
- [ ] Sanitize all user inputs (already implemented)

### 16. Maintenance
- [ ] Schedule regular email deliverability checks
- [ ] Plan for SMTP credential rotation
- [ ] Monitor PHPMailer for security updates
- [ ] Keep documentation updated

## Rollback Plan

### If Issues Arise

1. **Database Rollback:**
   ```sql
   ALTER TABLE tournament_participants
   DROP COLUMN phone_number,
   DROP COLUMN experience_level,
   DROP COLUMN player_role,
   DROP COLUMN additional_info;
   ```

2. **Code Rollback:**
   - Restore `tournament_api.php` from backup
   - Remove EmailNotification class
   - Revert frontend forms

3. **Emergency Fix:**
   - Set `registration_status = 'confirmed'` as default
   - Disable email sending temporarily
   - Manual approval via database

## Sign-Off

- [ ] Development team tested all features
- [ ] QA team verified functionality
- [ ] Organizers trained on new workflow
- [ ] Email deliverability confirmed
- [ ] Performance benchmarks met
- [ ] Security review completed
- [ ] Documentation updated
- [ ] Backup and rollback plan ready

## Contact Information

**Technical Support:**
- SMTP Issues: [Your SMTP Provider Support]
- Database Issues: [Your DBA Contact]
- Application Issues: [Your Dev Team Contact]

**Escalation:**
- Critical email failures: [Emergency Contact]
- Database corruption: [Emergency DBA Contact]

---

**Deployment Date:** _______________

**Deployed By:** _______________

**Verified By:** _______________

**Notes:**
_______________________________________________
_______________________________________________
_______________________________________________
