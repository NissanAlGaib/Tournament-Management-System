// Admin Role Management Page
// Handles pending role requests and user role assignments

import { 
    isAdmin, 
    getPendingRoleRequests, 
    approveRoleRequest, 
    rejectRoleRequest,
    getAllUsers,
    assignRole,
    removeRole,
    getAllRoles
} from './core/auth.js';
import { displayUserRoleBadges } from './roleUtils.js';

let allUsers = [];
let allRoles = [];
let pendingRequests = [];

/**
 * Check if user is admin, redirect if not
 */
function checkAdminAccess() {
    if (!isAdmin()) {
        alert('Access denied. Admin privileges required.');
        window.location.href = '../home/dashboard.php';
    }
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    alertContainer.appendChild(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 150);
    }, 5000);
}

/**
 * Load and display pending role requests
 */
async function loadPendingRequests() {
    const container = document.getElementById('pending-requests-container');
    
    try {
        pendingRequests = await getPendingRoleRequests();
        
        // Update count badge
        document.getElementById('pending-count').textContent = pendingRequests.length;
        
        if (pendingRequests.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-check-circle fa-3x"></i>
                    <p class="mt-2">No pending role requests</p>
                </div>
            `;
            return;
        }
        
        let html = '<div class="table-responsive"><table class="table table-hover">';
        html += `
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Requested Role</th>
                    <th>Reason</th>
                    <th>Request Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        `;
        
        pendingRequests.forEach(request => {
            html += `
                <tr>
                    <td><strong>${escapeHtml(request.username)}</strong></td>
                    <td>${escapeHtml(request.email)}</td>
                    <td>
                        <span class="badge bg-warning text-dark">
                            ${escapeHtml(request.role_name)}
                        </span>
                    </td>
                    <td>${request.reason ? escapeHtml(request.reason) : '<em class="text-muted">No reason provided</em>'}</td>
                    <td>${new Date(request.created_at).toLocaleString()}</td>
                    <td>
                        <button class="btn btn-sm btn-success me-1" onclick="handleApprove(${request.id})">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="handleReject(${request.id})">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        container.innerHTML = html;
    } catch (error) {
        console.error('Error loading pending requests:', error);
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Failed to load pending requests. Please try again.
            </div>
        `;
    }
}

/**
 * Load and display all users
 */
async function loadUsers() {
    const container = document.getElementById('users-container');
    
    try {
        allUsers = await getAllUsers();
        allRoles = await getAllRoles();
        
        displayUsers(allUsers);
    } catch (error) {
        console.error('Error loading users:', error);
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Failed to load users. Please try again.
            </div>
        `;
    }
}

/**
 * Display users in the table
 */
function displayUsers(users) {
    const container = document.getElementById('users-container');
    
    if (users.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-users fa-3x"></i>
                <p class="mt-2">No users found</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-striped table-hover">';
    html += `
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Current Roles</th>
                <th>Member Since</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    users.forEach(user => {
        const roleBadges = user.roles.map(role => {
            const colorClass = getRoleBadgeColor(role.role_name);
            return `<span class="badge ${colorClass} me-1">${escapeHtml(role.role_name)}</span>`;
        }).join('');
        
        html += `
            <tr>
                <td>${user.id}</td>
                <td><strong>${escapeHtml(user.username)}</strong></td>
                <td>${escapeHtml(user.email)}</td>
                <td>${roleBadges || '<em class="text-muted">No roles</em>'}</td>
                <td>${new Date(user.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="openManageRolesModal(${user.id})">
                        <i class="fas fa-cog"></i> Manage Roles
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

/**
 * Get badge color for role
 */
function getRoleBadgeColor(roleName) {
    const colors = {
        'Admin': 'bg-danger',
        'Organizer': 'bg-warning text-dark',
        'Player': 'bg-primary'
    };
    return colors[roleName] || 'bg-secondary';
}

/**
 * Handle approve button click
 */
window.handleApprove = async function(requestId) {
    if (!confirm('Are you sure you want to approve this role request?')) {
        return;
    }
    
    try {
        const result = await approveRoleRequest(requestId);
        if (result.success) {
            showAlert('Role request approved successfully!', 'success');
            await loadPendingRequests();
            await loadUsers(); // Refresh users list to show updated roles
        } else {
            showAlert('Failed to approve role request: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error approving request:', error);
        showAlert('An error occurred while approving the request.', 'danger');
    }
};

/**
 * Handle reject button click
 */
window.handleReject = async function(requestId) {
    if (!confirm('Are you sure you want to reject this role request?')) {
        return;
    }
    
    try {
        const result = await rejectRoleRequest(requestId);
        if (result.success) {
            showAlert('Role request rejected.', 'info');
            await loadPendingRequests();
        } else {
            showAlert('Failed to reject role request: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error rejecting request:', error);
        showAlert('An error occurred while rejecting the request.', 'danger');
    }
};

/**
 * Open manage roles modal for a user
 */
window.openManageRolesModal = function(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (!user) return;
    
    const userRoleIds = user.roles.map(r => r.id);
    
    let modalHtml = `
        <div class="modal fade" id="manageRolesModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Roles for ${escapeHtml(user.username)}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Current Email:</strong> ${escapeHtml(user.email)}</p>
                        <hr>
                        <h6>Roles:</h6>
    `;
    
    allRoles.forEach(role => {
        const hasRole = userRoleIds.includes(role.id);
        modalHtml += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="${role.id}" 
                       id="role-${role.id}" ${hasRole ? 'checked' : ''}>
                <label class="form-check-label" for="role-${role.id}">
                    ${escapeHtml(role.role_name)} 
                    <small class="text-muted">${escapeHtml(role.description)}</small>
                </label>
            </div>
        `;
    });
    
    modalHtml += `
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveUserRoles(${userId})">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('manageRolesModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('manageRolesModal'));
    modal.show();
};

/**
 * Save user roles
 */
window.saveUserRoles = async function(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (!user) return;
    
    const currentRoleIds = user.roles.map(r => r.id);
    const selectedRoleIds = [];
    
    // Get selected roles from checkboxes
    allRoles.forEach(role => {
        const checkbox = document.getElementById(`role-${role.id}`);
        if (checkbox && checkbox.checked) {
            selectedRoleIds.push(role.id);
        }
    });
    
    // Determine roles to add and remove
    const rolesToAdd = selectedRoleIds.filter(id => !currentRoleIds.includes(id));
    const rolesToRemove = currentRoleIds.filter(id => !selectedRoleIds.includes(id));
    
    try {
        // Add new roles
        for (const roleId of rolesToAdd) {
            await assignRole(userId, roleId);
        }
        
        // Remove unchecked roles
        for (const roleId of rolesToRemove) {
            await removeRole(userId, roleId);
        }
        
        showAlert('User roles updated successfully!', 'success');
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('manageRolesModal'));
        modal.hide();
        
        // Refresh users list
        await loadUsers();
    } catch (error) {
        console.error('Error saving user roles:', error);
        showAlert('An error occurred while saving user roles.', 'danger');
    }
};

/**
 * Filter users by search input
 */
function setupUserSearch() {
    const searchInput = document.getElementById('user-search');
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const filteredUsers = allUsers.filter(user => 
            user.username.toLowerCase().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm)
        );
        displayUsers(filteredUsers);
    });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Initialize page
 */
async function init() {
    // Check admin access
    checkAdminAccess();
    
    // Display user role badges
    const badgeContainer = document.getElementById('user-role-badges');
    if (badgeContainer) {
        displayUserRoleBadges(badgeContainer);
    }
    
    // Load data
    await Promise.all([
        loadPendingRequests(),
        loadUsers()
    ]);
    
    // Setup search
    setupUserSearch();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', init);
