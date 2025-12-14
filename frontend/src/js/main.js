// Import authentication module
import * as Auth from './core/auth.js';

// DOM Elements
let mainContent;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    mainContent = document.getElementById('main-content');
    
    // Check if user is already logged in
    if (Auth.isAuthenticated()) {
        // Redirect to homepage if already logged in
        window.location.href = 'pages/home/index.php';
        return;
    }
    
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
                
                // Redirect based on user role after successful login
                setTimeout(() => {
                    // Check if user is admin and redirect to admin panel
                    if (Auth.isAdmin()) {
                        window.location.href = 'pages/admin/dashboard.php';
                    } else {
                        window.location.href = 'pages/home/index.php';
                    }
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
