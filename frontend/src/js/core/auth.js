// Authentication API Module
// Handles all authentication-related API calls

const AUTH_API_URL = '../../../backend/api/auth_api.php';

/**
 * Login user with username and password
 * @param {string} username - User's username
 * @param {string} password - User's password
 * @returns {Promise<Object>} Response data with success status and user info
 */
export async function login(username, password) {
    try {
        const response = await fetch(AUTH_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'login',
                username: username,
                password: password
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // NOTE: Storing user data in localStorage is for demonstration only
            // In production, use secure httpOnly cookies or server-side sessions
            // to prevent XSS attacks and ensure proper security
            localStorage.setItem('user', JSON.stringify(data.user));
        }
        
        return data;
    } catch (error) {
        console.error('Login error:', error);
        throw new Error('An error occurred during login. Please try again.');
    }
}

/**
 * Register a new user
 * @param {string} username - Desired username
 * @param {string} email - User's email address
 * @param {string} password - Desired password
 * @returns {Promise<Object>} Response data with success status
 */
export async function register(username, email, password) {
    try {
        const response = await fetch(AUTH_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'register',
                username: username,
                email: email,
                password: password
            })
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Registration error:', error);
        throw new Error('An error occurred during registration. Please try again.');
    }
}

/**
 * Logout current user
 * Clears user data from localStorage
 */
export function logout() {
    localStorage.removeItem('user');
}

/**
 * Get current logged-in user
 * @returns {Object|null} User object or null if not logged in
 */
export function getCurrentUser() {
    const userStr = localStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
}

/**
 * Check if user is authenticated
 * @returns {boolean} True if user is logged in
 */
export function isAuthenticated() {
    return getCurrentUser() !== null;
}
