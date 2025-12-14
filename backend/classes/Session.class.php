<?php
/**
 * Session Management Class
 * Handles server-side session storage and validation
 */
class Session {
    private $pdo;
    private $session_expiry = 86400; // 24 hours in seconds

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new session
     * @param int $userId - User ID
     * @param string $token - JWT token
     * @param int $expiry - Session expiry time in seconds (default 24 hours)
     * @return bool - True if session created successfully
     */
    public function create($userId, $token, $expiry = null) {
        try {
            $expiry = $expiry ?? $this->session_expiry;
            $expiresAt = date('Y-m-d H:i:s', time() + $expiry);
            
            // Get client information
            $ipAddress = $this->getClientIp();
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null;

            $query = "INSERT INTO sessions (user_id, token, ip_address, user_agent, expires_at) 
                      VALUES (:user_id, :token, :ip_address, :user_agent, :expires_at)";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
            $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);
            $stmt->bindParam(':expires_at', $expiresAt, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Session creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate a session token
     * @param string $token - JWT token to validate
     * @return array|false - Session data or false if invalid
     */
    public function validate($token) {
        try {
            $query = "SELECT s.*, u.username, u.email 
                      FROM sessions s
                      JOIN users u ON s.user_id = u.id
                      WHERE s.token = :token 
                      AND s.expires_at > NOW()";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($session) {
                // Update last activity
                $this->updateActivity($token);
                return $session;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Session validation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update session last activity timestamp
     * @param string $token - JWT token
     * @return bool - True if updated successfully
     */
    public function updateActivity($token) {
        try {
            $query = "UPDATE sessions SET last_activity = NOW() WHERE token = :token";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Session activity update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Destroy a session by token
     * @param string $token - JWT token
     * @return bool - True if session destroyed successfully
     */
    public function destroy($token) {
        try {
            $query = "DELETE FROM sessions WHERE token = :token";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Session destroy error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Destroy all sessions for a user
     * @param int $userId - User ID
     * @return bool - True if sessions destroyed successfully
     */
    public function destroyAllUserSessions($userId) {
        try {
            $query = "DELETE FROM sessions WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User sessions destroy error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean up expired sessions
     * @return int - Number of sessions cleaned up
     */
    public function cleanupExpired() {
        try {
            $query = "DELETE FROM sessions WHERE expires_at < NOW()";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Session cleanup error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all active sessions for a user
     * @param int $userId - User ID
     * @return array - Array of active sessions
     */
    public function getUserSessions($userId) {
        try {
            $query = "SELECT id, ip_address, user_agent, created_at, last_activity, expires_at 
                      FROM sessions 
                      WHERE user_id = :user_id 
                      AND expires_at > NOW()
                      ORDER BY last_activity DESC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user sessions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if a session exists and is valid
     * @param string $token - JWT token
     * @return bool - True if session exists and is valid
     */
    public function exists($token) {
        return $this->validate($token) !== false;
    }

    /**
     * Get client IP address
     * @return string|null - Client IP address
     */
    private function getClientIp() {
        $ip = null;
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Validate IP
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
            return substr($ip, 0, 45); // Limit to 45 chars for IPv6
        }
        
        return null;
    }

    /**
     * Set custom expiry time
     * @param int $seconds - Expiry time in seconds
     */
    public function setExpiry($seconds) {
        $this->session_expiry = $seconds;
    }

    /**
     * Get current expiry time
     * @return int - Expiry time in seconds
     */
    public function getExpiry() {
        return $this->session_expiry;
    }
}
