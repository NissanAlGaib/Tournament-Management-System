// Import authentication module
import * as Auth from "./core/auth.js";
import { getViewPath } from "./pathHelper.js";

// Expose Auth module globally for AJAX-loaded pages
window.Auth = Auth;

// DOM Elements
let homeContent;
let currentSection = "dashboard";

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
    .getElementById("nav-profile")
    ?.addEventListener("click", () => loadSection("profile"));

  // Set up logout buttons
  document
    .getElementById("sidebar-logout-btn")
    ?.addEventListener("click", handleLogout);

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
  const navButtons = ["nav-dashboard", "nav-tournaments", "nav-profile"];
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
