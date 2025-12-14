<?php
require_once __DIR__ . '/../../../includes/path_helper.php';
require_once __DIR__ . '/../../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">My Teams</h1>
        <button onclick="window.history.back()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
            ← Back
        </button>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading your teams...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No teams</h3>
        <p class="mt-1 text-sm text-gray-500">You are not a captain of any teams yet.</p>
    </div>

    <!-- Teams List -->
    <div id="teamsContainer" class="hidden space-y-6">
        <!-- Team cards will be inserted here -->
    </div>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold mb-4">Add Team Member</h3>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
            <input type="text" id="newMemberUsername" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter username">
        </div>
        <div class="flex gap-2">
            <button onclick="confirmAddMember()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Add Member
            </button>
            <button onclick="closeAddMemberModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
let currentTeamId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadMyTeams();
});

async function loadMyTeams() {
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const container = document.getElementById('teamsContainer');

    loadingState.classList.remove('hidden');
    emptyState.classList.add('hidden');
    container.classList.add('hidden');

    try {
        const response = await fetch(TournamentAPI.baseURL + '?action=my-teams', {
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error('Failed to load teams');
        }

        const data = await response.json();

        if (data.success) {
            const teams = data.teams || [];
            loadingState.classList.add('hidden');

            if (teams.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                container.classList.remove('hidden');
                renderTeams(teams);
            }
        } else {
            throw new Error(data.message || 'Failed to load teams');
        }
    } catch (error) {
        console.error('Error loading teams:', error);
        loadingState.classList.add('hidden');
        emptyState.classList.remove('hidden');
        emptyState.querySelector('p').textContent = 'Error loading teams. Please try again.';
    }
}

async function renderTeams(teams) {
    const container = document.getElementById('teamsContainer');
    
    const teamCards = await Promise.all(teams.map(async team => {
        // Load team members
        const membersResponse = await fetch(TournamentAPI.baseURL + `?action=team-members&team_id=${team.id}`, {
            credentials: 'include'
        });
        
        let members = [];
        if (membersResponse.ok) {
            const membersData = await membersResponse.json();
            if (membersData.success) {
                members = membersData.members || [];
            }
        }

        return createTeamCard(team, members);
    }));

    container.innerHTML = teamCards.join('');
}

function createTeamCard(team, members) {
    const statusColors = {
        'active': 'bg-green-100 text-green-800',
        'disbanded': 'bg-gray-100 text-gray-800',
        'disqualified': 'bg-red-100 text-red-800'
    };

    return `
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800">${team.team_name}</h3>
                    ${team.team_tag ? `<span class="text-sm text-gray-500">[${team.team_tag}]</span>` : ''}
                    <p class="text-sm text-gray-600 mt-1">Tournament: ${team.tournament_name}</p>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded ${statusColors[team.team_status] || 'bg-gray-100 text-gray-800'}">
                    ${team.team_status.toUpperCase()}
                </span>
            </div>

            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="text-sm font-semibold text-gray-700">Team Members (${members.length})</h4>
                    ${team.team_status === 'active' ? `
                    <button onclick="openAddMemberModal(${team.id})" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                        Add Member
                    </button>
                    ` : ''}
                </div>
                <div class="space-y-2">
                    ${members.length > 0 ? members.map(member => `
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <div class="flex items-center">
                                <span class="font-medium">${member.username}</span>
                                ${member.role === 'captain' ? '<span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 text-xs rounded">Captain</span>' : ''}
                                ${member.role === 'co_captain' ? '<span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded">Co-Captain</span>' : ''}
                            </div>
                            ${member.role !== 'captain' && team.team_status === 'active' ? `
                            <button onclick="removeMember(${team.id}, ${member.id}, '${member.username}')" class="text-red-600 hover:text-red-800 text-sm">
                                Remove
                            </button>
                            ` : ''}
                        </div>
                    `).join('') : '<p class="text-sm text-gray-500 italic">No members yet</p>'}
                </div>
            </div>
        </div>
    `;
}

function openAddMemberModal(teamId) {
    currentTeamId = teamId;
    document.getElementById('addMemberModal').classList.remove('hidden');
    document.getElementById('newMemberUsername').value = '';
}

function closeAddMemberModal() {
    document.getElementById('addMemberModal').classList.add('hidden');
    currentTeamId = null;
}

async function confirmAddMember() {
    const username = document.getElementById('newMemberUsername').value.trim();

    if (!username) {
        alert('Please enter a username');
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
                action: 'add-team-member',
                team_id: currentTeamId,
                username: username
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('Member added successfully');
            closeAddMemberModal();
            loadMyTeams(); // Reload teams
        } else {
            alert('Error: ' + (data.message || 'Failed to add member'));
        }
    } catch (error) {
        console.error('Error adding member:', error);
        alert('Error adding member. Please try again.');
    }
}

async function removeMember(teamId, memberId, username) {
    if (!confirm(`Are you sure you want to remove ${username} from the team?`)) {
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
                action: 'remove-team-member',
                team_id: teamId,
                member_id: memberId
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('Member removed successfully');
            loadMyTeams(); // Reload teams
        } else {
            alert('Error: ' + (data.message || 'Failed to remove member'));
        }
    } catch (error) {
        console.error('Error removing member:', error);
        alert('Error removing member. Please try again.');
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
