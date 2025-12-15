// Player Profile JavaScript
console.log("Profile.js loaded!");
import { getCurrentUser, logout, getToken } from "./core/auth.js";
import { getPagePath } from "./pathHelper.js";
import { displayUserRoleBadges } from "./roleUtils.js";

/**
 * Get the base path for API calls
 */
function getApiBasePath() {
  const pathname = window.location.pathname;
  const frontendIndex = pathname.indexOf("/frontend/");
  if (frontendIndex !== -1) {
    const basePath = pathname.substring(0, frontendIndex);
    return basePath + "/backend/api/";
  }
  return "/backend/api/";
}

const API_BASE_PATH = getApiBasePath();
const PLAYER_STATS_API_URL = API_BASE_PATH + "player_stats_api.php";

console.log("API_BASE_PATH:", API_BASE_PATH);
console.log("PLAYER_STATS_API_URL:", PLAYER_STATS_API_URL);

/**
 * Fetch player statistics from backend
 */
async function fetchPlayerStats() {
  try {
    console.log("Fetching stats from:", PLAYER_STATS_API_URL + "?action=stats");

    const token = getToken();
    const headers = {
      "Content-Type": "application/json",
    };

    if (token) {
      headers["Authorization"] = `Bearer ${token}`;
    }

    const response = await fetch(PLAYER_STATS_API_URL + "?action=stats", {
      method: "GET",
      credentials: "include",
      headers: headers,
    });

    console.log("Stats response status:", response.status);

    if (!response.ok) {
      const errorData = await response.json();
      console.error("API Error Details:", errorData);
      throw new Error(
        errorData.message || `HTTP error! status: ${response.status}`
      );
    }

    const data = await response.json();
    console.log("Stats data:", data);

    if (data.success) {
      return data.stats;
    } else {
      throw new Error(data.message || "Failed to fetch player stats");
    }
  } catch (error) {
    console.error("Error fetching player stats:", error);
    return null;
  }
}

/**
 * Fetch match history from backend
 */
async function fetchMatchHistory(limit = 20) {
  try {
    console.log(
      "Fetching match history from:",
      `${PLAYER_STATS_API_URL}?action=match_history&limit=${limit}`
    );

    const token = getToken();
    const headers = {
      "Content-Type": "application/json",
    };

    if (token) {
      headers["Authorization"] = `Bearer ${token}`;
    }

    const response = await fetch(
      `${PLAYER_STATS_API_URL}?action=match_history&limit=${limit}`,
      {
        method: "GET",
        credentials: "include",
        headers: headers,
      }
    );

    console.log("Match history response status:", response.status);

    if (!response.ok) {
      const errorData = await response.json();
      console.error("Match History API Error Details:", errorData);
      throw new Error(
        errorData.message || `HTTP error! status: ${response.status}`
      );
    }

    const data = await response.json();
    console.log("Match history data:", data);

    if (data.success) {
      return data.matches;
    } else {
      throw new Error(data.message || "Failed to fetch match history");
    }
  } catch (error) {
    console.error("Error fetching match history:", error);
    return [];
  }
}

/**
 * Fetch player achievements from backend
 */
async function fetchAchievements() {
  try {
    console.log(
      "Fetching achievements from:",
      PLAYER_STATS_API_URL + "?action=achievements"
    );

    const token = getToken();
    const headers = {
      "Content-Type": "application/json",
    };

    if (token) {
      headers["Authorization"] = `Bearer ${token}`;
    }

    const response = await fetch(
      PLAYER_STATS_API_URL + "?action=achievements",
      {
        method: "GET",
        credentials: "include",
        headers: headers,
      }
    );

    console.log("Achievements response status:", response.status);

    if (!response.ok) {
      const errorData = await response.json();
      console.error("Achievements API Error Details:", errorData);
      throw new Error(
        errorData.message || `HTTP error! status: ${response.status}`
      );
    }

    const data = await response.json();
    console.log("Achievements data:", data);

    if (data.success) {
      return data.achievements;
    } else {
      throw new Error(data.message || "Failed to fetch achievements");
    }
  } catch (error) {
    console.error("Error fetching achievements:", error);
    return [];
  }
}

/**
 * Display player statistics
 */
function displayStatistics(stats) {
  if (!stats) {
    console.error("No stats to display");
    return;
  }

  console.log("Displaying statistics:", stats);

  // Update win rate
  const winRateElement = document.getElementById("stat-win-rate");
  console.log("Win rate element:", winRateElement);
  if (winRateElement) {
    winRateElement.textContent = `${stats.win_rate}%`;
  } else {
    console.warn("Win rate element not found");
  }

  const winRateBar = document.getElementById("stat-win-rate-bar");
  if (winRateBar) {
    winRateBar.style.width = `${stats.win_rate}%`;
  } else {
    console.warn("Win rate bar not found");
  }

  // Update total matches
  const matchesElement = document.getElementById("stat-total-matches");
  if (matchesElement) {
    matchesElement.textContent = stats.total_matches;
  }

  // Update championships
  const championshipsElement = document.getElementById("stat-championships");
  if (championshipsElement) {
    championshipsElement.textContent = stats.championships;
  }

  const champBar = document.getElementById("stat-championships-bar");
  if (champBar) {
    // Adjust bar width based on championships (scale to look good)
    const barWidth = Math.min(100, (stats.championships / 10) * 100);
    champBar.style.width = `${barWidth}%`;
  }

  // Update header stats
  updateHeaderStats(stats);
}

/**
 * Update header stats display
 */
function updateHeaderStats(stats) {
  // Update wins in header
  const winsElement = document.querySelector(
    ".flex.items-center.space-x-4 > div:nth-child(1)"
  );
  if (winsElement) {
    const winsText = winsElement.querySelector("div:nth-child(2)");
    if (winsText) {
      winsText.textContent = `${stats.wins} Wins`;
    }
  }

  // Update tournaments in header
  const tournamentsElement = document.querySelector(
    ".flex.items-center.space-x-4 > div:nth-child(2)"
  );
  if (tournamentsElement) {
    const tournamentsText =
      tournamentsElement.querySelector("div:nth-child(2)");
    if (tournamentsText) {
      tournamentsText.textContent = `${stats.total_tournaments} Tournaments`;
    }
  }

  // Update level in header
  const levelElement = document.querySelector(
    ".flex.items-center.space-x-4 > div:nth-child(3)"
  );
  if (levelElement) {
    const levelText = levelElement.querySelector("div:nth-child(2)");
    if (levelText) {
      levelText.textContent = `Level ${stats.level}`;
    }
  }
}

/**
 * Display match history
 */
function displayMatchHistory(matches) {
  const container = document.getElementById("match-history-container");

  if (!container) {
    console.error("Match history container not found");
    return;
  }

  console.log("Displaying match history, count:", matches?.length);

  if (!matches || matches.length === 0) {
    container.innerHTML = `
            <div class="text-center text-gray-400 py-12">
                <svg class="w-16 h-16 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-lg">No matches played yet</p>
                <p class="text-sm text-gray-500 mt-2">Join a tournament to start your journey!</p>
            </div>
        `;
    return;
  }

  let html = "";
  matches.forEach((match) => {
    const isWin = match.result === "win";
    const borderColor = isWin ? "border-green-500/30" : "border-red-500/30";
    const bgColor = isWin ? "bg-green-500/20" : "bg-red-500/20";
    const textColor = isWin ? "text-green-400" : "text-red-400";
    const iconColor = isWin ? "text-green-400" : "text-red-400";
    const resultText = isWin ? "WIN" : "LOSS";

    const icon = isWin
      ? `<svg class="w-6 h-6 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
               </svg>`
      : `<svg class="w-6 h-6 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
               </svg>`;

    const timeAgo = getTimeAgo(match.end_time);
    const opponentName = match.opponent_name || "Unknown Opponent";
    const matchTitle = isWin
      ? `Victory vs ${opponentName}`
      : `Defeat vs ${opponentName}`;
    const tournamentInfo = `${match.tournament_name} - ${match.round_name}`;

    html += `
            <div class="flex items-center justify-between p-4 bg-gray-900 rounded-xl border ${borderColor}">
                <div class="flex items-center space-x-4">
                    <div class="p-2 ${bgColor} rounded-lg">
                        ${icon}
                    </div>
                    <div>
                        <p class="text-white font-semibold">${escapeHtml(
                          matchTitle
                        )}</p>
                        <p class="text-sm text-gray-400">${escapeHtml(
                          tournamentInfo
                        )}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="${textColor} font-bold">${resultText}</p>
                    <p class="text-xs text-gray-400">${timeAgo}</p>
                </div>
            </div>
        `;
  });

  container.innerHTML = html;
}

/**
 * Display achievements
 */
function displayAchievements(achievements) {
  const container = document.getElementById("achievements-container");

  if (!container) {
    console.error("Achievements container not found");
    return;
  }

  console.log("Displaying achievements, count:", achievements?.length);

  if (!achievements || achievements.length === 0) {
    container.innerHTML = `
            <div class="text-center text-gray-400 py-6">
                <svg class="w-12 h-12 mx-auto mb-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
                <p class="text-sm">No achievements yet</p>
            </div>
        `;
    return;
  }

  let html = "";
  achievements.slice(0, 5).forEach((achievement) => {
    const borderColor = `border-${achievement.badge_color.replace(
      "-400",
      "-500"
    )}/30`;

    html += `
            <div class="flex items-center space-x-3 p-3 bg-gray-900 rounded-lg border ${borderColor}">
                <svg class="w-8 h-8 text-${
                  achievement.badge_color
                }" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <div>
                    <p class="text-white font-semibold text-sm">${escapeHtml(
                      achievement.title
                    )}</p>
                    <p class="text-gray-400 text-xs">${escapeHtml(
                      achievement.description
                    )}</p>
                </div>
            </div>
        `;
  });

  container.innerHTML = html;
}

/**
 * Get time ago string
 */
function getTimeAgo(dateString) {
  if (!dateString) return "Recently";

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
  return String(unsafe)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

/**
 * Initialize profile page
 */
async function init() {
  console.log("Init function called!");

  // Get current user
  const user = getCurrentUser();
  console.log("Current user:", user);

  if (!user) {
    console.log("No user found, redirecting to login");
    window.location.href = getPagePath("auth/login.php");
    return;
  }

  console.log("User found, proceeding with profile setup");

  // Display user info
  const usernameElement = document.getElementById("profile-username");
  const emailElement = document.getElementById("profile-email");

  if (usernameElement) {
    usernameElement.textContent = user.username;
  }
  if (emailElement) {
    emailElement.textContent = user.email;
  }

  // Display user role badges
  const badgeContainer = document.getElementById("user-role-badges");
  if (badgeContainer) {
    displayUserRoleBadges(badgeContainer);
  }

  // Setup logout button
  const logoutBtn = document.getElementById("logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", async () => {
      await logout();
      window.location.href = getPagePath("auth/login.php");
    });
  }

  // Fetch and display data
  try {
    console.log("Fetching player data from:", PLAYER_STATS_API_URL);

    const [stats, matches, achievements] = await Promise.all([
      fetchPlayerStats(),
      fetchMatchHistory(10),
      fetchAchievements(),
    ]);

    console.log("Fetched stats:", stats);
    console.log("Fetched matches:", matches);
    console.log("Fetched achievements:", achievements);

    if (stats) {
      displayStatistics(stats);
    } else {
      console.warn("No stats data received");
    }

    displayMatchHistory(matches);
    displayAchievements(achievements);
  } catch (error) {
    console.error("Error loading profile data:", error);
  }
}

// Initialize on page load
// Handle both direct page load and AJAX-loaded content
if (document.readyState === "loading") {
  // Still loading, wait for DOMContentLoaded
  document.addEventListener("DOMContentLoaded", init);
} else {
  // DOM already loaded (e.g., via AJAX), run immediately
  console.log("DOM already loaded, running init immediately");
  init();
}
