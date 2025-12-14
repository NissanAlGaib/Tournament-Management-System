<?php
require_once __DIR__ . '/../../../helpers/path_helper.php';
$pageTitle = 'Tournament Bracket';
require_once __DIR__ . '/../../../includes/header.php';
?>

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

<script>
(function() {
    let currentTournamentId = null;
    let currentTournament = null;
    let currentMatches = [];
    let draggedParticipant = null;

    // Get tournament ID from URL
    function getTournamentId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id') || window.currentTournamentId;
    }

    // Show notification toast
    function showNotification(message, type = 'success') {
        const toast = document.getElementById('notificationToast');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        
        toast.innerHTML = `
            <div class="${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 animate-slide-in">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${type === 'success' ? 
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>' :
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                    }
                </svg>
                <span class="font-medium">${escapeHtml(message)}</span>
            </div>
        `;
        toast.classList.remove('hidden');
        
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    // Load bracket
    async function loadBracket() {
        currentTournamentId = getTournamentId();
        
        if (!currentTournamentId) {
            showNotification('No tournament ID provided', 'error');
            return;
        }

        try {
            const token = localStorage.getItem('auth_token');
            const response = await fetch(`<?php echo getBackendPath('api/tournament_api.php'); ?>?action=tournament-bracket&tournament_id=${currentTournamentId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            const data = await response.json();

            if (data.success) {
                currentTournament = data.tournament;
                currentMatches = data.matches;
                
                document.getElementById('tournamentName').textContent = 'Bracket - ' + (currentTournament.name || 'Tournament');
                
                if (currentMatches.length === 0) {
                    showEmptyState();
                } else {
                    renderBracket();
                }
            } else {
                throw new Error(data.message || 'Failed to load bracket');
            }
        } catch (error) {
            console.error('Error loading bracket:', error);
            showNotification(error.message, 'error');
        } finally {
            document.getElementById('loadingState').classList.add('hidden');
        }
    }

    // Show empty state with generate button
    function showEmptyState() {
        document.getElementById('emptyState').classList.remove('hidden');
        document.getElementById('generateBracketBtn').classList.remove('hidden');
    }

    // Generate bracket
    window.generateBracket = async function() {
        if (!confirm('Generate bracket for this tournament? This cannot be undone.')) {
            return;
        }

        try {
            const token = localStorage.getItem('auth_token');
            const response = await fetch('<?php echo getBackendPath('api/tournament_api.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    action: 'generate-bracket',
                    tournament_id: currentTournamentId
                })
            });
            const data = await response.json();

            if (data.success) {
                showNotification('Bracket generated successfully!', 'success');
                document.getElementById('emptyState').classList.add('hidden');
                document.getElementById('generateBracketBtn').classList.add('hidden');
                loadBracket(); // Reload to show bracket
            } else {
                throw new Error(data.message || 'Failed to generate bracket');
            }
        } catch (error) {
            console.error('Error generating bracket:', error);
            showNotification(error.message, 'error');
        }
    };

    // Render bracket
    function renderBracket() {
        const container = document.getElementById('bracketView');
        document.getElementById('bracketContainer').classList.remove('hidden');
        
        // Group matches by round
        const rounds = {};
        currentMatches.forEach(match => {
            if (!rounds[match.round_number]) {
                rounds[match.round_number] = [];
            }
            rounds[match.round_number].push(match);
        });

        const maxRound = Math.max(...Object.keys(rounds).map(Number));
        
        let html = '<div class="flex items-start">';
        
        for (let roundNum = 1; roundNum <= maxRound; roundNum++) {
            const matches = rounds[roundNum] || [];
            const roundName = getRoundName(roundNum, maxRound);
            
            html += `
                <div class="bracket-round">
                    <h3 class="text-lg font-bold text-cyan-400 mb-4 text-center">${roundName}</h3>
            `;
            
            matches.forEach(match => {
                html += renderMatch(match);
            });
            
            html += '</div>';
        }
        
        html += '</div>';
        container.innerHTML = html;
        
        // Setup drag and drop
        setupDragAndDrop();
    }

    // Render a single match
    function renderMatch(match) {
        const isCompleted = match.match_status === 'completed';
        const isBye = match.match_status === 'bye';
        
        const p1Name = match.participant1_team_name || match.participant1_name || 'TBD';
        const p2Name = match.participant2_team_name || match.participant2_name || 'TBD';
        
        const p1IsWinner = match.winner_id && match.winner_id == match.participant1_id;
        const p2IsWinner = match.winner_id && match.winner_id == match.participant2_id;
        
        return `
            <div class="bracket-match ${isCompleted ? 'completed' : ''} ${isBye ? 'bye' : ''}" data-match-id="${match.id}">
                <div class="match-round-label">Match ${match.match_number}</div>
                <div class="bracket-participant ${p1IsWinner ? 'winner' : ''} ${!match.participant1_id ? 'empty' : ''}"
                     draggable="${match.participant1_id ? 'true' : 'false'}"
                     data-participant-id="${match.participant1_id || ''}"
                     data-match-id="${match.id}">
                    ${escapeHtml(p1Name)}
                </div>
                <div class="vs-divider">VS</div>
                <div class="bracket-participant ${p2IsWinner ? 'winner' : ''} ${!match.participant2_id ? 'empty' : ''}"
                     draggable="${match.participant2_id ? 'true' : 'false'}"
                     data-participant-id="${match.participant2_id || ''}"
                     data-match-id="${match.id}">
                    ${escapeHtml(p2Name)}
                </div>
            </div>
        `;
    }

    // Setup drag and drop
    function setupDragAndDrop() {
        const participants = document.querySelectorAll('.bracket-participant[draggable="true"]');
        
        participants.forEach(participant => {
            participant.addEventListener('dragstart', handleDragStart);
            participant.addEventListener('dragend', handleDragEnd);
            participant.addEventListener('dragover', handleDragOver);
            participant.addEventListener('drop', handleDrop);
        });
    }

    function handleDragStart(e) {
        draggedParticipant = {
            participantId: e.target.dataset.participantId,
            matchId: e.target.dataset.matchId
        };
        e.target.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    }

    function handleDragEnd(e) {
        e.target.classList.remove('dragging');
        document.querySelectorAll('.drop-zone').forEach(el => {
            el.classList.remove('drop-zone');
        });
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        
        // Allow drop anywhere within the same match to set winner
        if (draggedParticipant && e.target.classList.contains('bracket-participant')) {
            const targetMatchId = e.target.closest('.bracket-match')?.dataset.matchId;
            if (targetMatchId === draggedParticipant.matchId) {
                e.target.classList.add('drop-zone');
                e.dataTransfer.dropEffect = 'move';
            }
        }
        
        return false;
    }

    async function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        
        e.target.classList.remove('drop-zone');
        
        if (!draggedParticipant) return false;
        
        const targetMatchId = e.target.closest('.bracket-match')?.dataset.matchId;
        
        // Only allow setting winner within the same match
        if (targetMatchId === draggedParticipant.matchId) {
            await setMatchWinner(draggedParticipant.matchId, draggedParticipant.participantId);
        }
        
        return false;
    }

    // Set match winner
    async function setMatchWinner(matchId, winnerId) {
        try {
            const token = localStorage.getItem('auth_token');
            const response = await fetch('<?php echo getBackendPath('api/tournament_api.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    action: 'set-match-winner',
                    match_id: matchId,
                    winner_id: winnerId
                })
            });
            const data = await response.json();

            if (data.success) {
                showNotification('Winner advanced to next round!', 'success');
                loadBracket(); // Reload bracket to show updates
            } else {
                throw new Error(data.message || 'Failed to set winner');
            }
        } catch (error) {
            console.error('Error setting winner:', error);
            showNotification(error.message, 'error');
        }
    }

    // Get round name
    function getRoundName(roundNum, maxRound) {
        const roundsFromEnd = maxRound - roundNum;
        
        if (roundsFromEnd === 0) return 'Finals';
        if (roundsFromEnd === 1) return 'Semi-Finals';
        if (roundsFromEnd === 2) return 'Quarter-Finals';
        
        return `Round ${roundNum}`;
    }

    // Helper function
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Setup generate button
    document.getElementById('generateBracketBtn')?.addEventListener('click', generateBracket);

    // Initialize
    loadBracket();
})();
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
