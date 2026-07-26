<?php
$db_host = 'localhost:3306';
$db_name = 'Project_cv';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Function untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function untuk cek role admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function untuk cek owner CV
function isOwner($user_id) {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id;
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
