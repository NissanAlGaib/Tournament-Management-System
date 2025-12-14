<?php
require_once __DIR__ . '/../../helpers/path_helper.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tournament Management System</title>
    <link rel="stylesheet" href="<?php echo getAssetPath('output.css'); ?>">
</head>
<body class="bg-gray-900 min-h-screen">
    <!-- Admin Navigation Bar -->
    <nav class="bg-gray-800/90 backdrop-blur-md border-b border-cyan-500/50 shadow-lg shadow-cyan-500/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-8">
                    <div class="flex items-center space-x-2">
                        <div class="p-2 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-xl font-bold text-white">Admin Panel</h1>
                    </div>
                    <nav class="hidden md:flex space-x-1">
                        <a href="<?php echo getPagePath('admin/dashboard.php'); ?>" class="px-4 py-2 bg-cyan-500/20 text-cyan-400 border border-cyan-500/50 rounded-lg">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Dashboard
                            </span>
                        </a>
                        <a href="<?php echo getPagePath('admin/users.php'); ?>" class="px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-700/50 rounded-lg transition-colors">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                Users
                            </span>
                        </a>
                        <a href="<?php echo getPagePath('admin/role-management.php'); ?>" class="px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-700/50 rounded-lg transition-colors">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Roles
                            </span>
                        </a>
                        <a href="<?php echo getPagePath('admin/activity.php'); ?>" class="px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-700/50 rounded-lg transition-colors">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Activity
                            </span>
                        </a>
                    </nav>
                </div>
                <div class="flex items-center space-x-4">
                    <div id="user-role-badges" class="flex items-center space-x-2"></div>
                    <a href="<?php echo getPagePath('home/index.php'); ?>" class="px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-700/50 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-white mb-2">Admin Dashboard</h1>
            <p class="text-gray-400">Welcome back, <span id="admin-username" class="text-cyan-400"></span></p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-2xl opacity-50 group-hover:opacity-75 blur transition duration-300"></div>
                <div class="relative bg-gray-800 rounded-2xl p-6 border border-cyan-500/30">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-cyan-500/20 rounded-xl">
                            <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-bold text-white" id="total-users">-</p>
                            <p class="text-sm text-gray-400">Total Users</p>
                        </div>
                    </div>
                    <div class="h-1 bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-cyan-500 to-purple-600 w-full"></div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-yellow-500 to-orange-600 rounded-2xl opacity-50 group-hover:opacity-75 blur transition duration-300"></div>
                <div class="relative bg-gray-800 rounded-2xl p-6 border border-yellow-500/30">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-yellow-500/20 rounded-xl">
                            <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-bold text-white" id="pending-requests">-</p>
                            <p class="text-sm text-gray-400">Pending Requests</p>
                        </div>
                    </div>
                    <div class="h-1 bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-yellow-500 to-orange-600 w-2/3"></div>
                    </div>
                </div>
            </div>

            <!-- Active Sessions -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-green-500 to-cyan-600 rounded-2xl opacity-50 group-hover:opacity-75 blur transition duration-300"></div>
                <div class="relative bg-gray-800 rounded-2xl p-6 border border-green-500/30">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-green-500/20 rounded-xl">
                            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-bold text-white" id="active-sessions">-</p>
                            <p class="text-sm text-gray-400">Active Sessions</p>
                        </div>
                    </div>
                    <div class="h-1 bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-green-500 to-cyan-600 w-3/4"></div>
                    </div>
                </div>
            </div>

            <!-- Total Tournaments (Demo) -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl opacity-50 group-hover:opacity-75 blur transition duration-300"></div>
                <div class="relative bg-gray-800 rounded-2xl p-6 border border-purple-500/30">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-purple-500/20 rounded-xl">
                            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-bold text-white">24</p>
                            <p class="text-sm text-gray-400">Tournaments</p>
                        </div>
                    </div>
                    <div class="h-1 bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-pink-600 w-4/5"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Users -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-2xl opacity-30 blur"></div>
                <div class="relative bg-gray-800 rounded-2xl border border-cyan-500/30 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4 border-b border-gray-700 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Recent Users
                        </h2>
                        <a href="<?php echo getPagePath('admin/users.php'); ?>" class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold">View All →</a>
                    </div>
                    <div class="p-6">
                        <div id="recent-users-container" class="space-y-3">
                            <div class="text-center text-gray-400 py-8">
                                <svg class="w-12 h-12 mx-auto mb-3 animate-spin text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <p>Loading recent users...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Activity -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 to-cyan-600 rounded-2xl opacity-30 blur"></div>
                <div class="relative bg-gray-800 rounded-2xl border border-purple-500/30 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4 border-b border-gray-700 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Recent Activity
                        </h2>
                        <a href="<?php echo getPagePath('admin/activity.php'); ?>" class="text-purple-400 hover:text-purple-300 text-sm font-semibold">View All →</a>
                    </div>
                    <div class="p-6">
                        <div id="recent-activity-container" class="space-y-3">
                            <div class="text-center text-gray-400 py-8">
                                <svg class="w-12 h-12 mx-auto mb-3 animate-spin text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <p>Loading recent activity...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script type="module" src="<?php echo getAssetPath('js/admin-dashboard.js'); ?>"></script>
</body>
</html>
