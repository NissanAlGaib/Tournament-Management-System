// Import authentication module
import * as Auth from './core/auth.js';

// DOM Elements
let homeContent;
let currentSection = 'dashboard';

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    homeContent = document.getElementById('home-content');
    
    // Check if user is logged in
    const user = Auth.getCurrentUser();
    if (!user) {
        // Redirect to login if not authenticated
        window.location.href = 'layout.php';
        return;
    }
    
    // Display username in nav
    const usernameEl = document.getElementById('nav-username');
    if (usernameEl) {
        usernameEl.textContent = `Hello, ${user.username}`;
    }
    
    // Set up navigation listeners
    document.getElementById('nav-dashboard')?.addEventListener('click', () => loadSection('dashboard'));
    document.getElementById('nav-tournaments')?.addEventListener('click', () => loadSection('tournaments'));
    document.getElementById('nav-profile')?.addEventListener('click', () => loadSection('profile'));
    
    // Set up logout buttons
    document.getElementById('sidebar-logout-btn')?.addEventListener('click', handleLogout);
    
    // Load dashboard by default
    loadSection('dashboard');
});

// Load section dynamically using AJAX
function loadSection(sectionName) {
    const sectionPath = `pages/home/${sectionName}.php`;
    
    fetch(sectionPath)
        .then(response => {
            if (!response.ok) {
                throw new Error('Section not found');
            }
            return response.text();
        })
        .then(html => {
            homeContent.innerHTML = html;
            currentSection = sectionName;
            
            // Update active nav button
            updateActiveNav(sectionName);
            
            // Setup section-specific functionality
            if (sectionName === 'dashboard') {
                setupDashboard();
            } else if (sectionName === 'profile') {
                setupProfile();
            }
        })
        .catch(error => {
            console.error('Error loading section:', error);
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
    const navButtons = ['nav-dashboard', 'nav-tournaments', 'nav-profile'];
    navButtons.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) {
            btn.className = 'w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-gray-700 transition-all';
        }
    });
    
    // Add active class to current section
    const activeBtn = document.getElementById(`nav-${sectionName}`);
    if (activeBtn) {
        activeBtn.className = 'w-full flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-purple-600 text-white font-semibold shadow-lg shadow-cyan-500/30 transition-all';
    }
}

// Setup dashboard functionality
function setupDashboard() {
    const user = Auth.getCurrentUser();
    const usernameEl = document.getElementById('dashboard-username');
    if (usernameEl && user) {
        usernameEl.textContent = user.username;
    }
}

// Setup profile functionality
function setupProfile() {
    const user = Auth.getCurrentUser();
    
    // Fill in user information
    const usernameEl = document.getElementById('profile-username');
    const emailEl = document.getElementById('profile-email');
    
    if (usernameEl && user) {
        usernameEl.textContent = user.username;
    }
    if (emailEl && user) {
        emailEl.textContent = user.email;
    }
    
    // Setup logout button in profile
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
}

// Handle logout
function handleLogout() {
    Auth.logout();
    window.location.href = 'layout.php';
}
