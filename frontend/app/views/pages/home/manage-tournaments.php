<!-- Notification Toast -->
<div id="notificationToast" class="hidden fixed top-4 right-4 z-[9999] max-w-md"></div>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">
            Manage My Tournaments
        </h1>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-500"></div>
        <p class="text-gray-400 mt-4">Loading your tournaments...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-white">No tournaments</h3>
        <p class="mt-1 text-sm text-gray-400">You haven't created any tournaments yet.</p>
    </div>

    <!-- Tournaments List -->
    <div id="tournamentsList" class="hidden space-y-6">
        <!-- Tournament cards will be inserted here -->
    </div>
</div>

<!-- Participants Modal -->
<div id="participantsModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-800 rounded-2xl border border-cyan-500/30 max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b border-gray-700 flex justify-between items-center">
            <h3 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">
                Tournament Participants
            </h3>
            <button type="button" id="closeParticipantsModal" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <div id="participantsContent">
                <!-- Participants will be loaded here -->
            </div>
        </div>
    </div>
</div>