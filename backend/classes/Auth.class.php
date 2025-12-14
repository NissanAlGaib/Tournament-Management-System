<?php
    class Authentication
    {
        private $conn;
        private $table_name = "users";

        public $id;
        public $username;
        public $email;
        public $password;

        public function __construct($db)
        {
            $this->conn = $db;
        }

        public function registerUser()
        {
            try {
                // Start transaction
                $this->conn->beginTransaction();

                $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (:username, :email, :password)";

                $stmt = $this->conn->prepare($query);

                $this->username = htmlspecialchars(strip_tags($this->username));
                $this->email = htmlspecialchars(strip_tags($this->email));
                $this->password = password_hash($this->password, PASSWORD_BCRYPT);

                $stmt->bindParam(":username", $this->username);
                $stmt->bindParam(":email", $this->email);
                $stmt->bindParam(":password", $this->password);

                if ($stmt->execute()) {
                    $this->id = $this->conn->lastInsertId();
                    
                    // Assign default "Player" role (role_id = 2)
                    $this->assignRole($this->id, 2);
                    
                    $this->conn->commit();
                    return true;
                }
                
                $this->conn->rollBack();
                return false;
            } catch (PDOException $e) {
                $this->conn->rollBack();
                error_log("Registration error: " . $e->getMessage());
                return false;
            }
        }

        public function loginUser()
        {
            $query = "SELECT id, username, email, password FROM " . $this->table_name . " WHERE username = :username LIMIT 1";

            $stmt = $this->conn->prepare($query);

            $this->username = htmlspecialchars(strip_tags($this->username));

            $stmt->bindParam(":username", $this->username);

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($this->password, $row['password'])) {
                    $this->id = $row['id'];
                    $this->email = $row['email'];
                    return true;
                }
            }
            return false;
        }

        /**
         * Get all roles for a user
         * @param int $userId - User ID
         * @return array - Array of role objects
         */
        public function getUserRoles($userId)
        {
            try {
                $query = "SELECT r.id, r.role_name, r.description 
                          FROM roles r
                          INNER JOIN user_roles ur ON r.id = ur.role_id
                          WHERE ur.user_id = :user_id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Get user roles error: " . $e->getMessage());
                return [];
            }
        }

        /**
         * Assign a role to a user
         * @param int $userId - User ID
         * @param int $roleId - Role ID
         * @return bool - True if role assigned successfully
         */
        public function assignRole($userId, $roleId)
        {
            try {
                // Check if role already assigned
                $checkQuery = "SELECT 1 FROM user_roles WHERE user_id = :user_id AND role_id = :role_id";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $checkStmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
                $checkStmt->execute();
                
                if ($checkStmt->rowCount() > 0) {
                    return true; // Role already assigned
                }

                $query = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
                
                return $stmt->execute();
            } catch (PDOException $e) {
                error_log("Assign role error: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Remove a role from a user
         * @param int $userId - User ID
         * @param int $roleId - Role ID
         * @return bool - True if role removed successfully
         */
        public function removeRole($userId, $roleId)
        {
            try {
                $query = "DELETE FROM user_roles WHERE user_id = :user_id AND role_id = :role_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
                
                return $stmt->execute();
            } catch (PDOException $e) {
                error_log("Remove role error: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Check if a user has a specific role
         * @param int $userId - User ID
         * @param string $roleName - Role name (e.g., 'Admin', 'Player', 'Organizer')
         * @return bool - True if user has the role
         */
        public function hasRole($userId, $roleName)
        {
            try {
                $query = "SELECT 1 FROM user_roles ur
                          INNER JOIN roles r ON ur.role_id = r.id
                          WHERE ur.user_id = :user_id AND r.role_name = :role_name";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':role_name', $roleName, PDO::PARAM_STR);
                $stmt->execute();
                
                return $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                error_log("Check role error: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Get all available roles
         * @return array - Array of all roles
         */
        public function getAllRoles()
        {
            try {
                $query = "SELECT id, role_name, description FROM roles ORDER BY id";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Get all roles error: " . $e->getMessage());
                return [];
            }
        }

        /**
         * Request a role upgrade (for Organizer role)
         * @param int $userId - User ID
         * @param int $roleId - Requested role ID
         * @param string $reason - Reason for the request
         * @return bool - True if request created successfully
         */
        public function requestRoleUpgrade($userId, $roleId, $reason = null)
        {
            try {
                // Check if there's already a pending request
                $checkQuery = "SELECT 1 FROM role_requests 
                              WHERE user_id = :user_id 
                              AND requested_role_id = :role_id 
                              AND status = 'pending'";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $checkStmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
                $checkStmt->execute();
                
                if ($checkStmt->rowCount() > 0) {
                    return false; // Already has pending request
                }

                $query = "INSERT INTO role_requests (user_id, requested_role_id, reason, status) 
                          VALUES (:user_id, :role_id, :reason, 'pending')";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
                $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
                
                return $stmt->execute();
            } catch (PDOException $e) {
                error_log("Request role upgrade error: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Get all pending role requests
         * @return array - Array of pending role requests
         */
        public function getPendingRoleRequests()
        {
            try {
                $query = "SELECT rr.id, rr.user_id, rr.requested_role_id, rr.reason, rr.created_at,
                                 u.username, u.email,
                                 r.role_name, r.description
                          FROM role_requests rr
                          INNER JOIN users u ON rr.user_id = u.id
                          INNER JOIN roles r ON rr.requested_role_id = r.id
                          WHERE rr.status = 'pending'
                          ORDER BY rr.created_at ASC";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Get pending role requests error: " . $e->getMessage());
                return [];
            }
        }

        /**
         * Approve or reject a role request
         * @param int $requestId - Role request ID
         * @param string $status - 'approved' or 'rejected'
         * @param int $reviewerId - Admin user ID who reviewed the request
         * @return bool - True if request processed successfully
         */
        public function processRoleRequest($requestId, $status, $reviewerId)
        {
            try {
                $this->conn->beginTransaction();

                // Get request details
                $query = "SELECT user_id, requested_role_id FROM role_requests WHERE id = :id AND status = 'pending'";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $requestId, PDO::PARAM_INT);
                $stmt->execute();
                
                if ($stmt->rowCount() === 0) {
                    $this->conn->rollBack();
                    return false; // Request not found or already processed
                }
                
                $request = $stmt->fetch(PDO::FETCH_ASSOC);

                // Update request status
                $updateQuery = "UPDATE role_requests 
                               SET status = :status, reviewed_by = :reviewer_id 
                               WHERE id = :id";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bindParam(':status', $status, PDO::PARAM_STR);
                $updateStmt->bindParam(':reviewer_id', $reviewerId, PDO::PARAM_INT);
                $updateStmt->bindParam(':id', $requestId, PDO::PARAM_INT);
                $updateStmt->execute();

                // If approved, assign the role
                if ($status === 'approved') {
                    $this->assignRole($request['user_id'], $request['requested_role_id']);
                }

                $this->conn->commit();
                return true;
            } catch (PDOException $e) {
                $this->conn->rollBack();
                error_log("Process role request error: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Get user by ID with roles
         * @param int $userId - User ID
         * @return array|false - User data with roles or false if not found
         */
        public function getUserById($userId)
        {
            try {
                $query = "SELECT id, username, email, created_at FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $user['roles'] = $this->getUserRoles($userId);
                    return $user;
                }
                
                return false;
            } catch (PDOException $e) {
                error_log("Get user by ID error: " . $e->getMessage());
                return false;
            }
        }
    }