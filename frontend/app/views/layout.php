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
    <nav class="bg-gray-800 border-b border-cyan-500 shadow-lg shadow-cyan-500/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">
                        Tournament Manager
                    </h1>
                </div>
                <div class="flex space-x-4">
                    <button id="nav-login" class="text-cyan-400 hover:text-cyan-300 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Login
                    </button>
                    <button id="nav-register" class="bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow-lg shadow-purple-500/50 transition-all duration-200">
                        Register
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Dynamic content will be loaded here -->
    </main>

    <script src="../../src/js/main.js"></script>
</body>
</html>