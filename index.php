<?php
require_once 'config/database.php';

// ── SMART ROUTER ──────────────────────────────────────────────
// Jika belum login → arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Jika admin → arahkan ke admin panel
if ($_SESSION['role'] === 'admin') {
    header('Location: admin/index.php');
    exit;
}

// Jika user biasa → arahkan ke CV miliknya
header('Location: profile.php?u=' . urlencode($_SESSION['username']));
exit;
// ─────────────────────────────────────────────────────────────

// Ambil CV default (is_default = TRUE)
$stmt = $pdo->query("SELECT cv.*, u.username, u.role 
                      FROM cv_data cv 
                      JOIN users u ON cv.user_id = u.id 
                      WHERE cv.is_default = TRUE 
                      LIMIT 1");
$cv = $stmt->fetch();

if (!$cv) {
    $stmt = $pdo->query("SELECT cv.*, u.username, u.role 
                          FROM cv_data cv 
                          JOIN users u ON cv.user_id = u.id 
                          LIMIT 1");
    $cv = $stmt->fetch();
}

// Parse data
$nama       = htmlspecialchars($cv['nama']);
$email      = htmlspecialchars($cv['email']);
$telepon    = htmlspecialchars($cv['telepon']);
$alamat     = htmlspecialchars($cv['alamat']);
$deskripsi  = nl2br(htmlspecialchars($cv['deskripsi']));
$keahlian   = array_filter(array_map('trim', explode(',', $cv['keahlian'])));
$foto       = $cv['foto'] ?: 'uploads/default/default.jpg';
$username   = $cv['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV — <?php echo $nama; ?></title>
    <meta name="description" content="Portfolio dan CV digital <?php echo $nama; ?>. <?php echo strip_tags($cv['deskripsi']); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Space+Grotesk:wght@300;400;500;600;700;800&family=Orbitron:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime('assets/css/style.css'); ?>">
</head>
<body>

<!-- Animated Canvas Background -->
<canvas id="bg-canvas"></canvas>

<div class="page-wrapper">

<!-- =================== NAVBAR =================== -->
<nav class="nav-top" id="navbar">
    <div class="nav-brand">
        <div class="nav-brand-icon">📄</div>
        <strong>CV App</strong>
        <?php if(isset($_SESSION['username'])): ?>
            <span class="user-badge">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <?php endif; ?>
    </div>
    <div class="nav-links">
        <?php if(isset($_SESSION['username'])): ?>
            <?php if($_SESSION['role'] === 'admin'): ?>
                <a href="admin/index.php">⚙️ Admin</a>
            <?php else: ?>
                <a href="edit.php">✏️ Edit CV</a>
            <?php endif; ?>
            <a href="logout.php">🚪 Logout</a>
        <?php else: ?>
            <a href="login.php">🔑 Login</a>
            <a href="register.php" class="nav-primary">📝 Daftar</a>
        <?php endif; ?>
    </div>
</nav>

<!-- =================== CV CONTENT =================== -->
<div class="app">

    <!-- Sticky Sidebar Nav -->
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
                <p class="label">✦ Portfolio &amp; CV Digital</p>
                <h1><?php echo $nama; ?></h1>
                <p class="role">
                    <?php
                    $role_label = $cv['role'] === 'admin' ? 'Administrator' : 'Professional';
                    echo $role_label;
                    ?>
                    &nbsp;·&nbsp; <?php echo $alamat ?: 'Indonesia'; ?>
                </p>
                <div class="top-card-actions">
                    <a href="#contact" class="btn-hero btn-hero-primary">📬 Hubungi Saya</a>
                    <a href="#about" class="btn-hero btn-hero-outline">Lihat Profil →</a>
                </div>
            </div>
            <div class="avatar-large">
                <img src="<?php echo $foto; ?>" alt="Foto <?php echo $nama; ?>">
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
                                <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="info-box">
                    <h3>🛠️ Tools &amp; Teknologi</h3>
                    <ul>
                        <li>PHP · MySQL · JavaScript</li>
                        <li>HTML5 · CSS3 · Bootstrap</li>
                        <li>Git · GitHub · VS Code</li>
                        <li>Linux · Docker · REST API</li>
                    </ul>
                </div>
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
                $experiences = explode("\n\n", $cv['pengalaman_kerja']);
                foreach($experiences as $exp):
                    if(trim($exp) == '') continue;
                    $lines = explode("\n", $exp);
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
                $edu_lines = explode("\n", $cv['pendidikan']);
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

<!-- FAB Edit CV -->
<?php if(isset($_SESSION['username']) && $_SESSION['role'] !== 'admin'): ?>
    <a href="edit.php" class="btn-edit-cv">✏️ Edit CV Saya</a>
<?php endif; ?>

</div><!-- .page-wrapper -->

<script src="assets/js/script.js?v=<?php echo filemtime('assets/js/script.js'); ?>"></script>
</body>
</html>