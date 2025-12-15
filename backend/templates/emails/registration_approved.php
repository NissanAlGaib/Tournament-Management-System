<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Approved</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 40px; text-align: center; border-radius: 8px 8px 0 0;">
                            <div style="font-size: 48px; margin-bottom: 10px;">🎉</div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px;">Registration Approved!</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="font-size: 16px; color: #333333; line-height: 1.6; margin: 0 0 20px 0;">
                                Hello <strong><?php echo htmlspecialchars($userName); ?></strong>,
                            </p>
                            
                            <p style="font-size: 16px; color: #333333; line-height: 1.6; margin: 0 0 20px 0;">
                                Great news! Your registration for <strong><?php echo htmlspecialchars($tournamentName); ?></strong> has been approved by the tournament organizer.
                            </p>
                            
                            <div style="background-color: #d1fae5; border-left: 4px solid #10b981; padding: 20px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; font-size: 16px; color: #065f46;">
                                    <strong>✅ Status:</strong> Confirmed Participant
                                </p>
                                <?php if ($tournamentStartDate): ?>
                                <p style="margin: 10px 0 0 0; font-size: 14px; color: #047857;">
                                    <strong>📅 Start Date:</strong> <?php echo htmlspecialchars($tournamentStartDate); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            
                            <h3 style="color: #333; font-size: 18px; margin: 30px 0 15px 0;">Next Steps:</h3>
                            <ul style="font-size: 15px; color: #555; line-height: 1.8; padding-left: 20px;">
                                <li>Review the tournament rules and schedule</li>
                                <li>Check-in before the tournament starts</li>
                                <li>Prepare for your matches</li>
                                <li>Join the tournament communication channels if available</li>
                            </ul>
                            
                            <!-- Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?php echo $appUrl; ?>/frontend/app/views/pages/home/tournament-details.php?id=<?php echo $tournamentId; ?>" 
                                           style="display: inline-block; padding: 14px 30px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold;">
                                            View Tournament Details
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 16px; color: #333; line-height: 1.6; margin: 20px 0 0 0;">
                                Good luck in the tournament! We hope you have a great experience.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; font-size: 14px; color: #666;">
                                Tournament Management System<br>
                                <a href="<?php echo $appUrl; ?>" style="color: #10b981; text-decoration: none;">Visit Platform</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
