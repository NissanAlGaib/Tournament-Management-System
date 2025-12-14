// Authentication API Module
// Handles all authentication-related API calls with JWT

const AUTH_API_URL = '../../../backend/api/auth_api.php';
const ADMIN_API_URL = '../../../backend/api/admin_api.php';

/**
 * Make authenticated API request with JWT token
 * @param {string} url - API endpoint URL
 * @param {Object} options - Fetch options
 * @returns {Promise<Response>} Fetch response
 */
async function authenticatedFetch(url, options = {}) {
    const token = getToken();
    
    if (!options.headers) {
        options.headers = {};
    }
    
    if (token) {
        options.headers['Authorization'] = `Bearer ${token}`;
    }
    
    options.headers['Content-Type'] = 'application/json';
    
    return fetch(url, options);
}

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
            // Store JWT token and user data in localStorage
            localStorage.setItem('auth_token', data.token);
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
        
        if (data.success) {
            // Store JWT token and user data in localStorage
            localStorage.setItem('auth_token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
        }
        
        return data;
    } catch (error) {
        console.error('Registration error:', error);
        throw new Error('An error occurred during registration. Please try again.');
    }
}

/**
 * Logout current user
 * Clears user data and token from localStorage
 */
export async function logout() {
    try {
        // Call logout API to destroy server-side session
        await authenticatedFetch(AUTH_API_URL, {
            method: 'POST',
            body: JSON.stringify({
                action: 'logout'
            })
        });
    } catch (error) {
        console.error('Logout error:', error);
    } finally {
        // Clear local storage regardless of API call result
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
    }
}

/**
 * Get JWT token
 * @returns {string|null} JWT token or null if not logged in
 */
export function getToken() {
    return localStorage.getItem('auth_token');
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
    return getToken() !== null && getCurrentUser() !== null;
}

/**
 * Verify token validity with server
 * @returns {Promise<boolean>} True if token is valid
 */
export async function verifyToken() {
    try {
        const response = await authenticatedFetch(AUTH_API_URL, {
            method: 'POST',
            body: JSON.stringify({
                action: 'verify-token'
            })
        });
        
        const data = await response.json();
        
        if (data.success && data.valid) {
            // Update user data if needed
            if (data.user) {
                localStorage.setItem('user', JSON.stringify(data.user));
            }
            return true;
        }
        
        // Token invalid, clear local data
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        return false;
    } catch (error) {
        console.error('Token verification error:', error);
        return false;
    }
}

/**
 * Get user roles
 * @returns {Array} Array of role objects
 */
export function getUserRoles() {
    const user = getCurrentUser();
    return user && user.roles ? user.roles : [];
}

/**
 * Check if user has a specific role
 * @param {string} roleName - Role name to check (e.g., 'Admin', 'Player', 'Organizer')
 * @returns {boolean} True if user has the role
 */
export function hasRole(roleName) {
    const roles = getUserRoles();
    return roles.some(role => role.role_name === roleName);
}

/**
 * Check if user is an admin
 * @returns {boolean} True if user is an admin
 */
export function isAdmin() {
    return hasRole('Admin');
}

/**
 * Check if user is an organizer
 * @returns {boolean} True if user is an organizer
 */
export function isOrganizer() {
    return hasRole('Organizer');
}

/**
 * Check if user is a player
 * @returns {boolean} True if user is a player
 */
export function isPlayer() {
    return hasRole('Player');
}

/**
 * Check if user has a specific role (API call)
 * @param {string} roleName - Role name to check
 * @returns {Promise<boolean>} True if user has the role
 */
export async function checkRole(roleName) {
    try {
        const response = await authenticatedFetch(AUTH_API_URL, {
            method: 'POST',
            body: JSON.stringify({
                action: 'check-role',
                role: roleName
            })
        });
        
        const data = await response.json();
        return data.success && data.hasRole;
    } catch (error) {
        console.error('Check role error:', error);
        return false;
    }
}

/**
 * Get all available roles
 * @returns {Promise<Array>} Array of all roles
 */
export async function getAllRoles() {
    try {
        const response = await fetch(AUTH_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'get-roles'
            })
        });
        
        const data = await response.json();
        return data.success ? data.roles : [];
    } catch (error) {
        console.error('Get roles error:', error);
        return [];
    }
}

/**
 * Request organizer role upgrade
 * @param {string} reason - Reason for requesting organizer role
 * @returns {Promise<Object>} Response data
 */
export async function requestOrganizerRole(reason = '') {
    try {
        const response = await authenticatedFetch(AUTH_API_URL, {
            method: 'POST',
            body: JSON.stringify({
                action: 'request-organizer-role',
                reason: reason
            })
        });
        
        return await response.json();
    } catch (error) {
        console.error('Request organizer role error:', error);
        throw new Error('Failed to submit organizer role request.');
    }
}

/**
 * Get pending role requests (Admin only)
 * @returns {Promise<Array>} Array of pending role requests
 */
export async function getPendingRoleRequests() {
    try {
        const response = await authenticatedFetch(`${ADMIN_API_URL}?action=pending-requests`, {
            method: 'GET'
        });
        
        const data = await response.json();
        return data.success ? data.requests : [];
    } catch (error) {
        console.error('Get pending role requests error:', error);
        return [];
    }
}

/**
 * Approve role request (Admin only)
 * @param {number} requestId - Role request ID
 * @returns {Promise<Object>} Response data
 */
export async function approveRoleRequest(requestId) {
    try {
        const response = await authenticatedFetch(ADMIN_API_URL, {
            method: 'POST',
            body: JSON.stringify({
                action: 'approve-request',
                request_id: requestId
            })
        });
        
        return await response.json();
    } catch (error) {
        console.error('Approve role request error:', error);
        throw new Error('Failed to approve role request.');
    }
}

/**
 * Reject role request (Admin only)
 * @param {number} requestId - Role request ID
 * @returns {Promise<Object>} Response data
 */
export async function rejectRoleRequest(requestId) {
    try {
        const response = await authenticatedFetch(ADMIN_API_URL, {
            method: 'POST',
            body: JSON.stringify({
                action: 'reject-request',
                request_id: requestId
            })
        });
        
        return await response.json();
    } catch (error) {
        console.error('Reject role request error:', error);
        throw new Error('Failed to reject role request.');
    }
}

/**
 * Get all users with roles (Admin only)
 * @returns {Promise<Array>} Array of users with their roles
 */
export async function getAllUsers() {
    try {
        const response = await authenticatedFetch(`${ADMIN_API_URL}?action=all-users`, {
            method: 'GET'
        });
        
        const data = await response.json();
        return data.success ? data.users : [];
    } catch (error) {
        console.error('Get all users error:', error);
        return [];
    }
}

/**
 * Assign role to user (Admin only)
 * @param {number} userId - User ID
 * @param {number} roleId - Role ID
 * @returns {Promise<Object>} Response data
 */
export async function assignRole(userId, roleId) {
    try {
        const response = await authenticatedFetch(ADMIN_API_URL, {
            method: 'POST',
            body: JSON.stringify({
                action: 'assign-role',
                user_id: userId,
                role_id: roleId
            })
        });
        
        return await response.json();
    } catch (error) {
        console.error('Assign role error:', error);
        throw new Error('Failed to assign role.');
    }
}

/**
 * Remove role from user (Admin only)
 * @param {number} userId - User ID
 * @param {number} roleId - Role ID
 * @returns {Promise<Object>} Response data
 */
export async function removeRole(userId, roleId) {
    try {
        const response = await authenticatedFetch(ADMIN_API_URL, {
            method: 'POST',
            body: JSON.stringify({
                action: 'remove-role',
                user_id: userId,
                role_id: roleId
            })
        });
        
        return await response.json();
    } catch (error) {
        console.error('Remove role error:', error);
        throw new Error('Failed to remove role.');
    }
}

