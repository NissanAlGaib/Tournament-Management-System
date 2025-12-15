<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Submitted</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px;">Registration Submitted</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="font-size: 16px; color: #333333; line-height: 1.6; margin: 0 0 20px 0;">
                                Hello <strong><?php echo htmlspecialchars($userName); ?></strong>,
                            </p>
                            
                            <p style="font-size: 16px; color: #333333; line-height: 1.6; margin: 0 0 20px 0;">
                                Thank you for registering for <strong><?php echo htmlspecialchars($tournamentName); ?></strong>!
                            </p>
                            
                            <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; font-size: 16px; color: #555;">
                                    <strong>📋 Status:</strong> Pending Review
                                </p>
                                <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">
                                    Your application is currently being reviewed by the tournament organizer. You will receive an email notification once a decision has been made.
                                </p>
                            </div>
                            
                            <p style="font-size: 16px; color: #333333; line-height: 1.6; margin: 20px 0;">
                                In the meantime, you can view the tournament details and track your registration status on our platform.
                            </p>
                            
                            <!-- Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?php echo $appUrl; ?>/frontend/app/views/pages/home/tournament-details.php?id=<?php echo $tournamentId; ?>" 
                                           style="display: inline-block; padding: 14px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold;">
                                            View Tournament Details
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 14px; color: #666; line-height: 1.6; margin: 20px 0 0 0;">
                                If you have any questions, please don't hesitate to contact the tournament organizer through our platform.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; font-size: 14px; color: #666;">
                                Tournament Management System<br>
                                <a href="<?php echo $appUrl; ?>" style="color: #667eea; text-decoration: none;">Visit Platform</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
