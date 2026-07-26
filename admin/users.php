<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Ambil semua user
$users = $pdo->query("SELECT u.*, cv.nama, cv.is_default FROM users u 
                       LEFT JOIN cv_data cv ON u.id = cv.user_id 
                       ORDER BY u.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User — Admin Panel</title>
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
            <a href="index.php">
                <div class="nav-icon">📊</div>
                Dashboard
            </a>
            <a href="users.php" class="active">
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
                <h1>Kelola User</h1>
                <p>Manajemen data akun dan hak akses pengguna</p>
            </div>
            <div class="topbar-actions">
                <a href="../register.php" class="topbar-btn primary">➕ Register Akun Baru</a>
            </div>
        </header>

        <div class="admin-content">
            <div class="admin-table-wrapper">
                <div class="table-header">
                    <h2>Semua User</h2>
                    <span style="color:var(--admin-muted); font-size: 0.85rem;">Total: <?php echo count($users); ?> user</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Default CV</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach($users as $user): ?>
                            <tr>
                                <td style="color:var(--admin-muted);"><?php echo $i++; ?></td>
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
                                    <?php if($user['is_default']): ?>
                                        <span style="color:#6ee7b7; font-size: 0.85rem; font-weight: 600;">⭐ Default</span>
                                    <?php else: ?>
                                        <a href="set_default.php?id=<?php echo $user['id']; ?>" class="btn-default">Jadikan Default</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn-edit" title="Edit User">✏️</a>
                                        <a href="../profile.php?u=<?php echo urlencode($user['username']); ?>" target="_blank" class="btn-view" title="Lihat Profil">👁️</a>
                                        <?php if($user['role'] !== 'admin'): ?>
                                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('Hapus user ini? Semua data CV yang bersangkutan akan terhapus juga secara permanen.')" title="Hapus User">🗑️</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </main>
</div>

<script>
// Simple Canvas BG
const canvas = document.getElementById('bg-canvas');
const ctx = canvas.getContext('2d');
let W = canvas.width = window.innerWidth;
let H = canvas.height = window.innerHeight;
const particles = [];
for (let i = 0; i < 40; i++) {
    particles.push({
        x: Math.random() * W, y: Math.random() * H,
        vx: (Math.random() - 0.5) * 0.5, vy: (Math.random() - 0.5) * 0.5,
        r: Math.random() * 2 + 1
    });
}
function loop() {
    ctx.clearRect(0, 0, W, H);
    ctx.fillStyle = 'rgba(99, 102, 241, 0.5)';
    particles.forEach(p => {
        p.x += p.vx; p.y += p.vy;
        if (p.x < 0 || p.x > W) p.vx *= -1;
        if (p.y < 0 || p.y > H) p.vy *= -1;
        ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2); ctx.fill();
    });
    requestAnimationFrame(loop);
}
loop();
window.addEventListener('resize', () => { W = canvas.width = window.innerWidth; H = canvas.height = window.innerHeight; });
</script>
</body>
</html>