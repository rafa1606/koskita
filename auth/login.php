<?php
require_once '../config/db.php';
if (isset($_SESSION['user_id'])) {
    redirect(in_array($_SESSION['role'], ['admin', 'pemilik']) ? 'admin/dashboard.php' : 'index.php');
}
$error = '';
$stat_kos   = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM kos"))[0] ?? 0);
$stat_area  = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM area"))[0] ?? 0);
$stat_kamar = (int)(mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM kamar WHERE status='tersedia'"))[0] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn,
            "SELECT id, nama, password, role, foto_profil FROM user WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['user_foto'] = $user['foto_profil'] ?? '';
            redirect(in_array($user['role'], ['admin', 'pemilik']) ? 'admin/dashboard.php' : 'index.php');
        } else {
            $error = 'Email atau password salah. Silakan coba lagi.';
        }
    }
}
$bg_images = [];
$res_bg = mysqli_query($conn, "SELECT foto FROM kos WHERE foto != '' LIMIT 16");
if ($res_bg) {
    while ($row = mysqli_fetch_assoc($res_bg)) {
        if (file_exists(__DIR__ . '/../assets/img/kos/' . $row['foto'])) {
            $bg_images[] = $row['foto'];
        }
    }
}
if (count($bg_images) > 0) {
    while (count($bg_images) < 16) {
        $bg_images = array_merge($bg_images, $bg_images);
    }
    $bg_images = array_slice($bg_images, 0, 16);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | KosKita</title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/logo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <style>
        body {
            margin: 0; padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f172a; /* Dark fallback */
            min-height: 100vh;
            overflow-x: hidden;
        }
        .auth-wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center; /* Center the form or flex-end to push right */
        }
        .bg-grid {
            position: fixed;
            inset: -50px; /* Bleed to avoid edges when rotating/scaling */
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            z-index: 1;
            transform: rotate(-4deg) scale(1.1); /* Slanted grid aesthetic */
        }
        @media (max-width: 768px) {
            .bg-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .bg-grid-item {
            width: 100%;
            height: 300px;
            background-size: cover;
            background-position: center;
            border-radius: 16px;
            opacity: 0.6;
        }
        .bg-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6); /* Dark tint */
            backdrop-filter: blur(2px);
            z-index: 2;
        }
        .auth-content {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 500px;
            padding: 1rem;
        }
        .auth-card-modern {
            background: #ffffff;
            border-radius: 32px;
            padding: 3.5rem 3.5rem;
            box-shadow: 0 24px 64px rgba(0,0,0,0.25);
            width: 100%;
        }
        .auth-card-modern h1 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--kk-blue); /* Senada dengan UI yang diminta */
            margin-bottom: 2rem; text-align: center;
            letter-spacing: -1px;
        }
        .auth-card-modern .step-text {
            text-align: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            display: block;
        }
        .form-label-modern {
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.35rem;
            display: flex;
        }
        .form-control-modern {
            background: #f1f5f9;
            border: none;
            border-radius: 12px;
            padding: 1rem 1.2rem;
            font-size: 0.95rem;
            font-weight: 500;
            color: #0f172a;
            width: 100%;
            margin-bottom: 1.25rem;
            transition: all 0.2s ease;
            outline: none;
        }
        .form-control-modern::placeholder {
            color: #94a3b8;
        }
        .form-control-modern:focus {
            background: #ffffff;
            box-shadow: 0 0 0 2px var(--kk-blue), 0 4px 12px rgba(6,182,212,0.15);
        }
        .btn-modern {
            background: var(--kk-blue);
            color: #ffffff;
            border: none;
            border-radius: 50px;
            padding: 1rem;
            width: 100%;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
            display: flex; justify-content: center; align-items: center; gap: 8px;
        }
        .btn-modern:hover {
            background: var(--kk-blue-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(6,182,212,0.3);
        }
        .auth-link-modern {
            text-align: center;
            font-size: 0.85rem;
            font-weight: 500;
            color: #64748b;
            margin-top: 1.5rem;
        }
        .auth-link-modern a {
            color: var(--kk-blue);
            font-weight: 700;
            text-decoration: none;
        }
        .auth-link-modern a:hover {
            text-decoration: underline;
        }
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #ef4444;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex; gap: 8px; align-items: center;
        }
        .input-icon-wrap { position: relative; }
        .toggle-pass {
            position: absolute; right: 1rem; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; cursor: pointer; font-size: 1rem;
            background: none; border: none; padding: 0;
            margin-top: -10px; /* Offset for the bottom margin of form-control */
        }
        .toggle-pass:hover { color: var(--kk-blue); }
        .role-tabs {
            display: flex; background: #f1f5f9; border-radius: 12px; padding: 4px; margin-bottom: 1.5rem;
        }
        .role-tab-btn {
            flex: 1; border: none; background: transparent; padding: 0.6rem;
            font-weight: 600; font-size: 0.85rem; color: #64748b;
            border-radius: 8px; transition: all 0.2s ease; cursor: pointer;
        }
        .role-tab-btn.active {
            background: #ffffff; color: var(--kk-blue); box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
<div class="bg-grid">
    <?php if(!empty($bg_images)): ?>
        <?php foreach($bg_images as $img): ?>
            <div class="bg-grid-item" style="background-image: url('<?= BASE_URL ?>/assets/img/kos/<?= htmlspecialchars($img) ?>')"></div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Fallbacks if no images -->
        <?php for($i=0; $i<16; $i++): ?>
            <div class="bg-grid-item" style="background: #1e293b;"></div>
        <?php endfor; ?>
    <?php endif; ?>
</div>
<div class="bg-overlay"></div>
<div class="auth-wrapper">
    <div class="auth-content">
        <div class="auth-card-modern">
            <a href="<?= BASE_URL ?>/index.php" style="display:inline-block; margin-bottom:1.5rem; color:#64748b; text-decoration:none; font-weight:700; font-size:0.8rem; letter-spacing:1px; text-transform:uppercase;">
                <i class="bi bi-arrow-left"></i> KEMBALI
            </a>
            <div class="mb-4 text-center">
                <span class="step-text text-center">KosKita Login</span>
                <h1 style="margin-bottom: 0.5rem; text-align: center;">Sign in</h1>
                <p class="text-muted" style="font-size: 0.95rem; line-height: 1.5; color: #64748b;">Temukan kos impianmu dengan mudah, cepat, dan aman hanya di KosKita.</p>
            </div>
            <?php if ($error): ?>
                <div class="error-box">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <!-- Tabs for Login Role (Visual only, usually role is determined by user, but let's keep the tabs to match previous logic) -->
                <div class="role-tabs">
                    <button type="button" class="role-tab-btn active" data-tab="penyewa" onclick="switchTab('penyewa')">Pencari Kos</button>
                    <button type="button" class="role-tab-btn" data-tab="pemilik" onclick="switchTab('pemilik')">Pemilik / Admin</button>
                </div>
                <label class="form-label-modern" for="email">Email*</label>
                <input type="email" id="email" name="email" class="form-control-modern" placeholder="Enter your email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <label class="form-label-modern" for="password">Password*</label>
                <div class="input-icon-wrap">
                    <input type="password" id="password" name="password" class="form-control-modern" placeholder="Enter your password" required>
                    <button type="button" class="toggle-pass" onclick="togglePwd('password', 'iconPwd1')" tabindex="-1">
                        <i class="bi bi-eye-slash" id="iconPwd1"></i>
                    </button>
                </div>
                <button type="submit" class="btn-modern">
                    Sign In
                </button>
            </form>
            <div class="auth-link-modern">
                Don't have an account? <a href='register.php'>Sign up</a>
            </div>
        </div>
    </div>
</div>
<script>
    function togglePwd(id, iconId) {
        const input = document.getElementById(id);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye-slash';
        }
    }
    function switchTab(tab) {
        document.querySelectorAll('.role-tab-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tab));
    }
</script>
</body>
</html>
