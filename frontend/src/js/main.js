// API endpoint
const API_URL = '../../backend/api/auth_api.php';

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
    const viewPath = `${viewName}.php`;
    
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
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'login',
                    username: username,
                    password: password
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                successDiv.textContent = data.message;
                successDiv.classList.remove('hidden');
                
                // Store user data (in a real app, use secure session management)
                localStorage.setItem('user', JSON.stringify(data.user));
                
                // Redirect or update UI after successful login
                setTimeout(() => {
                    showWelcomeMessage(data.user);
                }, 1000);
            } else {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.classList.remove('hidden');
            console.error('Login error:', error);
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
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'register',
                    username: username,
                    email: email,
                    password: password
                })
            });
            
            const data = await response.json();
            
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
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.classList.remove('hidden');
            console.error('Registration error:', error);
        }
    });
}

// Show welcome message after login
function showWelcomeMessage(user) {
    mainContent.innerHTML = `
        <div class="flex justify-center items-center min-h-[calc(100vh-12rem)]">
            <div class="bg-gray-800 shadow-2xl shadow-cyan-500/20 rounded-lg border border-cyan-500/30 p-12 text-center max-w-2xl">
                <h2 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500 mb-4">
                    Welcome, ${user.username}!
                </h2>
                <p class="text-gray-300 text-lg mb-6">
                    You have successfully logged in to the Tournament Management System.
                </p>
                <div class="space-y-2 text-left bg-gray-700 p-6 rounded-md border border-gray-600">
                    <p class="text-gray-400"><span class="text-cyan-400 font-semibold">Username:</span> ${user.username}</p>
                    <p class="text-gray-400"><span class="text-cyan-400 font-semibold">Email:</span> ${user.email}</p>
                    <p class="text-gray-400"><span class="text-cyan-400 font-semibold">User ID:</span> ${user.id}</p>
                </div>
            </div>
        </div>
    `;
}
