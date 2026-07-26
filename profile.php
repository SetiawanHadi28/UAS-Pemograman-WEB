<?php
// File: profile.php

// Ambil username dari URL atau nama file
if (isset($_GET['u'])) {
    $username = $_GET['u'];
} else {
    $username = basename($_SERVER['PHP_SELF'], '.php');
}

require_once 'config/database.php';
require_once 'config/tools_helper.php';

// Cari user berdasarkan username
$stmt = $pdo->prepare("SELECT u.*, cv.* FROM users u 
                        JOIN cv_data cv ON u.id = cv.user_id 
                        WHERE u.username = ? AND u.status = 'active'");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    die("<!DOCTYPE html><html><head><meta charset='UTF-8'><title>404</title>
    <style>body{background:#040d1a;color:#eef2ff;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:Inter,sans-serif;flex-direction:column;gap:16px;margin:0}
    h1{font-size:4rem;margin:0;background:linear-gradient(135deg,#6366f1,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
    p{color:#6b82a8}a{color:#a5b4fc;text-decoration:none;border:1px solid rgba(99,102,241,.3);padding:10px 22px;border-radius:30px;transition:all .2s}
    a:hover{background:rgba(99,102,241,.15);color:#c7d2fe}</style></head>
    <body><h1>404</h1><p>User tidak ditemukan</p><a href='index.php'>← Kembali ke CV Default</a></body></html>");
}

// Parse data
$nama      = htmlspecialchars($user['nama'] ?: $user['username']);
$email     = htmlspecialchars($user['email']);
$telepon   = htmlspecialchars($user['telepon']);
$alamat    = htmlspecialchars($user['alamat']);
$deskripsi = nl2br(htmlspecialchars($user['deskripsi']));
$keahlian  = array_filter(array_map('trim', explode(',', $user['keahlian'])));
$tools_list = get_tools_from_skills(array_values($keahlian));
$foto      = $user['foto'] ?: 'uploads/default/default.jpg';
$role      = $user['role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV — <?php echo $nama; ?></title>
    <meta name="description" content="CV digital <?php echo $nama; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Space+Grotesk:wght@300;400;500;600;700;800&family=Orbitron:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime('assets/css/style.css'); ?>">
</head>
<body>

<canvas id="bg-canvas"></canvas>

<div class="page-wrapper">

<!-- NAVBAR -->
<nav class="nav-top" id="navbar">
    <div class="nav-brand">
        <div class="nav-brand-icon">📄</div>
        <strong>CV App</strong>
        <span class="cv-badge">📄 <?php echo $username; ?></span>
    </div>
    <div class="nav-links">
        <a href="index.php">🏠 Default CV</a>
        <?php if(isset($_SESSION['username'])): ?>
            <?php if($_SESSION['role'] === 'admin'): ?>
                <a href="admin/index.php">⚙️ Admin</a>
            <?php else: ?>
                <a href="edit.php">✏️ Edit CV</a>
            <?php endif; ?>
            <a href="logout.php">🚪 Logout</a>
        <?php else: ?>
            <a href="login.php" class="nav-primary">🔑 Login</a>
        <?php endif; ?>
    </div>
</nav>

<!-- CV CONTENT -->
<div class="app">

    <aside class="sidebar fade-in">
        <div class="sidebar-brand">
            <div class="avatar-small">
                <img src="<?php echo $foto; ?>" alt="Foto <?php echo $nama; ?>">
            </div>
            <div>
                <h2><?php echo $nama; ?></h2>
                <p>@<?php echo $username; ?></p>
            </div>
        </div>
        <nav class="sidebar-menu">
            <a href="#about">👤 Tentang</a>
            <a href="#skills">💡 Keahlian</a>
            <a href="#experience">💼 Pengalaman</a>
            <a href="#education">🎓 Pendidikan</a>
            <a href="#contact">📬 Kontak</a>
        </nav>
    </aside>

    <main class="main-content">

        <!-- Hero Card -->
        <div class="top-card fade-in">
            <div class="top-card-info">
                <p class="label">Portfolio &amp; CV Digital</p>
                <h1><?php echo $nama; ?></h1>
                <p class="role">
                    <?php echo $role === 'admin' ? 'Administrator' : 'Professional'; ?>
                    &nbsp;·&nbsp; <?php echo $alamat ?: 'Indonesia'; ?>
                </p>
                <div class="top-card-actions">
                    <a href="#contact" class="btn-hero btn-hero-primary">📬 Hubungi Saya</a>
                    <a href="#about" class="btn-hero btn-hero-outline">Lihat Profil →</a>
                </div>
            </div>
            <div class="avatar-large">
                <img src="<?php echo $foto; ?>" alt="Foto <?php echo $nama; ?>">
                <div class="avatar-status" title="Online"></div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row fade-in">
            <div class="stat-card">
                <strong class="stat-number" data-target="5">0</strong>
                <span>Proyek</span>
            </div>
            <div class="stat-card">
                <strong class="stat-number" data-target="3">0</strong>
                <span>Sertifikat</span>
            </div>
            <div class="stat-card">
                <strong class="stat-number" data-target="3.85">0</strong>
                <span>IPK</span>
            </div>
        </div>

        <!-- About -->
        <section id="about" class="panel fade-in">
            <div class="panel-header">
                <div class="panel-header-icon">👤</div>
                <h2>Tentang Saya</h2>
            </div>
            <p class="panel-text"><?php echo $deskripsi; ?></p>
        </section>

        <!-- Skills -->
        <section id="skills" class="panel fade-in">
            <div class="panel-header">
                <div class="panel-header-icon">💡</div>
                <h2>Keahlian</h2>
            </div>
            <div class="grid-two">
                <div class="info-box">
                    <h3>📋 Kompetensi Utama</h3>
                    <div class="skills-container">
                        <?php foreach($keahlian as $skill): ?>
                            <?php if(trim($skill) !== ''): ?>
                                <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php foreach($tools_list as $tool_group): ?>
                <div class="info-box">
                    <h3><?php echo $tool_group['icon']; ?> <?php echo htmlspecialchars($tool_group['label']); ?></h3>
                    <ul>
                        <?php foreach($tool_group['items'] as $item): ?>
                            <li><?php echo htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Experience -->
        <section id="experience" class="panel fade-in">
            <div class="panel-header">
                <div class="panel-header-icon">💼</div>
                <h2>Pengalaman</h2>
            </div>
            <div class="timeline">
                <?php
                $experiences = explode("\n\n", $user['pengalaman_kerja']);
                foreach($experiences as $exp):
                    if(trim($exp) == '') continue;
                    $lines   = explode("\n", $exp);
                    $title   = trim($lines[0] ?? '');
                    $company = trim($lines[1] ?? '');
                    $desc    = array_slice($lines, 2);
                ?>
                <div class="timeline-item">
                    <span class="timeline-date">📅 <?php echo htmlspecialchars($title); ?></span>
                    <h3><?php echo htmlspecialchars($company); ?></h3>
                    <ul>
                        <?php foreach($desc as $d): ?>
                            <?php $d = trim($d); if($d == '' || $d == '•') continue; ?>
                            <li><?php echo htmlspecialchars(ltrim($d, '•· ')); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Education -->
        <section id="education" class="panel fade-in">
            <div class="panel-header">
                <div class="panel-header-icon">🎓</div>
                <h2>Pendidikan</h2>
            </div>
            <div class="data-grid">
                <?php
                $edu_lines = explode("\n", $user['pendidikan']);
                foreach($edu_lines as $edu):
                    $edu = trim($edu);
                    if($edu == '') continue;
                ?>
                <div class="data-row">
                    <span><?php echo htmlspecialchars($edu); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Contact -->
        <section id="contact" class="panel fade-in">
            <div class="panel-header">
                <div class="panel-header-icon">📬</div>
                <h2>Kontak</h2>
            </div>
            <div class="contact-grid">
                <div>
                    <strong>📱 Telepon</strong>
                    <p><?php echo $telepon ?: 'Belum diisi'; ?></p>
                </div>
                <div>
                    <strong>📧 Email</strong>
                    <p><?php echo $email; ?></p>
                </div>
                <div>
                    <strong>📍 Lokasi</strong>
                    <p><?php echo $alamat ?: 'Belum diisi'; ?></p>
                </div>
                <div>
                    <strong>🔗 CV Link</strong>
                    <p>
                        <a href="profile.php?u=<?php echo $username; ?>">
                            profile.php?u=<?php echo $username; ?>
                        </a>
                    </p>
                </div>
            </div>
        </section>

    </main>
</div>

<!-- FAB Edit CV (hanya untuk pemilik CV) -->
<?php if(isset($_SESSION['username']) && $_SESSION['username'] === $username): ?>
    <a href="edit.php" class="btn-edit-cv">✏️ Edit CV Saya</a>
<?php endif; ?>

</div><!-- .page-wrapper -->

<script src="assets/js/script.js?v=<?php echo filemtime('assets/js/script.js'); ?>"></script>
</body>
</html>
