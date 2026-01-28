<?php
/**
 * Helper Functions
 * Utility functions for the application
 */

/**
 * Sanitize output to prevent XSS
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// IMPLEMENTATION: XSS prevention helper. Use `escape()` when printing
// user-controlled data to mitigate Cross-Site Scripting (XSS).

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// IMPLEMENTATION: Session-based authentication helpers. Pages call
// `requireLogin()` to protect routes; `isLoggedIn()` checks session.

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// IMPLEMENTATION: CSRF protection helpers. Include `generateCSRFToken()` in
// forms and verify with `verifyCSRFToken()` on POST handlers.

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// IMPLEMENTATION: Use `verifyCSRFToken()` in POST endpoints (add/edit/delete)
// to protect against cross-site request forgery.

/**
 * Display flash messages
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// IMPLEMENTATION: Flash messaging helpers used across pages to provide
// user feedback after actions (success/error notices).

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Format price in Nepali Rupees
 */
function formatPrice($price) {
    return 'Rs. ' . number_format($price, 2);
}

?>