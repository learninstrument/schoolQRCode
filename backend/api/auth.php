<?php
/**
 * Authentication Middleware
 * Include this at the top of any API file that needs protection.
 */
session_start();

function require_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Please log in.'
        ]);
        exit();
    }
}
