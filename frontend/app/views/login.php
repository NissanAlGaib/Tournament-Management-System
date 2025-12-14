<div class="flex justify-center items-center min-h-[calc(100vh-12rem)]">
    <div class="w-full max-w-md">
        <div class="bg-gray-800 shadow-2xl shadow-cyan-500/20 rounded-lg border border-cyan-500/30 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-cyan-600 to-purple-600 px-8 py-6">
                <h2 class="text-3xl font-bold text-white text-center">Login</h2>
            </div>
            
            <!-- Form -->
            <div class="px-8 py-6">
                <form id="login-form" class="space-y-6">
                    <!-- Username Field -->
                    <div>
                        <label for="login-username" class="block text-sm font-medium text-cyan-400 mb-2">
                            Username
                        </label>
                        <input 
                            type="text" 
                            id="login-username" 
                            name="username" 
                            required
                            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all duration-200"
                            placeholder="Enter your username"
                        >
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="login-password" class="block text-sm font-medium text-cyan-400 mb-2">
                            Password
                        </label>
                        <input 
                            type="password" 
                            id="login-password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all duration-200"
                            placeholder="Enter your password"
                        >
                    </div>

                    <!-- Error Message -->
                    <div id="login-error" class="hidden bg-red-900/50 border border-red-500 text-red-400 px-4 py-3 rounded-md text-sm">
                    </div>

                    <!-- Success Message -->
                    <div id="login-success" class="hidden bg-green-900/50 border border-green-500 text-green-400 px-4 py-3 rounded-md text-sm">
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-md shadow-lg shadow-purple-500/50 transition-all duration-200 transform hover:scale-[1.02]"
                    >
                        Login
                    </button>
                </form>

                <!-- Register Link -->
                <div class="mt-6 text-center">
                    <p class="text-gray-400 text-sm">
                        Don't have an account? 
                        <button id="switch-to-register" class="text-cyan-400 hover:text-cyan-300 font-medium transition-colors duration-200">
                            Register here
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
