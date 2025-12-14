// Import authentication module
import * as Auth from './core/auth.js';

// DOM Elements
let mainContent;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    mainContent = document.getElementById('main-content');
    
    // Set up navigation listeners
    document.getElementById('nav-login')?.addEventListener('click', () => loadView('login'));
    document.getElementById('nav-register')?.addEventListener('click', () => loadView('register'));
    
    // Load login view by default
    loadView('login');
});

// Load view dynamically using AJAX
function loadView(viewName) {
    const viewPath = `pages/auth/${viewName}.php`;
    
    fetch(viewPath)
        .then(response => {
            if (!response.ok) {
                throw new Error('View not found');
            }
            return response.text();
        })
        .then(html => {
            mainContent.innerHTML = html;
            
            // Attach form listeners after content is loaded
            if (viewName === 'login') {
                setupLoginForm();
            } else if (viewName === 'register') {
                setupRegisterForm();
            }
        })
        .catch(error => {
            console.error('Error loading view:', error);
            mainContent.innerHTML = `
                <div class="text-center text-red-400">
                    <p>Error loading ${viewName} view</p>
                </div>
            `;
        });
}

// Setup login form
function setupLoginForm() {
    const form = document.getElementById('login-form');
    const errorDiv = document.getElementById('login-error');
    const successDiv = document.getElementById('login-success');
    const switchBtn = document.getElementById('switch-to-register');
    
    switchBtn?.addEventListener('click', () => loadView('register'));
    
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const username = document.getElementById('login-username').value;
        const password = document.getElementById('login-password').value;
        
        // Hide previous messages
        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');
        
        try {
            const data = await Auth.login(username, password);
            
            if (data.success) {
                successDiv.textContent = data.message;
                successDiv.classList.remove('hidden');
                
                // Redirect or update UI after successful login
                setTimeout(() => {
                    showWelcomeMessage(data.user);
                }, 1000);
            } else {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.textContent = error.message || 'An error occurred. Please try again.';
            errorDiv.classList.remove('hidden');
        }
    });
}

// Setup register form
function setupRegisterForm() {
    const form = document.getElementById('register-form');
    const errorDiv = document.getElementById('register-error');
    const successDiv = document.getElementById('register-success');
    const switchBtn = document.getElementById('switch-to-login');
    
    switchBtn?.addEventListener('click', () => loadView('login'));
    
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const username = document.getElementById('register-username').value;
        const email = document.getElementById('register-email').value;
        const password = document.getElementById('register-password').value;
        
        // Hide previous messages
        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');
        
        try {
            const data = await Auth.register(username, email, password);
            
            if (data.success) {
                successDiv.textContent = data.message + ' You can now login.';
                successDiv.classList.remove('hidden');
                
                // Clear form
                form.reset();
                
                // Redirect to login after 2 seconds
                setTimeout(() => {
                    loadView('login');
                }, 2000);
            } else {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.textContent = error.message || 'An error occurred. Please try again.';
            errorDiv.classList.remove('hidden');
        }
    });
}

// Show welcome message after login
function showWelcomeMessage(user) {
    mainContent.innerHTML = `
        <div class="flex justify-center items-center min-h-[calc(100vh-12rem)] px-4">
            <div class="w-full max-w-2xl">
                <!-- Welcome Card -->
                <div class="relative group">
                    <!-- Animated background glow -->
                    <div class="absolute -inset-1 bg-gradient-to-r from-cyan-500 via-purple-500 to-cyan-500 rounded-3xl opacity-75 group-hover:opacity-100 blur-lg transition duration-500 animate-gradient-x"></div>
                    
                    <!-- Main card -->
                    <div class="relative bg-gray-900 rounded-3xl overflow-hidden border border-cyan-500/30">
                        <!-- Success Icon -->
                        <div class="flex justify-center pt-12">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-full blur-xl opacity-50"></div>
                                <div class="relative bg-gradient-to-br from-cyan-500 to-purple-600 p-6 rounded-full">
                                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="px-8 py-8 text-center">
                            <h2 class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-purple-400 to-cyan-400 mb-3 tracking-tight">
                                Welcome Back!
                            </h2>
                            <p class="text-2xl font-bold text-white mb-2">${user.username}</p>
                            <p class="text-gray-400 text-lg mb-8">
                                You have successfully logged in to the Tournament Management System
                            </p>
                            
                            <!-- User Info Card -->
                            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Username -->
                                    <div class="bg-gray-900 rounded-xl p-4 border border-cyan-500/30">
                                        <div class="flex items-center justify-center mb-2">
                                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Username</p>
                                        <p class="text-white font-bold text-lg">${user.username}</p>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div class="bg-gray-900 rounded-xl p-4 border border-purple-500/30">
                                        <div class="flex items-center justify-center mb-2">
                                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Email</p>
                                        <p class="text-white font-bold text-sm truncate">${user.email}</p>
                                    </div>
                                    
                                    <!-- User ID -->
                                    <div class="bg-gray-900 rounded-xl p-4 border border-cyan-500/30">
                                        <div class="flex items-center justify-center mb-2">
                                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">User ID</p>
                                        <p class="text-white font-bold text-lg">#${user.id}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- CTA Button -->
                            <div class="mt-8">
                                <button class="group relative inline-flex items-center">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-xl blur opacity-60 group-hover:opacity-100 transition duration-300"></div>
                                    <div class="relative bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-bold px-8 py-4 rounded-xl shadow-lg transition-all duration-300">
                                        <span class="flex items-center">
                                            Get Started
                                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        @keyframes gradient-x {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .animate-gradient-x {
            background-size: 200% 200%;
            animation: gradient-x 3s ease infinite;
        }
        </style>
    `;
}
