<?php
require_once 'config/database.php';

if (!isLoggedIn() || isAdmin()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data CV user
$stmt = $pdo->prepare("SELECT * FROM cv_data WHERE user_id = ?");
$stmt->execute([$user_id]);
$cv = $stmt->fetch();

if (!$cv) {
    // Buat CV kosong jika belum ada
    $stmt = $pdo->prepare("INSERT INTO cv_data (user_id, nama, email) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $_SESSION['username'], '']);
    $stmt = $pdo->prepare("SELECT * FROM cv_data WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cv = $stmt->fetch();
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
    
    try {
        $stmt = $pdo->prepare("UPDATE cv_data SET 
                                nama = ?, email = ?, telepon = ?, alamat = ?, 
                                deskripsi = ?, pendidikan = ?, 
                                pengalaman_kerja = ?, keahlian = ? 
                                WHERE user_id = ?");
        $stmt->execute([$nama, $email, $telepon, $alamat, $deskripsi, $pendidikan, $pengalaman_kerja, $keahlian, $user_id]);
        
        // Handle foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/photos/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed)) {
                $new_name = uniqid() . '.' . $ext;
                $upload_path = $upload_dir . $new_name;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                    $stmt = $pdo->prepare("UPDATE cv_data SET foto = ? WHERE user_id = ?");
                    $stmt->execute(['uploads/photos/' . $new_name, $user_id]);
                }
            }
        }
        
        $message = 'CV berhasil diperbarui!';
        $messageType = 'success';
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM cv_data WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cv = $stmt->fetch();
        
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Space+Grotesk:wght@300;400;500;600;700;800&family=Orbitron:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="assets/css/edit.css?v=<?php echo filemtime('assets/css/edit.css'); ?>">
</head>
<body class="edit-page">

<div class="nav-top">
    <div class="brand">
        <strong>✏️ Edit CV</strong>
        <span class="user-badge">👤 <?php echo $_SESSION['username']; ?></span>
    </div>
    <div class="nav-links">
        <a href="index.php">🏠 Lihat CV</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</div>

<div class="edit-container">
    <div class="edit-header">
        <h1>✏️ Edit CV Saya</h1>
        <p class="subtitle">Perbarui informasi CV Anda</p>
    </div>

    <?php if($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($cv['nama']); ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($cv['email']); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Telepon</label>
                <input type="text" name="telepon" class="form-control" value="<?php echo htmlspecialchars($cv['telepon']); ?>">
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <input type="text" name="alamat" class="form-control" value="<?php echo htmlspecialchars($cv['alamat']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Deskripsi / Tentang Saya</label>
            <textarea name="deskripsi" class="form-control" rows="3"><?php echo htmlspecialchars($cv['deskripsi']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Pendidikan</label>
            <textarea name="pendidikan" class="form-control" rows="3"><?php echo htmlspecialchars($cv['pendidikan']); ?></textarea>
            <span class="hint">Pisahkan dengan baris baru</span>
        </div>

        <div class="form-group">
            <label>Pengalaman Kerja</label>
            <textarea name="pengalaman_kerja" class="form-control" rows="4"><?php echo htmlspecialchars($cv['pengalaman_kerja']); ?></textarea>
            <span class="hint">Pisahkan setiap pengalaman dengan baris kosong</span>
        </div>

        <div class="form-group">
            <label>Keahlian</label>
            <input type="text" name="keahlian" class="form-control" value="<?php echo htmlspecialchars($cv['keahlian']); ?>">
            <span class="hint">Pisahkan dengan koma</span>
        </div>

        <div class="form-group">
            <label>Foto Profile</label>
            <div class="file-input-wrapper">
                <input type="file" name="foto" class="form-control" accept="image/*">
                <?php if($cv['foto']): ?>
                    <img src="<?php echo htmlspecialchars($cv['foto']); ?>" alt="Foto" class="current-photo">
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Batalkan</a>
        </div>
    </form>
</div>

</body>
</html>