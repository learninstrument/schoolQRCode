<?php
/**
 * Delete Course API Endpoint
 * POST: Deletes a course by ID
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
    echo json_encode(['success' => false, 'message' => 'Valid course ID is required.']);
    exit();
}

try {
    // Check if any certificates are using this course
    $check = $pdo->prepare("SELECT COUNT(*) as cnt FROM certificates WHERE course_id = :id");
    $check->execute(['id' => $id]);
    $result = $check->fetch();

    if ($result['cnt'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => "Cannot delete: {$result['cnt']} certificate(s) are using this course."
        ]);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = :id");
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Course not found.']);
        exit();
    }

    echo json_encode(['success' => true, 'message' => 'Course deleted successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete course: ' . $e->getMessage()
    ]);
}
