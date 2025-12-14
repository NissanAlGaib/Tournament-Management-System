<div class="flex justify-center items-center min-h-[calc(100vh-12rem)] px-4">
    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="relative group">
            <!-- Animated background glow -->
            <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 via-purple-500 to-cyan-500 rounded-2xl opacity-75 group-hover:opacity-100 blur transition duration-500 animate-gradient-x"></div>
            
            <!-- Main card -->
            <div class="relative bg-gray-900 rounded-2xl overflow-hidden">
                <!-- Header with animated gradient -->
                <div class="relative bg-gradient-to-br from-cyan-600 via-purple-600 to-cyan-700 px-8 py-8">
                    <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center mb-2">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-bold text-white text-center tracking-tight">Welcome Back</h2>
                        <p class="text-cyan-100 text-center mt-2 text-sm">Sign in to your account</p>
                    </div>
                </div>
                
                <!-- Form -->
                <div class="px-8 py-8 bg-gray-800">
                    <form id="login-form" class="space-y-5">
                        <!-- Username Field -->
                        <div class="space-y-2">
                            <label for="login-username" class="block text-sm font-semibold text-cyan-300 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Username
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="login-username" 
                                    name="username" 
                                    required
                                    class="w-full px-4 py-3.5 bg-gray-900 border-2 border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all duration-300"
                                    placeholder="Enter your username"
                                >
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-2">
                            <label for="login-password" class="block text-sm font-semibold text-cyan-300 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Password
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="login-password" 
                                    name="password" 
                                    required
                                    class="w-full px-4 py-3.5 bg-gray-900 border-2 border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all duration-300"
                                    placeholder="Enter your password"
                                >
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div id="login-error" class="hidden bg-red-500/10 border-2 border-red-500/50 text-red-400 px-4 py-3 rounded-xl text-sm backdrop-blur-sm">
                        </div>

                        <!-- Success Message -->
                        <div id="login-success" class="hidden bg-green-500/10 border-2 border-green-500/50 text-green-400 px-4 py-3 rounded-xl text-sm backdrop-blur-sm">
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            class="group relative w-full bg-gradient-to-r from-cyan-500 via-purple-500 to-cyan-600 hover:from-cyan-600 hover:via-purple-600 hover:to-cyan-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] mt-6"
                        >
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Sign In
                            </span>
                        </button>
                    </form>

                    <!-- Register Link -->
                    <div class="mt-8 text-center pb-2">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-700"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-gray-800 text-gray-400">New here?</span>
                            </div>
                        </div>
                        <button id="switch-to-register" class="mt-4 inline-flex items-center text-cyan-400 hover:text-cyan-300 font-semibold transition-all duration-200 group">
                            Create an account
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
