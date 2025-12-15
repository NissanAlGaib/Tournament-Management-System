// Admin Users Management JavaScript
import { isAdmin, getAllUsers } from "./core/auth.js";
import { displayUserRoleBadges } from "./roleUtils.js";
import { getPagePath } from "./pathHelper.js";
import { printUserManagement } from "./printUtils.js";

let allUsers = [];
let currentSearchTerm = "";

/**
 * Check if user is admin, redirect if not
 */
function checkAdminAccess() {
  if (!isAdmin()) {
    alert("Access denied. Admin privileges required.");
    window.location.href = getPagePath("home/index.php");
  }
}

/**
 * Load and display all users
 */
async function loadUsers() {
  const container = document.getElementById("users-container");

  try {
    allUsers = await getAllUsers();
    displayUsers(allUsers);
  } catch (error) {
    console.error("Error loading users:", error);
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
  const container = document.getElementById("users-container");

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
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Roles</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Member Since</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
    `;

  users.forEach((user) => {
    const roleBadges = user.roles
      .map((role) => {
        const colorClass = getRoleBadgeColor(role.role_name);
        return `<span class="px-2.5 py-1 ${colorClass} text-xs font-semibold rounded-full">${escapeHtml(
          role.role_name
        )}</span>`;
      })
      .join(" ");

    html += `
            <tr class="hover:bg-gray-900/50 transition-colors">
                <td class="px-4 py-4 text-gray-400">#${user.id}</td>
                <td class="px-4 py-4 text-white font-semibold">${escapeHtml(
                  user.username
                )}</td>
                <td class="px-4 py-4 text-gray-400">${escapeHtml(
                  user.email
                )}</td>
                <td class="px-4 py-4">${
                  roleBadges || '<em class="text-gray-500">No roles</em>'
                }</td>
                <td class="px-4 py-4 text-gray-400 text-sm">${new Date(
                  user.created_at
                ).toLocaleDateString()}</td>
                <td class="px-4 py-4">
                    <a 
                        href="role-management.php" 
                        class="px-3 py-1.5 bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-400 text-sm font-semibold rounded-lg border border-cyan-500/50 transition-colors inline-flex items-center"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Manage Roles
                    </a>
                </td>
            </tr>
        `;
  });

  html += "</tbody></table></div>";
  container.innerHTML = html;
}

/**
 * Get role badge color
 */
function getRoleBadgeColor(roleName) {
  const colors = {
    Admin: "bg-red-500/20 text-red-400 border border-red-500/50",
    Organizer: "bg-yellow-500/20 text-yellow-400 border border-yellow-500/50",
    Player: "bg-cyan-500/20 text-cyan-400 border border-cyan-500/50",
  };
  return (
    colors[roleName] || "bg-gray-500/20 text-gray-400 border border-gray-500/50"
  );
}

/**
 * Filter users by search input
 */
function setupUserSearch() {
  const searchInput = document.getElementById("user-search");
  searchInput.addEventListener("input", (e) => {
    currentSearchTerm = e.target.value.toLowerCase();
    const filteredUsers = allUsers.filter(
      (user) =>
        user.username.toLowerCase().includes(currentSearchTerm) ||
        user.email.toLowerCase().includes(currentSearchTerm)
    );
    displayUsers(filteredUsers);
  });
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

  // Load users
  await loadUsers();

  // Setup search
  setupUserSearch();

  // Setup print button
  setupPrintButton();
}

/**
 * Setup print button functionality
 */
function setupPrintButton() {
  const printBtn = document.getElementById("print-users-btn");
  if (printBtn) {
    printBtn.addEventListener("click", () => {
      printUserManagement(allUsers, { searchTerm: currentSearchTerm });
    });
  }
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", init);
