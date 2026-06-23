<?php
/**
 * Dashboard Stats API Endpoint
 * GET: Returns certificate statistics for the admin dashboard
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
    // Total certificates
    $totalStmt = $pdo->query("SELECT COUNT(*) as total FROM certificates");
    $total = (int) $totalStmt->fetch()['total'];

    // Certificates today
    $todayStmt = $pdo->prepare(
        "SELECT COUNT(*) as today FROM certificates WHERE DATE(created_at) = CURDATE()"
    );
    $todayStmt->execute();
    $today = (int) $todayStmt->fetch()['today'];

    // Certificates this month
    $monthStmt = $pdo->prepare(
        "SELECT COUNT(*) as this_month FROM certificates 
         WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
    );
    $monthStmt->execute();
    $thisMonth = (int) $monthStmt->fetch()['this_month'];

    // Recent 5 certificates
    $recentStmt = $pdo->query(
        "SELECT c.id, c.name, c.reg_number, c.level, c.date_of_award, c.created_at,
                COALESCE(co.course_name, '') AS course_name
         FROM certificates c
         LEFT JOIN courses co ON c.course_id = co.id
         ORDER BY c.id DESC LIMIT 5"
    );
    $recent = $recentStmt->fetchAll();

    echo json_encode([
        'success'    => true,
        'data'       => [
            'total'      => $total,
            'today'      => $today,
            'this_month' => $thisMonth,
            'recent'     => $recent
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch stats: ' . $e->getMessage()
    ]);
}
