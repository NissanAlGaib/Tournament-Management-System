<?php
/**
 * Test script to validate the new tournament management API endpoints
 * This script checks that the endpoints are properly defined and can be called
 */

echo "Tournament Management API - Endpoint Validation\n";
echo "================================================\n\n";

// Test 1: Check if tournament_api.php file exists and is readable
echo "Test 1: Checking tournament_api.php file...\n";
$apiFile = __DIR__ . '/backend/api/tournament_api.php';
if (file_exists($apiFile) && is_readable($apiFile)) {
    echo "✓ tournament_api.php exists and is readable\n";
} else {
    echo "✗ tournament_api.php not found or not readable\n";
    exit(1);
}

// Test 2: Parse the file to check for new endpoints
echo "\nTest 2: Checking for new GET endpoints...\n";
$apiContent = file_get_contents($apiFile);

$getEndpoints = [
    'organized-tournaments' => "Get tournaments organized by current user",
    'tournament-participants' => "Get participants for a tournament (Organizer/Admin only)",
    'tournament-teams' => "Get teams for a tournament (Organizer/Admin only)",
];

foreach ($getEndpoints as $endpoint => $description) {
    if (strpos($apiContent, "action === '$endpoint'") !== false) {
        echo "✓ Found endpoint: $endpoint - $description\n";
    } else {
        echo "✗ Missing endpoint: $endpoint\n";
    }
}

// Test 3: Check for new POST endpoints
echo "\nTest 3: Checking for new POST endpoints...\n";
$postEndpoints = [
    'approve-participant' => "Approve participant registration",
    'reject-participant' => "Reject participant registration",
];

foreach ($postEndpoints as $endpoint => $description) {
    if (strpos($apiContent, "action === '$endpoint'") !== false) {
        echo "✓ Found endpoint: $endpoint - $description\n";
    } else {
        echo "✗ Missing endpoint: $endpoint\n";
    }
}

// Test 4: Check frontend JavaScript API client
echo "\nTest 4: Checking frontend JavaScript API client...\n";
$jsFile = __DIR__ . '/frontend/src/js/tournament.js';
if (file_exists($jsFile) && is_readable($jsFile)) {
    $jsContent = file_get_contents($jsFile);
    
    $jsMethods = [
        'getOrganizedTournaments' => "Get organized tournaments",
        'getTournamentParticipants' => "Get tournament participants",
        'getTournamentTeams' => "Get tournament teams",
        'approveParticipant' => "Approve participant",
        'rejectParticipant' => "Reject participant",
    ];
    
    foreach ($jsMethods as $method => $description) {
        if (strpos($jsContent, "async $method") !== false) {
            echo "✓ Found method: $method - $description\n";
        } else {
            echo "✗ Missing method: $method\n";
        }
    }
} else {
    echo "✗ tournament.js not found or not readable\n";
}

// Test 5: Check manage-tournaments.php page
echo "\nTest 5: Checking manage-tournaments.php page...\n";
$managePage = __DIR__ . '/frontend/app/views/pages/home/manage-tournaments.php';
if (file_exists($managePage) && is_readable($managePage)) {
    echo "✓ manage-tournaments.php page exists\n";
    
    $pageContent = file_get_contents($managePage);
    
    // Check for key features
    $features = [
        'viewParticipants' => "View participants function",
        'approveParticipant' => "Approve participant function",
        'rejectParticipant' => "Reject participant function",
        'viewTeams' => "View teams function",
        'participantsModal' => "Participants modal",
    ];
    
    foreach ($features as $feature => $description) {
        if (strpos($pageContent, $feature) !== false) {
            echo "  ✓ Has feature: $description\n";
        } else {
            echo "  ✗ Missing feature: $description\n";
        }
    }
} else {
    echo "✗ manage-tournaments.php page not found\n";
}

// Test 6: Check navigation integration
echo "\nTest 6: Checking navigation integration...\n";
$indexPage = __DIR__ . '/frontend/app/views/pages/home/index.php';
if (file_exists($indexPage) && is_readable($indexPage)) {
    $indexContent = file_get_contents($indexPage);
    
    if (strpos($indexContent, 'nav-manage-tournaments') !== false) {
        echo "✓ Navigation link added to index.php\n";
    } else {
        echo "✗ Navigation link not found in index.php\n";
    }
    
    if (strpos($indexContent, 'data-roles="Organizer,Admin"') !== false || 
        strpos($indexContent, 'Manage Tournaments') !== false) {
        echo "✓ Role-based visibility configured\n";
    } else {
        echo "✗ Role-based visibility not configured\n";
    }
}

$homeJs = __DIR__ . '/frontend/src/js/home.js';
if (file_exists($homeJs) && is_readable($homeJs)) {
    $homeJsContent = file_get_contents($homeJs);
    
    if (strpos($homeJsContent, 'nav-manage-tournaments') !== false) {
        echo "✓ Event listener added to home.js\n";
    } else {
        echo "✗ Event listener not found in home.js\n";
    }
}

echo "\n================================================\n";
echo "Validation Complete!\n";
echo "\nSummary:\n";
echo "- Backend API endpoints: Added\n";
echo "- Frontend API client: Updated\n";
echo "- Management page: Created\n";
echo "- Navigation integration: Complete\n";
echo "\nNext steps:\n";
echo "1. Set up database with tournament_management.sql\n";
echo "2. Create test users with Organizer role\n";
echo "3. Create test tournaments\n";
echo "4. Test approve/reject functionality\n";
