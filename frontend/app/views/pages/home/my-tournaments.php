<?php
require_once __DIR__ . '/../../../includes/path_helper.php';
require_once __DIR__ . '/../../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">My Tournaments</h1>
        <div class="flex gap-2">
            <span id="notificationBadge" class="hidden px-3 py-1 bg-red-500 text-white rounded-full text-sm">
                <span id="notificationCount">0</span> new
            </span>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-6 border-b border-gray-200">
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
        <p class="mt-4 text-gray-600">Loading your tournaments...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No tournaments</h3>
        <p class="mt-1 text-sm text-gray-500">You haven't joined any tournaments yet.</p>
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

<script>
let myTournamentsData = [];
let currentFilter = 'all';

// Load user's tournaments on page load
document.addEventListener('DOMContentLoaded', function() {
    loadMyTournaments();
});

async function loadMyTournaments() {
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const grid = document.getElementById('tournamentsGrid');

    loadingState.classList.remove('hidden');
    emptyState.classList.add('hidden');
    grid.classList.add('hidden');

    try {
        const response = await fetch(TournamentAPI.baseURL + '?action=my-tournaments', {
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error('Failed to load tournaments');
        }

        const data = await response.json();

        if (data.success) {
            myTournamentsData = data.tournaments || [];
            renderMyTournaments();
            loadNotifications();
        } else {
            throw new Error(data.message || 'Failed to load tournaments');
        }
    } catch (error) {
        console.error('Error loading tournaments:', error);
        loadingState.classList.add('hidden');
        emptyState.classList.remove('hidden');
        emptyState.querySelector('p').textContent = 'Error loading tournaments. Please try again.';
    }
}

function renderMyTournaments() {
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const grid = document.getElementById('tournamentsGrid');

    loadingState.classList.add('hidden');

    let filteredTournaments = myTournamentsData;

    // Apply filter
    if (currentFilter !== 'all') {
        filteredTournaments = myTournamentsData.filter(t => {
            if (currentFilter === 'upcoming') return t.status === 'open' || t.status === 'registration_closed';
            if (currentFilter === 'ongoing') return t.status === 'ongoing';
            if (currentFilter === 'completed') return t.status === 'completed';
            return true;
        });
    }

    if (filteredTournaments.length === 0) {
        emptyState.classList.remove('hidden');
        grid.classList.add('hidden');
    } else {
        emptyState.classList.add('hidden');
        grid.classList.remove('hidden');
        grid.innerHTML = filteredTournaments.map(tournament => createMyTournamentCard(tournament)).join('');
    }
}

function createMyTournamentCard(tournament) {
    const statusColors = {
        'draft': 'bg-gray-100 text-gray-800',
        'open': 'bg-green-100 text-green-800',
        'registration_closed': 'bg-yellow-100 text-yellow-800',
        'ongoing': 'bg-blue-100 text-blue-800',
        'completed': 'bg-purple-100 text-purple-800',
        'cancelled': 'bg-red-100 text-red-800'
    };

    const canWithdraw = tournament.status === 'open' || tournament.status === 'registration_closed';
    const isWithdrawn = tournament.registration_status === 'withdrawn';

    return `
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-xl font-semibold text-gray-800">${tournament.name}</h3>
                <span class="px-2 py-1 text-xs font-semibold rounded ${statusColors[tournament.status] || 'bg-gray-100 text-gray-800'}">
                    ${tournament.status.replace('_', ' ').toUpperCase()}
                </span>
            </div>

            <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Starts: ${new Date(tournament.start_date).toLocaleDateString()}</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>${tournament.registered_participants || 0} / ${tournament.max_participants || tournament.tournament_size} Participants</span>
                </div>
                ${tournament.team_name ? `
                <div class="flex items-center text-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Team: ${tournament.team_name}</span>
                </div>
                ` : ''}
                ${isWithdrawn ? `
                <div class="flex items-center text-red-600 font-semibold">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span>Withdrawn</span>
                </div>
                ` : ''}
            </div>

            <div class="flex gap-2">
                <button onclick="viewTournamentDetails(${tournament.id})" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-sm">
                    View Details
                </button>
                ${!isWithdrawn && canWithdraw ? `
                <button onclick="withdrawFromTournament(${tournament.id}, '${tournament.name}')" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors text-sm">
                    Withdraw
                </button>
                ` : ''}
            </div>
        </div>
    `;
}

function filterMyTournaments(filter) {
    currentFilter = filter;

    // Update tab styles
    document.querySelectorAll('.tournament-filter-tab').forEach(tab => {
        tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    event.target.classList.remove('border-transparent', 'text-gray-500');
    event.target.classList.add('active', 'border-blue-500', 'text-blue-600');

    renderMyTournaments();
}

function viewTournamentDetails(tournamentId) {
    // Use AJAX navigation if available
    if (typeof TournamentUI !== 'undefined' && TournamentUI.navigateToDetails) {
        TournamentUI.navigateToDetails(tournamentId);
    } else {
        window.location.href = `tournament-details.php?id=${tournamentId}`;
    }
}

async function withdrawFromTournament(tournamentId, tournamentName) {
    if (!confirm(`Are you sure you want to withdraw from "${tournamentName}"?\n\nThis action cannot be undone.`)) {
        return;
    }

    try {
        const response = await fetch(TournamentAPI.baseURL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                action: 'withdraw',
                tournament_id: tournamentId
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('Successfully withdrawn from tournament');
            loadMyTournaments(); // Reload tournaments
        } else {
            alert('Error: ' + (data.message || 'Failed to withdraw from tournament'));
        }
    } catch (error) {
        console.error('Error withdrawing from tournament:', error);
        alert('Error withdrawing from tournament. Please try again.');
    }
}

async function loadNotifications() {
    try {
        const response = await fetch(TournamentAPI.baseURL + '?action=notifications', {
            credentials: 'include'
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success && data.notifications) {
                const unreadCount = data.notifications.filter(n => !n.is_read).length;
                if (unreadCount > 0) {
                    document.getElementById('notificationBadge').classList.remove('hidden');
                    document.getElementById('notificationCount').textContent = unreadCount;
                }
            }
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
    }
}

// Make baseURL available
if (typeof TournamentAPI === 'undefined') {
    window.TournamentAPI = {
        baseURL: '<?php echo getBackendPath('api/tournament_api.php'); ?>'
    };
}
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
