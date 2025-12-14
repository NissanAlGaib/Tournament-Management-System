// Import authentication module
import * as Auth from "./core/auth.js";

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
          alert("Please provide a reason for your request");
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
            alert("✓ Your request has been submitted successfully! An admin will review it soon.");
            organizerSection.style.display = "none";
          } else {
            alert("Failed to submit request: " + result.message);
            requestBtn.disabled = false;
            requestBtn.innerHTML = `
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
              </svg>
              Submit Request
            `;
          }
        } catch (error) {
          alert("Error submitting request: " + error.message);
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

// Handle logout
function handleLogout() {
  Auth.logout();
  window.location.href = "layout.php";
}
