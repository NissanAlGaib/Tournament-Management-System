<?php
/**
 * Email Testing Script
 * 
 * This script tests the email notification system.
 * Run this from your browser to verify SMTP configuration.
 * 
 * Usage: http://localhost/Tournament-Management-System/test-email-notifications.php
 */

require_once 'vendor/autoload.php';
require_once 'backend/api/database.php';
require_once 'backend/classes/EmailNotification.class.php';

// Styling for output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Notification Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        button:hover {
            opacity: 0.9;
        }
        .code {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Notification Test</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Get database connection
                $database = new Database();
                $db = $database->getConnection();
                
                if (!$db) {
                    throw new Exception('Database connection failed');
                }
                
                // Create email notification instance
                $emailNotification = new EmailNotification($db);
                
                // Get form data
                $testEmail = $_POST['test_email'];
                $testType = $_POST['test_type'];
                $userName = $_POST['user_name'] ?? 'Test User';
                $tournamentName = $_POST['tournament_name'] ?? 'Test Tournament';
                
                echo '<div class="info"><strong>Testing Email Configuration...</strong></div>';
                
                // Send test email based on type
                $result = false;
                
                switch ($testType) {
                    case 'registration_submitted':
                        $result = $emailNotification->sendRegistrationSubmitted(
                            $testEmail,
                            $userName,
                            $tournamentName,
                            999
                        );
                        break;
                        
                    case 'registration_approved':
                        $result = $emailNotification->sendRegistrationApproved(
                            $testEmail,
                            $userName,
                            $tournamentName,
                            999,
                            date('Y-m-d H:i:s', strtotime('+7 days'))
                        );
                        break;
                        
                    case 'registration_rejected':
                        $result = $emailNotification->sendRegistrationRejected(
                            $testEmail,
                            $userName,
                            $tournamentName,
                            'This is a test rejection reason.'
                        );
                        break;
                }
                
                if ($result) {
                    echo '<div class="success">';
                    echo '<strong>✅ Email Sent Successfully!</strong><br>';
                    echo "Test email sent to: <strong>$testEmail</strong><br>";
                    echo "Email Type: <strong>" . ucwords(str_replace('_', ' ', $testType)) . "</strong><br>";
                    echo "Check your inbox (and spam folder) for the test email.";
                    echo '</div>';
                } else {
                    echo '<div class="error">';
                    echo '<strong>❌ Email Failed to Send</strong><br>';
                    echo 'Check the following:<br>';
                    echo '1. SMTP credentials in backend/config/email_config.php<br>';
                    echo '2. PHP error log for details<br>';
                    echo '3. Enable debug mode in email_config.php';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>❌ Error:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
                
                echo '<div class="info">';
                echo '<strong>Troubleshooting Steps:</strong><br>';
                echo '1. Verify database connection<br>';
                echo '2. Check if PHPMailer is installed (composer install)<br>';
                echo '3. Verify email_config.php exists and has correct SMTP settings<br>';
                echo '4. Enable debug mode in email_config.php';
                echo '</div>';
            }
        }
        ?>
        
        <form method="POST">
            <label for="test_email">Test Email Address *</label>
            <input type="email" id="test_email" name="test_email" required 
                   placeholder="your-email@example.com"
                   value="<?php echo htmlspecialchars($_POST['test_email'] ?? ''); ?>">
            
            <label for="test_type">Email Type to Test *</label>
            <select id="test_type" name="test_type" required>
                <option value="registration_submitted">Registration Submitted</option>
                <option value="registration_approved">Registration Approved</option>
                <option value="registration_rejected">Registration Rejected</option>
            </select>
            
            <label for="user_name">Test User Name</label>
            <input type="text" id="user_name" name="user_name" 
                   placeholder="Test User"
                   value="<?php echo htmlspecialchars($_POST['user_name'] ?? 'Test User'); ?>">
            
            <label for="tournament_name">Test Tournament Name</label>
            <input type="text" id="tournament_name" name="tournament_name" 
                   placeholder="Test Tournament"
                   value="<?php echo htmlspecialchars($_POST['tournament_name'] ?? 'Test Tournament'); ?>">
            
            <button type="submit">Send Test Email</button>
        </form>
        
        <div class="info" style="margin-top: 30px;">
            <strong>📝 Configuration Check:</strong><br>
            <?php
            $configPath = 'backend/config/email_config.php';
            if (file_exists($configPath)) {
                echo "✅ email_config.php exists<br>";
                
                $config = require $configPath;
                echo "SMTP Host: <strong>" . htmlspecialchars($config['smtp']['host']) . "</strong><br>";
                echo "SMTP Port: <strong>" . htmlspecialchars($config['smtp']['port']) . "</strong><br>";
                echo "SMTP Username: <strong>" . htmlspecialchars($config['smtp']['username']) . "</strong><br>";
                echo "From Email: <strong>" . htmlspecialchars($config['from']['email']) . "</strong><br>";
                
                if (empty($config['smtp']['username']) || empty($config['smtp']['password'])) {
                    echo '<br><span style="color: #dc3545;">⚠️ Warning: SMTP credentials appear to be empty. Please update email_config.php</span>';
                }
            } else {
                echo "❌ email_config.php not found<br>";
                echo "Please copy email_config.example.php to email_config.php and configure it.";
            }
            
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                echo "<br>✅ PHPMailer is installed";
            } else {
                echo "<br>❌ PHPMailer not found. Run: composer install";
            }
            ?>
        </div>
        
        <div class="info" style="margin-top: 20px;">
            <strong>💡 Tip:</strong> For Gmail users, you need to:<br>
            1. Enable 2-Factor Authentication<br>
            2. Generate an App Password at: 
            <a href="https://myaccount.google.com/apppasswords" target="_blank">
                Google App Passwords
            </a><br>
            3. Use the 16-character app password (not your regular password)
        </div>
    </div>
</body>
</html>
