// Admin Dashboard JavaScript
import { 
    isAdmin,
    getCurrentUser,
    getAllUsers,
    getPendingRoleRequests,
    getDashboardStats,
    getActivityLog
} from './core/auth.js';
import { displayUserRoleBadges } from './roleUtils.js';
import { getPagePath } from './pathHelper.js';

/**
 * Check if user is admin, redirect if not
 */
function checkAdminAccess() {
    if (!isAdmin()) {
        alert('Access denied. Admin privileges required.');
        window.location.href = getPagePath('home/index.php');
    }
}

/**
 * Load dashboard statistics
 */
async function loadDashboardStats() {
    try {
        // Get all users
        const users = await getAllUsers();
        document.getElementById('total-users').textContent = users.length;

        // Get pending role requests
        const requests = await getPendingRoleRequests();
        document.getElementById('pending-requests').textContent = requests.length;

        // Get dashboard stats from backend
        const stats = await getDashboardStats();
        
        if (stats) {
            document.getElementById('active-sessions').textContent = stats.active_sessions;
            
            // Update tournaments count in the HTML
            const tournamentCountEl = document.querySelector('#total-tournaments');
            if (tournamentCountEl) {
                tournamentCountEl.textContent = stats.tournament_count;
            }
        } else {
            // Set fallback values on error
            document.getElementById('active-sessions').textContent = '0';
        }

        return { users, requests };
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
        // Set fallback values on error
        document.getElementById('active-sessions').textContent = '0';
        return { users: [], requests: [] };
    }
}

/**
 * Display recent users
 */
function displayRecentUsers(users) {
    const container = document.getElementById('recent-users-container');
    
    if (!users || users.length === 0) {
        container.innerHTML = `
            <div class="text-center text-gray-400 py-8">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <p>No users found</p>
            </div>
        `;
        return;
    }

    // Sort by created_at and take last 5
    const recentUsers = users
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 5);

    let html = '';
    recentUsers.forEach(user => {
        const roleBadges = user.roles.map(role => {
            const colorClass = getRoleBadgeColor(role.role_name);
            return `<span class="px-2 py-0.5 ${colorClass} text-xs font-semibold rounded-full">${escapeHtml(role.role_name)}</span>`;
        }).join(' ');

        html += `
            <div class="flex items-center justify-between p-3 bg-gray-900 rounded-lg border border-gray-700 hover:border-cyan-500/50 transition-colors">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-semibold">${escapeHtml(user.username)}</p>
                        <p class="text-xs text-gray-400">${escapeHtml(user.email)}</p>
                    </div>
                </div>
                <div class="flex flex-col items-end space-y-1">
                    <div class="flex space-x-1">
                        ${roleBadges}
                    </div>
                    <p class="text-xs text-gray-500">${getTimeAgo(user.created_at)}</p>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

/**
 * Display recent activity
 */
async function displayRecentActivity(requests) {
    const container = document.getElementById('recent-activity-container');

    try {
        // Fetch activity log from backend
        const activities = await getActivityLog();
        
        if (!activities || activities.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-400 py-8">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <p>No recent activity</p>
                </div>
            `;
            return;
        }

        // Sort by time
        activities.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));

        let html = '';
        activities.slice(0, 5).forEach(activity => {
            const iconSvg = getActivityIcon(activity.type);
            const colorClass = getActivityColor(activity.type);

            html += `
                <div class="flex items-start space-x-3 p-3 bg-gray-900 rounded-lg border border-gray-700">
                    <div class="flex-shrink-0 p-2 ${colorClass} rounded-lg">
                        ${iconSvg}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm">${escapeHtml(activity.user)}: ${escapeHtml(activity.action)}</p>
                        <p class="text-xs text-gray-400 mt-1">${getTimeAgo(activity.timestamp)}</p>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    } catch (error) {
        console.error('Error loading activity:', error);
        container.innerHTML = `
            <div class="text-center text-gray-400 py-8">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <p>Failed to load activity</p>
            </div>
        `;
    }
}

/**
 * Get role badge color
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
 * Get activity icon SVG
 */
function getActivityIcon(type) {
    const icons = {
        'role': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>',
        'tournament': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>',
        'user': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>'
    };
    return icons[type] || icons['user'];
}

/**
 * Get activity color class
 */
function getActivityColor(type) {
    const colors = {
        'role': 'bg-yellow-500/20 text-yellow-400',
        'tournament': 'bg-purple-500/20 text-purple-400',
        'user': 'bg-green-500/20 text-green-400'
    };
    return colors[type] || 'bg-cyan-500/20 text-cyan-400';
}

/**
 * Get time ago string
 */
function getTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`;
    if (seconds < 604800) return `${Math.floor(seconds / 86400)} days ago`;
    return date.toLocaleDateString();
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Initialize dashboard
 */
async function init() {
    // Check admin access
    checkAdminAccess();

    // Display current user info
    const user = getCurrentUser();
    if (user) {
        document.getElementById('admin-username').textContent = user.username;
    }

    // Display user role badges
    const badgeContainer = document.getElementById('user-role-badges');
    if (badgeContainer) {
        displayUserRoleBadges(badgeContainer);
    }

    // Load dashboard data
    const { users, requests } = await loadDashboardStats();
    
    // Display recent data
    displayRecentUsers(users);
    displayRecentActivity(requests);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', init);
