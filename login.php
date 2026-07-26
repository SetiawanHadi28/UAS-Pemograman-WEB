<?php
require_once 'config/database.php';

// Jika sudah login, redirect langsung
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: profile.php?u=' . urlencode($_SESSION['username']));
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND status = 'active'");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: profile.php?u=' . urlencode($user['username']));
        }
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — CV App</title>
    <meta name="description" content="Login ke CV App untuk mengelola portfolio dan CV digital Anda.">
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
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(0, 255, 136, 0.15);
            box-shadow: 0 40px 100px rgba(0,0,0,0.7), 0 0 60px rgba(0,255,136,0.04);
            backdrop-filter: blur(2px);
        }

        /* ── Left Promo Panel ── */
        .promo-panel {
            flex: 1.1;
            background: linear-gradient(145deg, #080b14, #0a1020, #050810);
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            border-right: 1px solid rgba(0,255,136,0.08);
        }

        .promo-panel::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0,255,136,0.08) 0%, transparent 70%);
        }

        .promo-panel::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0,200,255,0.06) 0%, transparent 70%);
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
            background: linear-gradient(135deg, var(--neon-green), var(--neon-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 6px 20px var(--accent-glow);
        }

        .promo-logo span {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            font-family: 'Orbitron', monospace;
            color: var(--neon-green);
            text-shadow: 0 0 20px var(--accent-glow);
        }

        .promo-content {
            position: relative;
            z-index: 1;
        }

        .promo-content h2 {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #fff 0%, var(--neon-green) 60%, var(--neon-blue) 100%);
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
            border-radius: 6px;
            background: rgba(0,255,136,0.08);
            border: 1px solid rgba(0,255,136,0.2);
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
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-left: 1px solid rgba(0,255,136,0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .form-header {
            margin-bottom: 36px;
        }

        .form-header h1 {
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 6px;
            font-family: 'Space Grotesk', sans-serif;
            background: linear-gradient(135deg, #ffffff, var(--neon-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-header p {
            color: var(--muted);
            font-size: 0.9rem;
        }

        /* ── Form Elements ── */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 8px;
            color: var(--text-secondary);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-family: 'JetBrains Mono', monospace;
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
            padding: 13px 16px 13px 44px;
            background: rgba(0,255,136,0.03);
            border: 1px solid rgba(0,255,136,0.1);
            border-radius: 8px;
            color: var(--text);
            font-size: 0.92rem;
            font-family: 'JetBrains Mono', monospace;
            transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
        }

        .form-group input::placeholder {
            color: var(--muted);
        }

        .form-group input:focus {
            outline: none;
            border-color: rgba(0, 255, 136, 0.5);
            background: rgba(0, 255, 136, 0.05);
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.08), 0 0 20px rgba(0,255,136,0.05);
        }

        /* ── Error ── */
        .alert-error {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            animation: shake 0.4s ease;
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
            padding: 13px;
            background: linear-gradient(135deg, rgba(0,255,136,0.15), rgba(0,200,255,0.15));
            border: 1px solid rgba(0, 255, 136, 0.35);
            border-radius: 8px;
            color: var(--neon-green);
            font-size: 0.95rem;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 0 20px rgba(0,255,136,0.08);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(0,255,136,0.1), transparent);
            opacity: 0;
            transition: opacity 0.25s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 40px rgba(0,255,136,0.2), 0 8px 25px rgba(0,0,0,0.3);
            border-color: rgba(0, 255, 136, 0.6);
        }

        .btn-login:hover::before {
            opacity: 1;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* ── Divider ── */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0 20px;
            color: var(--muted);
            font-size: 0.8rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-subtle);
        }

        /* ── Demo Accounts ── */
        .demo-accounts {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .demo-account-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: rgba(0,255,136,0.02);
            border: 1px solid rgba(0,255,136,0.08);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            text-align: left;
            font-family: 'JetBrains Mono', monospace;
            color: var(--text-secondary);
            font-size: 0.82rem;
        }

        .demo-account-btn:hover {
            background: rgba(0,255,136,0.06);
            border-color: rgba(0,255,136,0.2);
            color: var(--neon-green);
            transform: translateX(4px);
        }

        .demo-badge {
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .demo-badge.admin {
            background: rgba(255, 230, 0, 0.1);
            color: #ffe600;
            border: 1px solid rgba(255, 230, 0, 0.2);
        }

        .demo-badge.user {
            background: rgba(0,255,136,0.1);
            color: var(--neon-green);
            border: 1px solid rgba(0,255,136,0.2);
        }

        .demo-creds {
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
        }

        /* ── Register Link ── */
        .register-prompt {
            margin-top: 24px;
            text-align: center;
            font-size: 0.875rem;
            color: var(--muted);
        }

        .register-prompt a {
            color: var(--neon-green);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
            font-family: 'JetBrains Mono', monospace;
        }

        .register-prompt a:hover {
            color: var(--neon-blue);
            text-shadow: 0 0 10px rgba(0,255,136,0.4);
            text-decoration: underline;
        }

        /* ── Responsive ── */
        @media (max-width: 720px) {
            body { padding: 16px; }
            .login-wrapper { flex-direction: column; min-height: auto; }
            .promo-panel { padding: 32px 28px; }
            .promo-content h2 { font-size: 1.6rem; }
            .form-panel { padding: 32px 28px; }
        }

        @media (max-width: 480px) {
            .promo-panel { display: none; }
            .form-panel { border-radius: 24px; padding: 36px 24px; }
            .login-wrapper { border-radius: 24px; }
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
            <h2>Buat CV Digital yang Profesional</h2>
            <p>Tampilkan portfolio dan riwayat hidup Anda secara online dengan tampilan yang elegan dan modern.</p>
            <div class="promo-features">
                <div class="promo-feature">
                    <div class="promo-feature-dot">⚡</div>
                    <span>Buat dan edit CV kapan saja, di mana saja</span>
                </div>
                <div class="promo-feature">
                    <div class="promo-feature-dot">🔗</div>
                    <span>Bagikan link CV Anda ke rekruter dengan mudah</span>
                </div>
                <div class="promo-feature">
                    <div class="promo-feature-dot">🎨</div>
                    <span>Tampilan modern dan responsif di semua perangkat</span>
                </div>
                <div class="promo-feature">
                    <div class="promo-feature-dot">🔒</div>
                    <span>Data Anda aman dan terenkripsi</span>
                </div>
            </div>
        </div>

        <div class="promo-footer">
            © 2026 CV App · Semua hak dilindungi
        </div>
    </div>

    <!-- Right: Form Panel -->
    <div class="form-panel">
        <div class="form-header">
            <h1>Selamat Datang 👋</h1>
            <p>Masuk untuk mengelola CV digital Anda</p>
        </div>

        <?php if($error): ?>
            <div class="alert-error">
                <span>⚠️</span>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username Anda"
                        autocomplete="username"
                        required
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔑</span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password Anda"
                        autocomplete="current-password"
                        required>
                </div>
            </div>
            <button type="submit" class="btn-login" id="btn-submit">
                Masuk →
            </button>
        </form>

        <!-- Demo Accounts -->
        <div class="divider">atau coba akun demo</div>
        <div class="demo-accounts">
            <button class="demo-account-btn" onclick="fillDemo('cecep_suwanda', 'cecep123')">
                <span class="demo-badge user">User</span>
                <span class="demo-creds">cecep_suwanda / cecep123</span>
                <span style="margin-left:auto; opacity:0.5;">→</span>
            </button>
            <button class="demo-account-btn" onclick="fillDemo('setiawan_hadi', 'hadi123')">
                <span class="demo-badge user">User</span>
                <span class="demo-creds">setiawan_hadi / hadi123</span>
                <span style="margin-left:auto; opacity:0.5;">→</span>
            </button>
        </div>

        <div class="register-prompt">
            Belum punya akun? <a href="register.php">Daftar sekarang</a>
        </div>
    </div>
</div>

<script>
// ── Fill demo credentials & auto-submit ──
function fillDemo(username, password) {
    const uField = document.getElementById('username');
    const pField = document.getElementById('password');

    // Animate typing effect
    uField.value = '';
    pField.value = '';

    let i = 0;
    const typeU = setInterval(() => {
        uField.value += username[i++];
        if (i >= username.length) {
            clearInterval(typeU);
            let j = 0;
            const typeP = setInterval(() => {
                pField.value += password[j++];
                if (j >= password.length) {
                    clearInterval(typeP);
                    // Brief pause then submit
                    setTimeout(() => {
                        document.getElementById('login-form').submit();
                    }, 300);
                }
            }, 40);
        }
    }, 40);
}

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

// ── Button loading state ──
document.getElementById('login-form').addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit');
    btn.textContent = 'Memproses...';
    btn.style.opacity = '0.8';
    btn.disabled = true;
    // Re-enable jika error (page reload)
    setTimeout(() => {
        btn.textContent = 'Masuk →';
        btn.style.opacity = '1';
        btn.disabled = false;
    }, 3000);
});
</script>
</body>
</html>