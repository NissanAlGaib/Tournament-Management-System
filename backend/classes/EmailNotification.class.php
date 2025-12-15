<?php
/**
 * EmailNotification Class
 * 
 * Handles sending email notifications using PHPMailer.
 * Supports tournament registration, approval, and rejection notifications.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailNotification {
    private $config;
    private $mailer;
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->config = require_once __DIR__ . '/../config/email_config.php';
        $this->initializeMailer();
    }
    
    /**
     * Initialize PHPMailer with configuration
     */
    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp']['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['smtp']['username'];
            $this->mailer->Password = $this->config['smtp']['password'];
            $this->mailer->SMTPSecure = $this->config['smtp']['encryption'];
            $this->mailer->Port = $this->config['smtp']['port'];
            
            // Content settings
            $this->mailer->isHTML($this->config['settings']['is_html']);
            $this->mailer->CharSet = $this->config['settings']['charset'];
            
            // Debug mode
            if ($this->config['settings']['debug']) {
                $this->mailer->SMTPDebug = 2;
            }
            
            // Set default sender
            $this->mailer->setFrom(
                $this->config['from']['email'],
                $this->config['from']['name']
            );
        } catch (Exception $e) {
            error_log("PHPMailer initialization error: " . $e->getMessage());
        }
    }
    
    /**
     * Send registration submission confirmation email
     */
    public function sendRegistrationSubmitted($userEmail, $userName, $tournamentName, $tournamentId) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $userName);
            
            $this->mailer->Subject = "Tournament Registration Submitted - $tournamentName";
            
            $body = $this->getTemplate('registration_submitted', [
                'userName' => $userName,
                'tournamentName' => $tournamentName,
                'tournamentId' => $tournamentId,
            ]);
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send registration approval email
     */
    public function sendRegistrationApproved($userEmail, $userName, $tournamentName, $tournamentId, $tournamentStartDate = null) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $userName);
            
            $this->mailer->Subject = "Registration Approved - $tournamentName";
            
            $body = $this->getTemplate('registration_approved', [
                'userName' => $userName,
                'tournamentName' => $tournamentName,
                'tournamentId' => $tournamentId,
                'tournamentStartDate' => $tournamentStartDate,
            ]);
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send registration rejection email
     */
    public function sendRegistrationRejected($userEmail, $userName, $tournamentName, $reason = null) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $userName);
            
            $this->mailer->Subject = "Registration Status Update - $tournamentName";
            
            $body = $this->getTemplate('registration_rejected', [
                'userName' => $userName,
                'tournamentName' => $tournamentName,
                'reason' => $reason,
            ]);
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get email template with variables replaced
     */
    private function getTemplate($templateName, $variables) {
        $templatePath = __DIR__ . "/../templates/emails/{$templateName}.php";
        
        if (!file_exists($templatePath)) {
            return $this->getDefaultTemplate($templateName, $variables);
        }
        
        // Extract variables for use in template
        extract($variables);
        extract(['appUrl' => $this->config['app_url']]);
        extract(['logoUrl' => $this->config['logo_url']]);
        
        // Start output buffering
        ob_start();
        include $templatePath;
        $content = ob_get_clean();
        
        return $content;
    }
    
    /**
     * Get default template if custom template doesn't exist
     */
    private function getDefaultTemplate($templateName, $variables) {
        $appUrl = $this->config['app_url'];
        
        switch ($templateName) {
            case 'registration_submitted':
                return "
                    <h2>Registration Submitted</h2>
                    <p>Hello {$variables['userName']},</p>
                    <p>Your registration for <strong>{$variables['tournamentName']}</strong> has been submitted successfully.</p>
                    <p>Your application is currently pending review by the tournament organizer. You will receive an email notification once your registration is reviewed.</p>
                    <p><a href='{$appUrl}/frontend/app/views/pages/home/tournament-details.php?id={$variables['tournamentId']}'>View Tournament Details</a></p>
                    <p>Thank you!</p>
                ";
                
            case 'registration_approved':
                $dateInfo = $variables['tournamentStartDate'] 
                    ? "<p><strong>Tournament Start Date:</strong> {$variables['tournamentStartDate']}</p>"
                    : "";
                return "
                    <h2>Registration Approved!</h2>
                    <p>Hello {$variables['userName']},</p>
                    <p>Great news! Your registration for <strong>{$variables['tournamentName']}</strong> has been approved.</p>
                    {$dateInfo}
                    <p>You are now confirmed as a participant. Please check the tournament details for more information about schedules, rules, and check-in procedures.</p>
                    <p><a href='{$appUrl}/frontend/app/views/pages/home/tournament-details.php?id={$variables['tournamentId']}'>View Tournament Details</a></p>
                    <p>Good luck in the tournament!</p>
                ";
                
            case 'registration_rejected':
                $reasonInfo = $variables['reason'] 
                    ? "<p><strong>Reason:</strong> {$variables['reason']}</p>"
                    : "";
                return "
                    <h2>Registration Status Update</h2>
                    <p>Hello {$variables['userName']},</p>
                    <p>We regret to inform you that your registration for <strong>{$variables['tournamentName']}</strong> was not approved.</p>
                    {$reasonInfo}
                    <p>If you have any questions, please contact the tournament organizer.</p>
                    <p>Thank you for your interest!</p>
                ";
                
            default:
                return "<p>Tournament notification</p>";
        }
    }
    
    /**
     * Create in-app notification
     */
    public function createInAppNotification($tournamentId, $userId, $type, $title, $message) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO tournament_notifications 
                (tournament_id, notification_type, title, message, target_audience, target_user_id, created_by)
                VALUES (?, ?, ?, ?, 'specific_user', ?, 1)
            ");
            
            $stmt->execute([$tournamentId, $type, $title, $message, $userId]);
            return true;
        } catch (Exception $e) {
            error_log("Failed to create in-app notification: " . $e->getMessage());
            return false;
        }
    }
}
