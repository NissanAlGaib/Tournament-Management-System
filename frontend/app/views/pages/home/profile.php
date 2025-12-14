<?php
require_once __DIR__ . '/../../../helpers/path_helper.php';
$pageTitle = 'Profile';
require_once __DIR__ . '/../../../includes/header.php';
?>
<div class="space-y-6">
    <!-- Profile Header -->
    <div class="relative">
        <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-2xl opacity-50 blur"></div>
        <div class="relative bg-gray-800 rounded-2xl border border-cyan-500/30 overflow-hidden">
            <!-- Cover Image -->
            <div class="h-48 bg-gradient-to-r from-cyan-600 via-purple-600 to-cyan-600 relative">
                <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
            </div>
            
            <!-- Profile Info -->
            <div class="px-8 pb-8">
                <div class="flex flex-col md:flex-row md:items-end md:space-x-6 -mt-16">
                    <!-- Avatar -->
                    <div class="relative group mb-4 md:mb-0">
                        <div class="absolute -inset-1 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-full blur opacity-75 group-hover:opacity-100 transition duration-300"></div>
                        <div class="relative w-32 h-32 bg-gray-900 rounded-full border-4 border-gray-800 flex items-center justify-center">
                            <svg class="w-16 h-16 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex-1">
                        <h1 class="text-3xl font-black text-white mb-1" id="profile-username"></h1>
                        <p class="text-gray-400" id="profile-email"></p>
                        <div class="flex items-center space-x-4 mt-3">
                            <div class="flex items-center text-sm text-gray-300">
                                <svg class="w-4 h-4 mr-1 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                </svg>
                                8 Wins
                            </div>
                            <div class="flex items-center text-sm text-gray-300">
                                <svg class="w-4 h-4 mr-1 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                12 Tournaments
                            </div>
                            <div class="flex items-center text-sm text-gray-300">
                                <svg class="w-4 h-4 mr-1 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                                Level 15
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3 mt-4 md:mt-0">
                        <button class="px-6 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition-colors">
                            Edit Profile
                        </button>
                        <button id="logout-btn" class="px-6 py-2.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 font-semibold rounded-lg transition-colors border border-red-500/50">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Organizer Role Section (for non-organizers) -->
    <div id="organizer-request-section" class="relative group" style="display: none;">
        <div class="absolute -inset-0.5 bg-gradient-to-r from-yellow-500 to-orange-600 rounded-2xl opacity-50 blur"></div>
        <div class="relative bg-gray-800 rounded-2xl border border-yellow-500/30 overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4 border-b border-gray-700">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    Become a Tournament Organizer
                </h2>
            </div>
            <div class="p-6">
                <div class="flex items-start space-x-4 mb-6">
                    <div class="flex-shrink-0 p-3 bg-yellow-500/20 rounded-xl">
                        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white mb-2">Want to create and manage tournaments?</h3>
                        <p class="text-gray-400 mb-4">Request the Organizer role to gain access to tournament creation and management features. An admin will review your request.</p>
                        <ul class="space-y-2 text-gray-300 text-sm mb-4">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Create and manage tournaments
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Organize matches and brackets
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Manage participants and schedules
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="bg-gray-900 rounded-xl p-4 border border-gray-700">
                    <label for="organizer-reason" class="block text-sm font-semibold text-cyan-300 mb-2">
                        Tell us why you want to be an organizer:
                    </label>
                    <textarea 
                        id="organizer-reason" 
                        rows="3" 
                        class="w-full px-4 py-3 bg-gray-800 border-2 border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all resize-none"
                        placeholder="I want to organize tournaments because..."
                    ></textarea>
                    <button 
                        id="request-organizer-btn" 
                        class="mt-4 w-full px-6 py-3 bg-gradient-to-r from-yellow-500 to-orange-600 hover:from-yellow-600 hover:to-orange-700 text-white font-bold rounded-lg shadow-lg shadow-yellow-500/30 transition-all flex items-center justify-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Submit Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Stats -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Stats Card -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-2xl opacity-30 blur"></div>
                <div class="relative bg-gray-800 rounded-2xl p-6 border border-cyan-500/30">
                    <h2 class="text-xl font-bold text-white mb-4">Statistics</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Win Rate</span>
                            <span class="text-white font-bold">67%</span>
                        </div>
                        <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-green-500 to-cyan-600 w-2/3"></div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Total Matches</span>
                            <span class="text-white font-bold">45</span>
                        </div>
                        <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-cyan-500 to-purple-600 w-full"></div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Championships</span>
                            <span class="text-white font-bold">8</span>
                        </div>
                        <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-yellow-500 to-orange-600 w-4/5"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Achievements Card -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 to-cyan-600 rounded-2xl opacity-30 blur"></div>
                <div class="relative bg-gray-800 rounded-2xl p-6 border border-purple-500/30">
                    <h2 class="text-xl font-bold text-white mb-4">Recent Achievements</h2>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3 p-3 bg-gray-900 rounded-lg border border-yellow-500/30">
                            <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <div>
                                <p class="text-white font-semibold text-sm">Champion</p>
                                <p class="text-gray-400 text-xs">Won Summer League</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-gray-900 rounded-lg border border-cyan-500/30">
                            <svg class="w-8 h-8 text-cyan-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <div>
                                <p class="text-white font-semibold text-sm">Top Player</p>
                                <p class="text-gray-400 text-xs">Ranked in top 10</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Match History -->
        <div class="lg:col-span-2">
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-2xl opacity-30 blur"></div>
                <div class="relative bg-gray-800 rounded-2xl border border-cyan-500/30 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4 border-b border-gray-700">
                        <h2 class="text-xl font-bold text-white">Match History</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <!-- Match Item -->
                        <div class="flex items-center justify-between p-4 bg-gray-900 rounded-xl border border-green-500/30">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-green-500/20 rounded-lg">
                                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white font-semibold">Victory vs Team Phoenix</p>
                                    <p class="text-sm text-gray-400">Summer League 2024 - Finals</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-green-400 font-bold">WIN</p>
                                <p class="text-xs text-gray-400">2 hours ago</p>
                            </div>
                        </div>

                        <!-- Match Item -->
                        <div class="flex items-center justify-between p-4 bg-gray-900 rounded-xl border border-green-500/30">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-green-500/20 rounded-lg">
                                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white font-semibold">Victory vs Elite Squad</p>
                                    <p class="text-sm text-gray-400">Summer League 2024 - Semi Finals</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-green-400 font-bold">WIN</p>
                                <p class="text-xs text-gray-400">1 day ago</p>
                            </div>
                        </div>

                        <!-- Match Item -->
                        <div class="flex items-center justify-between p-4 bg-gray-900 rounded-xl border border-red-500/30">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-red-500/20 rounded-lg">
                                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white font-semibold">Defeat vs Thunder Warriors</p>
                                    <p class="text-sm text-gray-400">Summer League 2024 - Quarter Finals</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-red-400 font-bold">LOSS</p>
                                <p class="text-xs text-gray-400">3 days ago</p>
                            </div>
                        </div>

                        <!-- Match Item -->
                        <div class="flex items-center justify-between p-4 bg-gray-900 rounded-xl border border-green-500/30">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-green-500/20 rounded-lg">
                                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white font-semibold">Victory vs Storm Breakers</p>
                                    <p class="text-sm text-gray-400">Summer League 2024 - Round of 16</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-green-400 font-bold">WIN</p>
                                <p class="text-xs text-gray-400">5 days ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-grid-pattern {
    background-image: 
        linear-gradient(to right, rgba(255,255,255,0.1) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>
<?php require_once __DIR__ . "/../../../includes/footer.php"; ?>
