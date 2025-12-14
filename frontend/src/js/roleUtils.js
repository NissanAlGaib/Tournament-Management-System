// Role-based UI Utilities
// Helper functions for conditional rendering based on user roles

import { getUserRoles, hasRole, isAdmin, isOrganizer, isPlayer } from './core/auth.js';

/**
 * Show or hide an element based on required role
 * @param {HTMLElement} element - DOM element to show/hide
 * @param {string|Array<string>} requiredRoles - Required role(s)
 * @param {boolean} hideIfNoRole - If true, hide element; if false, show element
 */
export function toggleElementByRole(element, requiredRoles, hideIfNoRole = true) {
    if (!element) return;
    
    const roles = Array.isArray(requiredRoles) ? requiredRoles : [requiredRoles];
    const userHasRole = roles.some(role => hasRole(role));
    
    if (hideIfNoRole) {
        element.style.display = userHasRole ? '' : 'none';
    } else {
        element.style.display = userHasRole ? 'none' : '';
    }
}

/**
 * Apply role-based visibility to all elements with data-role attribute
 * Usage: <div data-role="Admin">Admin only content</div>
 */
export function applyRoleBasedVisibility() {
    const elements = document.querySelectorAll('[data-role]');
    
    elements.forEach(element => {
        const requiredRole = element.getAttribute('data-role');
        const hideMode = element.getAttribute('data-role-hide') !== 'false';
        toggleElementByRole(element, requiredRole, hideMode);
    });
}

/**
 * Apply role-based visibility to all elements with data-roles attribute (multiple roles)
 * Usage: <div data-roles="Admin,Organizer">Admin or Organizer content</div>
 */
export function applyMultiRoleVisibility() {
    const elements = document.querySelectorAll('[data-roles]');
    
    elements.forEach(element => {
        const requiredRoles = element.getAttribute('data-roles').split(',').map(r => r.trim());
        const hideMode = element.getAttribute('data-role-hide') !== 'false';
        toggleElementByRole(element, requiredRoles, hideMode);
    });
}

/**
 * Display role badges for current user
 * @param {HTMLElement} container - Container element to display badges
 */
export function displayUserRoleBadges(container) {
    if (!container) return;
    
    const roles = getUserRoles();
    container.innerHTML = '';
    
    if (roles.length === 0) {
        container.innerHTML = '<span class="badge badge-secondary">No Roles</span>';
        return;
    }
    
    const roleColors = {
        'Admin': 'badge-danger',
        'Organizer': 'badge-warning',
        'Player': 'badge-primary'
    };
    
    roles.forEach(role => {
        const badge = document.createElement('span');
        badge.className = `badge ${roleColors[role.role_name] || 'badge-secondary'} mr-1`;
        badge.textContent = role.role_name;
        badge.title = role.description || '';
        container.appendChild(badge);
    });
}

/**
 * Create navigation items based on user roles
 * @returns {Array<Object>} Array of navigation items with visibility
 */
export function getRoleBasedNavigation() {
    const navigation = [
        {
            name: 'Dashboard',
            url: '/home/dashboard.php',
            icon: 'fa-home',
            roles: ['Player', 'Organizer', 'Admin']
        },
        {
            name: 'Tournaments',
            url: '/home/tournaments.php',
            icon: 'fa-trophy',
            roles: ['Player', 'Organizer', 'Admin']
        },
        {
            name: 'Create Tournament',
            url: '/organizer/create-tournament.php',
            icon: 'fa-plus-circle',
            roles: ['Organizer', 'Admin']
        },
        {
            name: 'Role Management',
            url: '/admin/role-management.php',
            icon: 'fa-users-cog',
            roles: ['Admin']
        },
        {
            name: 'Profile',
            url: '/home/profile.php',
            icon: 'fa-user',
            roles: ['Player', 'Organizer', 'Admin']
        }
    ];
    
    return navigation.filter(item => {
        return item.roles.some(role => hasRole(role));
    });
}

/**
 * Show admin controls if user is admin
 * @param {HTMLElement} container - Container for admin controls
 */
export function showAdminControls(container) {
    if (!container) return;
    
    if (isAdmin()) {
        container.style.display = '';
    } else {
        container.style.display = 'none';
    }
}

/**
 * Show organizer controls if user is organizer or admin
 * @param {HTMLElement} container - Container for organizer controls
 */
export function showOrganizerControls(container) {
    if (!container) return;
    
    if (isOrganizer() || isAdmin()) {
        container.style.display = '';
    } else {
        container.style.display = 'none';
    }
}

/**
 * Disable element if user doesn't have required role
 * @param {HTMLElement} element - DOM element to disable
 * @param {string|Array<string>} requiredRoles - Required role(s)
 */
export function disableElementByRole(element, requiredRoles) {
    if (!element) return;
    
    const roles = Array.isArray(requiredRoles) ? requiredRoles : [requiredRoles];
    const userHasRole = roles.some(role => hasRole(role));
    
    if (!userHasRole) {
        element.disabled = true;
        element.classList.add('disabled');
        element.title = 'You do not have permission to perform this action';
    }
}

/**
 * Create a role-based notification/alert
 * @param {string} message - Message to display
 * @param {string} role - Role that can see this message
 * @returns {HTMLElement|null} Alert element or null if user doesn't have role
 */
export function createRoleBasedAlert(message, role) {
    if (!hasRole(role)) {
        return null;
    }
    
    const alert = document.createElement('div');
    alert.className = 'alert alert-info alert-dismissible fade show';
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    return alert;
}

/**
 * Initialize all role-based UI features
 * Call this function on page load
 */
export function initializeRoleBasedUI() {
    // Apply role-based visibility
    applyRoleBasedVisibility();
    applyMultiRoleVisibility();
    
    // Display role badges if container exists
    const roleBadgeContainer = document.getElementById('user-role-badges');
    if (roleBadgeContainer) {
        displayUserRoleBadges(roleBadgeContainer);
    }
    
    // Show/hide admin controls
    const adminControls = document.querySelectorAll('.admin-only');
    adminControls.forEach(control => {
        showAdminControls(control);
    });
    
    // Show/hide organizer controls
    const organizerControls = document.querySelectorAll('.organizer-only');
    organizerControls.forEach(control => {
        showOrganizerControls(control);
    });
}

/**
 * Check if user can perform action and show error if not
 * @param {string|Array<string>} requiredRoles - Required role(s)
 * @param {string} actionName - Name of the action for error message
 * @returns {boolean} True if user can perform action
 */
export function canPerformAction(requiredRoles, actionName = 'this action') {
    const roles = Array.isArray(requiredRoles) ? requiredRoles : [requiredRoles];
    const userHasRole = roles.some(role => hasRole(role));
    
    if (!userHasRole) {
        alert(`You do not have permission to perform ${actionName}. Required role(s): ${roles.join(', ')}`);
        return false;
    }
    
    return true;
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeRoleBasedUI);
} else {
    initializeRoleBasedUI();
}
