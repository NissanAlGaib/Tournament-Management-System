/**
 * Path Helper for JavaScript
 * Provides dynamic path generation that works with subdirectory deployments
 */

/**
 * Get the base path of the application
 * Works regardless of whether the app is deployed at root or in a subdirectory
 * 
 * @returns {string} Base path (e.g., "/Tournament-Management-System" or "")
 */
export function getBasePath() {
    const pathname = window.location.pathname;
    
    // Find the position of /frontend/ in the path
    const frontendIndex = pathname.indexOf('/frontend/');
    
    if (frontendIndex !== -1) {
        // Extract everything before /frontend/
        return pathname.substring(0, frontendIndex);
    }
    
    // Fallback: return empty string (assuming root deployment)
    return '';
}

/**
 * Get the path to a resource relative to the project root
 * 
 * @param {string} path Path relative to project root (e.g., "backend/api/auth_api.php")
 * @returns {string} Full path from web root
 */
export function getPath(path) {
    const basePath = getBasePath();
    path = path.replace(/^\/+/, ''); // Remove leading slashes
    return basePath + '/' + path;
}

/**
 * Get the path to a frontend resource
 * 
 * @param {string} path Path relative to frontend directory
 * @returns {string} Full path from web root
 */
export function getFrontendPath(path) {
    return getPath('frontend/' + path.replace(/^\/+/, ''));
}

/**
 * Get the path to a backend resource
 * 
 * @param {string} path Path relative to backend directory
 * @returns {string} Full path from web root
 */
export function getBackendPath(path) {
    return getPath('backend/' + path.replace(/^\/+/, ''));
}

/**
 * Get the path to a page
 * 
 * @param {string} path Path relative to frontend/app/views/pages directory
 * @returns {string} Full path from web root
 */
export function getPagePath(path) {
    return getFrontendPath('app/views/pages/' + path.replace(/^\/+/, ''));
}

/**
 * Get the path to a view
 * 
 * @param {string} path Path relative to frontend/app/views directory
 * @returns {string} Full path from web root
 */
export function getViewPath(path) {
    return getFrontendPath('app/views/' + path.replace(/^\/+/, ''));
}
