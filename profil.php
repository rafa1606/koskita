<?php
require_once 'config/db.php';
requireLogin();
$user_id = $_SESSION['user_id'];
$tab     = $_GET['tab'] ?? 'profil';
$success = '';
$errors  = [];
$res  = mysqli_query($conn, "SELECT * FROM user WHERE id = $user_id LIMIT 1");
$user = mysqli_fetch_assoc($res);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    $nama    = trim($_POST['nama']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    if ($nama === '')  $errors[] = 'Nama wajib diisi.';
    if ($email === '') $errors[] = 'Email wajib diisi.';
    if ($email !== '') {
        $email_e = mysqli_real_escape_string($conn, $email);
        $cek = mysqli_query($conn, "SELECT id FROM user WHERE email = '$email_e' AND id != $user_id LIMIT 1");
        if (mysqli_num_rows($cek) > 0) {
            $errors[] = 'Email sudah digunakan oleh akun lain.';
        }
    }
    $foto_profil = $user['foto_profil'];
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        $file_tmp    = $_FILES['foto_profil']['tmp_name'];
        $file_size   = $_FILES['foto_profil']['size'];
        $ext         = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) {
            $errors[] = 'Format foto tidak valid. Hanya jpg, png, atau webp.';
        } elseif ($file_size > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran foto maksimal 2MB.';
        } else {
            $upload_dir = __DIR__ . '/assets/img/profil/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $nama_file_baru = 'user_' . $user_id . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($file_tmp, $upload_dir . $nama_file_baru)) {
                $errors[] = 'Gagal mengupload foto. Coba lagi.';
            } else {
                if (!empty($user['foto_profil'])) {
                    $lama = $upload_dir . $user['foto_profil'];
                    if (file_exists($lama)) unlink($lama);
                }
                $foto_profil = $nama_file_baru;
            }
        }
    }
    if (empty($errors)) {
        $nama_e    = mysqli_real_escape_string($conn, $nama);
        $email_e   = mysqli_real_escape_string($conn, $email);
        $telepon_e = mysqli_real_escape_string($conn, $telepon);
        $foto_e    = mysqli_real_escape_string($conn, $foto_profil);
        mysqli_query($conn, "UPDATE user SET nama='$nama_e', email='$email_e', telepon='$telepon_e', foto_profil='$foto_e' WHERE id = $user_id");
        $_SESSION['user_nama'] = $nama;
            $_SESSION['user_foto'] = $foto_profil;
        $res  = mysqli_query($conn, "SELECT * FROM user WHERE id = $user_id LIMIT 1");
        $user = mysqli_fetch_assoc($res);
        $success = 'Profil berhasil diperbarui.';
    }
    $tab = 'profil';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_password'])) {
    $pass_lama    = $_POST['pass_lama']    ?? '';
    $pass_baru    = $_POST['pass_baru']    ?? '';
    $pass_konfirm = $_POST['pass_konfirm'] ?? '';
    if ($pass_lama === '')          $errors[] = 'Password lama wajib diisi.';
    if (strlen($pass_baru) < 6)    $errors[] = 'Password baru minimal 6 karakter.';
    if ($pass_baru !== $pass_konfirm) $errors[] = 'Konfirmasi password tidak cocok.';
    if (empty($errors)) {
        if (!password_verify($pass_lama, $user['password'])) {
            $errors[] = 'Password lama yang kamu masukkan salah.';
        } else {
            $hash   = password_hash($pass_baru, PASSWORD_DEFAULT);
            $hash_e = mysqli_real_escape_string($conn, $hash);
            mysqli_query($conn, "UPDATE user SET password = '$hash_e' WHERE id = $user_id");
            $success = 'Password berhasil diubah.';
        }
    }
    $tab = 'password';
}
$reservasis = [];
if ($tab === 'reservasi') {
    $sql_r = "SELECT r.id, k2.nama_kos, k.nomor_kamar, t.nama_tipe,
                     r.tanggal_masuk, r.durasi, k.harga,
                     (r.durasi * k.harga) AS total_harga,
                     r.status, r.tanggal_pesan
              FROM reservasi r
              JOIN kamar k      ON r.id_kamar = k.id
              JOIN kos k2       ON k.id_kos   = k2.id
              JOIN tipe_kamar t ON k.id_tipe  = t.id
              WHERE r.id_user = $user_id
              ORDER BY r.tanggal_pesan DESC";
    $res_r      = mysqli_query($conn, $sql_r);
    $reservasis = mysqli_fetch_all($res_r, MYSQLI_ASSOC);
}
$inisial    = strtoupper(substr($user['nama'], 0, 1));
$has_foto   = !empty($user['foto_profil']) && file_exists(__DIR__ . '/assets/img/profil/' . $user['foto_profil']);
$page_title = 'Profil Saya';
include 'includes/header.php';
?>
<style>
.profil-wrap {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 767px) {
    .profil-wrap { grid-template-columns: 1fr; }
}
.profil-sidebar {
    background: var(--kk-surface);
    border-radius: var(--kk-radius-lg);
    border: 1px solid var(--kk-border);
    overflow: hidden;
    box-shadow: var(--kk-shadow);
}
.profil-sidebar-header {
    padding: 2rem 1.5rem 1.5rem;
    text-align: center;
    border-bottom: 1px solid var(--kk-border);
}
.profil-avatar {
    width: 80px; height: 80px;
    border-radius: 50%;
    margin: 0 auto .9rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; font-weight: 800; color: #fff;
    background: linear-gradient(135deg, var(--kk-blue), var(--kk-blue-dark));
    border: 3px solid rgba(255,255,255,.12);
    overflow: hidden;
    flex-shrink: 0;
}
.profil-avatar img {
    width: 100%; height: 100%; object-fit: cover;
}
.profil-sidebar-name {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 700; font-size: 1.05rem; color: var(--kk-text);
    margin-bottom: .2rem;
}
.profil-sidebar-email {
    font-size: .78rem; color: var(--kk-muted);
    margin-bottom: .65rem;
    word-break: break-all;
}
.badge-role {
    display: inline-block;
    padding: 3px 12px; border-radius: 50px;
    font-size: .7rem; font-weight: 700; letter-spacing: .04em;
    text-transform: uppercase;
}
.badge-role-penyewa { background: rgba(59,130,246,.2); color: var(--kk-blue-dark); }
.badge-role-admin   { background: rgba(249,115,22,.25);  color: #fdba74; }
.profil-nav { padding: .75rem 0; }
.profil-nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: .75rem 1.5rem;
    font-size: .88rem; font-weight: 500;
    color: var(--kk-muted);
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all .2s;
}
.profil-nav-item:hover {
    color: var(--kk-text);
    background: #f1f5f9;
}
.profil-nav-item.active {
    color: var(--kk-blue-dark);
    background: rgba(59,130,246,.1);
    border-left-color: var(--kk-blue);
    font-weight: 600;
}
.profil-nav-item i { font-size: 1rem; width: 18px; text-align: center; }
.profil-panel {
    background: var(--kk-surface);
    border-radius: var(--kk-radius-lg);
    border: 1px solid var(--kk-border);
    overflow: hidden;
    box-shadow: var(--kk-shadow);
}
.profil-panel-header {
    padding: 1.4rem 2rem;
    border-bottom: 1px solid var(--kk-border);
    background: #f8fafc;
}
.profil-panel-title {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.1rem; font-weight: 700;
    color: var(--kk-text); margin: 0;
}
.profil-panel-body { padding: 2rem; }
.profil-label {
    font-size: .8rem; font-weight: 700; color: var(--kk-text);
    margin-bottom: .4rem; display: block;
}
.profil-input {
    border: 1px solid var(--kk-border);
    border-radius: 10px;
    padding: .65rem 1rem;
    font-size: .9rem; color: var(--kk-text);
    background: #ffffff;
    transition: border-color .2s, box-shadow .2s;
    width: 100%; outline: none;
}
.profil-input::placeholder { color: rgba(0,0,0,.4); }
.profil-input:focus {
    border-color: var(--kk-blue);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
    background: #ffffff;
}
.profil-btn {
    background: var(--kk-blue);
    color: #fff; border: none;
    border-radius: 10px; font-weight: 700;
    font-size: .9rem; padding: .65rem 1.75rem;
    cursor: pointer;
    box-shadow: var(--kk-shadow-blue);
    transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex; align-items: center; gap: 7px;
}
.profil-btn:hover {
    background: var(--kk-blue-dark);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
    transform: translateY(-2px);
}
.profil-btn:active {
    transform: translateY(0) scale(0.97);
}
.profil-alert {
    border-radius: 10px; padding: .75rem 1rem;
    font-size: .88rem; display: flex; align-items: flex-start; gap: 9px;
    margin-bottom: 1.5rem;
}
.profil-alert-success { background: rgba(22,163,74,.12); border: 1px solid rgba(22,163,74,.3); color: #6ee7a0; }
.profil-alert-error   { background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.3); color: #fca5a5; }
.foto-upload-area {
    display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.foto-upload-preview {
    width: 60px; height: 60px; border-radius: 50%;
    background: linear-gradient(135deg, var(--kk-blue), var(--kk-blue-dark));
    display: flex; align-items: center; justify-content: center;
    color: var(--kk-text); font-weight: 800; font-size: 1.3rem;
    overflow: hidden; flex-shrink: 0;
    border: 2px solid rgba(59, 130, 246, 0.4);
}
.foto-upload-preview img { width: 100%; height: 100%; object-fit: cover; }
.reservasi-badge {
    display: inline-block; padding: 3px 12px;
    border-radius: 50px; font-size: .75rem; font-weight: 700;
}
.reservasi-badge-pending  { background: rgba(202,138,4,.15);  color: #fcd34d; }
.reservasi-badge-diterima { background: rgba(22,163,74,.15);  color: #6ee7a0; }
.reservasi-badge-ditolak  { background: rgba(220,38,38,.15);  color: #fca5a5; }
</style>
<main class="flex-grow-1 py-5" style="background:var(--kk-dark)">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0" style="font-size:.82rem">
                <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none" style="color:var(--kk-muted)">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="color:var(--kk-text); font-weight:600;">Profil Saya</li>
            </ol>
        </nav>
        <div class="profil-wrap">
            <aside class="profil-sidebar">
                <div class="profil-sidebar-header">
                    <div class="profil-avatar">
                        <?php if ($has_foto): ?>
                            <img src="<?= BASE_URL ?>/assets/img/profil/<?= htmlspecialchars($user['foto_profil']) ?>"
                                 alt="Foto <?= htmlspecialchars($user['nama']) ?>">
                        <?php else: ?>
                            <?= $inisial ?>
                        <?php endif; ?>
                    </div>
                    <div class="profil-sidebar-name"><?= htmlspecialchars($user['nama']) ?></div>
                    <div class="profil-sidebar-email"><?= htmlspecialchars($user['email']) ?></div>
                    <span class="badge-role <?= $user['role'] === 'admin' ? 'badge-role-admin' : 'badge-role-penyewa' ?>">
                        <?= ucfirst($user['role']) ?>
                    </span>
                </div>
                <nav class="profil-nav">
                    <a href="?tab=profil"
                       class="profil-nav-item <?= $tab === 'profil' ? 'active' : '' ?>">
                        <i class="bi bi-person-fill"></i> Profile Info
                    </a>
                    <a href="?tab=password"
                       class="profil-nav-item <?= $tab === 'password' ? 'active' : '' ?>">
                        <i class="bi bi-shield-lock-fill"></i> Ganti Password
                    </a>
                    <a href="?tab=reservasi"
                       class="profil-nav-item <?= $tab === 'reservasi' ? 'active' : '' ?>">
                        <i class="bi bi-journal-check"></i> Riwayat Reservasi
                    </a>
                </nav>
            </aside>
            <div class="profil-panel">
                <?php
                if ($success): ?>
                    <div class="profil-alert profil-alert-success mx-4 mt-4 mb-0">
                        <i class="bi bi-check-circle-fill flex-shrink-0 mt-1"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="profil-alert profil-alert-error mx-4 mt-4 mb-0">
                        <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-1"></i>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php  if ($tab === 'profil'): ?>
                    <div class="profil-panel-header">
                        <p class="profil-panel-title">
                            <i class="bi bi-person-fill me-2" style="color:var(--kk-blue)"></i>Profile Info
                        </p>
                    </div>
                    <div class="profil-panel-body">
                        <form method="POST" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="update_profil" value="1">
                            <div class="mb-4">
                                <label class="profil-label">Foto Profil</label>
                                <div class="foto-upload-area">
                                    <div class="foto-upload-preview" id="sidePreview">
                                        <?php if ($has_foto): ?>
                                            <img id="previewImg"
                                                 src="<?= BASE_URL ?>/assets/img/profil/<?= htmlspecialchars($user['foto_profil']) ?>"
                                                 alt="">
                                        <?php else: ?>
                                            <span id="previewInisial"><?= $inisial ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <input type="file" name="foto_profil" id="inputFoto"
                                               class="form-control form-control-sm"
                                               style="border-radius:8px;max-width:260px;background:#ffffff;border-color:var(--kk-border);color:var(--kk-text)"
                                               accept=".jpg,.jpeg,.png,.webp">
                                        <div class="form-text mt-1" style="font-size:.76rem;color:var(--kk-muted)">
                                            Format jpg, png, webp. Maks 2MB.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="profil-label" for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" id="nama" name="nama"
                                       class="profil-input"
                                       value="<?= htmlspecialchars($user['nama']) ?>"
                                       placeholder="Nama lengkap kamu" required>
                            </div>
                            <div class="mb-3">
                                <label class="profil-label" for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email"
                                       class="profil-input"
                                       value="<?= htmlspecialchars($user['email']) ?>"
                                       placeholder="nama@email.com" required>
                            </div>
                            <div class="mb-4">
                                <label class="profil-label" for="telepon">No. Telepon</label>
                                <input type="text" id="telepon" name="telepon"
                                       class="profil-input"
                                       value="<?= htmlspecialchars($user['telepon'] ?? '') ?>"
                                       placeholder="08xxxxxxxxxx">
                            </div>
                            <button type="submit" class="profil-btn">
                                <i class="bi bi-floppy"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                <?php  elseif ($tab === 'password'): ?>
                    <div class="profil-panel-header">
                        <p class="profil-panel-title">
                            <i class="bi bi-shield-lock-fill me-2" style="color:var(--kk-blue)"></i>Ganti Password
                        </p>
                    </div>
                    <div class="profil-panel-body">
                        <form method="POST" novalidate>
                            <input type="hidden" name="ganti_password" value="1">
                            <div class="mb-3">
                                <label class="profil-label" for="pass_lama">Password Lama <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" id="pass_lama" name="pass_lama"
                                           class="profil-input" style="padding-right:2.8rem"
                                           placeholder="Masukkan password lama">
                                    <button type="button" class="btn-toggle-pass" data-target="pass_lama">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="profil-label" for="pass_baru">Password Baru <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" id="pass_baru" name="pass_baru"
                                           class="profil-input" style="padding-right:2.8rem"
                                           placeholder="Minimal 6 karakter">
                                    <button type="button" class="btn-toggle-pass" data-target="pass_baru">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                                <div class="form-text" style="font-size:.76rem;color:var(--kk-muted)">Minimal 6 karakter.</div>
                            </div>
                            <div class="mb-4">
                                <label class="profil-label" for="pass_konfirm">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" id="pass_konfirm" name="pass_konfirm"
                                           class="profil-input" style="padding-right:2.8rem"
                                           placeholder="Ulangi password baru">
                                    <button type="button" class="btn-toggle-pass" data-target="pass_konfirm">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="profil-btn">
                                <i class="bi bi-lock-fill"></i> Ubah Password
                            </button>
                        </form>
                    </div>
                <?php  elseif ($tab === 'reservasi'): ?>
                    <div class="profil-panel-header">
                        <p class="profil-panel-title">
                            <i class="bi bi-journal-check me-2" style="color:var(--kk-blue)"></i>Riwayat Reservasi
                        </p>
                    </div>
                    <div class="profil-panel-body p-0">
                        <?php if (empty($reservasis)): ?>
                            <div class="text-center py-5 px-3">
                                <i class="bi bi-journal-x" style="font-size:3rem;color:var(--kk-muted)"></i>
                                <p class="mt-3 mb-2 fw-semibold" style="color:var(--kk-muted)">Belum ada riwayat reservasi.</p>
                                <a href="<?= BASE_URL ?>/index.php#kos-list" class="profil-btn text-decoration-none d-inline-flex">
                                    <i class="bi bi-search"></i> Cari Kos
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size:.85rem">
                                    <thead style="background:#f8fafc">
                                        <tr>
                                            <th class="px-4 py-3 fw-semibold" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">#</th>
                                            <th class="py-3 fw-semibold" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Kos / Kamar</th>
                                            <th class="py-3 fw-semibold" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Tipe</th>
                                            <th class="py-3 fw-semibold" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Tgl Masuk</th>
                                            <th class="py-3 fw-semibold" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Durasi</th>
                                            <th class="py-3 fw-semibold" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Total</th>
                                            <th class="py-3 pe-4 fw-semibold" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reservasis as $i => $r):
                                            if ($r['status'] === 'diterima') {
                                                $badge_cls = 'reservasi-badge-diterima';
                                            } elseif ($r['status'] === 'ditolak') {
                                                $badge_cls = 'reservasi-badge-ditolak';
                                            } else {
                                                $badge_cls = 'reservasi-badge-pending';
                                            }
                                        ?>
                                        <tr>
                                            <td class="px-4" style="color:var(--kk-muted)"><?= $i + 1 ?></td>
                                            <td>
                                                <p class="fw-semibold mb-0" style="color:var(--kk-text)"><?= htmlspecialchars($r['nama_kos']) ?></p>
                                                <p class="mb-0" style="font-size:.78rem;color:var(--kk-muted)">Kamar <?= htmlspecialchars($r['nomor_kamar']) ?></p>
                                            </td>
                                            <td><?= htmlspecialchars($r['nama_tipe']) ?></td>
                                            <td><?= date('d M Y', strtotime($r['tanggal_masuk'])) ?></td>
                                            <td><?= $r['durasi'] ?> bln</td>
                                            <td class="fw-semibold" style="color:var(--kk-blue)">
                                                Rp <?= number_format($r['total_harga'], 0, ',', '.') ?>
                                            </td>
                                            <td class="pe-4">
                                                <span class="reservasi-badge <?= $badge_cls ?>">
                                                    <?= ucfirst($r['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<style>
.btn-toggle-pass {
    position: absolute; right: .75rem; top: 50%;
    transform: translateY(-50%);
    background: none; border: none; padding: 0;
    color: rgba(255,255,255,.3); cursor: pointer; font-size: .95rem;
    line-height: 1;
}
.btn-toggle-pass:hover { color: #2563eb; }
</style>
<script>
document.getElementById('inputFoto').addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
        var preview = document.getElementById('sidePreview');
        preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover">';
    };
    reader.readAsDataURL(file);
});
document.querySelectorAll('.btn-toggle-pass').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var targetId = this.getAttribute('data-target');
        var input    = document.getElementById(targetId);
        var icon     = this.querySelector('i');
        if (input.type === 'password') {
            input.type    = 'text';
            icon.className = 'bi bi-eye';
        } else {
            input.type    = 'password';
            icon.className = 'bi bi-eye-slash';
        }
    });
});
</script>
<?php include 'includes/footer.php'; ?>
