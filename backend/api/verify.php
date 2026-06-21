<?php
/**
 * Verify Certificate API Endpoint
 * GET: Accepts ?id=<certificate_id>
 * Returns: JSON with validity status, student name, photo, reg_number, date_of_award
 */

require_once __DIR__ . '/../db.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed. Use GET.'
    ]);
    exit();
}

// Validate input
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'valid'   => false,
        'message' => 'A valid certificate ID is required.'
    ]);
    exit();
}

try {
    // Query the database using prepared statement
    $stmt = $pdo->prepare(
        "SELECT id, name, photo, reg_number, date_of_award 
         FROM certificates WHERE id = :id LIMIT 1"
    );
    $stmt->execute(['id' => $id]);
    $certificate = $stmt->fetch();

    if ($certificate) {
        echo json_encode([
            'success' => true,
            'valid'   => true,
            'message' => 'Certificate is valid.',
            'data'    => [
                'id'            => (int) $certificate['id'],
                'name'          => $certificate['name'],
                'photo'         => $certificate['photo'],
                'reg_number'    => $certificate['reg_number'],
                'date_of_award' => $certificate['date_of_award']
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => true,
            'valid'   => false,
            'message' => 'Invalid Certificate / Record Not Found.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'valid'   => false,
        'message' => 'Verification failed: ' . $e->getMessage()
    ]);
}
