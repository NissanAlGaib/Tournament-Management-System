<?php
require_once __DIR__ . '/../../../helpers/path_helper.php';
$pageTitle = 'Manage Tournaments';
require_once __DIR__ . '/../../../includes/header.php';
?>

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

<script>
(function() {
    let currentTournaments = [];

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

    // Load tournaments
    async function loadTournaments() {
        try {
            const token = localStorage.getItem('auth_token');
            const response = await fetch('<?php echo getBackendPath('api/tournament_api.php'); ?>?action=organized-tournaments', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            const data = await response.json();

            if (data.success) {
                currentTournaments = data.tournaments;
                renderTournaments(data.tournaments);
            } else {
                throw new Error(data.message || 'Failed to load tournaments');
            }
        } catch (error) {
            console.error('Error loading tournaments:', error);
            showNotification(error.message, 'error');
        }
    }

    // Render tournaments
    function renderTournaments(tournaments) {
        document.getElementById('loadingState').classList.add('hidden');
        
        if (tournaments.length === 0) {
            document.getElementById('emptyState').classList.remove('hidden');
            return;
        }

        const container = document.getElementById('tournamentsList');
        container.classList.remove('hidden');
        
        container.innerHTML = tournaments.map(tournament => `
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 hover:border-cyan-500/50 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-white mb-2">${escapeHtml(tournament.name)}</h3>
                        <p class="text-gray-400 text-sm">${escapeHtml(tournament.description || 'No description')}</p>
                    </div>
                    <span class="px-3 py-1 rounded-lg text-sm font-semibold ${getStatusBadgeClass(tournament.status)}">
                        ${getStatusText(tournament.status)}
                    </span>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-gray-400 text-xs mb-1">Confirmed</div>
                        <div class="text-green-400 text-lg font-bold">${tournament.confirmed_count || 0}</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-gray-400 text-xs mb-1">Pending</div>
                        <div class="text-yellow-400 text-lg font-bold">${tournament.pending_count || 0}</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-gray-400 text-xs mb-1">Rejected</div>
                        <div class="text-red-400 text-lg font-bold">${tournament.rejected_count || 0}</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-gray-400 text-xs mb-1">Max Participants</div>
                        <div class="text-white text-lg font-bold">${tournament.max_participants || tournament.tournament_size}</div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="viewParticipants(${tournament.id})" 
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Manage Participants
                    </button>
                    <button onclick="viewBracket(${tournament.id})" 
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        View Bracket
                    </button>
                    ${tournament.is_team_based == 1 ? `
                    <button onclick="viewTeams(${tournament.id})" 
                            class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        View Teams
                    </button>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }

    // View bracket
    window.viewBracket = function(tournamentId) {
        window.viewTournamentBracket(tournamentId);
    };

    // View participants
    window.viewParticipants = async function(tournamentId) {
        try {
            const token = localStorage.getItem('auth_token');
            const response = await fetch(`<?php echo getBackendPath('api/tournament_api.php'); ?>?action=tournament-participants&tournament_id=${tournamentId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            const data = await response.json();

            if (data.success) {
                showParticipantsModal(data.participants, tournamentId);
            } else {
                throw new Error(data.message || 'Failed to load participants');
            }
        } catch (error) {
            console.error('Error loading participants:', error);
            showNotification(error.message, 'error');
        }
    };

    // Show participants modal
    function showParticipantsModal(participants, tournamentId) {
        const modal = document.getElementById('participantsModal');
        const content = document.getElementById('participantsContent');
        
        if (participants.length === 0) {
            content.innerHTML = `
                <div class="text-center py-8 text-gray-400">
                    No participants yet
                </div>
            `;
        } else {
            content.innerHTML = `
                <div class="space-y-3">
                    ${participants.map(participant => `
                        <div class="bg-gray-700/50 rounded-lg p-4 flex justify-between items-center">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <div>
                                        <div class="text-white font-semibold">${escapeHtml(participant.username)}</div>
                                        <div class="text-gray-400 text-sm">${escapeHtml(participant.email)}</div>
                                        ${participant.team_name ? `<div class="text-purple-400 text-sm">Team: ${escapeHtml(participant.team_name)}</div>` : ''}
                                    </div>
                                </div>
                                ${participant.registration_notes ? `
                                    <div class="mt-2 text-gray-400 text-sm">
                                        <strong>Notes:</strong> ${escapeHtml(participant.registration_notes)}
                                    </div>
                                ` : ''}
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 rounded-lg text-sm font-semibold ${getRegistrationBadgeClass(participant.registration_status)}">
                                    ${participant.registration_status}
                                </span>
                                ${participant.registration_status === 'pending' ? `
                                    <button onclick="approveParticipant(${participant.id}, ${tournamentId})" 
                                            class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors">
                                        Approve
                                    </button>
                                    <button onclick="rejectParticipant(${participant.id}, ${tournamentId})" 
                                            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition-colors">
                                        Reject
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        modal.classList.remove('hidden');
    }

    // Approve participant
    window.approveParticipant = async function(participantId, tournamentId) {
        if (!confirm('Are you sure you want to approve this participant?')) {
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
                    action: 'approve-participant',
                    participant_id: participantId
                })
            });
            const data = await response.json();

            if (data.success) {
                showNotification('Participant approved successfully!', 'success');
                viewParticipants(tournamentId); // Reload participants
                loadTournaments(); // Reload tournament counts
            } else {
                throw new Error(data.message || 'Failed to approve participant');
            }
        } catch (error) {
            console.error('Error approving participant:', error);
            showNotification(error.message, 'error');
        }
    };

    // Reject participant
    window.rejectParticipant = async function(participantId, tournamentId) {
        if (!confirm('Are you sure you want to reject this participant?')) {
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
                    action: 'reject-participant',
                    participant_id: participantId
                })
            });
            const data = await response.json();

            if (data.success) {
                showNotification('Participant rejected successfully!', 'success');
                viewParticipants(tournamentId); // Reload participants
                loadTournaments(); // Reload tournament counts
            } else {
                throw new Error(data.message || 'Failed to reject participant');
            }
        } catch (error) {
            console.error('Error rejecting participant:', error);
            showNotification(error.message, 'error');
        }
    };

    // View teams
    window.viewTeams = async function(tournamentId) {
        try {
            const token = localStorage.getItem('auth_token');
            const response = await fetch(`<?php echo getBackendPath('api/tournament_api.php'); ?>?action=tournament-teams&tournament_id=${tournamentId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            const data = await response.json();

            if (data.success) {
                showTeamsModal(data.teams);
            } else {
                throw new Error(data.message || 'Failed to load teams');
            }
        } catch (error) {
            console.error('Error loading teams:', error);
            showNotification(error.message, 'error');
        }
    };

    // Show teams modal
    function showTeamsModal(teams) {
        const modal = document.getElementById('participantsModal');
        const content = document.getElementById('participantsContent');
        
        if (teams.length === 0) {
            content.innerHTML = `
                <div class="text-center py-8 text-gray-400">
                    No teams registered yet
                </div>
            `;
        } else {
            content.innerHTML = `
                <div class="space-y-3">
                    ${teams.map(team => `
                        <div class="bg-gray-700/50 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="text-white font-bold text-lg">${escapeHtml(team.team_name)}
                                        ${team.team_tag ? `<span class="text-purple-400 text-sm ml-2">[${escapeHtml(team.team_tag)}]</span>` : ''}
                                    </div>
                                    <div class="text-gray-400 text-sm">Captain: ${escapeHtml(team.captain_name)}</div>
                                </div>
                                <span class="px-3 py-1 rounded-lg text-sm font-semibold ${team.team_status === 'active' ? 'bg-green-600 text-white' : 'bg-gray-600 text-white'}">
                                    ${team.team_status}
                                </span>
                            </div>
                            <div class="text-cyan-400 text-sm">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                ${team.member_count} members
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        modal.classList.remove('hidden');
    }

    // Helper functions
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getStatusBadgeClass(status) {
        const classes = {
            'draft': 'bg-gray-600 text-white',
            'open': 'bg-green-600 text-white',
            'registration_closed': 'bg-yellow-600 text-white',
            'ongoing': 'bg-blue-600 text-white',
            'completed': 'bg-purple-600 text-white',
            'cancelled': 'bg-red-600 text-white'
        };
        return classes[status] || 'bg-gray-600 text-white';
    }

    function getStatusText(status) {
        const texts = {
            'draft': 'Draft',
            'open': 'Open',
            'registration_closed': 'Closed',
            'ongoing': 'Ongoing',
            'completed': 'Completed',
            'cancelled': 'Cancelled'
        };
        return texts[status] || status;
    }

    function getRegistrationBadgeClass(status) {
        const classes = {
            'pending': 'bg-yellow-600 text-white',
            'confirmed': 'bg-green-600 text-white',
            'rejected': 'bg-red-600 text-white',
            'withdrawn': 'bg-gray-600 text-white',
            'waitlist': 'bg-blue-600 text-white'
        };
        return classes[status] || 'bg-gray-600 text-white';
    }

    function showError(message) {
        document.getElementById('loadingState').classList.add('hidden');
        showNotification(message, 'error');
    }

    // Close modal
    document.getElementById('closeParticipantsModal')?.addEventListener('click', () => {
        document.getElementById('participantsModal').classList.add('hidden');
    });

    // Initialize
    loadTournaments();
})();
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
