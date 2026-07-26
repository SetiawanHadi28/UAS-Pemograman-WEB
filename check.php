<?php
require 'config/database.php';
$stmt = $pdo->prepare("SELECT u.id as u_id, cv.id as cv_id, u.username FROM users u JOIN cv_data cv ON u.id = cv.user_id WHERE u.username = 'randi_irwana'");
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
