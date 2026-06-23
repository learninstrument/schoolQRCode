<?php
/**
 * Courses API Endpoint
 * GET:  Returns all courses
 * POST: Adds a new course (name required)
 */

require_once __DIR__ . '/auth.php';
require_auth();

require_once __DIR__ . '/../db.php';

// ── GET: List all courses ──
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT id, course_name, created_at FROM courses ORDER BY course_name ASC");
        $courses = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'count'   => count($courses),
            'data'    => $courses
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch courses: ' . $e->getMessage()
        ]);
    }
    exit();
}

// ── POST: Add a new course ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseName = trim($_POST['course_name'] ?? '');

    if (empty($courseName)) {
        echo json_encode(['success' => false, 'message' => 'Course name is required.']);
        exit();
    }

    try {
        // Check for duplicate
        $check = $pdo->prepare("SELECT id FROM courses WHERE course_name = :name LIMIT 1");
        $check->execute(['name' => $courseName]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'This course already exists.']);
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO courses (course_name) VALUES (:name)");
        $stmt->execute(['name' => $courseName]);

        echo json_encode([
            'success' => true,
            'message' => 'Course added successfully.',
            'data'    => [
                'id'          => (int) $pdo->lastInsertId(),
                'course_name' => $courseName
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add course: ' . $e->getMessage()
        ]);
    }
    exit();
}

// ── Other methods ──
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method Not Allowed. Use GET or POST.']);
