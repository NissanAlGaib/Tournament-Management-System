<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Demo - Tournament Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-user-shield"></i> Role-Based Features Demo</h3>
                    </div>
                    <div class="card-body">
                        <!-- User Info Section -->
                        <div class="mb-4">
                            <h5>Current User Information</h5>
                            <div id="user-info" class="alert alert-info">
                                <p><strong>Username:</strong> <span id="username">-</span></p>
                                <p><strong>Email:</strong> <span id="email">-</span></p>
                                <p><strong>Roles:</strong> <span id="user-role-badges"></span></p>
                            </div>
                        </div>

                        <!-- Player Only Section -->
                        <div data-role="Player" class="mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <i class="fas fa-gamepad"></i> Player Features
                                </div>
                                <div class="card-body">
                                    <p>This section is visible to all Players.</p>
                                    <button class="btn btn-primary">Join Tournament</button>
                                    <button class="btn btn-secondary">View My Matches</button>
                                </div>
                            </div>
                        </div>

                        <!-- Organizer Only Section -->
                        <div data-roles="Organizer,Admin" class="mb-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning">
                                    <i class="fas fa-trophy"></i> Organizer Features
                                </div>
                                <div class="card-body">
                                    <p>This section is visible to Organizers and Admins.</p>
                                    <button class="btn btn-warning">Create Tournament</button>
                                    <button class="btn btn-secondary">Manage My Tournaments</button>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Only Section -->
                        <div data-role="Admin" class="mb-4">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <i class="fas fa-crown"></i> Admin Features
                                </div>
                                <div class="card-body">
                                    <p>This section is visible only to Admins.</p>
                                    <a href="../admin/role-management.php" class="btn btn-danger">
                                        <i class="fas fa-users-cog"></i> Manage Roles
                                    </a>
                                    <button class="btn btn-secondary">System Settings</button>
                                </div>
                            </div>
                        </div>

                        <!-- Request Organizer Role (Non-Organizers) -->
                        <div id="request-organizer-section" class="mb-4" style="display: none;">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <i class="fas fa-hand-paper"></i> Become an Organizer
                                </div>
                                <div class="card-body">
                                    <p>Want to create and manage tournaments? Request the Organizer role!</p>
                                    <div class="mb-3">
                                        <label for="reason" class="form-label">Reason for request:</label>
                                        <textarea class="form-control" id="reason" rows="3" 
                                                  placeholder="Tell us why you want to be an organizer..."></textarea>
                                    </div>
                                    <button id="request-organizer-btn" class="btn btn-info">
                                        <i class="fas fa-paper-plane"></i> Submit Request
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Role Check Test Section -->
                        <div class="mb-4">
                            <h5>Test Role Checking</h5>
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-primary" onclick="testRole('Admin')">
                                    Test Admin
                                </button>
                                <button class="btn btn-outline-warning" onclick="testRole('Organizer')">
                                    Test Organizer
                                </button>
                                <button class="btn btn-outline-info" onclick="testRole('Player')">
                                    Test Player
                                </button>
                            </div>
                            <div id="role-test-result" class="mt-2"></div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <button id="logout-btn" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module">
        import { 
            getCurrentUser, 
            hasRole, 
            isAuthenticated,
            requestOrganizerRole,
            logout,
            isOrganizer,
            getUserRoles
        } from '../../../src/js/core/auth.js';
        import { 
            displayUserRoleBadges, 
            initializeRoleBasedUI 
        } from '../../../src/js/roleUtils.js';

        // Check authentication
        if (!isAuthenticated()) {
            alert('Please login first');
            window.location.href = '../auth/login.php';
        }

        // Display user info
        function displayUserInfo() {
            const user = getCurrentUser();
            if (user) {
                document.getElementById('username').textContent = user.username;
                document.getElementById('email').textContent = user.email;
                
                // Display role badges
                displayUserRoleBadges(document.getElementById('user-role-badges'));
                
                // Show request organizer section if not already an organizer or admin
                if (!isOrganizer() && !hasRole('Admin')) {
                    document.getElementById('request-organizer-section').style.display = '';
                }
            }
        }

        // Request organizer role
        document.getElementById('request-organizer-btn')?.addEventListener('click', async () => {
            const reason = document.getElementById('reason').value.trim();
            
            if (!reason) {
                alert('Please provide a reason for your request');
                return;
            }
            
            try {
                const result = await requestOrganizerRole(reason);
                if (result.success) {
                    alert(result.message);
                    document.getElementById('request-organizer-section').style.display = 'none';
                } else {
                    alert('Failed to submit request: ' + result.message);
                }
            } catch (error) {
                alert('Error submitting request: ' + error.message);
            }
        });

        // Test role checking
        window.testRole = function(roleName) {
            const hasIt = hasRole(roleName);
            const resultDiv = document.getElementById('role-test-result');
            
            if (hasIt) {
                resultDiv.innerHTML = `<div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> You HAVE the ${roleName} role!
                </div>`;
            } else {
                resultDiv.innerHTML = `<div class="alert alert-warning">
                    <i class="fas fa-times-circle"></i> You DO NOT have the ${roleName} role.
                </div>`;
            }
        };

        // Logout
        document.getElementById('logout-btn').addEventListener('click', async () => {
            if (confirm('Are you sure you want to logout?')) {
                await logout();
                window.location.href = '../auth/login.php';
            }
        });

        // Initialize
        displayUserInfo();
        initializeRoleBasedUI();
    </script>
</body>
</html>
