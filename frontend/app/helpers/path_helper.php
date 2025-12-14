<?php
/**
 * Path Helper Functions
 * Provides dynamic path generation for the Tournament Management System
 */

/**
 * Get the base path of the application
 * Works regardless of whether the app is deployed at root or in a subdirectory
 * 
 * @return string Base path (e.g., "/Tournament-Management-System" or "")
 */
function getBasePath() {
    // Get the script name (e.g., /Tournament-Management-System/frontend/app/views/pages/home/index.php)
    $scriptName = $_SERVER['SCRIPT_NAME'];
    
    // Find the position of /frontend/ in the path
    $frontendPos = strpos($scriptName, '/frontend/');
    
    if ($frontendPos !== false) {
        // Extract everything before /frontend/
        return substr($scriptName, 0, $frontendPos);
    }
    
    // Fallback: return empty string (assuming root deployment)
    return '';
}

/**
 * Get the path to a resource relative to the project root
 * 
 * @param string $path Path relative to project root (e.g., "backend/api/auth_api.php")
 * @return string Full path from web root
 */
function getPath($path) {
    $basePath = getBasePath();
    $path = ltrim($path, '/');
    return $basePath . '/' . $path;
}

/**
 * Get the path to a frontend resource
 * 
 * @param string $path Path relative to frontend directory
 * @return string Full path from web root
 */
function getFrontendPath($path) {
    return getPath('frontend/' . ltrim($path, '/'));
}

/**
 * Get the path to a backend resource
 * 
 * @param string $path Path relative to backend directory
 * @return string Full path from web root
 */
function getBackendPath($path) {
    return getPath('backend/' . ltrim($path, '/'));
}

/**
 * Get the path to an asset (CSS, JS, images)
 * 
 * @param string $path Path relative to frontend/src directory
 * @return string Full path from web root
 */
function getAssetPath($path) {
    return getFrontendPath('src/' . ltrim($path, '/'));
}

/**
 * Get the path to a page
 * 
 * @param string $path Path relative to frontend/app/views/pages directory
 * @return string Full path from web root
 */
function getPagePath($path) {
    return getFrontendPath('app/views/pages/' . ltrim($path, '/'));
}
