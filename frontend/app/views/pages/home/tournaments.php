<div class="space-y-6">
    <!-- Tournaments Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-purple-400 to-cyan-400">
                Tournaments
            </h1>
            <p class="text-gray-400 mt-2">Browse and join exciting tournaments</p>
        </div>
        <button class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-xl blur opacity-60 group-hover:opacity-100 transition duration-300"></div>
            <div class="relative bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-bold px-6 py-3 rounded-xl transition-all duration-300 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Tournament
            </div>
        </button>
    </div>

    <!-- Filter Tabs -->
    <div class="flex space-x-2 border-b border-gray-700 pb-4">
        <button class="px-6 py-2.5 bg-gradient-to-r from-cyan-500 to-purple-600 text-white font-semibold rounded-lg shadow-lg shadow-cyan-500/30">
            All Tournaments
        </button>
        <button class="px-6 py-2.5 bg-gray-800 text-gray-400 hover:text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors">
            Active
        </button>
        <button class="px-6 py-2.5 bg-gray-800 text-gray-400 hover:text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors">
            Upcoming
        </button>
        <button class="px-6 py-2.5 bg-gray-800 text-gray-400 hover:text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors">
            Completed
        </button>
    </div>

    <!-- Tournaments Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Tournament Card 1 -->
        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-2xl opacity-50 group-hover:opacity-75 blur transition duration-300"></div>
            <div class="relative bg-gray-800 rounded-2xl border border-cyan-500/30 overflow-hidden">
                <!-- Tournament Banner -->
                <div class="relative h-40 bg-gradient-to-br from-cyan-600 via-purple-600 to-cyan-700 flex items-center justify-center">
                    <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                    <svg class="w-20 h-20 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    <span class="absolute top-4 right-4 px-3 py-1 bg-green-500/90 text-white text-xs font-bold rounded-full">
                        LIVE
                    </span>
                </div>
                
                <!-- Tournament Info -->
                <div class="p-6">
                    <h3 class="text-xl font-bold text-white mb-2">Summer League 2024</h3>
                    <p class="text-gray-400 text-sm mb-4">Battle it out in the ultimate summer tournament championship</p>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center text-sm text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Ends in 5 days
                        </div>
                        <div class="flex items-center text-sm text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            128/256 players
                        </div>
                        <div class="flex items-center text-sm text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Prize: $5,000
                        </div>
                    </div>

                    <button class="w-full bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-bold py-3 rounded-xl transition-all duration-300 transform hover:scale-[1.02]">
                        View Details
                    </button>
                </div>
            </div>
        </div>

        <!-- Tournament Card 2 -->
        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 to-cyan-600 rounded-2xl opacity-50 group-hover:opacity-75 blur transition duration-300"></div>
            <div class="relative bg-gray-800 rounded-2xl border border-purple-500/30 overflow-hidden">
                <!-- Tournament Banner -->
                <div class="relative h-40 bg-gradient-to-br from-purple-600 via-cyan-600 to-purple-700 flex items-center justify-center">
                    <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                    <svg class="w-20 h-20 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span class="absolute top-4 right-4 px-3 py-1 bg-blue-500/90 text-white text-xs font-bold rounded-full">
                        UPCOMING
                    </span>
                </div>
                
                <!-- Tournament Info -->
                <div class="p-6">
                    <h3 class="text-xl font-bold text-white mb-2">Winter Championship</h3>
                    <p class="text-gray-400 text-sm mb-4">The most anticipated tournament of the season</p>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center text-sm text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Starts Dec 20, 2024
                        </div>
                        <div class="flex items-center text-sm text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            64/512 players
                        </div>
                        <div class="flex items-center text-sm text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Prize: $10,000
                        </div>
                    </div>

                    <button class="w-full bg-gradient-to-r from-purple-600 to-cyan-500 hover:from-purple-700 hover:to-cyan-600 text-white font-bold py-3 rounded-xl transition-all duration-300 transform hover:scale-[1.02]">
                        Register Now
                    </button>
                </div>
            </div>
        </div>

        <!-- Tournament Card 3 -->
        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-2xl opacity-50 group-hover:opacity-75 blur transition duration-300"></div>
            <div class="relative bg-gray-800 rounded-2xl border border-cyan-500/30 overflow-hidden">
                <!-- Tournament Banner -->
                <div class="relative h-40 bg-gradient-to-br from-cyan-600 via-purple-600 to-cyan-700 flex items-center justify-center">
                    <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                    <svg class="w-20 h-20 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                    <span class="absolute top-4 right-4 px-3 py-1 bg-yellow-500/90 text-gray-900 text-xs font-bold rounded-full">
                        FEATURED
                    </span>
                </div>
                
                <!-- Tournament Info -->
                <div class="p-6">
                    <h3 class="text-xl font-bold text-white mb-2">Spring Masters</h3>
                    <p class="text-gray-400 text-sm mb-4">Elite tournament for professional players</p>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center text-sm text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Starts Mar 1, 2025
                        </div>
                        <div class="flex items-center text-sm text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            0/128 players
                        </div>
                        <div class="flex items-center text-sm text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Prize: $25,000
                        </div>
                    </div>

                    <button class="w-full bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-bold py-3 rounded-xl transition-all duration-300 transform hover:scale-[1.02]">
                        Early Registration
                    </button>
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
