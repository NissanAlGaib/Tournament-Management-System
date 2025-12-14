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
    .getElementById("nav-profile")
    ?.addEventListener("click", () => loadSection("profile"));

  // Set up logout buttons
  document
    .getElementById("sidebar-logout-btn")
    ?.addEventListener("click", handleLogout);

  // Set up notification bell
  setupNotificationCenter();

  // Load dashboard by default
  loadSection("dashboard");
});

// Load section dynamically using AJAX
function loadSection(sectionName) {
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

// Update active navigation button
function updateActiveNav(sectionName) {
  // Remove active class from all nav buttons
  const navButtons = [
    "nav-dashboard",
    "nav-tournaments",
    "nav-my-tournaments",
    "nav-team-management",
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

// Setup tournaments section
function setupTournaments() {
  console.log("Setting up tournaments section...");

  // Load tournament.js dynamically if not already loaded
  if (typeof window.TournamentAPI === "undefined") {
    const script = document.createElement("script");
    script.src =
      "/GitHub Repos/Tournament-Management-System/frontend/src/js/tournament.js";
    script.onload = function () {
      console.log("Tournament.js loaded");
      initTournamentPage();
    };
    script.onerror = function () {
      console.error("Failed to load tournament.js");
    };
    document.head.appendChild(script);
  } else {
    initTournamentPage();
  }

  function initTournamentPage() {
    if (
      typeof window.TournamentAPI === "undefined" ||
      typeof window.TournamentUI === "undefined"
    ) {
      console.error("TournamentAPI or TournamentUI not available");
      return;
    }

    console.log("Initializing tournament page...");

    // Set API base URL
    window.TournamentAPI.baseURL =
      "/GitHub Repos/Tournament-Management-System/backend/api/tournament_api.php";
    console.log("API URL set to:", window.TournamentAPI.baseURL);

    // Load tournaments
    loadTournaments();
    setupFilterButtons();
    setupCreateButton();

    function setupFilterButtons() {
      document.querySelectorAll(".filter-tab").forEach((button) => {
        button.addEventListener("click", function () {
          document.querySelectorAll(".filter-tab").forEach((btn) => {
            btn.classList.remove(
              "active",
              "bg-gradient-to-r",
              "from-cyan-500",
              "to-purple-600",
              "text-white",
              "shadow-lg",
              "shadow-cyan-500/30"
            );
            btn.classList.add("bg-gray-800", "text-gray-400");
          });

          this.classList.remove("bg-gray-800", "text-gray-400");
          this.classList.add(
            "active",
            "bg-gradient-to-r",
            "from-cyan-500",
            "to-purple-600",
            "text-white",
            "shadow-lg",
            "shadow-cyan-500/30"
          );

          const status = this.dataset.status;
          loadTournaments(status);
        });
      });
    }

    function setupCreateButton() {
      const createBtn = document.getElementById("createTournamentBtn");
      if (!createBtn) {
        console.log("Create tournament button not found");
        return;
      }

      console.log("Checking user roles for create button...");
      console.log("isOrganizer:", Auth.isOrganizer());
      console.log("isAdmin:", Auth.isAdmin());

      // Show button only for Organizers and Admins
      if (Auth.isOrganizer() || Auth.isAdmin()) {
        console.log("User has permission - showing Create Tournament button");
        createBtn.classList.remove("hidden");
        createBtn.addEventListener("click", function () {
          // Open the create tournament modal
          if (typeof window.openCreateTournamentModal === "function") {
            window.openCreateTournamentModal();
          } else {
            console.error("Create tournament modal function not found");
          }
        });
      } else {
        console.log("User does not have Organizer or Admin role");
      }
    }

    async function loadTournaments(status = null) {
      const loadingState = document.getElementById("loadingState");
      const tournamentsGrid = document.getElementById("tournamentsGrid");
      const emptyState = document.getElementById("emptyState");

      if (!loadingState || !tournamentsGrid || !emptyState) {
        console.error("Tournament page elements not found");
        return;
      }

      loadingState.classList.remove("hidden");
      tournamentsGrid.classList.add("hidden");
      emptyState.classList.add("hidden");

      try {
        console.log("Fetching tournaments with status:", status);
        const result = await window.TournamentAPI.getTournaments(status);
        console.log("API response:", result);

        if (
          result.success &&
          result.tournaments &&
          result.tournaments.length > 0
        ) {
          console.log("Rendering", result.tournaments.length, "tournaments");
          tournamentsGrid.innerHTML = result.tournaments
            .map((tournament) =>
              window.TournamentUI.renderTournamentCard(tournament)
            )
            .join("");

          loadingState.classList.add("hidden");
          tournamentsGrid.classList.remove("hidden");
        } else {
          console.log("No tournaments found");
          loadingState.classList.add("hidden");
          emptyState.classList.remove("hidden");
        }
      } catch (error) {
        console.error("Error loading tournaments:", error);
        loadingState.classList.add("hidden");
        emptyState.classList.remove("hidden");
      }
    }
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
