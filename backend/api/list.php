<?php
/**
 * List Certificates API Endpoint
 * GET: Returns all certificates ordered by most recent
 * Optional: ?search=<term> to filter by name or reg_number
 */

require_once __DIR__ . '/auth.php';
require_auth();

require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed. Use GET.']);
    exit();
}

try {
    $search = trim($_GET['search'] ?? '');

    if (!empty($search)) {
        $stmt = $pdo->prepare(
            "SELECT id, name, photo, reg_number, date_of_award, created_at 
             FROM certificates 
             WHERE name LIKE :search OR reg_number LIKE :search2
             ORDER BY id DESC"
        );
        $searchTerm = "%{$search}%";
        $stmt->execute(['search' => $searchTerm, 'search2' => $searchTerm]);
    } else {
        $stmt = $pdo->query(
            "SELECT id, name, photo, reg_number, date_of_award, created_at 
             FROM certificates 
             ORDER BY id DESC"
        );
    }

    $certificates = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'count'   => count($certificates),
        'data'    => $certificates
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch certificates: ' . $e->getMessage()
    ]);
}
