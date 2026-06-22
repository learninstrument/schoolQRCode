<?php
/**
 * Delete Certificate API Endpoint
 * POST: Deletes a certificate by ID and removes its photo file
 */

require_once __DIR__ . '/auth.php';
require_auth();

require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed. Use POST.']);
    exit();
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    echo json_encode(['success' => false, 'message' => 'Valid certificate ID is required.']);
    exit();
}

try {
    // Get the photo path before deleting
    $stmt = $pdo->prepare("SELECT photo FROM certificates WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $cert = $stmt->fetch();

    if (!$cert) {
        echo json_encode(['success' => false, 'message' => 'Certificate not found.']);
        exit();
    }

    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = :id");
    $stmt->execute(['id' => $id]);

    // Delete the photo file
    $photoPath = __DIR__ . '/../' . $cert['photo'];
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }

    echo json_encode(['success' => true, 'message' => 'Certificate deleted successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete certificate: ' . $e->getMessage()
    ]);
}
