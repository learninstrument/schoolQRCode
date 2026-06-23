<?php
/**
 * Generate Certificate API Endpoint
 * POST: Accepts multipart form data with:
 *   - name (string): Student full name
 *   - photo (file): Student passport photo (JPG/PNG, max 2MB)
 *   - reg_number (string): Registration number (e.g. NBTE/NSQ/2018/330790)
 * Returns: JSON with id, name, reg_number, date_of_award, photo URL
 */

require_once __DIR__ . '/auth.php';
require_auth();

require_once __DIR__ . '/../db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed. Use POST.'
    ]);
    exit();
}

// ── Validate Fields ─────────────────────────────────────
$name       = trim($_POST['name'] ?? '');
$regNumber  = trim($_POST['reg_number'] ?? '');
$courseId   = filter_var($_POST['course_id'] ?? '', FILTER_VALIDATE_INT);
$level      = trim($_POST['level'] ?? '');
$dateOfAward = trim($_POST['date_of_award'] ?? '');

if (empty($name)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Student name is required.'
    ]);
    exit();
}

if (empty($regNumber)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Registration number is required.'
    ]);
    exit();
}

if (!$courseId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please select a course.'
    ]);
    exit();
}

if (empty($level)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please select a level.'
    ]);
    exit();
}

if (empty($dateOfAward) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfAward)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please select a valid date of award.'
    ]);
    exit();
}

// ── Validate Photo Upload ───────────────────────────────
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = 'Student photo is required.';
    if (isset($_FILES['photo'])) {
        switch ($_FILES['photo']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = 'Photo file is too large (max 2MB).';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = 'No photo file was uploaded.';
                break;
            default:
                $errorMsg = 'Photo upload error. Please try again.';
        }
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $errorMsg
    ]);
    exit();
}

$photo = $_FILES['photo'];

// Check file size (max 2MB)
$maxSize = 2 * 1024 * 1024; // 2MB
if ($photo['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Photo must be less than 2MB.'
    ]);
    exit();
}

// Check file type using MIME
$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $photo['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Photo must be a JPG or PNG image.'
    ]);
    exit();
}

// ── Save Photo ──────────────────────────────────────────
$uploadsDir = __DIR__ . '/../uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Generate unique filename
$extension = ($mimeType === 'image/png') ? 'png' : 'jpg';
$filename  = 'photo_' . uniqid() . '_' . time() . '.' . $extension;
$filePath  = $uploadsDir . '/' . $filename;

if (!move_uploaded_file($photo['tmp_name'], $filePath)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save photo. Please try again.'
    ]);
    exit();
}

// Relative path for storage in DB (relative to backend folder)
$photoDbPath = 'uploads/' . $filename;

// ── Insert into Database ────────────────────────────────
try {
    $stmt = $pdo->prepare(
        "INSERT INTO certificates (name, photo, reg_number, course_id, level, date_of_award) 
         VALUES (:name, :photo, :reg_number, :course_id, :level, :date_of_award)"
    );
    $stmt->execute([
        'name'          => $name,
        'photo'         => $photoDbPath,
        'reg_number'    => $regNumber,
        'course_id'     => $courseId,
        'level'         => $level,
        'date_of_award' => $dateOfAward,
    ]);

    $newId = (int) $pdo->lastInsertId();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Certificate generated successfully.',
        'data'    => [
            'id'            => $newId,
            'name'          => $name,
            'reg_number'    => $regNumber,
            'date_of_award' => $dateOfAward,
            'photo'         => $photoDbPath
        ]
    ]);
} catch (PDOException $e) {
    // Clean up uploaded file on DB failure
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate certificate: ' . $e->getMessage()
    ]);
}
