<?php
/**
 * Email Configuration Example
 * 
 * Copy this file to email_config.php and update with your SMTP settings.
 * DO NOT commit email_config.php with real credentials to version control!
 */

return [
    // SMTP Settings
    'smtp' => [
        'host' => 'smtp.example.com',    // Your SMTP host (e.g., smtp.gmail.com)
        'port' => 587,                    // 587 for TLS, 465 for SSL
        'username' => 'your-email@example.com',  // Your SMTP username/email
        'password' => 'your-password-here',      // Your SMTP password or app password
        'encryption' => 'tls',            // 'tls' or 'ssl'
    ],
    
    // Sender Information
    'from' => [
        'email' => 'noreply@tournament-management.com',
        'name' => 'Tournament Management System',
    ],
    
    // Email Settings
    'settings' => [
        'is_html' => true,
        'charset' => 'UTF-8',
        'debug' => false,  // Set to true for debugging SMTP issues
    ],
    
    // Application URLs - Update these to match your environment
    'app_url' => 'http://localhost/Tournament-Management-System',
    'logo_url' => 'http://localhost/Tournament-Management-System/assets/logo.png',
];
