<?php
require_once __DIR__ . '/../../../helpers/path_helper.php';
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../../../includes/header.php';
?>
<div class="space-y-6">
    <!-- Dashboard Header -->
    <div class="relative">
        <div class="bg-gradient-to-r from-cyan-600 via-purple-600 to-cyan-600 rounded-2xl p-8 border border-cyan-500/30">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-black text-white mb-2">Dashboard</h1>
                    <p class="text-cyan-100">Welcome back, <span id="dashboard-username" class="font-bold"></span>!</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-20 h-20 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Tournaments -->
        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-2xl opacity-50 group-hover:opacity-75 blur transition duration-300"></div>
            <div class="relative bg-gray-800 rounded-2xl p-6 border border-cyan-500/30">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-cyan-500/20 rounded-xl">
                        <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p id="stat-total-tournaments" class="text-3xl font-bold text-white">0</p>
                        <p class="text-sm text-gray-400">Total Tournaments</p>
                    </div>
                </div>
                <div class="h-1 bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-cyan-500 to-purple-600 w-3/4"></div>
                </div>
            </div>
        </div>

        <!-- Active Tournaments -->
        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 to-cyan-600 rounded-2xl opacity-50 group-hover:opacity-75 blur transition duration-300"></div>
            <div class="relative bg-gray-800 rounded-2xl p-6 border border-purple-500/30">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-500/20 rounded-xl">
                        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p id="stat-active-tournaments" class="text-3xl font-bold text-white">0</p>
                        <p class="text-sm text-gray-400">Active Now</p>
                    </div>
                </div>
                <div class="h-1 bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-cyan-600 w-1/2"></div>
                </div>
            </div>
        </div>

        <!-- Your Wins -->
        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-2xl opacity-50 group-hover:opacity-75 blur transition duration-300"></div>
            <div class="relative bg-gray-800 rounded-2xl p-6 border border-cyan-500/30">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-500/20 rounded-xl">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p id="stat-championships" class="text-3xl font-bold text-white">0</p>
                        <p class="text-sm text-gray-400">Championships</p>
                    </div>
                </div>
                <div class="h-1 bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-green-500 to-cyan-600 w-2/3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="relative group">
        <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-2xl opacity-30 blur"></div>
        <div class="relative bg-gray-800 rounded-2xl border border-cyan-500/30 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4 border-b border-gray-700">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Recent Activity
                </h2>
            </div>
            <div id="recent-activity-container" class="p-6 space-y-4">
                <!-- Activity Item -->
                <div class="flex items-start space-x-4 p-4 bg-gray-900 rounded-xl border border-gray-700 hover:border-cyan-500/50 transition-colors">
                    <div class="flex-shrink-0 p-2 bg-cyan-500/20 rounded-lg">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-medium">Won Championship in "Summer League 2024"</p>
                        <p class="text-sm text-gray-400 mt-1">2 hours ago</p>
                    </div>
                </div>

                <!-- Activity Item -->
                <div class="flex items-start space-x-4 p-4 bg-gray-900 rounded-xl border border-gray-700 hover:border-cyan-500/50 transition-colors">
                    <div class="flex-shrink-0 p-2 bg-purple-500/20 rounded-lg">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-medium">Registered for "Winter Championship 2025"</p>
                        <p class="text-sm text-gray-400 mt-1">1 day ago</p>
                    </div>
                </div>

                <!-- Activity Item -->
                <div class="flex items-start space-x-4 p-4 bg-gray-900 rounded-xl border border-gray-700 hover:border-cyan-500/50 transition-colors">
                    <div class="flex-shrink-0 p-2 bg-cyan-500/20 rounded-lg">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-medium">Joined team "Elite Gamers"</p>
                        <p class="text-sm text-gray-400 mt-1">3 days ago</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . "/../../../includes/footer.php"; ?>