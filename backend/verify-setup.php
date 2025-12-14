<?php
/**
 * Database Setup Verification Script
 * Run this script to verify your role-based access control setup
 * Access: http://localhost/your-project/backend/verify-setup.php
 */

header("Content-Type: text/html; charset=UTF-8");

require_once 'api/database.php';

$database = new Database();
$db = $database->getConnection();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RBAC Setup Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
            border-left: 4px solid #007bff;
            padding-left: 10px;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 RBAC Setup Verification</h1>
        <p>This script verifies your Role-Based Access Control system setup.</p>

        <?php
        $errors = [];
        $warnings = [];
        $info = [];

        // Check database connection
        if (!$db) {
            echo '<div class="status error">❌ Database connection failed!</div>';
            exit();
        }
        echo '<div class="status success">✅ Database connection successful</div>';

        // Check if tables exist
        echo '<h2>📊 Database Tables</h2>';
        
        $requiredTables = ['users', 'roles', 'user_roles', 'role_requests', 'sessions'];
        $tableStatus = [];
        
        foreach ($requiredTables as $table) {
            try {
                $query = "SELECT COUNT(*) FROM `$table`";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $count = $stmt->fetchColumn();
                $tableStatus[$table] = ['exists' => true, 'count' => $count];
            } catch (PDOException $e) {
                $tableStatus[$table] = ['exists' => false, 'count' => 0];
                $errors[] = "Table `$table` does not exist";
            }
        }

        echo '<table>';
        echo '<tr><th>Table</th><th>Status</th><th>Row Count</th></tr>';
        foreach ($tableStatus as $table => $status) {
            $badge = $status['exists'] ? 
                '<span class="badge badge-success">EXISTS</span>' : 
                '<span class="badge badge-danger">MISSING</span>';
            echo "<tr><td><code>$table</code></td><td>$badge</td><td>{$status['count']}</td></tr>";
        }
        echo '</table>';

        // Check roles
        if ($tableStatus['roles']['exists']) {
            echo '<h2>👥 Default Roles</h2>';
            $query = "SELECT * FROM roles ORDER BY id";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($roles) === 0) {
                $warnings[] = "No roles found in database. Run the migration script!";
            } else {
                echo '<table>';
                echo '<tr><th>ID</th><th>Role Name</th><th>Description</th></tr>';
                foreach ($roles as $role) {
                    echo "<tr>
                        <td>{$role['id']}</td>
                        <td><strong>{$role['role_name']}</strong></td>
                        <td>{$role['description']}</td>
                    </tr>";
                }
                echo '</table>';

                // Check for required roles
                $requiredRoles = ['Admin', 'Player', 'Organizer'];
                $existingRoles = array_column($roles, 'role_name');
                foreach ($requiredRoles as $reqRole) {
                    if (!in_array($reqRole, $existingRoles)) {
                        $errors[] = "Required role '$reqRole' is missing";
                    }
                }
            }
        }

        // Check users and their roles
        if ($tableStatus['users']['exists'] && $tableStatus['user_roles']['exists']) {
            echo '<h2>👤 Users & Role Assignments</h2>';
            
            $query = "SELECT u.id, u.username, u.email, 
                             GROUP_CONCAT(r.role_name SEPARATOR ', ') as roles
                      FROM users u
                      LEFT JOIN user_roles ur ON u.id = ur.user_id
                      LEFT JOIN roles r ON ur.role_id = r.id
                      GROUP BY u.id
                      ORDER BY u.id";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($users) === 0) {
                echo '<div class="status info">ℹ️ No users registered yet.</div>';
            } else {
                echo '<table>';
                echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Roles</th></tr>';
                
                $hasAdmin = false;
                foreach ($users as $user) {
                    $roles = $user['roles'] ?: '<em>No roles assigned</em>';
                    if (strpos($roles, 'Admin') !== false) {
                        $hasAdmin = true;
                    }
                    echo "<tr>
                        <td>{$user['id']}</td>
                        <td><strong>{$user['username']}</strong></td>
                        <td>{$user['email']}</td>
                        <td>{$roles}</td>
                    </tr>";
                }
                echo '</table>';

                if (!$hasAdmin) {
                    $warnings[] = "No admin user found! Create one using: <code>INSERT INTO user_roles (user_id, role_id) VALUES (1, 1);</code>";
                } else {
                    $info[] = "Admin user(s) found ✓";
                }
            }
        }

        // Check role requests
        if ($tableStatus['role_requests']['exists']) {
            echo '<h2>📋 Role Requests</h2>';
            
            $query = "SELECT rr.id, rr.status, u.username, r.role_name, rr.created_at
                      FROM role_requests rr
                      JOIN users u ON rr.user_id = u.id
                      JOIN roles r ON rr.requested_role_id = r.id
                      ORDER BY rr.created_at DESC
                      LIMIT 10";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($requests) === 0) {
                echo '<div class="status info">ℹ️ No role requests yet.</div>';
            } else {
                echo '<table>';
                echo '<tr><th>ID</th><th>User</th><th>Requested Role</th><th>Status</th><th>Date</th></tr>';
                foreach ($requests as $req) {
                    $statusBadge = $req['status'] === 'pending' ? 
                        '<span class="badge" style="background: #ffc107;">PENDING</span>' :
                        ($req['status'] === 'approved' ? 
                            '<span class="badge badge-success">APPROVED</span>' :
                            '<span class="badge badge-danger">REJECTED</span>');
                    
                    echo "<tr>
                        <td>{$req['id']}</td>
                        <td>{$req['username']}</td>
                        <td>{$req['role_name']}</td>
                        <td>$statusBadge</td>
                        <td>{$req['created_at']}</td>
                    </tr>";
                }
                echo '</table>';
            }
        }

        // Check active sessions
        if ($tableStatus['sessions']['exists']) {
            echo '<h2>🔑 Active Sessions</h2>';
            
            $query = "SELECT COUNT(*) FROM sessions WHERE expires_at > NOW()";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $activeCount = $stmt->fetchColumn();

            echo '<div class="status info">ℹ️ ' . $activeCount . ' active session(s)</div>';
        }

        // Check required files
        echo '<h2>📁 Required Files</h2>';
        
        $requiredFiles = [
            'classes/JWT.class.php' => 'JWT Helper Class',
            'classes/Session.class.php' => 'Session Management Class',
            'classes/Auth.class.php' => 'Authentication Class',
            'middleware/auth_middleware.php' => 'Auth Middleware',
            'api/admin_api.php' => 'Admin API',
            'api/auth_api.php' => 'Auth API'
        ];

        echo '<table>';
        echo '<tr><th>File</th><th>Description</th><th>Status</th></tr>';
        foreach ($requiredFiles as $file => $desc) {
            $exists = file_exists(__DIR__ . '/' . $file);
            $badge = $exists ? 
                '<span class="badge badge-success">EXISTS</span>' : 
                '<span class="badge badge-danger">MISSING</span>';
            
            echo "<tr>
                <td><code>$file</code></td>
                <td>$desc</td>
                <td>$badge</td>
            </tr>";
            
            if (!$exists) {
                $errors[] = "Required file missing: $file";
            }
        }
        echo '</table>';

        // Summary
        echo '<h2>📝 Summary</h2>';

        if (count($errors) > 0) {
            echo '<div class="status error"><strong>❌ Errors Found:</strong><ul>';
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo '</ul></div>';
        }

        if (count($warnings) > 0) {
            echo '<div class="status warning"><strong>⚠️ Warnings:</strong><ul>';
            foreach ($warnings as $warning) {
                echo "<li>$warning</li>";
            }
            echo '</ul></div>';
        }

        if (count($info) > 0) {
            echo '<div class="status info"><strong>ℹ️ Information:</strong><ul>';
            foreach ($info as $i) {
                echo "<li>$i</li>";
            }
            echo '</ul></div>';
        }

        if (count($errors) === 0 && count($warnings) === 0) {
            echo '<div class="status success">
                <strong>✅ All checks passed!</strong><br>
                Your RBAC system is properly set up and ready to use.
            </div>';
        }
        ?>

        <h2>🚀 Next Steps</h2>
        <ol>
            <li>If you see any errors, run the database migration: <code>backend/database/setup_roles.sql</code></li>
            <li>Create your first admin user (see warnings above)</li>
            <li>Test the system by logging in</li>
            <li>Access the admin panel: <code>frontend/app/views/pages/admin/role-management.php</code></li>
            <li>Request organizer role from your profile page</li>
        </ol>

        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666;">
            <strong>Security Note:</strong> Delete this verification script after setup is complete!
        </p>
    </div>
</body>
</html>
