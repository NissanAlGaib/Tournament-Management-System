<?php
/**
 * JWT (JSON Web Token) Helper Class
 * Handles token generation, validation, and decoding for authentication
 */
class JWT {
    private $secret_key;
    private $algorithm = 'HS256';
    private $token_expiry = 86400; // 24 hours in seconds

    public function __construct() {
        // Use environment variable or default secret (CHANGE THIS IN PRODUCTION!)
        $this->secret_key = getenv('JWT_SECRET') ?: 'your_super_secret_jwt_key_change_in_production_2024';
    }

    /**
     * Generate a JWT token
     * @param array $payload - Data to encode in the token
     * @param int $expiry - Token expiry time in seconds (default 24 hours)
     * @return string - JWT token
     */
    public function generate($payload, $expiry = null) {
        $expiry = $expiry ?? $this->token_expiry;
        
        // Create header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ]);

        // Create payload with standard claims
        $now = time();
        $payload = array_merge($payload, [
            'iat' => $now,              // Issued at
            'exp' => $now + $expiry,    // Expiration time
            'nbf' => $now               // Not before
        ]);
        
        $payload_json = json_encode($payload);

        // Encode header and payload
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload_json);

        // Create signature
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret_key, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        return $jwt;
    }

    /**
     * Validate and decode a JWT token
     * @param string $token - JWT token to validate
     * @return array|false - Decoded payload or false if invalid
     */
    public function validate($token) {
        if (empty($token)) {
            return false;
        }

        // Split the token
        $tokenParts = explode('.', $token);
        if (count($tokenParts) !== 3) {
            return false;
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $tokenParts;

        // Verify signature
        $signature = $this->base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret_key, true);

        if (!hash_equals($signature, $expectedSignature)) {
            return false; // Invalid signature
        }

        // Decode payload
        $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);

        if (!$payload) {
            return false; // Invalid JSON
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false; // Token expired
        }

        // Check not before
        if (isset($payload['nbf']) && $payload['nbf'] > time()) {
            return false; // Token not yet valid
        }

        return $payload;
    }

    /**
     * Decode a token without validation (use with caution)
     * @param string $token - JWT token to decode
     * @return array|false - Decoded payload or false if invalid
     */
    public function decode($token) {
        $tokenParts = explode('.', $token);
        if (count($tokenParts) !== 3) {
            return false;
        }

        $payload = json_decode($this->base64UrlDecode($tokenParts[1]), true);
        return $payload ?: false;
    }

    /**
     * Check if a token is expired without full validation
     * @param string $token - JWT token
     * @return bool - True if expired, false otherwise
     */
    public function isExpired($token) {
        $payload = $this->decode($token);
        if (!$payload || !isset($payload['exp'])) {
            return true;
        }
        return $payload['exp'] < time();
    }

    /**
     * Get the user ID from a token
     * @param string $token - JWT token
     * @return int|null - User ID or null if not found
     */
    public function getUserId($token) {
        $payload = $this->validate($token);
        return $payload && isset($payload['user_id']) ? (int)$payload['user_id'] : null;
    }

    /**
     * Base64 URL encode
     * @param string $data - Data to encode
     * @return string - Encoded string
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     * @param string $data - Data to decode
     * @return string - Decoded string
     */
    private function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    /**
     * Set custom expiry time
     * @param int $seconds - Expiry time in seconds
     */
    public function setExpiry($seconds) {
        $this->token_expiry = $seconds;
    }

    /**
     * Get current expiry time
     * @return int - Expiry time in seconds
     */
    public function getExpiry() {
        return $this->token_expiry;
    }
}
