<?php
require_once __DIR__ . '/../../../helpers/path_helper.php';
$pageTitle = 'Tournament Details';
require_once __DIR__ . '/../../../includes/header.php';
?>

<div id="tournamentDetailsContainer">
    <!-- Loading State -->
    <div id="loadingDetailsState" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-500"></div>
        <p class="text-gray-400 mt-4">Loading tournament details...</p>
    </div>

    <!-- Tournament Details Content (loaded dynamically) -->
    <div id="tournamentDetailsContent" class="hidden"></div>
</div>

<!-- Join Tournament Modal -->
<div id="joinTournamentModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-800 rounded-2xl border border-cyan-500/30 max-w-md w-full p-6 relative max-h-[90vh] overflow-y-auto">
        <button type="button" id="closeJoinModal" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <h3 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400 mb-4">
            Join Tournament
        </h3>

        <form id="joinTournamentForm">
            <input type="hidden" id="joinTournamentId" name="tournament_id">
            <input type="hidden" id="isTeamBasedTournament" name="is_team_based" value="0">

            <!-- Team-based tournament section -->
            <div id="teamRegistrationSection" class="hidden">
                <div class="mb-4 p-3 bg-purple-900/30 border border-purple-500/30 rounded-lg">
                    <p class="text-sm text-purple-300">
                        👥 This is a team-based tournament. As team captain, you'll register your entire team.
                    </p>
                </div>

                <div class="mb-4">
                    <label for="team_name" class="block text-sm font-medium text-gray-300 mb-2">
                        Team Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="team_name" name="team_name"
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500"
                        placeholder="Enter team name">
                </div>

                <div class="mb-4">
                    <label for="team_tag" class="block text-sm font-medium text-gray-300 mb-2">
                        Team Tag (Optional)
                    </label>
                    <input type="text" id="team_tag" name="team_tag" maxlength="10"
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500"
                        placeholder="e.g., TMS">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Team Members <span class="text-red-400">*</span>
                    </label>
                    <p class="text-xs text-gray-400 mb-3">Enter username for each teammate below:</p>
                    <div id="teamMembersContainer">
                        <!-- Team member inputs will be added here dynamically -->
                    </div>
                    <p id="teamSizeHint" class="text-xs text-cyan-400 mt-2 font-medium"></p>
                </div>
            </div>

            <!-- Solo registration section -->
            <div id="soloRegistrationSection">
                <div class="mb-4 p-3 bg-cyan-900/30 border border-cyan-500/30 rounded-lg">
                    <p class="text-sm text-cyan-300">
                        🏃 You'll be registered as an individual player.
                    </p>
                </div>
            </div>

            <div class="mb-4">
                <label for="registration_notes" class="block text-sm font-medium text-gray-300 mb-2">
                    Notes (Optional):
                </label>
                <textarea id="registration_notes" name="notes" rows="3"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                    placeholder="Any special notes..."></textarea>
            </div>

            <div class="flex space-x-3">
                <button type="button" id="cancelJoinBtn" class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all">
                    Join Now
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Team Invite Modal -->
<div id="teamInviteModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-800 rounded-2xl border border-cyan-500/30 max-w-md w-full p-6 relative">
        <button type="button" id="closeInviteModal" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <h3 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400 mb-4">
            Invite Players to Team
        </h3>

        <form id="teamInviteForm">
            <input type="hidden" id="inviteTeamId" name="team_id">

            <div class="mb-4">
                <label for="invite_username" class="block text-sm font-medium text-gray-300 mb-2">
                    Username to Invite:
                </label>
                <input type="text" id="invite_username" name="username" required
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500"
                    placeholder="Enter username">
            </div>

            <div class="mb-4">
                <label for="invite_role" class="block text-sm font-medium text-gray-300 mb-2">
                    Role:
                </label>
                <select id="invite_role" name="role" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500">
                    <option value="member">Member</option>
                    <option value="co-captain">Co-Captain</option>
                </select>
            </div>

            <div class="flex space-x-3">
                <button type="button" id="cancelInviteBtn" class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all">
                    Send Invite
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        let currentTournament = null;
        let currentUserTeam = null;

        // Get tournament ID from URL or pass it dynamically
        function getTournamentId() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('id') || window.currentTournamentId;
        }

        // Load tournament details
        async function loadTournamentDetails() {
            const tournamentId = getTournamentId();

            if (!tournamentId) {
                showError('No tournament ID provided');
                return;
            }

            try {
                // Get auth token from localStorage
                const token = localStorage.getItem('auth_token');
                const headers = {};
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }

                const response = await fetch(`<?php echo getBackendPath('api/tournament_api.php'); ?>?action=tournament&id=${tournamentId}`, {
                    headers: headers
                });
                const data = await response.json();

                if (data.success && data.tournament) {
                    currentTournament = data.tournament;
                    renderTournamentDetails(data.tournament);
                } else {
                    throw new Error(data.message || 'Failed to load tournament');
                }
            } catch (error) {
                console.error('Error loading tournament:', error);
                showError(error.message);
            }
        }

        // Render tournament details
        function renderTournamentDetails(tournament) {
            document.getElementById('loadingDetailsState').classList.add('hidden');
            const container = document.getElementById('tournamentDetailsContent');
            container.classList.remove('hidden');

            // Check if user is logged in and get their info
            const userDataStr = localStorage.getItem('user');
            const userData = userDataStr ? JSON.parse(userDataStr) : null;
            const userId = userData ? userData.id : null;
            const isOrganizer = userId && tournament.organizer_id == userId;

            const isTeamBased = tournament.is_team_based == 1;
            const spotsRemaining = (tournament.max_participants || tournament.tournament_size) - (tournament.participants_count || 0);
            const canJoin = tournament.status === 'open' && spotsRemaining > 0 && !isOrganizer;

            container.innerHTML = `
            <!-- Back Button -->
            <button onclick="window.history.back()" class="mb-4 flex items-center text-cyan-400 hover:text-cyan-300 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Tournaments
            </button>
            
            <!-- Tournament Header -->
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-2xl border border-cyan-500/30 p-8 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400 mb-2">
                            ${escapeHtml(tournament.name)}
                        </h1>
                        <p class="text-gray-400">Organized by <span class="text-cyan-400">${escapeHtml(tournament.organizer_name || 'Unknown')}</span></p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-4 py-2 rounded-lg text-sm font-semibold ${getStatusBadgeClass(tournament.status)}">
                            ${getStatusText(tournament.status)}
                        </span>
                        ${isTeamBased ? '<span class="px-3 py-1 bg-purple-600 text-white rounded-lg text-xs font-semibold">TEAM-BASED</span>' : ''}
                    </div>
                </div>
                
                ${tournament.description ? `<p class="text-gray-300 mb-4">${escapeHtml(tournament.description)}</p>` : ''}
                
                <!-- Winner Announcement (if completed) -->
                ${tournament.status === 'completed' && (tournament.winner_name || tournament.winner_team_name) ? `
                    <div class="bg-gradient-to-r from-yellow-900/30 to-orange-900/30 border-2 border-yellow-500/50 rounded-xl p-6 mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <svg class="w-16 h-16 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold text-yellow-400 mb-1">🏆 Tournament Winner</h3>
                                <p class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-400">
                                    ${escapeHtml(tournament.winner_team_name || tournament.winner_name)}
                                </p>
                            </div>
                        </div>
                    </div>
                ` : ''}
                
                <!-- Key Info Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-700/50 rounded-lg p-4">
                        <div class="text-gray-400 text-sm mb-1">Game</div>
                        <div class="text-white font-semibold">${escapeHtml(tournament.game_type || 'Not specified')}</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-4">
                        <div class="text-gray-400 text-sm mb-1">Format</div>
                        <div class="text-white font-semibold">${formatTournamentFormat(tournament.format)}</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-4">
                        <div class="text-gray-400 text-sm mb-1">Participants</div>
                        <div class="text-white font-semibold">${tournament.participants_count || 0}/${tournament.max_participants || tournament.tournament_size}</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-4">
                        <div class="text-gray-400 text-sm mb-1">Entry Fee</div>
                        <div class="text-white font-semibold">${tournament.entry_fee > 0 ? '$' + parseFloat(tournament.entry_fee).toFixed(2) : 'Free'}</div>
                    </div>
                </div>
                
                <!-- Action Button -->
                ${canJoin ? `
                    <button id="joinTournamentBtn" class="w-full md:w-auto px-8 py-3 bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-bold rounded-xl transition-all flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m0-3h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Join Tournament
                    </button>
                ` : `
                    <div class="text-gray-400 italic">
                        ${isOrganizer ? 'You cannot join your own tournament' : 
                          tournament.status !== 'open' ? 'Registration is closed' : 'Tournament is full'}
                    </div>
                `}
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Schedule -->
                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Schedule
                        </h2>
                        <div class="space-y-3">
                            ${tournament.registration_deadline ? `
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Registration Deadline:</span>
                                    <span class="text-white font-semibold">${formatDate(tournament.registration_deadline)}</span>
                                </div>
                            ` : ''}
                            ${tournament.start_date ? `
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Start Date:</span>
                                    <span class="text-white font-semibold">${formatDate(tournament.start_date)}</span>
                                </div>
                            ` : ''}
                            ${tournament.end_date ? `
                                <div class="flex justify-between">
                                    <span class="text-gray-400">End Date:</span>
                                    <span class="text-white font-semibold">${formatDate(tournament.end_date)}</span>
                                </div>
                            ` : ''}
                            ${tournament.estimated_duration_hours ? `
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Estimated Duration:</span>
                                    <span class="text-white font-semibold">${tournament.estimated_duration_hours} hours</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <!-- Rules -->
                    ${tournament.rules ? `
                        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                            <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Tournament Rules
                            </h2>
                            <div class="text-gray-300 whitespace-pre-wrap">${escapeHtml(tournament.rules)}</div>
                        </div>
                    ` : ''}
                    
                    ${tournament.match_rules ? `
                        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                            <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Match Rules
                            </h2>
                            <div class="text-gray-300 whitespace-pre-wrap">${escapeHtml(tournament.match_rules)}</div>
                        </div>
                    ` : ''}
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Prize Pool -->
                    ${tournament.prizes && tournament.prizes.length > 0 ? `
                        <div class="bg-gradient-to-br from-yellow-900/30 to-orange-900/30 rounded-xl border border-yellow-500/30 p-6">
                            <h2 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-400 mb-4 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Prize Pool
                            </h2>
                            <div class="space-y-3">
                                ${tournament.prizes.map(prize => `
                                    <div class="bg-gray-800/50 rounded-lg p-4 border border-yellow-500/20">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-yellow-400 font-bold">${getPlacementText(prize.placement)}</span>
                                            <span class="text-white font-bold text-lg">${formatPrizeAmount(prize.prize_amount, prize.currency)}</span>
                                        </div>
                                        ${prize.prize_description ? `<p class="text-gray-400 text-sm">${escapeHtml(prize.prize_description)}</p>` : ''}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Tournament Info -->
                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                        <h2 class="text-xl font-bold text-white mb-4">Tournament Info</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Scoring System:</span>
                                <span class="text-white">${escapeHtml(tournament.scoring_system || 'Standard')}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Visibility:</span>
                                <span class="text-white capitalize">${escapeHtml(tournament.visibility || 'Public')}</span>
                            </div>
                            ${tournament.is_public ? `
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Public:</span>
                                    <span class="text-green-400">Yes</span>
                                </div>
                            ` : ''}
                            <div class="flex justify-between">
                                <span class="text-gray-400">Created:</span>
                                <span class="text-white">${formatDate(tournament.created_at)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

            // Setup join button handler
            const joinBtn = document.getElementById('joinTournamentBtn');
            if (joinBtn) {
                joinBtn.addEventListener('click', () => openJoinModal(tournament, isTeamBased));
            }
        }

        // Helper functions
        function escapeHtml(text) {
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
                'open': 'Open for Registration',
                'registration_closed': 'Registration Closed',
                'ongoing': 'Ongoing',
                'completed': 'Completed',
                'cancelled': 'Cancelled'
            };
            return texts[status] || status;
        }

        function formatTournamentFormat(format) {
            const formats = {
                'single_elimination': 'Single Elimination',
                'double_elimination': 'Double Elimination',
                'round_robin': 'Round Robin',
                'swiss': 'Swiss',
                'custom': 'Custom'
            };
            return formats[format] || format;
        }

        function formatDate(dateString) {
            if (!dateString) return 'TBD';
            const date = new Date(dateString);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getPlacementText(placement) {
            const suffixes = {
                1: 'st',
                2: 'nd',
                3: 'rd'
            };
            const suffix = suffixes[placement] || 'th';
            return `${placement}${suffix} Place`;
        }

        function formatPrizeAmount(amount, currency) {
            const symbols = {
                'USD': '$',
                'EUR': '€',
                'GBP': '£'
            };
            const symbol = symbols[currency] || currency;
            const numAmount = parseFloat(amount);
            if (isNaN(numAmount)) {
                return 'TBD';
            }
            return `${symbol}${numAmount.toFixed(2)}`;
        }

        function showError(message) {
            document.getElementById('loadingDetailsState').classList.add('hidden');
            const container = document.getElementById('tournamentDetailsContent');
            container.classList.remove('hidden');
            container.innerHTML = `
            <div class="text-center py-12">
                <svg class="w-24 h-24 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-xl font-bold text-white mb-2">Error Loading Tournament</h3>
                <p class="text-gray-400">${escapeHtml(message)}</p>
                <button onclick="window.history.back()" class="mt-4 px-6 py-2 bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg transition-colors">
                    Go Back
                </button>
            </div>
        `;
        }

        // Join modal functions
        function openJoinModal(tournament, isTeamBased) {
            const modal = document.getElementById('joinTournamentModal');
            document.getElementById('joinTournamentId').value = tournament.id;
            document.getElementById('isTeamBasedTournament').value = isTeamBased ? '1' : '0';

            // Show/hide sections based on tournament type
            if (isTeamBased) {
                document.getElementById('teamRegistrationSection').classList.remove('hidden');
                document.getElementById('soloRegistrationSection').classList.add('hidden');

                // Set up team member inputs
                const teamSize = tournament.team_size || 5;
                setupTeamMemberInputs(teamSize);
            } else {
                document.getElementById('teamRegistrationSection').classList.add('hidden');
                document.getElementById('soloRegistrationSection').classList.remove('hidden');
            }

            modal.classList.remove('hidden');
        }

        function setupTeamMemberInputs(teamSize) {
            const container = document.getElementById('teamMembersContainer');
            const hint = document.getElementById('teamSizeHint');
            container.innerHTML = '';

            // Create teamSize-1 inputs (captain is already counted)
            for (let i = 0; i < teamSize - 1; i++) {
                const div = document.createElement('div');
                div.className = 'mb-3';

                const label = document.createElement('label');
                label.className = 'block text-xs font-medium text-gray-400 mb-1';
                label.textContent = `Teammate ${i + 1}`;

                const input = document.createElement('input');
                input.type = 'text';
                input.name = `team_member_${i}`;
                input.className = 'w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500';
                input.placeholder = `Enter username for teammate ${i + 1}`;
                input.required = true;

                div.appendChild(label);
                div.appendChild(input);
                container.appendChild(div);
            }

            hint.textContent = `Team needs ${teamSize} players total (you + ${teamSize - 1} teammates)`;
        }

        function setupJoinTypeHandlers() {
            const joinTypeRadios = document.querySelectorAll('input[name="join_type"]');
            joinTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'team') {
                        document.getElementById('teamSelection').classList.remove('hidden');
                        checkTeamSelection();
                    } else {
                        document.getElementById('teamSelection').classList.add('hidden');
                        document.getElementById('newTeamFields').classList.add('hidden');
                    }
                });
            });

            const teamSelect = document.getElementById('team_selection');
            teamSelect.addEventListener('change', checkTeamSelection);
        }

        function checkTeamSelection() {
            const teamSelect = document.getElementById('team_selection');
            if (teamSelect.value === 'new') {
                document.getElementById('newTeamFields').classList.remove('hidden');
            } else {
                document.getElementById('newTeamFields').classList.add('hidden');
            }
        }

        // Close modal handlers
        document.getElementById('closeJoinModal')?.addEventListener('click', () => {
            document.getElementById('joinTournamentModal').classList.add('hidden');
        });

        document.getElementById('cancelJoinBtn')?.addEventListener('click', () => {
            document.getElementById('joinTournamentModal').classList.add('hidden');
        });

        document.getElementById('closeInviteModal')?.addEventListener('click', () => {
            document.getElementById('teamInviteModal').classList.add('hidden');
        });

        document.getElementById('cancelInviteBtn')?.addEventListener('click', () => {
            document.getElementById('teamInviteModal').classList.add('hidden');
        });

        // Join tournament form submission
        document.getElementById('joinTournamentForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const isTeamBased = formData.get('is_team_based') === '1';

            const data = {
                action: 'register',
                tournament_id: formData.get('tournament_id'),
                notes: formData.get('notes')
            };

            if (isTeamBased) {
                // Team-based registration
                data.create_team = true;
                data.team_name = formData.get('team_name');
                data.team_tag = formData.get('team_tag');

                // Collect team member usernames
                const teamMembers = [];
                let i = 0;
                while (formData.has(`team_member_${i}`)) {
                    const username = formData.get(`team_member_${i}`).trim();
                    if (username) {
                        teamMembers.push(username);
                    }
                    i++;
                }
                data.team_members = teamMembers;
            }

            try {
                const token = localStorage.getItem('auth_token');
                const headers = {
                    'Content-Type': 'application/json'
                };
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }

                const response = await fetch('<?php echo getBackendPath('api/tournament_api.php'); ?>', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Successfully joined the tournament!');
                    document.getElementById('joinTournamentModal').classList.add('hidden');
                    loadTournamentDetails(); // Reload to show updated status
                } else {
                    alert('Error: ' + (result.message || 'Failed to join tournament'));
                }
            } catch (error) {
                console.error('Error joining tournament:', error);
                alert('Error joining tournament: ' + error.message);
            }
        });

        // Team invite form submission
        document.getElementById('teamInviteForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                action: 'invite_to_team',
                team_id: formData.get('team_id'),
                username: formData.get('username'),
                role: formData.get('role')
            };

            try {
                const token = localStorage.getItem('auth_token');
                const headers = {
                    'Content-Type': 'application/json'
                };
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }

                const response = await fetch('<?php echo getBackendPath('api/tournament_api.php'); ?>', {
                    method: 'POST',
                    headers: headers,
                    credentials: 'include',
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Invite sent successfully!');
                    document.getElementById('teamInviteModal').classList.add('hidden');
                    this.reset();
                } else {
                    alert('Error: ' + (result.message || 'Failed to send invite'));
                }
            } catch (error) {
                console.error('Error sending invite:', error);
                alert('Error sending invite: ' + error.message);
            }
        });

        // Initialize
        loadTournamentDetails();
    })();
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>