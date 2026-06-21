<?php
/**
 * Auth Check API Endpoint
 * GET: Checks if the user is currently logged in
 */
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo json_encode(['success' => true, 'logged_in' => true, 'username' => $_SESSION['admin_username'] ?? '']);
} else {
    echo json_encode(['success' => true, 'logged_in' => false]);
}
