<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-white">My Teams</h1>
        <button onclick="window.history.back()" class="px-4 py-2 text-gray-400 hover:text-white">
            ← Back
        </button>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
        <p class="mt-4 text-gray-400">Loading your teams...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-white">No teams</h3>
        <p class="mt-1 text-sm text-gray-400">You are not a captain of any teams yet.</p>
    </div>

    <!-- Teams List -->
    <div id="teamsContainer" class="hidden space-y-6">
        <!-- Team cards will be inserted here -->
    </div>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4 border border-cyan-500/30">
        <h3 class="text-xl font-semibold mb-4 text-white">Add Team Member</h3>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Username</label>
            <input type="text" id="newMemberUsername" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="Enter username">
        </div>
        <div class="flex gap-2">
            <button onclick="confirmAddMember()" class="flex-1 px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700">
                Add Member
            </button>
            <button onclick="closeAddMemberModal()" class="flex-1 px-4 py-2 bg-gray-700 text-gray-300 rounded hover:bg-gray-600">
                Cancel
            </button>
        </div>
    </div>
</div>