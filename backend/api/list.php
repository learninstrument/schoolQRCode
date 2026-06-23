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
            "SELECT c.id, c.name, c.photo, c.reg_number, c.level, c.date_of_award, c.created_at,
                    COALESCE(co.course_name, '') AS course_name
             FROM certificates c
             LEFT JOIN courses co ON c.course_id = co.id
             WHERE c.name LIKE :search OR c.reg_number LIKE :search2
             ORDER BY c.id DESC"
        );
        $searchTerm = "%{$search}%";
        $stmt->execute(['search' => $searchTerm, 'search2' => $searchTerm]);
    } else {
        $stmt = $pdo->query(
            "SELECT c.id, c.name, c.photo, c.reg_number, c.level, c.date_of_award, c.created_at,
                    COALESCE(co.course_name, '') AS course_name
             FROM certificates c
             LEFT JOIN courses co ON c.course_id = co.id
             ORDER BY c.id DESC"
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
