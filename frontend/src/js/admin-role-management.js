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
    
    const typeColors = {
        'success': 'bg-green-500/10 border-green-500/50 text-green-400',
        'danger': 'bg-red-500/10 border-red-500/50 text-red-400',
        'warning': 'bg-yellow-500/10 border-yellow-500/50 text-yellow-400',
        'info': 'bg-cyan-500/10 border-cyan-500/50 text-cyan-400'
    };
    
    alert.className = `${typeColors[type]} border-2 px-4 py-3 rounded-xl flex items-center justify-between mb-4 backdrop-blur-sm`;
    alert.innerHTML = `
        <span class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            ${message}
        </span>
        <button type="button" class="ml-4 text-current hover:opacity-75 transition-opacity" onclick="this.parentElement.remove()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    alertContainer.appendChild(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 300ms';
        setTimeout(() => alert.remove(), 300);
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
                <div class="text-center text-gray-400 py-12">
                    <svg class="w-16 h-16 mx-auto mb-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-lg">No pending role requests</p>
                </div>
            `;
            return;
        }
        
        let html = '<div class="overflow-x-auto"><table class="w-full">';
        html += `
            <thead class="bg-gray-900 border-b border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Requested Role</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Request Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
        `;
        
        pendingRequests.forEach(request => {
            html += `
                <tr class="hover:bg-gray-900/50 transition-colors">
                    <td class="px-4 py-4 text-white font-semibold">${escapeHtml(request.username)}</td>
                    <td class="px-4 py-4 text-gray-400">${escapeHtml(request.email)}</td>
                    <td class="px-4 py-4">
                        <span class="px-3 py-1 bg-yellow-500/20 text-yellow-400 text-sm font-semibold rounded-full border border-yellow-500/50">
                            ${escapeHtml(request.role_name)}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-gray-400 max-w-xs truncate">${request.reason ? escapeHtml(request.reason) : '<em class="text-gray-500">No reason provided</em>'}</td>
                    <td class="px-4 py-4 text-gray-400 text-sm">${new Date(request.created_at).toLocaleString()}</td>
                    <td class="px-4 py-4">
                        <div class="flex space-x-2">
                            <button 
                                class="px-3 py-1.5 bg-green-500/20 hover:bg-green-500/30 text-green-400 text-sm font-semibold rounded-lg border border-green-500/50 transition-colors flex items-center" 
                                onclick="handleApprove(${request.id})"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve
                            </button>
                            <button 
                                class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 text-sm font-semibold rounded-lg border border-red-500/50 transition-colors flex items-center" 
                                onclick="handleReject(${request.id})"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reject
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        container.innerHTML = html;
    } catch (error) {
        console.error('Error loading pending requests:', error);
        container.innerHTML = `
            <div class="bg-red-500/10 border-2 border-red-500/50 text-red-400 px-4 py-3 rounded-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
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
            <div class="bg-red-500/10 border-2 border-red-500/50 text-red-400 px-4 py-3 rounded-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
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
            <div class="text-center text-gray-400 py-12">
                <svg class="w-16 h-16 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <p class="text-lg">No users found</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="overflow-x-auto"><table class="w-full">';
    html += `
        <thead class="bg-gray-900 border-b border-gray-700">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">ID</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Username</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Email</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Current Roles</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Member Since</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
    `;
    
    users.forEach(user => {
        const roleBadges = user.roles.map(role => {
            const colorClass = getRoleBadgeColor(role.role_name);
            return `<span class="px-2.5 py-1 ${colorClass} text-xs font-semibold rounded-full">${escapeHtml(role.role_name)}</span>`;
        }).join(' ');
        
        html += `
            <tr class="hover:bg-gray-900/50 transition-colors">
                <td class="px-4 py-4 text-gray-400">#${user.id}</td>
                <td class="px-4 py-4 text-white font-semibold">${escapeHtml(user.username)}</td>
                <td class="px-4 py-4 text-gray-400">${escapeHtml(user.email)}</td>
                <td class="px-4 py-4">${roleBadges || '<em class="text-gray-500">No roles</em>'}</td>
                <td class="px-4 py-4 text-gray-400 text-sm">${new Date(user.created_at).toLocaleDateString()}</td>
                <td class="px-4 py-4">
                    <button 
                        class="px-4 py-2 bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-400 font-semibold rounded-lg border border-cyan-500/50 transition-colors flex items-center text-sm" 
                        onclick="openManageRolesModal(${user.id})"
                    >
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Manage Roles
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
        'Admin': 'bg-red-500/20 text-red-400 border border-red-500/50',
        'Organizer': 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50',
        'Player': 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/50'
    };
    return colors[roleName] || 'bg-gray-500/20 text-gray-400 border border-gray-500/50';
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
        <div id="manageRolesModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="relative bg-gray-800 rounded-2xl border border-cyan-500/30 max-w-md w-full shadow-2xl shadow-cyan-500/20">
                <!-- Header -->
                <div class="bg-gradient-to-r from-cyan-600 to-purple-600 px-6 py-4 rounded-t-2xl border-b border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Manage Roles for ${escapeHtml(user.username)}
                        </h3>
                        <button type="button" class="text-white/70 hover:text-white transition-colors" onclick="closeManageRolesModal()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="px-6 py-5">
                    <div class="mb-4">
                        <p class="text-gray-400"><span class="font-semibold text-white">Email:</span> ${escapeHtml(user.email)}</p>
                    </div>
                    
                    <div class="border-t border-gray-700 pt-4 mb-4">
                        <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Roles</h4>
                        <div class="space-y-2">
    `;
    
    allRoles.forEach(role => {
        const hasRole = userRoleIds.includes(role.id);
        modalHtml += `
            <label class="flex items-start p-3 bg-gray-900 rounded-lg border border-gray-700 hover:border-cyan-500/50 transition-colors cursor-pointer">
                <input type="checkbox" value="${role.id}" id="role-${role.id}" ${hasRole ? 'checked' : ''} 
                       class="mt-1 w-4 h-4 text-cyan-500 bg-gray-700 border-gray-600 rounded focus:ring-cyan-500 focus:ring-2 cursor-pointer">
                <div class="ml-3">
                    <div class="text-white font-semibold">${escapeHtml(role.role_name)}</div>
                    <div class="text-sm text-gray-400">${escapeHtml(role.description)}</div>
                </div>
            </label>
        `;
    });
    
    modalHtml += `
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-900 rounded-b-2xl border-t border-gray-700 flex justify-end space-x-3">
                    <button type="button" class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition-colors" onclick="closeManageRolesModal()">
                        Cancel
                    </button>
                    <button type="button" class="px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-bold rounded-lg shadow-lg shadow-cyan-500/30 transition-all" onclick="saveUserRoles(${userId})">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('manageRolesModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to container
    const modalContainer = document.getElementById('modal-container');
    modalContainer.innerHTML = modalHtml;
};

/**
 * Close manage roles modal
 */
window.closeManageRolesModal = function() {
    const modal = document.getElementById('manageRolesModal');
    if (modal) {
        modal.remove();
    }
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
        closeManageRolesModal();
        
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
