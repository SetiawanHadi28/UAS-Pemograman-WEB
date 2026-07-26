<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$messageType = '';

// Ganti password admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old = md5($_POST['old_password']);
    $new = md5($_POST['new_password']);
    $confirm = md5($_POST['confirm_password']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND password = ?");
    $stmt->execute([$_SESSION['user_id'], $old]);
    
    if ($stmt->rowCount() > 0) {
        if ($new === $confirm) {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new, $_SESSION['user_id']]);
            $message = 'Password berhasil diubah!';
            $messageType = 'success';
        } else {
            $message = 'Password baru tidak cocok dengan konfirmasi!';
            $messageType = 'error';
        }
    } else {
        $message = 'Password lama salah!';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan — Admin Panel</title>
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
            <a href="users.php">
                <div class="nav-icon">👥</div>
                Kelola User
            </a>
            
            <div class="sidebar-nav-label">Pengaturan</div>
            <a href="settings.php" class="active">
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
                <h1>Pengaturan Sistem</h1>
                <p>Ubah password dan lihat info sistem</p>
            </div>
            <div class="topbar-actions">
                <!-- Actions if needed -->
            </div>
        </header>

        <div class="admin-content">
            <div class="admin-form-wrapper" style="max-width: 500px;">
                
                <?php if($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <span><?php echo $messageType === 'success' ? '✅' : '⚠️'; ?></span>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="admin-form-card">
                    <h2>🔑 Ganti Password Admin</h2>
                    <p class="card-desc">Pastikan untuk menggunakan kombinasi huruf dan angka.</p>

                    <form method="POST">
                        <div class="form-group">
                            <label>Password Lama</label>
                            <input type="password" name="old_password" placeholder="Masukkan password lama" required>
                        </div>
                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" name="new_password" placeholder="Masukkan password baru" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 24px;">
                            <label>Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" placeholder="Ulangi password baru" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="change_password" class="btn-save" style="width: 100%;">💾 Ganti Password</button>
                        </div>
                    </form>
                </div>

                <div class="admin-form-card" style="margin-top: 24px;">
                    <h2>ℹ️ Info Sistem</h2>
                    <p class="card-desc">Informasi teknis database.</p>
                    <div style="font-size: 0.85rem; color: var(--admin-secondary); line-height: 1.6;">
                        <strong>PHP Version:</strong> <?php echo phpversion(); ?><br>
                        <strong>Total User Terdaftar:</strong> <?php echo $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?><br>
                        <strong>Total CV Data:</strong> <?php echo $pdo->query("SELECT COUNT(*) FROM cv_data")->fetchColumn(); ?>
                    </div>
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