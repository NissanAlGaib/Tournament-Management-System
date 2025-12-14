<?php
require_once __DIR__ . '/../../../helpers/path_helper.php';
?>
<div class="space-y-6">
    <!-- Tournaments Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-purple-400 to-cyan-400">
                Tournaments
            </h1>
            <p class="text-gray-400 mt-2">Browse and join exciting tournaments</p>
        </div>
        <button id="createTournamentBtn" data-roles="Organizer,Admin" class="relative group hidden">
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
        <button class="filter-tab active px-6 py-2.5 bg-gradient-to-r from-cyan-500 to-purple-600 text-white font-semibold rounded-lg shadow-lg shadow-cyan-500/30" data-status="">
            All Tournaments
        </button>
        <button class="filter-tab px-6 py-2.5 bg-gray-800 text-gray-400 hover:text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors" data-status="open">
            Open
        </button>
        <button class="filter-tab px-6 py-2.5 bg-gray-800 text-gray-400 hover:text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors" data-status="ongoing">
            Ongoing
        </button>
        <button class="filter-tab px-6 py-2.5 bg-gray-800 text-gray-400 hover:text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors" data-status="completed">
            Completed
        </button>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-500"></div>
        <p class="text-gray-400 mt-4">Loading tournaments...</p>
    </div>

    <!-- Tournaments Grid -->
    <div id="tournamentsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
        <!-- Tournament cards will be inserted here dynamically -->
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="text-center py-12 hidden">
        <svg class="w-24 h-24 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
        </svg>
        <h3 class="text-xl font-bold text-white mb-2">No tournaments found</h3>
        <p class="text-gray-400">Check back later for new tournaments</p>
    </div>
</div>

<style>
    .bg-grid-pattern {
        background-image:
            linear-gradient(to right, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
        background-size: 20px 20px;
    }

    .filter-tab.active {
        background: linear-gradient(to right, #06b6d4, #9333ea);
        color: white;
        box-shadow: 0 10px 15px -3px rgba(6, 182, 212, 0.3);
    }

    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }
</style>

<script>
    console.log('=== TOURNAMENT PAGE LOADED ===');
    console.log('Script execution test');
</script>
<script src="<?php echo getAssetPath('js/tournament.js'); ?>"></script>
<script>
    console.log('=== TOURNAMENT INITIALIZATION STARTING ===');
    (function() {
        console.log('Inline script started');
        console.log('TournamentAPI defined?', typeof TournamentAPI !== 'undefined');
        console.log('TournamentUI defined?', typeof TournamentUI !== 'undefined');

        // Wait for tournament.js to load if it hasn't yet
        function initTournamentPage() {
            if (typeof TournamentAPI === 'undefined' || typeof TournamentUI === 'undefined') {
                console.log('Waiting for tournament.js to load...');
                setTimeout(initTournamentPage, 100);
                return;
            }

            console.log('Tournament scripts loaded successfully');

            let currentFilter = '';

            // Set the base API path dynamically
            TournamentAPI.baseURL = '<?php echo getBackendPath('api/tournament_api.php'); ?>';
            console.log('Tournament API URL:', TournamentAPI.baseURL);

            // Load tournaments immediately (DOM is already loaded since this is AJAX-loaded content)
            console.log('Loading tournaments...');
            loadTournaments();
            setupFilterButtons();
            setupCreateButton();

            // Setup filter buttons
            function setupFilterButtons() {
                document.querySelectorAll('.filter-tab').forEach(button => {
                    button.addEventListener('click', function() {
                        // Update active state
                        document.querySelectorAll('.filter-tab').forEach(btn => {
                            btn.classList.remove('active', 'bg-gradient-to-r', 'from-cyan-500', 'to-purple-600', 'text-white', 'shadow-lg', 'shadow-cyan-500/30');
                            btn.classList.add('bg-gray-800', 'text-gray-400');
                        });

                        this.classList.remove('bg-gray-800', 'text-gray-400');
                        this.classList.add('active', 'bg-gradient-to-r', 'from-cyan-500', 'to-purple-600', 'text-white', 'shadow-lg', 'shadow-cyan-500/30');

                        // Load tournaments with filter
                        currentFilter = this.dataset.status;
                        loadTournaments(currentFilter);
                    });
                });
            }

            // Setup create tournament button
            function setupCreateButton() {
                const createBtn = document.getElementById('createTournamentBtn');
                if (createBtn) {
                    createBtn.addEventListener('click', function() {
                        window.location.href = '/frontend/app/views/pages/home/create-tournament.php';
                    });
                }
            }

            // Load and display tournaments
            async function loadTournaments(status = null) {
                const loadingState = document.getElementById('loadingState');
                const tournamentsGrid = document.getElementById('tournamentsGrid');
                const emptyState = document.getElementById('emptyState');

                // Show loading
                loadingState.classList.remove('hidden');
                tournamentsGrid.classList.add('hidden');
                emptyState.classList.add('hidden');

                try {
                    console.log('Fetching tournaments with status:', status);
                    const result = await TournamentAPI.getTournaments(status);
                    console.log('Tournament API response:', result);

                    if (result.success && result.tournaments && result.tournaments.length > 0) {
                        // Render tournaments
                        console.log('Rendering', result.tournaments.length, 'tournaments');
                        tournamentsGrid.innerHTML = result.tournaments.map(tournament =>
                            TournamentUI.renderTournamentCard(tournament)
                        ).join('');

                        loadingState.classList.add('hidden');
                        tournamentsGrid.classList.remove('hidden');
                    } else {
                        // Show empty state
                        console.log('No tournaments found, showing empty state');
                        loadingState.classList.add('hidden');
                        emptyState.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Error loading tournaments:', error);
                    loadingState.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                }
            }

            // Make functions available globally
            window.TournamentUI = TournamentUI;
            window.loadTournaments = loadTournaments;
        }

        // Start initialization
        initTournamentPage();
    })();
</script>