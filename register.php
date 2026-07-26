<?php
require_once 'config/database.php';

// Jika sudah login, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: profile.php?u=' . urlencode($_SESSION['username']));
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    $confirm_password = md5(trim($_POST['confirm_password']));
    
    // Validasi input
    if (strlen($_POST['password']) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } else {
        // Cek username/email sudah ada
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username atau email sudah terdaftar!';
        } else {
            // Insert user baru
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, 'user', 'active')");
            $stmt->execute([$username, $password, $email]);
            
            // Buat CV default untuk user baru
            $user_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO cv_data (user_id, nama, email, deskripsi) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $username, $email, 'Saya adalah seorang profesional yang sedang membangun portofolio.']);
            
            $success = 'Pendaftaran berhasil! Mengalihkan ke halaman login...';
            echo "<script>setTimeout(() => { window.location.href = 'login.php'; }, 2500);</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — CV App</title>
    <meta name="description" content="Daftar akun baru di CV App">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Space+Grotesk:wght@300;400;500;600;700;800&family=Orbitron:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #080b14;
            --accent: #00ff88;
            --accent-2: #00c8ff;
            --accent-glow: rgba(0, 255, 136, 0.35);
            --text: #e8ecf8;
            --text-secondary: #8899cc;
            --muted: #4a5580;
            --border: rgba(0, 255, 136, 0.2);
            --border-subtle: rgba(255, 255, 255, 0.05);
            --panel: rgba(10, 15, 30, 0.9);
            --neon-green: #00ff88;
            --neon-blue: #00c8ff;
            --neon-purple: #bf5fff;
        }

        html { scroll-behavior: smooth; }

        body {
            min-height: 100vh;
            font-family: 'Space Grotesk', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow: hidden;
        }

        /* ── Animated Canvas ── */
        #bg-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        /* ── Layout ── */
        .login-wrapper {
            position: relative;
            z-index: 10;
            display: flex;
            width: 100%;
            max-width: 960px;
            min-height: 560px;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: 0 40px 100px rgba(0,0,0,0.6), 0 0 0 1px rgba(99,102,241,0.1);
            backdrop-filter: blur(2px);
        }

        /* ── Left Promo Panel ── */
        .promo-panel {
            flex: 1.1;
            background: linear-gradient(145deg, #0f0c29, #1a0733, #0f0c29);
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .promo-panel::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99,102,241,0.3) 0%, transparent 70%);
        }

        .promo-panel::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(139,92,246,0.2) 0%, transparent 70%);
        }

        .promo-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
        }

        .promo-logo-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 6px 20px var(--accent-glow);
        }

        .promo-logo span {
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .promo-content {
            position: relative;
            z-index: 1;
        }

        .promo-content h2 {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.04em;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #fff 0%, #c7d2fe 60%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .promo-content p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .promo-features {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .promo-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .promo-feature-dot {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: rgba(99,102,241,0.15);
            border: 1px solid rgba(99,102,241,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .promo-footer {
            position: relative;
            z-index: 1;
            font-size: 0.78rem;
            color: var(--muted);
        }

        /* ── Right Form Panel ── */
        .form-panel {
            flex: 1;
            background: var(--panel);
            padding: 40px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-left: 1px solid var(--border-subtle);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .form-header {
            margin-bottom: 28px;
        }

        .form-header h1 {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            margin-bottom: 6px;
        }

        .form-header p {
            color: var(--muted);
            font-size: 0.9rem;
        }

        /* ── Form Elements ── */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 8px;
            color: var(--text-secondary);
            letter-spacing: 0.02em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            pointer-events: none;
            opacity: 0.6;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            color: var(--text);
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
        }

        .form-group input::placeholder {
            color: var(--muted);
        }

        .form-group input:focus {
            outline: none;
            border-color: rgba(99, 102, 241, 0.6);
            background: rgba(99, 102, 241, 0.05);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        /* ── Error & Success ── */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            animation: shake 0.4s ease;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #6ee7b7;
            animation: none;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }

        /* ── Button ── */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 6px 20px var(--accent-glow);
            letter-spacing: 0.01em;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
            opacity: 0;
            transition: opacity 0.25s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px var(--accent-glow);
        }

        .btn-login:hover::before {
            opacity: 1;
        }

        /* ── Register Link ── */
        .register-prompt {
            margin-top: 24px;
            text-align: center;
            font-size: 0.875rem;
            color: var(--muted);
        }

        .register-prompt a {
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .register-prompt a:hover {
            color: var(--text);
            text-decoration: underline;
        }

        /* ── Responsive ── */
        @media (max-width: 720px) {
            body { padding: 16px; }
            .login-wrapper { flex-direction: column; min-height: auto; }
            .promo-panel { display: none; } /* Hide on small screens for register */
            .form-panel { padding: 32px 28px; border-left: none; border-radius: 28px; }
        }
    </style>
</head>
<body>

<canvas id="bg-canvas"></canvas>

<div class="login-wrapper">
    <!-- Left: Promo Panel -->
    <div class="promo-panel">
        <div class="promo-logo">
            <div class="promo-logo-icon">📄</div>
            <span>CV App</span>
        </div>

        <div class="promo-content">
            <h2>Mulai Bangun Karier Anda Hari Ini</h2>
            <p>Bergabunglah dengan profesional lain dan buat CV digital interaktif yang akan memukau para rekruter.</p>
            <div class="promo-features">
                <div class="promo-feature">
                    <div class="promo-feature-dot">🚀</div>
                    <span>Proses pendaftaran cepat & gratis</span>
                </div>
                <div class="promo-feature">
                    <div class="promo-feature-dot">🎨</div>
                    <span>Akses instan ke desain premium</span>
                </div>
                <div class="promo-feature">
                    <div class="promo-feature-dot">🔗</div>
                    <span>Link personal langsung aktif</span>
                </div>
            </div>
        </div>

        <div class="promo-footer">
            © 2026 CV App · Membangun portfolio masa depan
        </div>
    </div>

    <!-- Right: Form Panel -->
    <div class="form-panel">
        <div class="form-header">
            <h1>Daftar Akun 🚀</h1>
            <p>Buat akun baru untuk mengelola CV Anda</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-error">
                <span>⚠️</span>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success">
                <span>✅</span>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="register-form">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" id="username" name="username" placeholder="Pilih username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <div class="input-wrapper">
                    <span class="input-icon">📧</span>
                    <input type="email" id="email" name="email" placeholder="nama@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔑</span>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔐</span>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password Anda" required minlength="6">
                </div>
            </div>

            <button type="submit" class="btn-login" id="btn-submit">
                Daftar Sekarang →
            </button>
        </form>

        <div class="register-prompt">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </div>
    </div>
</div>

<script>
// ── Matrix Digital Rain Background ──
const canvas = document.getElementById('bg-canvas');
const ctx    = canvas.getContext('2d');
let W = canvas.width  = window.innerWidth;
let H = canvas.height = window.innerHeight;

const CHARS = '01アイウエオカキクケコサシスセソタチツテトナニヌネノ<>{}[]()ABCDEFabcdef01234789!@#$%^&*';
const FONT_SIZE = 14;
const cols = Math.floor(W / FONT_SIZE);
const drops = new Array(cols).fill(1).map(() => Math.random() * -50);
const speeds = new Array(cols).fill(0).map(() => 0.3 + Math.random() * 0.7);
const COLORS = ['#00ff88', '#00ff88', '#00ff88', '#00c8ff', '#bf5fff'];
const colColors = drops.map(() => COLORS[Math.floor(Math.random() * COLORS.length)]);

function drawMatrix() {
    ctx.fillStyle = 'rgba(8, 11, 20, 0.06)';
    ctx.fillRect(0, 0, W, H);
    for (let i = 0; i < cols; i++) {
        const x = i * FONT_SIZE;
        const y = drops[i] * FONT_SIZE;
        ctx.font = `bold ${FONT_SIZE}px 'JetBrains Mono', monospace`;
        ctx.fillStyle = '#ffffff';
        ctx.globalAlpha = 0.8;
        ctx.fillText(CHARS[Math.floor(Math.random() * CHARS.length)], x, y);
        ctx.fillStyle = colColors[i];
        ctx.globalAlpha = 0.5;
        ctx.font = `${FONT_SIZE}px monospace`;
        ctx.fillText(CHARS[Math.floor(Math.random() * CHARS.length)], x, y + FONT_SIZE);
        ctx.fillStyle = colColors[i];
        ctx.globalAlpha = 0.15;
        ctx.fillText(CHARS[Math.floor(Math.random() * CHARS.length)], x, y + FONT_SIZE * 2);
        ctx.globalAlpha = 1;
        if (y > H && Math.random() > 0.975) {
            drops[i] = 0;
            colColors[i] = COLORS[Math.floor(Math.random() * COLORS.length)];
        }
        drops[i] += speeds[i];
    }
}

let matrixInterval = setInterval(drawMatrix, 50);

window.addEventListener('resize', () => {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
});

// Button loading state
document.getElementById('register-form').addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit');
    btn.textContent = 'Memproses...';
    btn.style.opacity = '0.8';
    btn.style.cursor = 'wait';
});
</script>
</body>
</html>