<?php
$_SERVER['SCRIPT_NAME'] = '/GitHub Repos/Tournament-Management-System/frontend/app/views/pages/home/tournaments.php';
require_once 'frontend/app/helpers/path_helper.php';
echo 'Asset Path: ' . getAssetPath('js/tournament.js') . "\n";
echo 'Backend Path: ' . getBackendPath('api/tournament_api.php') . "\n";
echo 'Base Path: ' . getBasePath() . "\n";
