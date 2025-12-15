/**
 * Print Utilities for Analytics Pages
 * Provides comprehensive printing functionality for all analytics views
 */

/**
 * Print styles to be injected into the print window
 */
const getPrintStyles = () => `
    <style>
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            body {
                background: white !important;
                color: black !important;
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
            }
            
            .print-header {
                border-bottom: 3px solid #06b6d4;
                padding-bottom: 15px;
                margin-bottom: 25px;
            }
            
            .print-header h1 {
                margin: 0 0 5px 0;
                color: #0c4a6e;
                font-size: 28px;
            }
            
            .print-header .subtitle {
                color: #64748b;
                font-size: 14px;
                margin: 5px 0;
            }
            
            .print-header .timestamp {
                color: #94a3b8;
                font-size: 12px;
                margin-top: 10px;
            }
            
            .print-section {
                page-break-inside: avoid;
                margin-bottom: 30px;
            }
            
            .print-section h2 {
                color: #0c4a6e;
                font-size: 20px;
                margin: 0 0 15px 0;
                padding-bottom: 8px;
                border-bottom: 2px solid #e2e8f0;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .stat-card {
                border: 2px solid #e2e8f0;
                border-radius: 8px;
                padding: 15px;
                background: #f8fafc;
            }
            
            .stat-card .label {
                color: #64748b;
                font-size: 12px;
                text-transform: uppercase;
                font-weight: bold;
                margin-bottom: 8px;
            }
            
            .stat-card .value {
                color: #0c4a6e;
                font-size: 32px;
                font-weight: bold;
            }
            
            .data-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 25px;
            }
            
            .data-table thead {
                background: #f1f5f9;
            }
            
            .data-table th {
                padding: 12px;
                text-align: left;
                font-weight: bold;
                color: #0c4a6e;
                border-bottom: 2px solid #cbd5e1;
                font-size: 14px;
            }
            
            .data-table td {
                padding: 10px 12px;
                border-bottom: 1px solid #e2e8f0;
                color: #334155;
                font-size: 13px;
            }
            
            .data-table tr:nth-child(even) {
                background: #f8fafc;
            }
            
            .badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 11px;
                font-weight: bold;
                margin-right: 5px;
            }
            
            .badge.admin {
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #fca5a5;
            }
            
            .badge.organizer {
                background: #fef3c7;
                color: #92400e;
                border: 1px solid #fcd34d;
            }
            
            .badge.player {
                background: #cffafe;
                color: #164e63;
                border: 1px solid #67e8f9;
            }
            
            .badge.active {
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #6ee7b7;
            }
            
            .badge.inactive {
                background: #f3f4f6;
                color: #4b5563;
                border: 1px solid #d1d5db;
            }
            
            .activity-item {
                padding: 12px;
                border-left: 3px solid #06b6d4;
                background: #f8fafc;
                margin-bottom: 10px;
                border-radius: 4px;
            }
            
            .activity-item .user {
                font-weight: bold;
                color: #0c4a6e;
            }
            
            .activity-item .action {
                color: #334155;
                margin: 5px 0;
            }
            
            .activity-item .timestamp {
                color: #94a3b8;
                font-size: 11px;
            }
            
            .session-item {
                padding: 12px;
                border: 1px solid #e2e8f0;
                background: #f8fafc;
                margin-bottom: 10px;
                border-radius: 4px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .session-item .user-info {
                flex: 1;
            }
            
            .session-item .username {
                font-weight: bold;
                color: #0c4a6e;
            }
            
            .session-item .details {
                color: #64748b;
                font-size: 11px;
                margin-top: 3px;
            }
            
            .session-item .status {
                color: #059669;
                font-size: 11px;
                font-weight: bold;
            }
            
            .print-footer {
                margin-top: 40px;
                padding-top: 15px;
                border-top: 2px solid #e2e8f0;
                text-align: center;
                color: #94a3b8;
                font-size: 11px;
            }
            
            .no-print {
                display: none !important;
            }
            
            @page {
                margin: 1.5cm;
            }
        }
    </style>
`;

/**
 * Format date for printing
 */
function formatPrintDate(date = new Date()) {
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

/**
 * Escape HTML for printing
 */
function escapeHtml(unsafe) {
  if (!unsafe) return "";
  return String(unsafe)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

/**
 * Print Dashboard Statistics
 */
export function printDashboardStats(stats, users, activities) {
  const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Admin Dashboard - Analytics Report</title>
            ${getPrintStyles()}
        </head>
        <body>
            <div class="print-header">
                <h1>🏆 Tournament Management System</h1>
                <div class="subtitle">Admin Dashboard - Analytics Report</div>
                <div class="timestamp">Generated on: ${formatPrintDate()}</div>
            </div>
            
            <div class="print-section">
                <h2>Dashboard Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="label">Total Users</div>
                        <div class="value">${escapeHtml(
                          stats.totalUsers || 0
                        )}</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Pending Requests</div>
                        <div class="value">${escapeHtml(
                          stats.pendingRequests || 0
                        )}</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Active Sessions</div>
                        <div class="value">${escapeHtml(
                          stats.activeSessions || 0
                        )}</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Total Tournaments</div>
                        <div class="value">${escapeHtml(
                          stats.totalTournaments || 0
                        )}</div>
                    </div>
                </div>
            </div>
            
            ${
              users && users.length > 0
                ? `
            <div class="print-section">
                <h2>Recent Users (Last ${Math.min(users.length, 10)})</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${users
                          .slice(0, 10)
                          .map(
                            (user) => `
                            <tr>
                                <td>${escapeHtml(user.username)}</td>
                                <td>${escapeHtml(user.email)}</td>
                                <td>
                                    ${
                                      user.roles &&
                                      user.roles
                                        .map((role) => {
                                          const roleClass =
                                            role.role_name.toLowerCase();
                                          return `<span class="badge ${roleClass}">${escapeHtml(
                                            role.role_name
                                          )}</span>`;
                                        })
                                        .join("")
                                    }
                                </td>
                                <td>${new Date(
                                  user.created_at
                                ).toLocaleDateString()}</td>
                            </tr>
                        `
                          )
                          .join("")}
                    </tbody>
                </table>
            </div>
            `
                : ""
            }
            
            ${
              activities && activities.length > 0
                ? `
            <div class="print-section">
                <h2>Recent Activity (Last ${Math.min(
                  activities.length,
                  10
                )})</h2>
                ${activities
                  .slice(0, 10)
                  .map(
                    (activity) => `
                    <div class="activity-item">
                        <div class="user">${escapeHtml(activity.user)}</div>
                        <div class="action">${escapeHtml(activity.action)}</div>
                        <div class="timestamp">${new Date(
                          activity.timestamp
                        ).toLocaleString()}</div>
                    </div>
                `
                  )
                  .join("")}
            </div>
            `
                : ""
            }
            
            <div class="print-footer">
                Tournament Management System - Confidential Analytics Report
            </div>
        </body>
        </html>
    `;

  openPrintWindow(printContent);
}

/**
 * Print Activity Monitoring Data
 */
export function printActivityMonitoring(sessions, activityLog) {
  const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>System Activity - Analytics Report</title>
            ${getPrintStyles()}
        </head>
        <body>
            <div class="print-header">
                <h1>🏆 Tournament Management System</h1>
                <div class="subtitle">System Activity - Monitoring Report</div>
                <div class="timestamp">Generated on: ${formatPrintDate()}</div>
            </div>
            
            ${
              sessions && sessions.length > 0
                ? `
            <div class="print-section">
                <h2>Active Sessions (${sessions.length})</h2>
                ${sessions
                  .map(
                    (session) => `
                    <div class="session-item">
                        <div class="user-info">
                            <div class="username">${escapeHtml(
                              session.username
                            )}</div>
                            <div class="details">
                                IP: ${escapeHtml(
                                  session.ip_address || "N/A"
                                )} | 
                                ${escapeHtml(
                                  session.user_agent
                                    ? getBrowserName(session.user_agent)
                                    : "Unknown Browser"
                                )}
                            </div>
                        </div>
                        <div class="status">
                            <span class="badge active">Active</span>
                            <div>${getTimeAgo(session.last_activity)}</div>
                        </div>
                    </div>
                `
                  )
                  .join("")}
            </div>
            `
                : `
            <div class="print-section">
                <h2>Active Sessions</h2>
                <p style="color: #94a3b8;">No active sessions at this time.</p>
            </div>
            `
            }
            
            ${
              activityLog && activityLog.length > 0
                ? `
            <div class="print-section">
                <h2>Activity Log (Last ${Math.min(activityLog.length, 50)})</h2>
                ${activityLog
                  .slice(0, 50)
                  .map(
                    (activity) => `
                    <div class="activity-item">
                        <div class="user">${escapeHtml(activity.user)}</div>
                        <div class="action">${escapeHtml(activity.action)}</div>
                        <div class="timestamp">${new Date(
                          activity.timestamp
                        ).toLocaleString()}</div>
                    </div>
                `
                  )
                  .join("")}
            </div>
            `
                : `
            <div class="print-section">
                <h2>Activity Log</h2>
                <p style="color: #94a3b8;">No recent activity recorded.</p>
            </div>
            `
            }
            
            <div class="print-footer">
                Tournament Management System - Confidential Activity Report
            </div>
        </body>
        </html>
    `;

  openPrintWindow(printContent);
}

/**
 * Print User Management Data
 */
export function printUserManagement(users, filters = {}) {
  const filteredUsers = applyFilters(users, filters);

  const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>User Management - Analytics Report</title>
            ${getPrintStyles()}
        </head>
        <body>
            <div class="print-header">
                <h1>🏆 Tournament Management System</h1>
                <div class="subtitle">User Management Report</div>
                ${
                  filters.searchTerm
                    ? `<div class="subtitle">Filter: "${escapeHtml(
                        filters.searchTerm
                      )}"</div>`
                    : ""
                }
                <div class="timestamp">Generated on: ${formatPrintDate()}</div>
            </div>
            
            <div class="print-section">
                <h2>Summary Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="label">Total Users</div>
                        <div class="value">${filteredUsers.length}</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Admins</div>
                        <div class="value">${countUsersByRole(
                          filteredUsers,
                          "Admin"
                        )}</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Organizers</div>
                        <div class="value">${countUsersByRole(
                          filteredUsers,
                          "Organizer"
                        )}</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Players</div>
                        <div class="value">${countUsersByRole(
                          filteredUsers,
                          "Player"
                        )}</div>
                    </div>
                </div>
            </div>
            
            ${
              filteredUsers && filteredUsers.length > 0
                ? `
            <div class="print-section">
                <h2>User List (${filteredUsers.length} users)</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${filteredUsers
                          .map(
                            (user) => `
                            <tr>
                                <td>${escapeHtml(user.user_id)}</td>
                                <td>${escapeHtml(user.username)}</td>
                                <td>${escapeHtml(user.email)}</td>
                                <td>
                                    ${
                                      user.roles &&
                                      user.roles
                                        .map((role) => {
                                          const roleClass =
                                            role.role_name.toLowerCase();
                                          return `<span class="badge ${roleClass}">${escapeHtml(
                                            role.role_name
                                          )}</span>`;
                                        })
                                        .join("")
                                    }
                                </td>
                                <td>${new Date(
                                  user.created_at
                                ).toLocaleDateString()}</td>
                            </tr>
                        `
                          )
                          .join("")}
                    </tbody>
                </table>
            </div>
            `
                : `
            <div class="print-section">
                <h2>User List</h2>
                <p style="color: #94a3b8;">No users found matching the criteria.</p>
            </div>
            `
            }
            
            <div class="print-footer">
                Tournament Management System - Confidential User Report
            </div>
        </body>
        </html>
    `;

  openPrintWindow(printContent);
}

/**
 * Helper: Apply filters to users
 */
function applyFilters(users, filters) {
  if (!filters.searchTerm) return users;

  const searchLower = filters.searchTerm.toLowerCase();
  return users.filter(
    (user) =>
      user.username?.toLowerCase().includes(searchLower) ||
      user.email?.toLowerCase().includes(searchLower)
  );
}

/**
 * Helper: Count users by role
 */
function countUsersByRole(users, roleName) {
  return users.filter(
    (user) =>
      user.roles && user.roles.some((role) => role.role_name === roleName)
  ).length;
}

/**
 * Helper: Get browser name from user agent
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
 * Helper: Get time ago string
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
 * Open print window and trigger print
 */
function openPrintWindow(content) {
  const printWindow = window.open("", "_blank", "width=800,height=600");

  if (!printWindow) {
    alert("Please allow pop-ups to print the report.");
    return;
  }

  printWindow.document.write(content);
  printWindow.document.close();

  // Wait for content to load, then print
  printWindow.onload = function () {
    printWindow.focus();
    setTimeout(() => {
      printWindow.print();
    }, 250);
  };
}

/**
 * Create print button element
 */
export function createPrintButton(text = "Print Report", icon = true) {
  const button = document.createElement("button");
  button.className =
    "px-4 py-2 bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg font-semibold transition-colors flex items-center space-x-2 no-print";

  if (icon) {
    button.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>${text}</span>
        `;
  } else {
    button.textContent = text;
  }

  return button;
}
