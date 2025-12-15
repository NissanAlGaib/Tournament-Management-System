// Admin Activity Monitoring JavaScript
import { isAdmin, getActiveSessions, getActivityLog } from "./core/auth.js";
import { displayUserRoleBadges } from "./roleUtils.js";
import { getPagePath } from "./pathHelper.js";
import { printActivityMonitoring } from "./printUtils.js";

/**
 * Check if user is admin, redirect if not
 */
function checkAdminAccess() {
  if (!isAdmin()) {
    alert("Access denied. Admin privileges required.");
    window.location.href = getPagePath("home/index.php");
  }
}

// Pagination state for active sessions
let currentSessionPage = 1;
const sessionsPerPage = 10;
let allSessions = [];

// Store activity data for printing
let activityData = {
  sessions: [],
  activityLog: [],
};

/**
 * Load active sessions from backend
 */
async function loadActiveSessions() {
  const container = document.getElementById("active-sessions-container");

  try {
    // Fetch active sessions from backend
    allSessions = await getActiveSessions();

    // Store sessions for printing
    activityData.sessions = allSessions || [];

    if (allSessions.length === 0) {
      container.innerHTML = `
                <div class="text-center text-gray-400 py-8">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p>No active sessions</p>
                </div>
            `;
      return;
    }

    // Reset page and display
    currentSessionPage = 1;
    displaySessions();
  } catch (error) {
    console.error("Error loading sessions:", error);
    container.innerHTML = `
            <div class="bg-red-500/10 border-2 border-red-500/50 text-red-400 px-4 py-3 rounded-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Failed to load sessions.
            </div>
        `;
  }
}

/**
 * Display active sessions with pagination
 */
function displaySessions() {
  const container = document.getElementById("active-sessions-container");
  const startIndex = 0;
  const endIndex = currentSessionPage * sessionsPerPage;
  const displayedSessions = allSessions.slice(startIndex, endIndex);
  const hasMore = endIndex < allSessions.length;

  let html = '<div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">';
  displayedSessions.forEach((session) => {
    const browserIcon = getBrowserIcon(session.user_agent);

    html += `
            <div class="flex items-center justify-between p-4 bg-gray-900 rounded-lg border border-gray-700 hover:border-green-500/50 transition-colors">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-green-500/20 rounded-lg">
                        ${browserIcon}
                    </div>
                    <div>
                        <p class="text-white font-semibold">${escapeHtml(
                          session.username
                        )}</p>
                        <p class="text-xs text-gray-400">IP: ${escapeHtml(
                          session.ip_address || "N/A"
                        )} • ${escapeHtml(
      getBrowserName(session.user_agent)
    )}</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-xs text-green-400">Active</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">${getTimeAgo(
                      session.last_activity
                    )}</p>
                </div>
            </div>
        `;
  });
  html += "</div>";

  // Add Load More button if there are more sessions
  if (hasMore) {
    html += `
            <div class="mt-4 text-center">
                <button onclick="loadMoreSessions()" class="px-4 py-2 bg-green-500/20 text-green-400 border border-green-500/50 rounded-lg hover:bg-green-500/30 transition-colors">
                    Load More (${allSessions.length - endIndex} remaining)
                </button>
            </div>
        `;
  }

  // Show session count
  if (allSessions.length > sessionsPerPage) {
    html =
      `<div class="mb-3 text-sm text-gray-400">Showing ${displayedSessions.length} of ${allSessions.length} sessions</div>` +
      html;
  }

  container.innerHTML = html;
}

/**
 * Load more sessions
 */
window.loadMoreSessions = function () {
  currentSessionPage++;
  displaySessions();
};

/**
 * Load activity log from backend
 */
async function loadActivityLog() {
  const container = document.getElementById("activity-log-container");

  try {
    // Fetch activity log from backend
    const activities = await getActivityLog();
    // Store activities for printing
    activityData.activityLog = activities || [];
    if (activities.length === 0) {
      container.innerHTML = `
                <div class="text-center text-gray-400 py-8">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p>No recent activity</p>
                </div>
            `;
      return;
    }

    let html = '<div class="space-y-2 max-h-[500px] overflow-y-auto pr-2">';
    activities.forEach((activity) => {
      const { icon, color } = getActivityTypeIcon(activity.type);

      html += `
                <div class="flex items-start space-x-3 p-3 bg-gray-900 rounded-lg border border-gray-700">
                    <div class="flex-shrink-0 p-2 ${color} rounded-lg">
                        ${icon}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm">
                            <span class="font-semibold">${escapeHtml(
                              activity.user
                            )}</span> ${escapeHtml(activity.action)}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">${getTimeAgo(
                          activity.timestamp
                        )}</p>
                    </div>
                </div>
            `;
    });
    html += "</div>";

    container.innerHTML = html;
  } catch (error) {
    console.error("Error loading activity log:", error);
    container.innerHTML = `
            <div class="bg-red-500/10 border-2 border-red-500/50 text-red-400 px-4 py-3 rounded-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Failed to load activity log.
            </div>
        `;
  }
}

/**
 * Get browser icon SVG
 */
function getBrowserIcon(userAgent) {
  return `
        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
    `;
}

/**
 * Get browser name from user agent
 */
function getBrowserName(userAgent) {
  if (!userAgent) return "Unknown";

  if (userAgent.includes("Firefox")) return "Firefox";
  if (userAgent.includes("Chrome")) return "Chrome";
  if (userAgent.includes("Safari")) return "Safari";
  if (userAgent.includes("Edge")) return "Edge";
  if (userAgent.includes("Opera")) return "Opera";

  return "Browser";
}

/**
 * Get activity type icon
 */
function getActivityTypeIcon(type) {
  const types = {
    login: {
      icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>',
      color: "bg-cyan-500/20 text-cyan-400",
    },
    role: {
      icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>',
      color: "bg-yellow-500/20 text-yellow-400",
    },
    tournament: {
      icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>',
      color: "bg-purple-500/20 text-purple-400",
    },
    tournament_created: {
      icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>',
      color: "bg-blue-500/20 text-blue-400",
    },
    tournament_completed: {
      icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>',
      color: "bg-green-500/20 text-green-400",
    },
    tournament_started: {
      icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
      color: "bg-orange-500/20 text-orange-400",
    },
    tournament_registration: {
      icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>',
      color: "bg-teal-500/20 text-teal-400",
    },
    user: {
      icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>',
      color: "bg-indigo-500/20 text-indigo-400",
    },
  };
  return types[type] || types["user"];
}

/**
 * Get time ago string
 */
function getTimeAgo(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);

  if (seconds < 60) return "Just now";
  if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`;
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`;
  if (seconds < 604800) return `${Math.floor(seconds / 86400)} days ago`;
  return date.toLocaleDateString();
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(unsafe) {
  if (!unsafe) return "";
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
  const badgeContainer = document.getElementById("user-role-badges");
  if (badgeContainer) {
    displayUserRoleBadges(badgeContainer);
  }

  // Load activity data
  await Promise.all([loadActiveSessions(), loadActivityLog()]);

  // Setup print button
  setupPrintButton();
}

/**
 * Setup print button functionality
 */
function setupPrintButton() {
  const printBtn = document.getElementById("print-activity-btn");
  if (printBtn) {
    printBtn.addEventListener("click", () => {
      printActivityMonitoring(activityData.sessions, activityData.activityLog);
    });
  }
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", init);
