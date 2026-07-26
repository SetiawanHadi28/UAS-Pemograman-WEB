<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$user = $pdo->prepare("SELECT u.*, cv.* FROM users u 
                        LEFT JOIN cv_data cv ON u.id = cv.user_id 
                        WHERE u.id = ?");
$user->execute([$id]);
$user = $user->fetch();

if (!$user) {
    die("User tidak ditemukan");
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);
    $alamat = trim($_POST['alamat']);
    $deskripsi = trim($_POST['deskripsi']);
    $pendidikan = trim($_POST['pendidikan']);
    $pengalaman_kerja = trim($_POST['pengalaman_kerja']);
    $keahlian = trim($_POST['keahlian']);
    $status = $_POST['status'];
    $role = $_POST['role'];
    
    try {
        // Update user
        $stmt = $pdo->prepare("UPDATE users SET email = ?, status = ?, role = ? WHERE id = ?");
        $stmt->execute([$email, $status, $role, $id]);
        
        // Update CV
        $stmt = $pdo->prepare("UPDATE cv_data SET 
                                nama = ?, telepon = ?, alamat = ?, 
                                deskripsi = ?, pendidikan = ?, 
                                pengalaman_kerja = ?, keahlian = ? 
                                WHERE user_id = ?");
        $stmt->execute([$nama, $telepon, $alamat, $deskripsi, $pendidikan, $pengalaman_kerja, $keahlian, $id]);
        
        // Handle foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/photos/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed)) {
                $new_name = uniqid() . '.' . $ext;
                $upload_path = $upload_dir . $new_name;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                    $stmt = $pdo->prepare("UPDATE cv_data SET foto = ? WHERE user_id = ?");
                    $stmt->execute(['uploads/photos/' . $new_name, $id]);
                }
            }
        }
        
        $message = 'Data berhasil diperbarui!';
        $messageType = 'success';
        
        // Refresh data
        $user = $pdo->prepare("SELECT u.*, cv.* FROM users u 
                                LEFT JOIN cv_data cv ON u.id = cv.user_id 
                                WHERE u.id = ?");
        $user->execute([$id]);
        $user = $user->fetch();
        
    } catch(Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User — Admin Panel</title>
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
                <h1>Edit User</h1>
                <p>Mengedit data profil dan pengaturan akun</p>
            </div>
            <div class="topbar-actions">
                <a href="users.php" class="topbar-btn">← Kembali ke Data User</a>
            </div>
        </header>

        <div class="admin-content">
            <div class="admin-form-wrapper">
                
                <?php if($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <span><?php echo $messageType === 'success' ? '✅' : '⚠️'; ?></span>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    
                    <!-- ── Data Akun ── -->
                    <div class="admin-form-card">
                        <h2>🔒 Data Akun</h2>
                        <p class="card-desc">Informasi login dan otorisasi akses user.</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="opacity:0.6; cursor:not-allowed;">
                                <div style="font-size: 0.75rem; color:var(--admin-muted); margin-top:4px;">Username tidak bisa diubah.</div>
                            </div>
                            <div class="form-group">
                                <label>Email Akun</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Status Akun</label>
                                <select name="status">
                                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>✅ Active</option>
                                    <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>🚫 Inactive</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Role (Hak Akses)</label>
                                <select name="role">
                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>👤 User Biasa</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>👑 Administrator</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ── Data CV Pribadi ── -->
                    <div class="admin-form-card">
                        <h2>📄 Data CV (Personal)</h2>
                        <p class="card-desc">Informasi detail profil untuk ditampilkan di halaman CV.</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" value="<?php echo htmlspecialchars($user['nama'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>No. Telepon / WhatsApp</label>
                                <input type="text" name="telepon" value="<?php echo htmlspecialchars($user['telepon'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Alamat Lengkap</label>
                            <input type="text" name="alamat" value="<?php echo htmlspecialchars($user['alamat'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>Deskripsi / Tentang Saya</label>
                            <textarea name="deskripsi"><?php echo htmlspecialchars($user['deskripsi'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- ── Data CV Profesional ── -->
                    <div class="admin-form-card">
                        <h2>💼 Data CV (Profesional)</h2>
                        <p class="card-desc">Riwayat pendidikan, pekerjaan, dan daftar keahlian.</p>

                        <div class="form-group">
                            <label>Riwayat Pendidikan</label>
                            <textarea name="pendidikan" rows="4"><?php echo htmlspecialchars($user['pendidikan'] ?? ''); ?></textarea>
                            <div style="font-size: 0.75rem; color:var(--admin-muted); margin-top:4px;">Setiap baris baru akan menjadi poin terpisah.</div>
                        </div>

                        <div class="form-group">
                            <label>Pengalaman Kerja</label>
                            <textarea name="pengalaman_kerja" rows="5"><?php echo htmlspecialchars($user['pengalaman_kerja'] ?? ''); ?></textarea>
                            <div style="font-size: 0.75rem; color:var(--admin-muted); margin-top:4px;">Gunakan 2x baris kosong (Enter dua kali) untuk memisahkan antar pengalaman kerja.</div>
                        </div>

                        <div class="form-group">
                            <label>Daftar Keahlian (Skills)</label>
                            <input type="text" name="keahlian" value="<?php echo htmlspecialchars($user['keahlian'] ?? ''); ?>">
                            <div style="font-size: 0.75rem; color:var(--admin-muted); margin-top:4px;">Pisahkan dengan koma (contoh: PHP, JavaScript, CSS). Tools akan di-generate otomatis.</div>
                        </div>

                        <div class="form-group">
                            <label>Upload Foto Baru (Opsional)</label>
                            <input type="file" name="foto" accept="image/*" style="padding: 8px;">
                            <?php if(!empty($user['foto'])): ?>
                                <div style="margin-top: 12px; display: flex; align-items: center; gap: 12px;">
                                    <img src="../<?php echo $user['foto']; ?>" alt="Current Photo" style="width: 50px; height: 50px; border-radius: 10px; object-fit: cover; border: 1px solid var(--admin-border);">
                                    <span style="font-size: 0.8rem; color:var(--admin-muted);">Foto saat ini</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 28px; display: flex; gap: 12px;">
                        <button type="submit" class="btn-save">💾 Simpan Perubahan</button>
                        <a href="users.php" class="btn-cancel">Batal</a>
                    </div>
                </form>

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