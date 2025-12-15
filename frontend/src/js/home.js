// Import authentication module
import * as Auth from "./core/auth.js";
import { getViewPath } from "./pathHelper.js";

// Expose Auth module globally for AJAX-loaded pages
window.Auth = Auth;

// DOM Elements
let homeContent;
let currentSection = "dashboard";
let currentTournamentId = null; // Store current tournament ID for details page

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  homeContent = document.getElementById("home-content");

  // Check if user is logged in
  const user = Auth.getCurrentUser();
  if (!user) {
    // Redirect to login if not authenticated
    window.location.href = "layout.php";
    return;
  }

  // Display username in nav
  const usernameEl = document.getElementById("nav-username");
  if (usernameEl) {
    usernameEl.textContent = `Hello, ${user.username}`;
  }

  // Set up navigation listeners
  document
    .getElementById("nav-dashboard")
    ?.addEventListener("click", () => loadSection("dashboard"));
  document
    .getElementById("nav-tournaments")
    ?.addEventListener("click", () => loadSection("tournaments"));
  document
    .getElementById("nav-my-tournaments")
    ?.addEventListener("click", () => loadSection("my-tournaments"));
  document
    .getElementById("nav-team-management")
    ?.addEventListener("click", () => loadSection("team-management"));
  document
    .getElementById("nav-manage-tournaments")
    ?.addEventListener("click", () => loadSection("manage-tournaments"));
  document
    .getElementById("nav-profile")
    ?.addEventListener("click", () => loadSection("profile"));

  // Set up logout buttons
  document
    .getElementById("sidebar-logout-btn")
    ?.addEventListener("click", handleLogout);

  // Set up notification bell
  setupNotificationCenter();

  // Apply role-based visibility
  applyRoleBasedVisibility();

  // Load dashboard by default
  loadSection("dashboard");
});

// Load section dynamically using AJAX
function loadSection(sectionName) {
  // Check permissions for restricted sections
  if (sectionName === "manage-tournaments") {
    const user = Auth.getCurrentUser();
    const isOrganizer = Auth.isOrganizer();
    const isAdmin = Auth.isAdmin();

    if (!isOrganizer && !isAdmin) {
      homeContent.innerHTML = `
        <div class="max-w-2xl mx-auto px-4 py-16">
          <div class="bg-red-900/20 border border-red-500/50 rounded-2xl p-8 text-center">
            <svg class="w-20 h-20 mx-auto mb-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <h2 class="text-2xl font-bold text-white mb-4">Access Restricted</h2>
            <p class="text-gray-300 mb-6">
              This page is only accessible to users with Organizer or Admin roles.
            </p>
            <p class="text-gray-400 mb-8">
              You currently have the following roles: <strong class="text-cyan-400">${
                user?.roles?.map((r) => r.role_name).join(", ") || "None"
              }</strong>
            </p>
            <button onclick="loadSection('profile')" class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all">
              Request Organizer Role
            </button>
          </div>
        </div>
      `;
      currentSection = sectionName;
      updateActiveNav(sectionName);
      return;
    }
  }

  const sectionPath = `${sectionName}.php`;

  fetch(sectionPath)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Section not found");
      }
      return response.text();
    })
    .then((html) => {
      homeContent.innerHTML = html;
      currentSection = sectionName;

      // Execute any script tags in the loaded HTML
      const scripts = homeContent.querySelectorAll("script");
      scripts.forEach((oldScript) => {
        const newScript = document.createElement("script");
        if (oldScript.src) {
          newScript.src = oldScript.src;
        } else {
          newScript.textContent = oldScript.textContent;
        }
        // Copy all attributes
        Array.from(oldScript.attributes).forEach((attr) => {
          newScript.setAttribute(attr.name, attr.value);
        });
        oldScript.parentNode.replaceChild(newScript, oldScript);
      });

      // Update active nav button
      updateActiveNav(sectionName);

      // Setup section-specific functionality
      if (sectionName === "dashboard") {
        setupDashboard();
      } else if (sectionName === "profile") {
        setupProfile();
      } else if (sectionName === "tournaments") {
        setupTournaments();
      } else if (sectionName === "my-tournaments") {
        setTimeout(() => setupMyTournaments(), 10);
      } else if (sectionName === "team-management") {
        setTimeout(() => setupTeamManagement(), 10);
      } else if (sectionName === "manage-tournaments") {
        setTimeout(() => setupManageTournaments(), 10);
      } else if (sectionName === "tournament-bracket") {
        setTimeout(() => setupTournamentBracket(), 10);
      } else if (sectionName === "tournament-details") {
        setTimeout(() => setupTournamentDetails(), 10);
      }
    })
    .catch((error) => {
      console.error("Error loading section:", error);
      homeContent.innerHTML = `
                <div class="text-center text-red-400 p-12">
                    <p class="text-xl">Error loading ${sectionName} section</p>
                </div>
            `;
    });
}

// Apply role-based visibility to navigation items
function applyRoleBasedVisibility() {
  const user = Auth.getCurrentUser();
  if (!user || !user.roles) return;

  const userRoles = user.roles.map((r) => r.role_name);

  // Find all elements with data-roles attribute
  document.querySelectorAll("[data-roles]").forEach((element) => {
    const requiredRoles = element
      .getAttribute("data-roles")
      .split(",")
      .map((r) => r.trim());
    const hasRequiredRole = requiredRoles.some((role) =>
      userRoles.includes(role)
    );

    if (hasRequiredRole) {
      element.classList.remove("hidden");
    } else {
      element.classList.add("hidden");
    }
  });
}

// Update active navigation button
function updateActiveNav(sectionName) {
  // Remove active class from all nav buttons
  const navButtons = [
    "nav-dashboard",
    "nav-tournaments",
    "nav-my-tournaments",
    "nav-team-management",
    "nav-manage-tournaments",
    "nav-profile",
  ];
  navButtons.forEach((id) => {
    const btn = document.getElementById(id);
    if (btn) {
      btn.className =
        "w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-gray-700 transition-all";
    }
  });

  // Add active class to current section
  const activeBtn = document.getElementById(`nav-${sectionName}`);
  if (activeBtn) {
    activeBtn.className =
      "w-full flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-purple-600 text-white font-semibold shadow-lg shadow-cyan-500/30 transition-all";
  }
}

// Setup dashboard functionality
function setupDashboard() {
  const user = Auth.getCurrentUser();
  const usernameEl = document.getElementById("dashboard-username");
  if (usernameEl && user) {
    usernameEl.textContent = user.username;
  }
}

// Setup profile functionality
function setupProfile() {
  const user = Auth.getCurrentUser();

  // Fill in user information
  const usernameEl = document.getElementById("profile-username");
  const emailEl = document.getElementById("profile-email");

  if (usernameEl && user) {
    usernameEl.textContent = user.username;
  }
  if (emailEl && user) {
    emailEl.textContent = user.email;
  }

  // Setup logout button in profile
  const logoutBtn = document.getElementById("logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", handleLogout);
  }

  // Show organizer request section if user is not an organizer or admin
  const organizerSection = document.getElementById("organizer-request-section");
  if (organizerSection && !Auth.isOrganizer() && !Auth.isAdmin()) {
    organizerSection.style.display = "";

    // Setup request organizer button
    const requestBtn = document.getElementById("request-organizer-btn");
    if (requestBtn) {
      requestBtn.addEventListener("click", async () => {
        const reasonEl = document.getElementById("organizer-reason");
        const reason = reasonEl?.value.trim();

        if (!reason) {
          showNotification("Please provide a reason for your request", "error");
          return;
        }

        try {
          requestBtn.disabled = true;
          requestBtn.innerHTML = `
            <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Submitting...
          `;

          const result = await Auth.requestOrganizerRole(reason);

          if (result.success) {
            showNotification(
              "Your request has been submitted successfully! An admin will review it soon.",
              "success"
            );
            organizerSection.style.display = "none";
          } else {
            showNotification(
              "Failed to submit request: " + result.message,
              "error"
            );
            requestBtn.disabled = false;
            requestBtn.innerHTML = `
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
              </svg>
              Submit Request
            `;
          }
        } catch (error) {
          showNotification(
            "Error submitting request: " + error.message,
            "error"
          );
          requestBtn.disabled = false;
          requestBtn.innerHTML = `
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            Submit Request
          `;
        }
      });
    }
  }
}

// Show notification
function showNotification(message, type = "info") {
  const typeColors = {
    success: "bg-green-500/10 border-green-500/50 text-green-400",
    error: "bg-red-500/10 border-red-500/50 text-red-400",
    info: "bg-cyan-500/10 border-cyan-500/50 text-cyan-400",
  };

  const notification = document.createElement("div");
  notification.className = `fixed top-4 right-4 z-50 ${typeColors[type]} border-2 px-6 py-4 rounded-xl shadow-lg backdrop-blur-sm max-w-md flex items-center justify-between`;
  notification.innerHTML = `
    <span class="flex items-center">
      <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <span>${message}</span>
    </span>
    <button type="button" class="ml-4 text-current hover:opacity-75 transition-opacity">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
  `;

  // Add to body
  document.body.appendChild(notification);

  // Setup close button
  const closeBtn = notification.querySelector("button");
  closeBtn.addEventListener("click", () => {
    notification.style.opacity = "0";
    notification.style.transition = "opacity 300ms";
    setTimeout(() => notification.remove(), 300);
  });

  // Auto-dismiss after 5 seconds
  setTimeout(() => {
    notification.style.opacity = "0";
    notification.style.transition = "opacity 300ms";
    setTimeout(() => notification.remove(), 300);
  }, 5000);
}

// Handle logout
async function handleLogout() {
  await Auth.logout();
  window.location.href = getViewPath("layout.php");
}

// Global function to join a tournament
window.joinTournament = async function (
  tournamentId,
  tournamentName,
  isTeamBased
) {
  if (!confirm(`Do you want to join "${tournamentName}"?`)) {
    return;
  }

  // If team-based, prompt for team selection/creation
  if (isTeamBased == 1 || isTeamBased === true) {
    // TODO: Show team selection modal
    alert(
      "This is a team-based tournament. Team selection will be implemented."
    );
    return;
  }

  try {
    const token = localStorage.getItem("auth_token");
    const headers = {
      "Content-Type": "application/json",
    };

    if (token) {
      headers["Authorization"] = `Bearer ${token}`;
    }

    if (typeof window.TournamentAPI === "undefined") {
      window.TournamentAPI = {
        baseURL:
          "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
      };
    }

    const response = await fetch(window.TournamentAPI.baseURL, {
      method: "POST",
      headers: headers,
      credentials: "include",
      body: JSON.stringify({
        action: "register",
        tournament_id: tournamentId,
      }),
    });

    const data = await response.json();

    if (data.success) {
      alert("Successfully registered for tournament!");
      // Reload the tournaments section to update the UI
      loadSection("tournaments");
    } else {
      alert("Error: " + (data.message || "Failed to register for tournament"));
    }
  } catch (error) {
    console.error("Error joining tournament:", error);
    alert("Error joining tournament. Please try again.");
  }
};

// Setup tournaments section
function setupTournaments() {
  console.log("Setting up tournaments section...");
  // The tournaments.php file has its own initialization with TournamentUI
  // Just ensure TournamentAPI is available
  if (typeof window.TournamentAPI === "undefined") {
    console.log("TournamentAPI not loaded yet");
  }
}

// Global functions for tournament actions (must be defined before setupMyTournaments)
window.viewTournamentDetails = function (tournamentId) {
  currentTournamentId = tournamentId;
  loadSection("tournament-details");
};

window.filterMyTournaments = function (filter) {
  // This will be overridden in setupMyTournaments with the proper closure
  console.log("Filter function called before setup");
};

window.withdrawFromTournament = async function (tournamentId, tournamentName) {
  if (
    !confirm(
      `Are you sure you want to withdraw from "${tournamentName}"?\n\nThis action cannot be undone.`
    )
  ) {
    return;
  }

  try {
    const token = localStorage.getItem("auth_token");
    const headers = {
      "Content-Type": "application/json",
    };

    if (token) {
      headers["Authorization"] = `Bearer ${token}`;
    }

    if (typeof window.TournamentAPI === "undefined") {
      window.TournamentAPI = {
        baseURL:
          "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
      };
    }

    const response = await fetch(window.TournamentAPI.baseURL, {
      method: "POST",
      headers: headers,
      credentials: "include",
      body: JSON.stringify({
        action: "withdraw",
        tournament_id: tournamentId,
      }),
    });

    const data = await response.json();

    if (data.success) {
      alert("Successfully withdrawn from tournament");
      // Reload the my tournaments section
      loadSection("my-tournaments");
    } else {
      alert("Error: " + (data.message || "Failed to withdraw from tournament"));
    }
  } catch (error) {
    console.error("Error withdrawing from tournament:", error);
    alert("Error withdrawing from tournament. Please try again.");
  }
};

// Setup my tournaments section
function setupMyTournaments() {
  console.log("Setting up my tournaments section...");

  // Ensure TournamentAPI is available
  if (typeof window.TournamentAPI === "undefined") {
    window.TournamentAPI = {
      baseURL:
        "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
    };
  }

  let myTournamentsData = [];
  let currentFilter = "all";

  // Initialize and load tournaments immediately
  loadMyTournaments();

  async function loadMyTournaments() {
    console.log("loadMyTournaments called");
    const loadingState = document.getElementById("loadingState");
    const emptyState = document.getElementById("emptyState");
    const grid = document.getElementById("tournamentsGrid");

    console.log("Elements found:", { loadingState, emptyState, grid });

    if (!loadingState || !emptyState || !grid) {
      console.error("Required elements not found!");
      return;
    }

    loadingState.classList.remove("hidden");
    emptyState.classList.add("hidden");
    grid.classList.add("hidden");

    console.log(
      "Fetching from:",
      window.TournamentAPI.baseURL + "?action=my-tournaments"
    );

    try {
      // Get auth token from localStorage
      const token = localStorage.getItem("auth_token");
      const headers = {
        "Content-Type": "application/json",
      };

      if (token) {
        headers["Authorization"] = `Bearer ${token}`;
      }

      const response = await fetch(
        window.TournamentAPI.baseURL + "?action=my-tournaments",
        {
          credentials: "include",
          headers: headers,
        }
      );

      console.log("Response status:", response.status);

      if (!response.ok) {
        throw new Error("Failed to load tournaments");
      }

      const data = await response.json();
      console.log("API Response:", data);

      if (data.success) {
        myTournamentsData = data.tournaments || [];
        console.log("Tournaments loaded:", myTournamentsData.length);
        renderMyTournaments();
        loadNotifications();
      } else {
        throw new Error(data.message || "Failed to load tournaments");
      }
    } catch (error) {
      console.error("Error loading tournaments:", error);
      loadingState.classList.add("hidden");
      emptyState.classList.remove("hidden");
      const errorP = emptyState.querySelector("p");
      if (errorP) {
        errorP.textContent = "Error loading tournaments. Please try again.";
      }
    }
  }

  function renderMyTournaments() {
    const loadingState = document.getElementById("loadingState");
    const emptyState = document.getElementById("emptyState");
    const grid = document.getElementById("tournamentsGrid");

    loadingState.classList.add("hidden");

    let filteredTournaments = myTournamentsData;

    console.log(
      "Rendering tournaments. Total:",
      myTournamentsData.length,
      "Current filter:",
      currentFilter
    );

    // Apply filter
    if (currentFilter !== "all") {
      filteredTournaments = myTournamentsData.filter((t) => {
        if (currentFilter === "upcoming")
          return t.status === "open" || t.status === "registration_closed";
        if (currentFilter === "ongoing") return t.status === "ongoing";
        if (currentFilter === "completed") return t.status === "completed";
        return true;
      });
      console.log("After filtering:", filteredTournaments.length);
    }

    if (filteredTournaments.length === 0) {
      console.log("No tournaments to display - showing empty state");
      emptyState.classList.remove("hidden");
      grid.classList.add("hidden");
    } else {
      console.log("Displaying", filteredTournaments.length, "tournaments");
      emptyState.classList.add("hidden");
      grid.classList.remove("hidden");
      grid.innerHTML = filteredTournaments
        .map((tournament) => createMyTournamentCard(tournament))
        .join("");
    }
  }

  function createMyTournamentCard(tournament) {
    const statusColors = {
      draft: "bg-gray-100 text-gray-800",
      open: "bg-green-100 text-green-800",
      registration_closed: "bg-yellow-100 text-yellow-800",
      ongoing: "bg-blue-100 text-blue-800",
      completed: "bg-purple-100 text-purple-800",
      cancelled: "bg-red-100 text-red-800",
    };

    const canWithdraw =
      tournament.status === "open" ||
      tournament.status === "registration_closed";
    const isWithdrawn = tournament.registration_status === "withdrawn";

    return `
      <div class="bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border border-gray-700">
        <div class="flex justify-between items-start mb-4">
          <h3 class="text-xl font-semibold text-white">${tournament.name}</h3>
          <span class="px-2 py-1 text-xs font-semibold rounded ${
            statusColors[tournament.status] || "bg-gray-100 text-gray-800"
          }">
            ${tournament.status.replace("_", " ").toUpperCase()}
          </span>
        </div>

        <div class="space-y-2 text-sm text-gray-400 mb-4">
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>Starts: ${new Date(
              tournament.start_date
            ).toLocaleDateString()}</span>
          </div>
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span>${tournament.registered_participants || 0} / ${
      tournament.max_participants || tournament.tournament_size
    } Participants</span>
          </div>
          ${
            tournament.team_name
              ? `
          <div class="flex items-center text-cyan-400">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span>Team: ${tournament.team_name}</span>
          </div>
          `
              : ""
          }
          ${
            isWithdrawn
              ? `
          <div class="flex items-center text-red-400 font-semibold">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>Withdrawn</span>
          </div>
          `
              : ""
          }
        </div>

        <div class="flex gap-2">
          <button onclick="viewTournamentDetails(${
            tournament.id
          })" class="flex-1 px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700 transition-colors text-sm">
            View Details
          </button>
          ${
            !isWithdrawn && canWithdraw
              ? `
          <button onclick="withdrawFromTournament(${tournament.id}, '${tournament.name}')" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors text-sm">
            Withdraw
          </button>
          `
              : ""
          }
        </div>
      </div>
    `;
  }

  // Override filterMyTournaments with proper closure access
  window.filterMyTournaments = function (filter) {
    currentFilter = filter;

    // Update tab styles
    document.querySelectorAll(".tournament-filter-tab").forEach((tab) => {
      tab.classList.remove("active", "border-blue-500", "text-blue-600");
      tab.classList.add("border-transparent", "text-gray-500");
    });

    event.target.classList.remove("border-transparent", "text-gray-500");
    event.target.classList.add("active", "border-blue-500", "text-blue-600");

    renderMyTournaments();
  };

  async function loadNotifications() {
    try {
      const token = localStorage.getItem("auth_token");
      const headers = {
        "Content-Type": "application/json",
      };

      if (token) {
        headers["Authorization"] = `Bearer ${token}`;
      }

      const response = await fetch(
        window.TournamentAPI.baseURL + "?action=notifications",
        {
          credentials: "include",
          headers: headers,
        }
      );

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.notifications) {
          const unreadCount = data.notifications.filter(
            (n) => !n.is_read
          ).length;
          if (unreadCount > 0) {
            const badge = document.getElementById("notificationBadge");
            const count = document.getElementById("notificationCount");
            if (badge && count) {
              badge.classList.remove("hidden");
              count.textContent = unreadCount;
            }
          }
        }
      }
    } catch (error) {
      console.error("Error loading notifications:", error);
    }
  }
}

// Setup tournament details section
function setupTournamentDetails() {
  console.log(
    "Setting up tournament details section for ID:",
    currentTournamentId
  );

  if (!currentTournamentId) {
    console.error("No tournament ID specified");
    const container = document.getElementById("tournamentDetailsContainer");
    if (container) {
      container.innerHTML = `
        <div class="text-center text-red-400 p-12">
          <p class="text-xl">No tournament specified</p>
        </div>
      `;
    }
    return;
  }

  // Ensure TournamentAPI is available
  if (typeof window.TournamentAPI === "undefined") {
    window.TournamentAPI = {
      baseURL:
        "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
    };
  }

  loadTournamentDetails(currentTournamentId);

  async function loadTournamentDetails(tournamentId) {
    const loadingState = document.getElementById("loadingDetailsState");
    const contentDiv = document.getElementById("tournamentDetailsContent");

    console.log("Loading tournament details for ID:", tournamentId);

    if (loadingState) loadingState.classList.remove("hidden");
    if (contentDiv) contentDiv.classList.add("hidden");

    try {
      const token = localStorage.getItem("auth_token");
      const headers = {
        "Content-Type": "application/json",
      };

      if (token) {
        headers["Authorization"] = `Bearer ${token}`;
      }

      const response = await fetch(
        window.TournamentAPI.baseURL + `?action=tournament&id=${tournamentId}`,
        {
          credentials: "include",
          headers: headers,
        }
      );

      console.log("Tournament details response status:", response.status);

      if (!response.ok) {
        throw new Error("Failed to load tournament details");
      }

      const data = await response.json();
      console.log("Tournament details data:", data);

      if (data.success && data.tournament) {
        renderTournamentDetails(data.tournament);
      } else {
        throw new Error(data.message || "Failed to load tournament details");
      }
    } catch (error) {
      console.error("Error loading tournament details:", error);
      if (loadingState) loadingState.classList.add("hidden");
      if (contentDiv) {
        contentDiv.classList.remove("hidden");
        contentDiv.innerHTML = `
          <div class="text-center text-red-400 p-12">
            <p class="text-xl">Error loading tournament details</p>
            <p class="text-sm mt-2">${error.message}</p>
          </div>
        `;
      }
    }
  }

  function renderTournamentDetails(tournament) {
    const loadingState = document.getElementById("loadingDetailsState");
    const contentDiv = document.getElementById("tournamentDetailsContent");

    if (loadingState) loadingState.classList.add("hidden");
    if (!contentDiv) return;

    contentDiv.classList.remove("hidden");

    // Render basic tournament details
    contentDiv.innerHTML = `
      <div class="space-y-6">
        <div class="bg-gray-800 rounded-2xl border border-cyan-500/30 p-6">
          <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400 mb-4">
            ${tournament.name}
          </h1>
          <div class="flex items-center space-x-4 text-gray-400">
            <span class="px-3 py-1 bg-cyan-500/20 text-cyan-400 rounded-lg">
              ${tournament.status.toUpperCase()}
            </span>
            <span>${tournament.format}</span>
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
          <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <h3 class="text-lg font-bold text-white mb-4">Tournament Info</h3>
            <div class="space-y-3 text-gray-400">
              <div class="flex justify-between">
                <span>Start Date:</span>
                <span class="text-white">${new Date(
                  tournament.start_date
                ).toLocaleString()}</span>
              </div>
              <div class="flex justify-between">
                <span>End Date:</span>
                <span class="text-white">${new Date(
                  tournament.end_date
                ).toLocaleString()}</span>
              </div>
              <div class="flex justify-between">
                <span>Participants:</span>
                <span class="text-white">${
                  tournament.participants_count || 0
                } / ${tournament.max_participants}</span>
              </div>
            </div>
          </div>

          <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <h3 class="text-lg font-bold text-white mb-4">Description</h3>
            <p class="text-gray-400">${
              tournament.description || "No description available."
            }</p>
          </div>
        </div>
      </div>
    `;
  }
}

// Setup team management section
function setupTeamManagement() {
  console.log("Setting up team management section...");

  // Ensure TournamentAPI is available
  if (typeof window.TournamentAPI === "undefined") {
    window.TournamentAPI = {
      baseURL:
        "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
    };
  }

  let currentTeamId = null;

  // Global functions for team management
  window.openAddMemberModal = function (teamId) {
    currentTeamId = teamId;
    document.getElementById("addMemberModal").classList.remove("hidden");
    document.getElementById("newMemberUsername").value = "";
  };

  window.closeAddMemberModal = function () {
    document.getElementById("addMemberModal").classList.add("hidden");
    currentTeamId = null;
  };

  window.confirmAddMember = async function () {
    const username = document.getElementById("newMemberUsername").value.trim();

    if (!username) {
      alert("Please enter a username");
      return;
    }

    try {
      const token = localStorage.getItem("auth_token");
      const headers = {
        "Content-Type": "application/json",
      };

      if (token) {
        headers["Authorization"] = `Bearer ${token}`;
      }

      const response = await fetch(window.TournamentAPI.baseURL, {
        method: "POST",
        headers: headers,
        credentials: "include",
        body: JSON.stringify({
          action: "add-team-member",
          team_id: currentTeamId,
          username: username,
        }),
      });

      const data = await response.json();

      if (data.success) {
        alert("Member added successfully");
        window.closeAddMemberModal();
        loadSection("team-management");
      } else {
        alert("Error: " + (data.message || "Failed to add member"));
      }
    } catch (error) {
      console.error("Error adding member:", error);
      alert("Error adding member. Please try again.");
    }
  };

  window.removeMember = async function (teamId, memberId, username) {
    if (
      !confirm(`Are you sure you want to remove ${username} from the team?`)
    ) {
      return;
    }

    try {
      const token = localStorage.getItem("auth_token");
      const headers = {
        "Content-Type": "application/json",
      };

      if (token) {
        headers["Authorization"] = `Bearer ${token}`;
      }

      const response = await fetch(window.TournamentAPI.baseURL, {
        method: "POST",
        headers: headers,
        credentials: "include",
        body: JSON.stringify({
          action: "remove-team-member",
          team_id: teamId,
          member_id: memberId,
        }),
      });

      const data = await response.json();

      if (data.success) {
        alert("Member removed successfully");
        loadSection("team-management");
      } else {
        alert("Error: " + (data.message || "Failed to remove member"));
      }
    } catch (error) {
      console.error("Error removing member:", error);
      alert("Error removing member. Please try again.");
    }
  };

  // Load teams immediately
  loadMyTeams();

  async function loadMyTeams() {
    const loadingState = document.getElementById("loadingState");
    const emptyState = document.getElementById("emptyState");
    const container = document.getElementById("teamsContainer");

    if (!loadingState || !emptyState || !container) {
      console.error("Required elements not found");
      return;
    }

    loadingState.classList.remove("hidden");
    emptyState.classList.add("hidden");
    container.classList.add("hidden");

    try {
      const token = localStorage.getItem("auth_token");
      const headers = {
        "Content-Type": "application/json",
      };

      if (token) {
        headers["Authorization"] = `Bearer ${token}`;
      }

      const response = await fetch(
        window.TournamentAPI.baseURL + "?action=my-teams",
        {
          credentials: "include",
          headers: headers,
        }
      );

      if (!response.ok) {
        throw new Error("Failed to load teams");
      }

      const data = await response.json();

      if (data.success) {
        const teams = data.teams || [];
        loadingState.classList.add("hidden");

        if (teams.length === 0) {
          emptyState.classList.remove("hidden");
        } else {
          container.classList.remove("hidden");
          await renderTeams(teams);
        }
      } else {
        throw new Error(data.message || "Failed to load teams");
      }
    } catch (error) {
      console.error("Error loading teams:", error);
      loadingState.classList.add("hidden");
      emptyState.classList.remove("hidden");
      const errorP = emptyState.querySelector("p");
      if (errorP) {
        errorP.textContent = "Error loading teams. Please try again.";
      }
    }
  }

  async function renderTeams(teams) {
    const container = document.getElementById("teamsContainer");

    const teamCards = await Promise.all(
      teams.map(async (team) => {
        // Load team members
        const token = localStorage.getItem("auth_token");
        const headers = {
          "Content-Type": "application/json",
        };

        if (token) {
          headers["Authorization"] = `Bearer ${token}`;
        }

        const membersResponse = await fetch(
          window.TournamentAPI.baseURL +
            `?action=team-members&team_id=${team.id}`,
          {
            credentials: "include",
            headers: headers,
          }
        );

        let members = [];
        if (membersResponse.ok) {
          const membersData = await membersResponse.json();
          if (membersData.success) {
            members = membersData.members || [];
          }
        }

        return createTeamCard(team, members);
      })
    );

    container.innerHTML = teamCards.join("");
  }

  function createTeamCard(team, members) {
    const statusColors = {
      active: "bg-green-100 text-green-800",
      disbanded: "bg-gray-100 text-gray-800",
      disqualified: "bg-red-100 text-red-800",
    };

    return `
      <div class="bg-gray-800 rounded-lg shadow-md p-6 border border-gray-700">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="text-xl font-semibold text-white">${team.team_name}</h3>
            ${
              team.team_tag
                ? `<span class="text-sm text-gray-400">[${team.team_tag}]</span>`
                : ""
            }
            <p class="text-sm text-gray-400 mt-1">Tournament: ${
              team.tournament_name
            }</p>
          </div>
          <span class="px-2 py-1 text-xs font-semibold rounded ${
            statusColors[team.team_status] || "bg-gray-100 text-gray-800"
          }">
            ${team.team_status.toUpperCase()}
          </span>
        </div>

        <div class="mb-4">
          <div class="flex justify-between items-center mb-2">
            <h4 class="text-sm font-semibold text-gray-300">Team Members (${
              members.length
            })</h4>
            ${
              team.team_status === "active"
                ? `
            <button onclick="openAddMemberModal(${team.id})" class="px-3 py-1 bg-cyan-600 text-white text-sm rounded hover:bg-cyan-700">
              Add Member
            </button>
            `
                : ""
            }
          </div>
          <div class="space-y-2">
            ${
              members.length > 0
                ? members
                    .map(
                      (member) => `
              <div class="flex justify-between items-center p-2 bg-gray-700/50 rounded">
                <div class="flex items-center">
                  <span class="font-medium text-white">${member.username}</span>
                  ${
                    member.role === "captain"
                      ? '<span class="ml-2 px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs rounded">Captain</span>'
                      : ""
                  }
                  ${
                    member.role === "co_captain"
                      ? '<span class="ml-2 px-2 py-0.5 bg-cyan-500/20 text-cyan-400 text-xs rounded">Co-Captain</span>'
                      : ""
                  }
                </div>
                ${
                  member.role !== "captain" && team.team_status === "active"
                    ? `
                <button onclick="removeMember(${team.id}, ${member.id}, '${member.username}')" class="text-red-400 hover:text-red-300 text-sm">
                  Remove
                </button>
                `
                    : ""
                }
              </div>
            `
                    )
                    .join("")
                : '<p class="text-sm text-gray-500 italic">No members yet</p>'
            }
          </div>
        </div>
      </div>
    `;
  }
}

// ===== Notification Center =====
function setupNotificationCenter() {
  const bellBtn = document.getElementById("notification-bell-btn");
  const dropdown = document.getElementById("notification-dropdown");
  const markAllReadBtn = document.getElementById("mark-all-read-btn");

  if (!bellBtn || !dropdown) return;

  // Load notifications initially
  loadNotificationCenter();

  // Refresh notifications every 30 seconds
  setInterval(loadNotificationCenter, 30000);

  // Toggle dropdown
  bellBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdown.classList.toggle("hidden");

    // Position dropdown below the bell button
    if (!dropdown.classList.contains("hidden")) {
      const rect = bellBtn.getBoundingClientRect();
      dropdown.style.top = `${rect.bottom + 8}px`;
      dropdown.style.right = `${window.innerWidth - rect.right}px`;
    }

    // Close profile menu if open
    const profileMenu = document.getElementById("profile-menu");
    if (profileMenu) {
      profileMenu.classList.add("hidden");
    }
  });

  // Mark all as read
  markAllReadBtn?.addEventListener("click", () => {
    markAllNotificationsAsRead();
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", (e) => {
    if (!bellBtn.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.add("hidden");
    }
  });
}

async function loadNotificationCenter() {
  const token = localStorage.getItem("auth_token");
  if (!token) return;

  try {
    const response = await fetch(
      "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php?action=notifications",
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    const data = await response.json();

    if (data.success) {
      const notifications = data.data || [];
      updateNotificationBadge(notifications);
      renderNotifications(notifications);
    }
  } catch (error) {
    console.error("Error loading notifications:", error);
  }
}

function updateNotificationBadge(notifications) {
  const badge = document.getElementById("notification-badge");
  if (!badge) return;

  const unreadCount = notifications.filter((n) => n.is_read === "0").length;

  if (unreadCount > 0) {
    badge.textContent = unreadCount > 99 ? "99+" : unreadCount;
    badge.classList.remove("hidden");
  } else {
    badge.classList.add("hidden");
  }
}

function renderNotifications(notifications) {
  const container = document.getElementById("notifications-container");
  if (!container) return;

  if (notifications.length === 0) {
    container.innerHTML = `
      <div class="p-8 text-center text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <p>No notifications yet</p>
      </div>
    `;
    return;
  }

  container.innerHTML = notifications
    .map((notification) => {
      const isUnread = notification.is_read === "0";
      const date = new Date(notification.created_at);
      const timeAgo = getTimeAgo(date);

      return `
      <div class="notification-item p-4 border-b border-gray-700 hover:bg-gray-750 cursor-pointer ${
        isUnread ? "bg-gray-800" : ""
      }" 
           data-notification-id="${notification.id}"
           onclick="window.handleNotificationClick(${notification.id}, '${
        notification.type
      }', ${notification.related_id})">
        <div class="flex items-start space-x-3">
          ${
            isUnread
              ? '<div class="w-2 h-2 bg-cyan-400 rounded-full mt-2 flex-shrink-0"></div>'
              : '<div class="w-2 h-2 flex-shrink-0"></div>'
          }
          <div class="flex-1 min-w-0">
            <p class="text-sm ${
              isUnread ? "font-semibold text-white" : "text-gray-300"
            }">${notification.message}</p>
            <p class="text-xs text-gray-500 mt-1">${timeAgo}</p>
          </div>
          ${
            isUnread
              ? `
            <button class="text-cyan-400 hover:text-cyan-300 text-xs flex-shrink-0"
                    onclick="event.stopPropagation(); window.markNotificationAsRead(${notification.id})">
              Mark as read
            </button>
          `
              : ""
          }
        </div>
      </div>
    `;
    })
    .join("");
}

// Global function for handling notification clicks
window.handleNotificationClick = async function (
  notificationId,
  type,
  relatedId
) {
  // Mark as read
  await markNotificationAsRead(notificationId);

  // Navigate based on notification type
  if (type === "tournament_update" && relatedId) {
    currentTournamentId = relatedId;
    loadSection("tournament-details");
  } else if (type === "registration_confirmed" && relatedId) {
    loadSection("my-tournaments");
  }

  // Close dropdown
  document.getElementById("notification-dropdown")?.classList.add("hidden");
};

// Global function for marking single notification as read
window.markNotificationAsRead = async function (notificationId) {
  const token = localStorage.getItem("auth_token");
  if (!token) return;

  try {
    const response = await fetch(
      "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          action: "mark_notification_read",
          notification_id: notificationId,
        }),
      }
    );

    const data = await response.json();

    if (data.success) {
      // Reload notifications to update UI
      loadNotificationCenter();
    }
  } catch (error) {
    console.error("Error marking notification as read:", error);
  }
};

async function markAllNotificationsAsRead() {
  const token = localStorage.getItem("auth_token");
  if (!token) return;

  try {
    const response = await fetch(
      "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          action: "mark_all_notifications_read",
        }),
      }
    );

    const data = await response.json();

    if (data.success) {
      showNotification("All notifications marked as read", "success");
      loadNotificationCenter();
    }
  } catch (error) {
    console.error("Error marking all notifications as read:", error);
  }
}

function getTimeAgo(date) {
  const seconds = Math.floor((new Date() - date) / 1000);

  const intervals = {
    year: 31536000,
    month: 2592000,
    week: 604800,
    day: 86400,
    hour: 3600,
    minute: 60,
  };

  for (const [unit, secondsInUnit] of Object.entries(intervals)) {
    const interval = Math.floor(seconds / secondsInUnit);
    if (interval >= 1) {
      return `${interval} ${unit}${interval > 1 ? "s" : ""} ago`;
    }
  }

  return "Just now";
}

// ===== Manage Tournaments =====
function setupManageTournaments() {
  let currentTournaments = [];

  // Close modal handler
  const closeModalBtn = document.getElementById("closeParticipantsModal");
  if (closeModalBtn) {
    closeModalBtn.addEventListener("click", () => {
      document.getElementById("participantsModal")?.classList.add("hidden");
    });
  }

  // Load tournaments
  loadManageTournaments();

  async function loadManageTournaments() {
    try {
      const token = localStorage.getItem("auth_token");
      const response = await fetch(
        "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php?action=organized-tournaments",
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      const data = await response.json();

      if (data.success) {
        currentTournaments = data.tournaments;
        renderManageTournaments(data.tournaments);
      } else {
        throw new Error(data.message || "Failed to load tournaments");
      }
    } catch (error) {
      console.error("Error loading tournaments:", error);
      showNotification(error.message, "error");
    }
  }

  function renderManageTournaments(tournaments) {
    document.getElementById("loadingState")?.classList.add("hidden");

    if (tournaments.length === 0) {
      document.getElementById("emptyState")?.classList.remove("hidden");
      return;
    }

    const container = document.getElementById("tournamentsList");
    if (!container) return;

    container.classList.remove("hidden");

    container.innerHTML = tournaments
      .map(
        (tournament) => `
      <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 hover:border-cyan-500/50 transition-all">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="text-xl font-bold text-white mb-2">${escapeHtml(
              tournament.name
            )}</h3>
            <p class="text-gray-400 text-sm">${escapeHtml(
              tournament.description || "No description"
            )}</p>
          </div>
          <span class="px-3 py-1 rounded-lg text-sm font-semibold ${getStatusBadgeClass(
            tournament.status
          )}">
            ${getStatusText(tournament.status)}
          </span>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
          <div class="bg-gray-700/50 rounded-lg p-3">
            <div class="text-gray-400 text-xs mb-1">Confirmed</div>
            <div class="text-green-400 text-lg font-bold">${
              tournament.confirmed_count || 0
            }</div>
          </div>
          <div class="bg-gray-700/50 rounded-lg p-3">
            <div class="text-gray-400 text-xs mb-1">Pending</div>
            <div class="text-yellow-400 text-lg font-bold">${
              tournament.pending_count || 0
            }</div>
          </div>
          <div class="bg-gray-700/50 rounded-lg p-3">
            <div class="text-gray-400 text-xs mb-1">Rejected</div>
            <div class="text-red-400 text-lg font-bold">${
              tournament.rejected_count || 0
            }</div>
          </div>
          <div class="bg-gray-700/50 rounded-lg p-3">
            <div class="text-gray-400 text-xs mb-1">Max Participants</div>
            <div class="text-white text-lg font-bold">${
              tournament.max_participants || tournament.tournament_size
            }</div>
          </div>
        </div>
        
        <div class="flex space-x-3">
          <button onclick="window.viewParticipants(${
            tournament.id
          })" class="flex-1 px-4 py-2 bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            Manage Participants
          </button>
          <button onclick="window.viewTournamentBracket(${
            tournament.id
          })" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            View Bracket
          </button>
          ${
            tournament.is_team_based == 1
              ? `
          <button onclick="window.viewTeams(${tournament.id})" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            View Teams
          </button>
          `
              : ""
          }
        </div>
      </div>
    `
      )
      .join("");
  }

  // Global functions for onclick handlers
  window.viewParticipants = async function (tournamentId) {
    try {
      const token = localStorage.getItem("auth_token");
      const response = await fetch(
        `/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php?action=tournament-participants&tournament_id=${tournamentId}`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      const data = await response.json();

      if (data.success) {
        showParticipantsModal(data.participants, tournamentId);
      } else {
        throw new Error(data.message || "Failed to load participants");
      }
    } catch (error) {
      console.error("Error loading participants:", error);
      showNotification(error.message, "error");
    }
  };

  window.viewTeams = async function (tournamentId) {
    try {
      const token = localStorage.getItem("auth_token");
      const response = await fetch(
        `/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php?action=tournament-teams&tournament_id=${tournamentId}`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      const data = await response.json();

      if (data.success) {
        showTeamsModal(data.teams);
      } else {
        throw new Error(data.message || "Failed to load teams");
      }
    } catch (error) {
      console.error("Error loading teams:", error);
      showNotification(error.message, "error");
    }
  };

  window.approveParticipant = async function (participantId, tournamentId) {
    if (!confirm("Are you sure you want to approve this participant?")) {
      return;
    }

    try {
      const token = localStorage.getItem("auth_token");
      const response = await fetch(
        "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            action: "approve-participant",
            participant_id: participantId,
          }),
        }
      );
      const data = await response.json();

      if (data.success) {
        showNotification("Participant approved successfully!", "success");
        window.viewParticipants(tournamentId);
        loadManageTournaments();
      } else {
        throw new Error(data.message || "Failed to approve participant");
      }
    } catch (error) {
      console.error("Error approving participant:", error);
      showNotification(error.message, "error");
    }
  };

  window.rejectParticipant = async function (participantId, tournamentId) {
    if (!confirm("Are you sure you want to reject this participant?")) {
      return;
    }

    try {
      const token = localStorage.getItem("auth_token");
      const response = await fetch(
        "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            action: "reject-participant",
            participant_id: participantId,
          }),
        }
      );
      const data = await response.json();

      if (data.success) {
        showNotification("Participant rejected successfully!", "success");
        window.viewParticipants(tournamentId);
        loadManageTournaments();
      } else {
        throw new Error(data.message || "Failed to reject participant");
      }
    } catch (error) {
      console.error("Error rejecting participant:", error);
      showNotification(error.message, "error");
    }
  };

  function showParticipantsModal(participants, tournamentId) {
    const modal = document.getElementById("participantsModal");
    const content = document.getElementById("participantsContent");
    if (!modal || !content) return;

    if (participants.length === 0) {
      content.innerHTML = `
        <div class="text-center py-8 text-gray-400">
          No participants yet
        </div>
      `;
    } else {
      content.innerHTML = `
        <div class="space-y-3">
          ${participants
            .map(
              (participant) => `
            <div class="bg-gray-700/50 rounded-lg p-4 flex justify-between items-center">
              <div class="flex-1">
                <div class="flex items-center space-x-3">
                  <div>
                    <div class="text-white font-semibold">${escapeHtml(
                      participant.username
                    )}</div>
                    <div class="text-gray-400 text-sm">${escapeHtml(
                      participant.email
                    )}</div>
                    ${
                      participant.team_name
                        ? `<div class="text-purple-400 text-sm">Team: ${escapeHtml(
                            participant.team_name
                          )}</div>`
                        : ""
                    }
                  </div>
                </div>
                ${
                  participant.registration_notes
                    ? `
                  <div class="mt-2 text-gray-400 text-sm">
                    <strong>Notes:</strong> ${escapeHtml(
                      participant.registration_notes
                    )}
                  </div>
                `
                    : ""
                }
              </div>
              <div class="flex items-center space-x-3">
                <span class="px-3 py-1 rounded-lg text-sm font-semibold ${getRegistrationBadgeClass(
                  participant.registration_status
                )}">
                  ${participant.registration_status}
                </span>
                ${
                  participant.registration_status === "pending"
                    ? `
                  <button onclick="window.approveParticipant(${participant.id}, ${tournamentId})" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors">
                    Approve
                  </button>
                  <button onclick="window.rejectParticipant(${participant.id}, ${tournamentId})" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition-colors">
                    Reject
                  </button>
                `
                    : ""
                }
              </div>
            </div>
          `
            )
            .join("")}
        </div>
      `;
    }

    modal.classList.remove("hidden");
  }

  function showTeamsModal(teams) {
    const modal = document.getElementById("participantsModal");
    const content = document.getElementById("participantsContent");
    if (!modal || !content) return;

    if (teams.length === 0) {
      content.innerHTML = `
        <div class="text-center py-8 text-gray-400">
          No teams registered yet
        </div>
      `;
    } else {
      content.innerHTML = `
        <div class="space-y-3">
          ${teams
            .map(
              (team) => `
            <div class="bg-gray-700/50 rounded-lg p-4">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <div class="text-white font-bold text-lg">${escapeHtml(
                    team.team_name
                  )}
                    ${
                      team.team_tag
                        ? `<span class="text-purple-400 text-sm ml-2">[${escapeHtml(
                            team.team_tag
                          )}]</span>`
                        : ""
                    }
                  </div>
                  <div class="text-gray-400 text-sm">Captain: ${escapeHtml(
                    team.captain_name
                  )}</div>
                </div>
                <span class="px-3 py-1 rounded-lg text-sm font-semibold ${
                  team.team_status === "active"
                    ? "bg-green-600 text-white"
                    : "bg-gray-600 text-white"
                }">
                  ${team.team_status}
                </span>
              </div>
              <div class="text-cyan-400 text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                ${team.member_count} members
              </div>
            </div>
          `
            )
            .join("")}
        </div>
      `;
    }

    modal.classList.remove("hidden");
  }

  function getStatusBadgeClass(status) {
    const classes = {
      draft: "bg-gray-600 text-white",
      open: "bg-green-600 text-white",
      registration_closed: "bg-yellow-600 text-white",
      ongoing: "bg-blue-600 text-white",
      completed: "bg-purple-600 text-white",
      cancelled: "bg-red-600 text-white",
    };
    return classes[status] || "bg-gray-600 text-white";
  }

  function getStatusText(status) {
    const texts = {
      draft: "Draft",
      open: "Open",
      registration_closed: "Closed",
      ongoing: "Ongoing",
      completed: "Completed",
      cancelled: "Cancelled",
    };
    return texts[status] || status;
  }

  function getRegistrationBadgeClass(status) {
    const classes = {
      pending: "bg-yellow-600 text-white",
      confirmed: "bg-green-600 text-white",
      rejected: "bg-red-600 text-white",
      withdrawn: "bg-gray-600 text-white",
      waitlist: "bg-blue-600 text-white",
    };
    return classes[status] || "bg-gray-600 text-white";
  }

  function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }
}

// ===== Tournament Bracket =====
let currentBracketTournamentId = null;

// Global function to view tournament bracket
window.viewTournamentBracket = function (tournamentId) {
  currentBracketTournamentId = tournamentId;
  loadSection("tournament-bracket");
};

function setupTournamentBracket() {
  let currentTournament = null;
  let currentMatches = [];
  let draggedParticipant = null;

  // Close modal handler (if needed)
  const backBtn = document.querySelector(
    'button[onclick="window.history.back()"]'
  );
  if (backBtn) {
    backBtn.onclick = (e) => {
      e.preventDefault();
      loadSection("manage-tournaments");
    };
  }

  // Load bracket
  loadBracket();

  async function loadBracket() {
    const tournamentId = currentBracketTournamentId;

    if (!tournamentId) {
      showNotification("No tournament ID provided", "error");
      return;
    }

    try {
      const token = localStorage.getItem("auth_token");
      const response = await fetch(
        `/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php?action=tournament-bracket&tournament_id=${tournamentId}`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      const data = await response.json();

      if (data.success) {
        currentTournament = data.tournament;
        currentMatches = data.matches;

        document.getElementById("tournamentName").textContent =
          "Bracket - " + (currentTournament.name || "Tournament");

        if (currentMatches.length === 0) {
          showEmptyState();
        } else {
          renderBracket();
        }
      } else {
        throw new Error(data.message || "Failed to load bracket");
      }
    } catch (error) {
      console.error("Error loading bracket:", error);
      showNotification(error.message, "error");
    } finally {
      document.getElementById("loadingState")?.classList.add("hidden");
    }
  }

  function showEmptyState() {
    document.getElementById("emptyState")?.classList.remove("hidden");
    document.getElementById("generateBracketBtn")?.classList.remove("hidden");
  }

  // Global reset match function
  window.resetMatch = async function (matchId) {
    if (!confirm("Reset this match? The winner will be cleared.")) {
      return;
    }

    try {
      const token = localStorage.getItem("auth_token");
      const response = await fetch(
        "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            action: "reset-match",
            match_id: matchId,
          }),
        }
      );
      const data = await response.json();

      if (data.success) {
        showNotification("Match reset successfully!", "success");
        loadBracket();
      } else {
        throw new Error(data.message || "Failed to reset match");
      }
    } catch (error) {
      console.error("Error resetting match:", error);
      showNotification(error.message, "error");
    }
  };

  // Global reset all matches function
  window.resetAllMatches = async function () {
    if (
      !confirm(
        "Reset ALL matches? All winners will be cleared. This cannot be undone."
      )
    ) {
      return;
    }

    try {
      const token = localStorage.getItem("auth_token");
      const response = await fetch(
        "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            action: "reset-all-matches",
            tournament_id: currentBracketTournamentId,
          }),
        }
      );
      const data = await response.json();

      if (data.success) {
        showNotification("All matches reset successfully!", "success");
        loadBracket();
      } else {
        throw new Error(data.message || "Failed to reset matches");
      }
    } catch (error) {
      console.error("Error resetting matches:", error);
      showNotification(error.message, "error");
    }
  };

  // Global generate bracket function
  window.generateBracket = async function () {
    if (
      !confirm("Generate bracket for this tournament? This cannot be undone.")
    ) {
      return;
    }

    try {
      const token = localStorage.getItem("auth_token");
      const response = await fetch(
        "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            action: "generate-bracket",
            tournament_id: currentBracketTournamentId,
          }),
        }
      );
      const data = await response.json();

      if (data.success) {
        showNotification("Bracket generated successfully!", "success");
        document.getElementById("emptyState")?.classList.add("hidden");
        document.getElementById("generateBracketBtn")?.classList.add("hidden");
        loadBracket();
      } else {
        throw new Error(data.message || "Failed to generate bracket");
      }
    } catch (error) {
      console.error("Error generating bracket:", error);
      showNotification(error.message, "error");
    }
  };

  // Setup generate button
  const generateBtn = document.getElementById("generateBracketBtn");
  if (generateBtn) {
    generateBtn.addEventListener("click", window.generateBracket);
  }

  function renderBracket() {
    const container = document.getElementById("bracketView");
    if (!container) return;

    document.getElementById("bracketContainer")?.classList.remove("hidden");

    // Group matches by round
    const rounds = {};
    currentMatches.forEach((match) => {
      if (!rounds[match.round_number]) {
        rounds[match.round_number] = [];
      }
      rounds[match.round_number].push(match);
    });

    const maxRound = Math.max(...Object.keys(rounds).map(Number));
    const hasAnyWinners = currentMatches.some(
      (m) => m.winner_id && m.match_status !== "bye"
    );

    let html = "";

    // Add reset all button if there are any winners set
    if (hasAnyWinners) {
      html += `<div class="mb-4 text-right">
        <button onclick="resetAllMatches()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors">
          ↺ Reset All Matches
        </button>
      </div>`;
    }

    html += '<div class="flex items-start">';

    for (let roundNum = 1; roundNum <= maxRound; roundNum++) {
      const matches = rounds[roundNum] || [];
      const roundName = getRoundName(roundNum, maxRound);

      html += `
        <div class="bracket-round">
          <h3 class="text-lg font-bold text-cyan-400 mb-4 text-center">${roundName}</h3>
      `;

      matches.forEach((match) => {
        html += renderMatch(match);
      });

      html += "</div>";
    }

    // Add champion podium
    const tournamentWinner =
      currentTournament?.winner_name ||
      currentTournament?.winner_team_name ||
      null;
    const isCompleted = currentTournament?.status === "completed";

    console.log("Champion podium debug:", {
      currentTournament,
      tournamentWinner,
      isCompleted,
      maxRound,
    });

    html += `
      <div class="bracket-round">
        <h3 class="text-lg font-bold text-yellow-400 mb-4 text-center">🏆 Champion</h3>
        <div class="champion-podium ${
          tournamentWinner ? "has-champion" : "empty drop-target"
        }" 
             data-round="${maxRound + 1}"
             data-match-id="final">
          <div class="champion-slot">
            ${
              tournamentWinner
                ? `
              <div class="text-2xl font-bold text-yellow-400 mb-2">👑</div>
              <div class="text-xl font-bold text-white">${escapeHtml(
                tournamentWinner
              )}</div>
              ${
                isCompleted
                  ? '<div class="text-sm text-green-400 mt-2">Tournament Completed</div>'
                  : ""
              }
            `
                : `
              <div class="text-gray-400 text-center">Drag winner here</div>
            `
            }
          </div>
        </div>
      </div>
    `;

    html += "</div>";
    container.innerHTML = html;

    console.log("Bracket HTML rendered, checking for champion podium...");
    setTimeout(() => {
      const podium = document.querySelector(".champion-podium");
      console.log("Champion podium element:", podium);
    }, 100);

    // Setup drag and drop
    setupDragAndDrop();
  }

  function renderMatch(match) {
    const isCompleted = match.match_status === "completed";
    const isBye = match.match_status === "bye";

    const p1Name =
      match.participant1_team_name || match.participant1_name || "TBD";
    const p2Name =
      match.participant2_team_name || match.participant2_name || "TBD";

    const p1IsWinner =
      match.winner_id && match.winner_id == match.participant1_id;
    const p2IsWinner =
      match.winner_id && match.winner_id == match.participant2_id;

    const hasWinner = match.winner_id && !isBye;

    return `
      <div class="bracket-match ${isCompleted ? "completed" : ""} ${
      isBye ? "bye" : ""
    }" data-match-id="${match.id}" data-round="${match.round_number}">
        <div class="match-round-label">
          Match ${match.match_number}
          ${
            hasWinner
              ? `<button onclick="resetMatch(${match.id})" class="ml-2 text-xs text-red-400 hover:text-red-300" title="Reset this match">↺</button>`
              : ""
          }
        </div>
        <div class="bracket-participant ${p1IsWinner ? "winner" : ""} ${
      !match.participant1_id ? "empty drop-target" : ""
    }"
             draggable="${
               match.participant1_id && !isCompleted ? "true" : "false"
             }"
             data-participant-id="${match.participant1_id || ""}"
             data-participant-name="${escapeHtml(p1Name)}"
             data-match-id="${match.id}"
             data-round="${match.round_number}">
          ${escapeHtml(p1Name)}
        </div>
        <div class="vs-divider">VS</div>
        <div class="bracket-participant ${p2IsWinner ? "winner" : ""} ${
      !match.participant2_id ? "empty drop-target" : ""
    }"
             draggable="${
               match.participant2_id && !isCompleted ? "true" : "false"
             }"
             data-participant-id="${match.participant2_id || ""}"
             data-participant-name="${escapeHtml(p2Name)}"
             data-match-id="${match.id}"
             data-round="${match.round_number}">
          ${escapeHtml(p2Name)}
        </div>
      </div>
    `;
  }

  function setupDragAndDrop() {
    const draggables = document.querySelectorAll(
      '.bracket-participant[draggable="true"]'
    );
    const dropTargets = document.querySelectorAll(
      ".bracket-participant.drop-target, .champion-podium.drop-target"
    );

    draggables.forEach((participant) => {
      participant.addEventListener("dragstart", handleDragStart);
      participant.addEventListener("dragend", handleDragEnd);
    });

    dropTargets.forEach((target) => {
      target.addEventListener("dragover", handleDragOver);
      target.addEventListener("dragleave", handleDragLeave);
      target.addEventListener("drop", handleDrop);
    });
  }

  function handleDragStart(e) {
    draggedParticipant = {
      participantId: e.target.dataset.participantId,
      participantName: e.target.dataset.participantName,
      matchId: e.target.dataset.matchId,
      roundNumber: parseInt(e.target.dataset.round),
    };
    e.target.classList.add("dragging");
    e.dataTransfer.effectAllowed = "move";
    e.dataTransfer.setData("text/html", e.target.innerHTML);
  }

  function handleDragEnd(e) {
    e.target.classList.remove("dragging");
    document.querySelectorAll(".drop-zone").forEach((el) => {
      el.classList.remove("drop-zone");
    });
  }

  function handleDragOver(e) {
    if (e.preventDefault) {
      e.preventDefault();
    }

    const target = e.target.closest(".bracket-participant, .champion-podium");

    if (!target || !draggedParticipant) {
      return false;
    }

    // Allow drop on empty bracket slots in the next round
    if (
      target.classList.contains("bracket-participant") &&
      target.classList.contains("empty")
    ) {
      const targetRound = parseInt(target.dataset.round);

      // Only allow dropping into the next round
      if (targetRound === draggedParticipant.roundNumber + 1) {
        target.classList.add("drop-zone");
        e.dataTransfer.dropEffect = "move";
      }
    }

    // Allow drop on champion podium from finals (last round)
    if (
      target.classList.contains("champion-podium") &&
      target.classList.contains("empty")
    ) {
      const targetRound = parseInt(target.dataset.round);

      // Only finals winner can be dragged to champion podium
      if (targetRound === draggedParticipant.roundNumber + 1) {
        target.classList.add("drop-zone");
        e.dataTransfer.dropEffect = "move";
      }
    }
  }

  function handleDragLeave(e) {
    if (e.target.classList.contains("bracket-participant")) {
      e.target.classList.remove("drop-zone");
    }
  }

  async function handleDrop(e) {
    if (e.stopPropagation) {
      e.stopPropagation();
    }
    if (e.preventDefault) {
      e.preventDefault();
    }

    const target = e.target.closest(".bracket-participant, .champion-podium");
    if (target) {
      target.classList.remove("drop-zone");
    }

    if (!draggedParticipant || !target) return false;

    const targetRound = parseInt(target.dataset.round);

    // Validate it's the next round
    if (targetRound === draggedParticipant.roundNumber + 1) {
      // Check if this is the champion podium
      if (target.classList.contains("champion-podium")) {
        // Set tournament winner and complete tournament
        await setTournamentWinner(
          draggedParticipant.matchId,
          draggedParticipant.participantId
        );
      } else {
        // Set this participant as the winner of their current match
        await setMatchWinner(
          draggedParticipant.matchId,
          draggedParticipant.participantId
        );
      }
    }

    return false;
  }

  async function setMatchWinner(matchId, winnerId) {
    try {
      const token = localStorage.getItem("auth_token");
      const response = await fetch(
        "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            action: "set-match-winner",
            match_id: matchId,
            winner_id: winnerId,
          }),
        }
      );
      const data = await response.json();

      if (data.success) {
        showNotification("Winner advanced to next round!", "success");
        loadBracket();
      } else {
        throw new Error(data.message || "Failed to set winner");
      }
    } catch (error) {
      console.error("Error setting winner:", error);
      showNotification(error.message, "error");
    }
  }

  async function setTournamentWinner(matchId, winnerId) {
    try {
      const token = localStorage.getItem("auth_token");
      const response = await fetch(
        "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            action: "set-tournament-winner",
            tournament_id: currentBracketTournamentId,
            match_id: matchId,
            winner_id: winnerId,
          }),
        }
      );
      const data = await response.json();

      if (data.success) {
        showNotification(
          "🏆 Tournament completed! Champion declared!",
          "success"
        );
        loadBracket();
      } else {
        throw new Error(data.message || "Failed to set tournament winner");
      }
    } catch (error) {
      console.error("Error setting tournament winner:", error);
      showNotification(error.message, "error");
    }
  }

  function getRoundName(roundNum, maxRound) {
    const roundsFromEnd = maxRound - roundNum;

    if (roundsFromEnd === 0) return "Finals";
    if (roundsFromEnd === 1) return "Semi-Finals";
    if (roundsFromEnd === 2) return "Quarter-Finals";

    return `Round ${roundNum}`;
  }

  function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }
}
