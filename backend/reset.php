<?php
require_once __DIR__ . '/db.php';

$hash = password_hash('password123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = 'admin'");
$stmt->execute([$hash]);

echo "Admin password reset successfully to: password123\n";
