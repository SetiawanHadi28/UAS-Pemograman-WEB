<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_GET['id'] ?? 0;

// Reset semua default
$pdo->exec("UPDATE cv_data SET is_default = FALSE");

// Set default untuk user ini
$stmt = $pdo->prepare("UPDATE cv_data SET is_default = TRUE WHERE user_id = ?");
$stmt->execute([$user_id]);

header('Location: users.php?default=1');
exit;
?>