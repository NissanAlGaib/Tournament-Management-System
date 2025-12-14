/**
 * Tournament Management JavaScript
 * Handles tournament creation, registration, and display
 */

const TournamentAPI = {
  baseURL: "/backend/api/tournament_api.php",

  /**
   * Get auth token from localStorage
   */
  getToken() {
    return localStorage.getItem("auth_token");
  },

  /**
   * Get all tournaments
   */
  async getTournaments(status = null) {
    try {
      let url = `${this.baseURL}?action=tournaments`;
      if (status) {
        url += `&status=${status}`;
      }

      const response = await fetch(url);
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching tournaments:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Get single tournament by ID
   */
  async getTournament(id) {
    try {
      const response = await fetch(
        `${this.baseURL}?action=tournament&id=${id}`
      );
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching tournament:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Get user's tournaments (participating and organizing)
   */
  async getMyTournaments() {
    try {
      const response = await fetch(`${this.baseURL}?action=my-tournaments`, {
        headers: {
          Authorization: `Bearer ${this.getToken()}`,
        },
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching my tournaments:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Get tournament leaderboard
   */
  async getLeaderboard(tournamentId) {
    try {
      const response = await fetch(
        `${this.baseURL}?action=leaderboard&tournament_id=${tournamentId}`
      );
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching leaderboard:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Create new tournament
   */
  async createTournament(tournamentData) {
    try {
      const response = await fetch(this.baseURL, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${this.getToken()}`,
        },
        body: JSON.stringify({
          action: "create",
          ...tournamentData,
        }),
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error creating tournament:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Update tournament
   */
  async updateTournament(id, tournamentData) {
    try {
      const response = await fetch(this.baseURL, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${this.getToken()}`,
        },
        body: JSON.stringify({
          id: id,
          ...tournamentData,
        }),
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error updating tournament:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Delete tournament
   */
  async deleteTournament(id) {
    try {
      const response = await fetch(`${this.baseURL}?id=${id}`, {
        method: "DELETE",
        headers: {
          Authorization: `Bearer ${this.getToken()}`,
        },
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error deleting tournament:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Register for tournament
   */
  async registerForTournament(tournamentId) {
    try {
      const response = await fetch(this.baseURL, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${this.getToken()}`,
        },
        body: JSON.stringify({
          action: "register",
          tournament_id: tournamentId,
        }),
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error registering for tournament:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Update tournament status
   */
  async updateStatus(tournamentId, status) {
    try {
      const response = await fetch(this.baseURL, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${this.getToken()}`,
        },
        body: JSON.stringify({
          action: "update-status",
          tournament_id: tournamentId,
          status: status,
        }),
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error updating tournament status:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Get organized tournaments (for organizers)
   */
  async getOrganizedTournaments() {
    try {
      const response = await fetch(
        `${this.baseURL}?action=organized-tournaments`,
        {
          headers: {
            Authorization: `Bearer ${this.getToken()}`,
          },
        }
      );
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching organized tournaments:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Get tournament participants (for organizers)
   */
  async getTournamentParticipants(tournamentId) {
    try {
      const response = await fetch(
        `${this.baseURL}?action=tournament-participants&tournament_id=${tournamentId}`,
        {
          headers: {
            Authorization: `Bearer ${this.getToken()}`,
          },
        }
      );
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching tournament participants:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Get tournament teams (for organizers)
   */
  async getTournamentTeams(tournamentId) {
    try {
      const response = await fetch(
        `${this.baseURL}?action=tournament-teams&tournament_id=${tournamentId}`,
        {
          headers: {
            Authorization: `Bearer ${this.getToken()}`,
          },
        }
      );
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching tournament teams:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Approve participant
   */
  async approveParticipant(participantId) {
    try {
      const response = await fetch(this.baseURL, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${this.getToken()}`,
        },
        body: JSON.stringify({
          action: "approve-participant",
          participant_id: participantId,
        }),
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error approving participant:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Reject participant
   */
  async rejectParticipant(participantId) {
    try {
      const response = await fetch(this.baseURL, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${this.getToken()}`,
        },
        body: JSON.stringify({
          action: "reject-participant",
          participant_id: participantId,
        }),
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error rejecting participant:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Get tournament bracket
   */
  async getTournamentBracket(tournamentId) {
    try {
      const response = await fetch(
        `${this.baseURL}?action=tournament-bracket&tournament_id=${tournamentId}`,
        {
          headers: {
            Authorization: `Bearer ${this.getToken()}`,
          },
        }
      );
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching tournament bracket:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Generate bracket for tournament
   */
  async generateBracket(tournamentId) {
    try {
      const response = await fetch(this.baseURL, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${this.getToken()}`,
        },
        body: JSON.stringify({
          action: "generate-bracket",
          tournament_id: tournamentId,
        }),
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error generating bracket:", error);
      return { success: false, message: error.message };
    }
  },

  /**
   * Set match winner
   */
  async setMatchWinner(matchId, winnerId) {
    try {
      const response = await fetch(this.baseURL, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${this.getToken()}`,
        },
        body: JSON.stringify({
          action: "set-match-winner",
          match_id: matchId,
          winner_id: winnerId,
        }),
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error setting match winner:", error);
      return { success: false, message: error.message };
    }
  },
};

/**
 * Tournament UI Manager
 */
const TournamentUI = {
  /**
   * Render tournament card
   */
  renderTournamentCard(tournament) {
    const statusBadge = this.getStatusBadge(tournament.status);
    const participantInfo = `${tournament.registered_participants || 0}/${
      tournament.max_participants || tournament.tournament_size
    }`;

    return `
            <div class="relative group cursor-pointer" onclick="TournamentUI.navigateToDetails(${tournament.id})">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-2xl opacity-50 group-hover:opacity-75 blur transition duration-300"></div>
                <div class="relative bg-gray-800 rounded-2xl border border-cyan-500/30 overflow-hidden hover:border-cyan-400/50 transition-all">
                    <div class="relative h-40 bg-gradient-to-br from-cyan-600 via-purple-600 to-cyan-700 flex items-center justify-center">
                        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                        <svg class="w-20 h-20 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        ${statusBadge}
                    </div>
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-white mb-2">${this.escapeHtml(
                          tournament.name
                        )}</h3>
                        <p class="text-gray-400 text-sm mb-4">${this.escapeHtml(
                          tournament.description || "No description"
                        )}</p>
                        
                        <div class="space-y-3 mb-4">
                            <div class="flex items-center text-sm text-gray-300">
                                <svg class="w-4 h-4 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                ${this.formatDate(tournament.start_date)}
                            </div>
                            <div class="flex items-center text-sm text-gray-300">
                                <svg class="w-4 h-4 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                ${participantInfo} players
                            </div>
                            ${
                              tournament.prizes && tournament.prizes.length > 0
                                ? `
                            <div class="flex items-center text-sm text-gray-300">
                                <svg class="w-4 h-4 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Prize: ${tournament.prizes[0].prize_amount} ${tournament.prizes[0].currency}
                            </div>
                            `
                                : ""
                            }
                        </div>

                        <button onclick="TournamentUI.viewTournament(${
                          tournament.id
                        })" class="w-full bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-600 hover:to-purple-700 text-white font-bold py-3 rounded-xl transition-all duration-300 transform hover:scale-[1.02]">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        `;
  },

  /**
   * Get status badge HTML
   */
  getStatusBadge(status) {
    const badges = {
      open: '<span class="absolute top-4 right-4 px-3 py-1 bg-green-500/90 text-white text-xs font-bold rounded-full">OPEN</span>',
      ongoing:
        '<span class="absolute top-4 right-4 px-3 py-1 bg-blue-500/90 text-white text-xs font-bold rounded-full">LIVE</span>',
      completed:
        '<span class="absolute top-4 right-4 px-3 py-1 bg-gray-500/90 text-white text-xs font-bold rounded-full">COMPLETED</span>',
      draft:
        '<span class="absolute top-4 right-4 px-3 py-1 bg-yellow-500/90 text-gray-900 text-xs font-bold rounded-full">DRAFT</span>',
      cancelled:
        '<span class="absolute top-4 right-4 px-3 py-1 bg-red-500/90 text-white text-xs font-bold rounded-full">CANCELLED</span>',
    };
    return badges[status] || "";
  },

  /**
   * Format date for display
   */
  formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  },

  /**
   * Escape HTML to prevent XSS
   */
  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  },

  /**
   * Navigate to tournament details page
   */
  navigateToDetails(tournamentId) {
    // Check if we're in the AJAX navigation context (home.js)
    if (typeof window.navigateTo === 'function') {
      // Use the AJAX navigation system
      window.currentTournamentId = tournamentId;
      window.navigateTo('tournament-details.php');
    } else {
      // Fallback to direct navigation
      window.location.href = `tournament-details.php?id=${tournamentId}`;
    }
  },

  /**
   * View tournament details (placeholder - to be implemented)
   */
  viewTournament(id) {
    this.navigateToDetails(id);
  },

  /**
   * Show notification
   */
  showNotification(message, type = "success") {
    // Simple notification - can be enhanced with a toast library
    const notification = document.createElement("div");
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white ${
      type === "success" ? "bg-green-500" : "bg-red-500"
    } z-50 animate-fade-in`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.remove();
    }, 3000);
  },
};

// Make available globally for browser use
window.TournamentAPI = TournamentAPI;
window.TournamentUI = TournamentUI;

// Export for use in other modules
if (typeof module !== "undefined" && module.exports) {
  module.exports = { TournamentAPI, TournamentUI };
}
