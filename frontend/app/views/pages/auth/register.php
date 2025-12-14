<div class="flex justify-center items-center min-h-[calc(100vh-12rem)]">
    <div class="w-full max-w-md">
        <div class="bg-gray-800 shadow-2xl shadow-purple-500/20 rounded-lg border border-purple-500/30 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-cyan-600 px-8 py-6">
                <h2 class="text-3xl font-bold text-white text-center">Register</h2>
            </div>
            
            <!-- Form -->
            <div class="px-8 py-6">
                <form id="register-form" class="space-y-6">
                    <!-- Username Field -->
                    <div>
                        <label for="register-username" class="block text-sm font-medium text-purple-400 mb-2">
                            Username
                        </label>
                        <input 
                            type="text" 
                            id="register-username" 
                            name="username" 
                            required
                            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            placeholder="Choose a username"
                        >
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="register-email" class="block text-sm font-medium text-purple-400 mb-2">
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="register-email" 
                            name="email" 
                            required
                            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            placeholder="Enter your email"
                        >
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="register-password" class="block text-sm font-medium text-purple-400 mb-2">
                            Password
                        </label>
                        <input 
                            type="password" 
                            id="register-password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            placeholder="Choose a password"
                        >
                    </div>

                    <!-- Error Message -->
                    <div id="register-error" class="hidden bg-red-900/50 border border-red-500 text-red-400 px-4 py-3 rounded-md text-sm">
                    </div>

                    <!-- Success Message -->
                    <div id="register-success" class="hidden bg-green-900/50 border border-green-500 text-green-400 px-4 py-3 rounded-md text-sm">
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-purple-600 to-cyan-500 hover:from-purple-700 hover:to-cyan-600 text-white font-semibold py-3 px-4 rounded-md shadow-lg shadow-cyan-500/50 transition-all duration-200 transform hover:scale-[1.02]"
                    >
                        Register
                    </button>
                </form>

                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-gray-400 text-sm">
                        Already have an account? 
                        <button id="switch-to-login" class="text-purple-400 hover:text-purple-300 font-medium transition-colors duration-200">
                            Login here
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
