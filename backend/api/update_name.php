<?php
/**
 * Update Certificate Name and Reg Number API Endpoint
 * POST: Updates the name and registration number of a specific certificate
 */

require_once __DIR__ . '/auth.php';
require_auth();

require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed. Use POST.']);
    exit();
}

$id = $_POST['id'] ?? null;
$name = trim($_POST['name'] ?? '');
$reg_number = trim($_POST['reg_number'] ?? '');

if (!$id || !$name || !$reg_number) {
    echo json_encode(['success' => false, 'message' => 'Missing ID, Name, or Registration Number.']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE certificates SET name = :name, reg_number = :reg_number WHERE id = :id");
    $stmt->execute(['name' => $name, 'reg_number' => $reg_number, 'id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Student details updated successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update student details: ' . $e->getMessage()
    ]);
}
