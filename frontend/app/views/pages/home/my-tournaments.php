<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-white">My Tournaments</h1>
        <div class="flex gap-2">
            <span id="notificationBadge" class="hidden px-3 py-1 bg-red-500 text-white rounded-full text-sm">
                <span id="notificationCount">0</span> new
            </span>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-6 border-b border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <button onclick="filterMyTournaments('all')" class="tournament-filter-tab active border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600">
                All
            </button>
            <button onclick="filterMyTournaments('upcoming')" class="tournament-filter-tab border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Upcoming
            </button>
            <button onclick="filterMyTournaments('ongoing')" class="tournament-filter-tab border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Ongoing
            </button>
            <button onclick="filterMyTournaments('completed')" class="tournament-filter-tab border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Completed
            </button>
        </nav>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
        <p class="mt-4 text-gray-400">Loading your tournaments...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-white">No tournaments</h3>
        <p class="mt-1 text-sm text-gray-400">You haven't joined any tournaments yet.</p>
        <div class="mt-6">
            <a href="#" onclick="loadPage('tournaments'); return false;" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                Browse Tournaments
            </a>
        </div>
    </div>

    <!-- Tournaments Grid -->
    <div id="tournamentsGrid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Tournament cards will be inserted here -->
    </div>
</div>