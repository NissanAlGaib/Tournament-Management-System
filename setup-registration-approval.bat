@echo off
REM Tournament Management System - Registration Approval Setup Script
REM This script automates the installation of the registration approval system

echo ========================================
echo Tournament Management System
echo Registration Approval Setup
echo ========================================
echo.

REM Check if composer is installed
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Composer is not installed or not in PATH
    echo Please install Composer from https://getcomposer.org/download/
    echo.
    pause
    exit /b 1
)

echo [1/5] Installing PHPMailer via Composer...
composer install
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Composer install failed
    pause
    exit /b 1
)
echo [SUCCESS] PHPMailer installed
echo.

echo [2/5] Checking database connection...
REM You can add MySQL connection test here if needed
echo [INFO] Please ensure MySQL is running
echo.

echo [3/5] Setting up email configuration...
if not exist "backend\config\email_config.php" (
    echo [INFO] Creating email_config.php from example...
    copy "backend\config\email_config.example.php" "backend\config\email_config.php"
    echo [WARNING] Please edit backend\config\email_config.php with your SMTP credentials
) else (
    echo [INFO] email_config.php already exists
)
echo.

echo [4/5] Database Migration Instructions
echo ========================================
echo Please run the following SQL migration manually:
echo.
echo Option 1 - Command Line:
echo mysql -u root -p tournament_management ^< backend\database\add_registration_fields.sql
echo.
echo Option 2 - phpMyAdmin:
echo 1. Open http://localhost/phpmyadmin
echo 2. Select 'tournament_management' database
echo 3. Click 'Import' tab
echo 4. Choose file: backend\database\add_registration_fields.sql
echo 5. Click 'Go'
echo.
pause

echo [5/5] Configuration Checklist
echo ========================================
echo Please complete these steps:
echo.
echo [ ] 1. Run database migration (see above)
echo [ ] 2. Edit backend\config\email_config.php:
echo        - Update SMTP host, username, password
echo        - Set correct app_url
echo [ ] 3. For Gmail users:
echo        - Enable 2-Factor Authentication
echo        - Generate App Password at:
echo          https://myaccount.google.com/apppasswords
echo [ ] 4. Test registration and email sending
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Next Steps:
echo 1. Configure SMTP settings in backend\config\email_config.php
echo 2. Run database migration
echo 3. Test the registration approval workflow
echo.
echo Documentation:
echo - Setup Guide: REGISTRATION_APPROVAL_SETUP.md
echo - Quick Reference: REGISTRATION_APPROVAL_SUMMARY.md
echo - Implementation Details: REGISTRATION_APPROVAL_IMPLEMENTATION.md
echo.

pause
