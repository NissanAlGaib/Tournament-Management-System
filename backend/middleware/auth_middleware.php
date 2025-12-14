<?php
/**
 * Authentication Middleware
 * Verifies JWT tokens and checks user roles for protected endpoints
 */

require_once __DIR__ . '/../classes/JWT.class.php';
require_once __DIR__ . '/../classes/Session.class.php';
require_once __DIR__ . '/../api/database.php';

class AuthMiddleware {
    private $jwt;
    private $session;
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->jwt = new JWT();
        $this->session = new Session($this->pdo);
    }

    /**
     * Verify JWT token from request headers
     * @return array|null - User data or null if invalid
     */
    public function verifyToken() {
        $token = $this->getTokenFromRequest();
        
        if (!$token) {
            return null;
        }

        // Validate JWT
        $payload = $this->jwt->validate($token);
        if (!$payload) {
            return null;
        }

        // Validate session in database
        $session = $this->session->validate($token);
        if (!$session) {
            return null;
        }

        return [
            'user_id' => $payload['user_id'],
            'username' => $payload['username'],
            'email' => $payload['email'],
            'roles' => $payload['roles'] ?? []
        ];
    }

    /**
     * Require authentication - sends 401 if not authenticated
     * @return array - User data
     */
    public function requireAuth() {
        $user = $this->verifyToken();
        
        if (!$user) {
            $this->sendUnauthorized('Authentication required');
        }
        
        return $user;
    }

    /**
     * Require specific role - sends 403 if user doesn't have role
     * @param string|array $roles - Required role(s)
     * @return array - User data
     */
    public function requireRole($roles) {
        $user = $this->requireAuth();
        
        $roles = is_array($roles) ? $roles : [$roles];
        $userRoles = array_column($user['roles'], 'role_name');
        
        $hasRole = false;
        foreach ($roles as $role) {
            if (in_array($role, $userRoles)) {
                $hasRole = true;
                break;
            }
        }
        
        if (!$hasRole) {
            $this->sendForbidden('Insufficient permissions');
        }
        
        return $user;
    }

    /**
     * Check if user has a specific role
     * @param array $user - User data from verifyToken()
     * @param string $roleName - Role name to check
     * @return bool - True if user has the role
     */
    public function hasRole($user, $roleName) {
        if (!isset($user['roles']) || !is_array($user['roles'])) {
            return false;
        }
        
        $userRoles = array_column($user['roles'], 'role_name');
        return in_array($roleName, $userRoles);
    }

    /**
     * Get JWT token from request headers or cookies
     * @return string|null - JWT token or null if not found
     */
    private function getTokenFromRequest() {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        
        // Check cookie
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }
        
        return null;
    }

    /**
     * Send 401 Unauthorized response
     * @param string $message - Error message
     */
    private function sendUnauthorized($message = 'Unauthorized') {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Send 403 Forbidden response
     * @param string $message - Error message
     */
    private function sendForbidden($message = 'Forbidden') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Get current user from token without requiring auth
     * @return array|null - User data or null if not authenticated
     */
    public function getCurrentUser() {
        return $this->verifyToken();
    }

    /**
     * Logout user by destroying session
     * @return bool - True if logout successful
     */
    public function logout() {
        $token = $this->getTokenFromRequest();
        
        if ($token) {
            $this->session->destroy($token);
            
            // Clear cookie if set
            if (isset($_COOKIE['auth_token'])) {
                setcookie('auth_token', '', time() - 3600, '/', '', false, true);
            }
            
            return true;
        }
        
        return false;
    }
}

/**
 * Helper function to get auth middleware instance
 * @return AuthMiddleware
 */
function getAuthMiddleware() {
    static $middleware = null;
    if ($middleware === null) {
        $middleware = new AuthMiddleware();
    }
    return $middleware;
}
