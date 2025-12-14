<!-- Create Tournament Modal -->
<div id="createTournamentModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-800 rounded-2xl border border-cyan-500/30 max-w-2xl w-full max-h-[90vh] overflow-y-auto relative">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-gray-800 border-b border-gray-700 p-6 flex items-center justify-between z-10">
            <div>
                <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">
                    Create Tournament
                </h2>
                <p class="text-sm text-gray-400 mt-1">Fill in the details to create your tournament</p>
            </div>
            <button type="button" id="closeModalBtn" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="createTournamentForm" class="p-6">
            <!-- Step Indicator -->
            <div class="flex justify-between mb-6">
                <div class="step-indicator flex-1 text-center">
                    <div class="step-number active w-8 h-8 rounded-full bg-gradient-to-r from-cyan-500 to-purple-600 text-white flex items-center justify-center mx-auto mb-2 text-sm font-semibold">1</div>
                    <div class="text-xs text-gray-400">Basic Info</div>
                </div>
                <div class="step-line flex-1 flex items-center px-2">
                    <div class="h-0.5 bg-gray-700 w-full"></div>
                </div>
                <div class="step-indicator flex-1 text-center">
                    <div class="step-number w-8 h-8 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center mx-auto mb-2 text-sm font-semibold">2</div>
                    <div class="text-xs text-gray-400">Configuration</div>
                </div>
                <div class="step-line flex-1 flex items-center px-2">
                    <div class="h-0.5 bg-gray-700 w-full"></div>
                </div>
                <div class="step-indicator flex-1 text-center">
                    <div class="step-number w-8 h-8 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center mx-auto mb-2 text-sm font-semibold">3</div>
                    <div class="text-xs text-gray-400">Schedule</div>
                </div>
            </div>

            <!-- Step 1: Basic Information -->
            <div id="step1" class="step-content space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                        Tournament Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="name" name="name" required
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                        placeholder="Enter tournament name">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="game_type" class="block text-sm font-medium text-gray-300 mb-2">
                            Game Type
                        </label>
                        <input type="text" id="game_type" name="game_type"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                            placeholder="e.g., Chess, Valorant">
                    </div>

                    <div>
                        <label for="format" class="block text-sm font-medium text-gray-300 mb-2">
                            Format <span class="text-red-400">*</span>
                        </label>
                        <select id="format" name="format" required
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                            <option value="single_elimination">Single Elimination</option>
                            <option value="double_elimination">Double Elimination</option>
                            <option value="round_robin">Round Robin</option>
                            <option value="swiss">Swiss</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                        placeholder="Describe your tournament..."></textarea>
                </div>
            </div>

            <!-- Step 2: Configuration -->
            <div id="step2" class="step-content space-y-4 hidden">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="tournament_size" class="block text-sm font-medium text-gray-300 mb-2">
                            Tournament Size <span class="text-red-400">*</span>
                        </label>
                        <select id="tournament_size" name="tournament_size" required
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                            <option value="8">8 Players</option>
                            <option value="16" selected>16 Players</option>
                            <option value="32">32 Players</option>
                            <option value="64">64 Players</option>
                        </select>
                    </div>

                    <div>
                        <label for="scoring_system" class="block text-sm font-medium text-gray-300 mb-2">
                            Scoring System
                        </label>
                        <select id="scoring_system" name="scoring_system"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                            <option value="best_of_1">Best of 1</option>
                            <option value="best_of_3" selected>Best of 3</option>
                            <option value="best_of_5">Best of 5</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="entry_fee" class="block text-sm font-medium text-gray-300 mb-2">
                            Entry Fee ($)
                        </label>
                        <input type="number" id="entry_fee" name="entry_fee" min="0" step="0.01" value="0"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                            placeholder="0.00">
                    </div>

                    <div>
                        <label for="visibility" class="block text-sm font-medium text-gray-300 mb-2">
                            Visibility
                        </label>
                        <select id="visibility" name="visibility"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                            <option value="public" selected>Public</option>
                            <option value="private">Private</option>
                            <option value="invite_only">Invite Only</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="rules" class="block text-sm font-medium text-gray-300 mb-2">
                        Tournament Rules (Optional)
                    </label>
                    <textarea id="rules" name="rules" rows="3"
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                        placeholder="General tournament rules..."></textarea>
                </div>
            </div>

            <!-- Step 3: Schedule & Prizes -->
            <div id="step3" class="step-content space-y-4 hidden">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="registration_deadline" class="block text-sm font-medium text-gray-300 mb-2">
                            Registration Deadline <span class="text-red-400">*</span>
                        </label>
                        <input type="datetime-local" id="registration_deadline" name="registration_deadline" required
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-300 mb-2">
                            Start Date <span class="text-red-400">*</span>
                        </label>
                        <input type="datetime-local" id="start_date" name="start_date" required
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Prizes (Optional)
                    </label>
                    <div id="prizesContainer" class="space-y-2">
                        <div class="prize-entry grid grid-cols-3 gap-2">
                            <input type="number" name="prizes[0][placement]" value="1" min="1" placeholder="Place"
                                class="bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-cyan-500 text-sm">
                            <input type="number" name="prizes[0][amount]" step="0.01" min="0" placeholder="Amount ($)"
                                class="bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-cyan-500 text-sm">
                            <input type="text" name="prizes[0][description]" placeholder="Description"
                                class="bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-cyan-500 text-sm">
                        </div>
                    </div>
                    <button type="button" id="addPrizeBtn"
                        class="mt-2 text-sm px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-cyan-400 rounded-lg transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Prize
                    </button>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-700">
                <button type="button" id="prevStepBtn" class="hidden px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors text-sm">
                    ← Previous
                </button>
                <div class="flex-1"></div>
                <div class="flex space-x-3">
                    <button type="button" id="nextStepBtn"
                        class="px-6 py-2 bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all text-sm">
                        Next →
                    </button>
                    <button type="submit" id="submitBtn" class="hidden px-6 py-2 bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all text-sm">
                        Create Tournament
                    </button>
                </div>
            </div>
        </form>

        <!-- Loading Overlay -->
        <div id="modalLoadingOverlay" class="hidden absolute inset-0 bg-gray-900 bg-opacity-75 rounded-2xl flex items-center justify-center z-20">
            <div class="text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-500 mb-4"></div>
                <p class="text-white text-lg">Creating tournament...</p>
            </div>
        </div>
    </div>
</div>

<script>
    console.log('=== CREATE TOURNAMENT MODAL SCRIPT LOADING ===');

    let currentStep = 1;
    const totalSteps = 3;
    let prizeCount = 1;
    let initialized = false;

    // Define the modal opening function globally first
    window.openCreateTournamentModal = function() {
        console.log('Modal opener function called');
        const modal = document.getElementById('createTournamentModal');
        if (modal) {
            modal.classList.remove('hidden');
            currentStep = 1;
            // Initialize modal functionality if not already done
            if (!initialized) {
                initializeModal();
            }
            showStep(1);
        } else {
            console.error('Create tournament modal element not found');
        }
    };
    console.log('Modal function defined:', typeof window.openCreateTournamentModal);

    function initializeModal() {
        console.log('Initializing modal...');
        const modal = document.getElementById('createTournamentModal');
        const closeBtn = document.getElementById('closeModalBtn');
        const form = document.getElementById('createTournamentForm');
        const nextBtn = document.getElementById('nextStepBtn');
        const prevBtn = document.getElementById('prevStepBtn');
        const submitBtn = document.getElementById('submitBtn');

        if (!nextBtn || !prevBtn) {
            console.error('Button elements not found!');
            return;
        }

        // Close modal
        function closeModal() {
            modal.classList.add('hidden');
            form.reset();
            currentStep = 1;
            showStep(1);
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });
        }

        // Next button
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                console.log('Next button clicked, current step:', currentStep);
                e.preventDefault();
                e.stopPropagation();
                if (currentStep < totalSteps) {
                    showStep(currentStep + 1);
                }
            });
            console.log('Next button listener attached');
        } else {
            console.error('Next button not found!');
        }

        // Previous button
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                console.log('Previous button clicked, current step:', currentStep);
                e.preventDefault();
                e.stopPropagation();
                if (currentStep > 1) {
                    showStep(currentStep - 1);
                }
            });
        }

        // Add prize functionality
        const addPrizeBtn = document.getElementById('addPrizeBtn');
        if (addPrizeBtn) {
            addPrizeBtn.addEventListener('click', function() {
                const container = document.getElementById('prizesContainer');
                const newPrize = document.createElement('div');
                newPrize.className = 'prize-entry grid grid-cols-3 gap-2';
                newPrize.innerHTML = `
                    <input type="number" name="prizes[${prizeCount}][placement]" value="${prizeCount + 1}" min="1" placeholder="Place"
                        class="bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-cyan-500 text-sm">
                    <input type="number" name="prizes[${prizeCount}][amount]" step="0.01" min="0" placeholder="Amount ($)"
                        class="bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-cyan-500 text-sm">
                    <input type="text" name="prizes[${prizeCount}][description]" placeholder="Description"
                        class="bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-cyan-500 text-sm">
                `;
                container.appendChild(newPrize);
                prizeCount++;
            });
        }

        // Form submission
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Show loading
                document.getElementById('modalLoadingOverlay').classList.remove('hidden');

                // Collect form data
                const formData = {
                    name: document.getElementById('name').value,
                    description: document.getElementById('description').value || '',
                    game_type: document.getElementById('game_type').value || '',
                    format: document.getElementById('format').value,
                    tournament_size: parseInt(document.getElementById('tournament_size').value),
                    scoring_system: document.getElementById('scoring_system').value,
                    entry_fee: parseFloat(document.getElementById('entry_fee').value) || 0,
                    visibility: document.getElementById('visibility').value,
                    is_public: document.getElementById('visibility').value === 'public' ? 1 : 0,
                    registration_deadline: document.getElementById('registration_deadline').value,
                    start_date: document.getElementById('start_date').value,
                    rules: document.getElementById('rules').value || '',
                    status: 'open'
                };

                // Collect prizes
                const prizes = [];
                const prizeEntries = document.querySelectorAll('.prize-entry');
                prizeEntries.forEach((entry, index) => {
                    const placement = entry.querySelector(`[name="prizes[${index}][placement]"]`)?.value;
                    const amount = entry.querySelector(`[name="prizes[${index}][amount]"]`)?.value;
                    const description = entry.querySelector(`[name="prizes[${index}][description]"]`)?.value;

                    if (placement && amount) {
                        prizes.push({
                            placement: parseInt(placement),
                            amount: parseFloat(amount),
                            currency: 'USD',
                            type: 'cash',
                            description: description || ''
                        });
                    }
                });

                if (prizes.length > 0) {
                    formData.prizes = prizes;
                }

                try {
                    console.log('Creating tournament with data:', formData);
                    const result = await TournamentAPI.createTournament(formData);

                    console.log('Tournament creation result:', result);

                    // Hide loading
                    document.getElementById('modalLoadingOverlay').classList.add('hidden');

                    if (result.success) {
                        // Show success message
                        if (typeof TournamentUI !== 'undefined') {
                            TournamentUI.showNotification('Tournament created successfully!', 'success');
                        } else {
                            alert('Tournament created successfully!');
                        }

                        // Close modal
                        closeModal();

                        // Reload tournaments if the function exists
                        if (typeof window.loadTournaments === 'function') {
                            window.loadTournaments();
                        } else {
                            // Refresh page as fallback
                            setTimeout(() => location.reload(), 1000);
                        }
                    } else {
                        throw new Error(result.message || 'Failed to create tournament');
                    }
                } catch (error) {
                    console.error('Error creating tournament:', error);
                    document.getElementById('modalLoadingOverlay').classList.add('hidden');

                    if (typeof TournamentUI !== 'undefined') {
                        TournamentUI.showNotification('Error: ' + error.message, 'error');
                    } else {
                        alert('Error creating tournament: ' + error.message);
                    }
                }
            });
        }

        initialized = true;
    }

    function showStep(step) {
        currentStep = step;

        // Hide all steps
        for (let i = 1; i <= totalSteps; i++) {
            const stepEl = document.getElementById(`step${i}`);
            if (stepEl) {
                stepEl.classList.add('hidden');
            }
            const stepNumber = document.querySelectorAll('.step-number')[i - 1];
            if (stepNumber) {
                stepNumber.classList.remove('bg-gradient-to-r', 'from-cyan-500', 'to-purple-600', 'text-white');
                stepNumber.classList.add('bg-gray-700', 'text-gray-400');
            }
        }

        // Show current step
        const currentStepEl = document.getElementById(`step${step}`);
        if (currentStepEl) {
            currentStepEl.classList.remove('hidden');
        }

        const currentStepNumber = document.querySelectorAll('.step-number')[step - 1];
        if (currentStepNumber) {
            currentStepNumber.classList.remove('bg-gray-700', 'text-gray-400');
            currentStepNumber.classList.add('bg-gradient-to-r', 'from-cyan-500', 'to-purple-600', 'text-white');
        }

        // Update buttons
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');
        const submitBtn = document.getElementById('submitBtn');

        if (prevBtn) prevBtn.classList.toggle('hidden', step === 1);
        if (nextBtn) nextBtn.classList.toggle('hidden', step === totalSteps);
        if (submitBtn) submitBtn.classList.toggle('hidden', step !== totalSteps);
    }
</script>