<div class="flex justify-center items-center min-h-[calc(100vh-12rem)] px-4">
    <div class="w-full max-w-md">
        <!-- Register Card -->
        <div class="relative group">
            <!-- Animated background glow -->
            <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 via-cyan-500 to-purple-500 rounded-2xl opacity-75 group-hover:opacity-100 blur transition duration-500 animate-gradient-x"></div>
            
            <!-- Main card -->
            <div class="relative bg-gray-900 rounded-2xl overflow-hidden">
                <!-- Header with animated gradient -->
                <div class="relative bg-gradient-to-br from-purple-600 via-cyan-600 to-purple-700 px-8 py-8">
                    <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center mb-2">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-bold text-white text-center tracking-tight">Join Us</h2>
                        <p class="text-purple-100 text-center mt-2 text-sm">Create your account to get started</p>
                    </div>
                </div>
                
                <!-- Form -->
                <div class="px-8 py-8 bg-gray-800">
                    <form id="register-form" class="space-y-5">
                        <!-- Username Field -->
                        <div class="space-y-2">
                            <label for="register-username" class="block text-sm font-semibold text-purple-300 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Username
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="register-username" 
                                    name="username" 
                                    required
                                    class="w-full px-4 py-3.5 bg-gray-900 border-2 border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300"
                                    placeholder="Choose a username"
                                >
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="space-y-2">
                            <label for="register-email" class="block text-sm font-semibold text-purple-300 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Email
                            </label>
                            <div class="relative">
                                <input 
                                    type="email" 
                                    id="register-email" 
                                    name="email" 
                                    required
                                    class="w-full px-4 py-3.5 bg-gray-900 border-2 border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300"
                                    placeholder="Enter your email"
                                >
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-2">
                            <label for="register-password" class="block text-sm font-semibold text-purple-300 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Password
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="register-password" 
                                    name="password" 
                                    required
                                    class="w-full px-4 py-3.5 bg-gray-900 border-2 border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all duration-300"
                                    placeholder="Choose a password"
                                >
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div id="register-error" class="hidden bg-red-500/10 border-2 border-red-500/50 text-red-400 px-4 py-3 rounded-xl text-sm backdrop-blur-sm">
                        </div>

                        <!-- Success Message -->
                        <div id="register-success" class="hidden bg-green-500/10 border-2 border-green-500/50 text-green-400 px-4 py-3 rounded-xl text-sm backdrop-blur-sm">
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            class="group relative w-full bg-gradient-to-r from-purple-500 via-cyan-500 to-purple-600 hover:from-purple-600 hover:via-cyan-600 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] mt-6"
                        >
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Create Account
                            </span>
                        </button>
                    </form>

                    <!-- Login Link -->
                    <div class="mt-8 text-center pb-2">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-700"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-gray-800 text-gray-400">Already a member?</span>
                            </div>
                        </div>
                        <button id="switch-to-login" class="mt-4 inline-flex items-center text-purple-400 hover:text-purple-300 font-semibold transition-all duration-200 group">
                            Sign in to your account
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
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
.bg-grid-pattern {
    background-image: 
        linear-gradient(to right, rgba(255,255,255,0.1) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>
