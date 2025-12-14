<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament Management System</title>
    <link rel="stylesheet" href="../../src/output.css">
</head>
<body class="bg-gray-900 min-h-screen">
    <!-- Navigation -->
    <nav class="relative bg-gray-800/90 backdrop-blur-md border-b border-cyan-500/50 shadow-lg shadow-cyan-500/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-3">
                    <!-- Logo Icon -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-lg blur opacity-75"></div>
                        <div class="relative bg-gray-900 p-2 rounded-lg border border-cyan-500/50">
                            <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                    </div>
                    <!-- Logo Text -->
                    <div>
                        <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-purple-400 to-cyan-400 tracking-tight">
                            Tournament Manager
                        </h1>
                        <p class="text-xs text-gray-500 font-medium">Compete. Win. Repeat.</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Login Button -->
                    <button id="nav-login" class="group relative px-5 py-2.5 text-cyan-400 hover:text-cyan-300 font-semibold rounded-lg transition-all duration-300 hover:bg-cyan-500/10">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Login
                        </span>
                    </button>
                    <!-- Register Button -->
                    <button id="nav-register" class="relative group">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-lg blur opacity-60 group-hover:opacity-100 transition duration-300"></div>
                        <div class="relative bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-bold px-6 py-2.5 rounded-lg shadow-lg shadow-purple-500/30 transition-all duration-300">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Register
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Dynamic content will be loaded here -->
    </main>

    <script type="module" src="../../src/js/main.js"></script>
</body>
</html>