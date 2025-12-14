<!-- Notification Toast -->
<div id="notificationToast" class="hidden fixed top-4 right-4 z-[9999] max-w-md"></div>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <button onclick="window.history.back()" class="mb-2 flex items-center text-cyan-400 hover:text-cyan-300 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </button>
            <h1 id="tournamentName" class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">
                Tournament Bracket
            </h1>
        </div>
        <button id="generateBracketBtn" class="hidden px-6 py-3 bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-bold rounded-xl transition-all">
            Generate Bracket
        </button>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-500"></div>
        <p class="text-gray-400 mt-4">Loading bracket...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-white">No bracket generated</h3>
        <p class="mt-1 text-sm text-gray-400">Click "Generate Bracket" to create the tournament bracket.</p>
    </div>

    <!-- Bracket Container -->
    <div id="bracketContainer" class="hidden">
        <div class="bg-gray-800/50 rounded-xl p-6 overflow-x-auto">
            <div id="bracketView" class="min-w-full">
                <!-- Bracket will be rendered here -->
            </div>
        </div>

        <!-- Instructions -->
        <div class="mt-6 bg-blue-900/30 border border-blue-500/30 rounded-lg p-4">
            <h3 class="text-blue-300 font-semibold mb-2">How to use:</h3>
            <ul class="text-blue-200 text-sm space-y-1">
                <li>• Drag a participant from a match and drop it onto the winner slot to advance them</li>
                <li>• Winners automatically advance to the next round</li>
                <li>• Green matches are completed, gray matches are pending</li>
            </ul>
        </div>
    </div>
</div>

<style>
.bracket-round {
    display: inline-block;
    vertical-align: top;
    margin-right: 40px;
}

.bracket-match {
    background: #1f2937;
    border: 2px solid #374151;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 20px;
    min-width: 250px;
    position: relative;
}

.bracket-match.completed {
    border-color: #10b981;
}

.bracket-match.bye {
    opacity: 0.5;
}

.bracket-participant {
    background: #374151;
    padding: 10px 12px;
    border-radius: 8px;
    margin: 4px 0;
    cursor: grab;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.bracket-participant:hover {
    background: #4b5563;
    border-color: #06b6d4;
}

.bracket-participant.dragging {
    opacity: 0.5;
    cursor: grabbing;
}

.bracket-participant.winner {
    background: #065f46;
    border-color: #10b981;
}

.bracket-participant.empty {
    background: #1f2937;
    border: 2px dashed #4b5563;
    color: #6b7280;
    font-style: italic;
    cursor: default;
}

.bracket-participant.drop-zone {
    border-color: #06b6d4;
    background: #164e63;
}

.match-round-label {
    position: absolute;
    top: -8px;
    left: 12px;
    background: #1f2937;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    color: #9ca3af;
    font-weight: 600;
}

.vs-divider {
    text-align: center;
    color: #6b7280;
    font-size: 12px;
    font-weight: bold;
    margin: 4px 0;
}
</style>
