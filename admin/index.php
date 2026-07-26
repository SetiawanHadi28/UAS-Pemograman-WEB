<?php
require_once '../config/database.php';

// Cek login & role admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Ambil statistik
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalActive = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
$totalCV = $pdo->query("SELECT COUNT(*) FROM cv_data")->fetchColumn();
$totalAdmin = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

// Ambil data user terbaru
$users = $pdo->query("SELECT u.*, cv.nama FROM users u 
                       LEFT JOIN cv_data cv ON u.id = cv.user_id 
                       ORDER BY u.created_at DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — CV App</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Space+Grotesk:wght@300;400;500;600;700;800&family=Orbitron:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?php echo filemtime('../assets/css/admin.css'); ?>">
</head>
<body class="admin-page">

<canvas id="bg-canvas"></canvas>

<div class="admin-layout">

    <!-- ── SIDEBAR ── -->
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">📄</div>
            <div class="sidebar-logo-text">
                <strong>CV App</strong>
                <span>Admin Panel</span>
            </div>
        </div>

        <div class="admin-user-info">
            <div class="admin-avatar">👑</div>
            <div class="info">
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <small>Administrator</small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-nav-label">Menu Utama</div>
            <a href="index.php" class="active">
                <div class="nav-icon">📊</div>
                Dashboard
            </a>
            <a href="users.php">
                <div class="nav-icon">👥</div>
                Kelola User
            </a>
            
            <div class="sidebar-nav-label">Pengaturan</div>
            <a href="settings.php">
                <div class="nav-icon">⚙️</div>
                Sistem & Keamanan
            </a>
            <a href="../index.php" target="_blank">
                <div class="nav-icon">🏠</div>
                Lihat CV Default
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="../logout.php" class="topbar-btn danger" style="width: 100%; justify-content: center; color: #fca5a5; border-color: rgba(239, 68, 68, 0.2);">
                🚪 Logout
            </a>
        </div>
    </aside>

    <!-- ── MAIN CONTENT ── -->
    <main class="admin-main">
        
        <header class="admin-topbar">
            <div class="topbar-title">
                <h1>Dashboard</h1>
                <p>Ringkasan sistem dan statistik hari ini</p>
            </div>
            <div class="topbar-actions">
                <a href="users.php" class="topbar-btn primary">➕ Tambah User</a>
            </div>
        </header>

        <div class="admin-content">
            
            <!-- STATS -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="stat-icon purple">👥</div>
                    <span class="number"><?php echo $totalUsers; ?></span>
                    <span class="label">Total User</span>
                </div>
                <div class="admin-stat-card">
                    <div class="stat-icon green">✅</div>
                    <span class="number"><?php echo $totalActive; ?></span>
                    <span class="label">User Aktif</span>
                </div>
                <div class="admin-stat-card">
                    <div class="stat-icon yellow">📄</div>
                    <span class="number"><?php echo $totalCV; ?></span>
                    <span class="label">Total CV</span>
                </div>
                <div class="admin-stat-card">
                    <div class="stat-icon red">👑</div>
                    <span class="number"><?php echo $totalAdmin; ?></span>
                    <span class="label">Admin</span>
                </div>
            </div>

            <!-- TABLE -->
            <div class="admin-table-wrapper">
                <div class="table-header">
                    <h2>📋 User Terbaru</h2>
                    <a href="users.php">Lihat Semua →</a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['nama'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge <?php echo $user['role'] === 'admin' ? 'role-admin' : 'role-user'; ?>">
                                        <?php echo $user['role']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $user['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $user['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn-edit" title="Edit">✏️ Edit</a>
                                        <a href="../profile.php?u=<?php echo $user['username']; ?>" target="_blank" class="btn-view" title="Lihat CV">👁️ Lihat</a>
                                        <?php if($user['role'] !== 'admin'): ?>
                                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('Hapus user ini?')" title="Hapus">🗑️</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> <!-- .admin-content -->
    </main>
</div> <!-- .admin-layout -->

<script>
// Canvas BG Animation (sama seperti login)
const canvas = document.getElementById('bg-canvas');
const ctx = canvas.getContext('2d');
let W = canvas.width = window.innerWidth;
let H = canvas.height = window.innerHeight;

const particles = [];
for (let i = 0; i < 40; i++) {
    particles.push({
        x: Math.random() * W,
        y: Math.random() * H,
        vx: (Math.random() - 0.5) * 0.5,
        vy: (Math.random() - 0.5) * 0.5,
        r: Math.random() * 2 + 1
    });
}

function loop() {
    ctx.clearRect(0, 0, W, H);
    ctx.fillStyle = 'rgba(99, 102, 241, 0.5)';
    
    particles.forEach(p => {
        p.x += p.vx;
        p.y += p.vy;
        if (p.x < 0 || p.x > W) p.vx *= -1;
        if (p.y < 0 || p.y > H) p.vy *= -1;
        
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fill();
    });
    
    requestAnimationFrame(loop);
}
loop();

window.addEventListener('resize', () => {
    W = canvas.width = window.innerWidth;
    H = canvas.height = window.innerHeight;
});
</script>

</body>
</html>