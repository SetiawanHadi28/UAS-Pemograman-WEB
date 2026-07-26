<?php
require 'config/database.php';
$stmt = $pdo->prepare("SELECT u.id as u_id, u.username, cv.id as cv_id FROM users u JOIN cv_data cv ON u.id = cv.user_id WHERE u.username = ? AND u.status = 'active'");
$stmt->execute(['randi_irwana']);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
