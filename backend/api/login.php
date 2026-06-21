<?php
/**
 * Login API Endpoint
 * POST: Handles admin login
 */
require_once __DIR__ . '/../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, password_hash FROM admins WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        // Login successful
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $username;

        echo json_encode(['success' => true, 'message' => 'Login successful.']);
    } else {
        // Login failed
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
