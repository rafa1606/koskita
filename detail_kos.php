<?php
require_once 'config/db.php';
$kos_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($kos_id <= 0) redirect('index.php');
$stmt = mysqli_prepare($conn, "SELECT * FROM kos WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $kos_id);
mysqli_stmt_execute($stmt);
$kos = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$kos) redirect('index.php');
$pemilik = null;
if (!empty($kos['id_pemilik'])) {
    $sp = mysqli_prepare($conn, "SELECT nama, role FROM user WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($sp, 'i', $kos['id_pemilik']);
    mysqli_stmt_execute($sp);
    $pemilik = mysqli_fetch_assoc(mysqli_stmt_get_result($sp));
}
$stmt2 = mysqli_prepare($conn,
    "SELECT km.*, t.nama_tipe FROM kamar km
     JOIN tipe_kamar t ON t.id = km.id_tipe
     WHERE km.id_kos = ? ORDER BY km.harga ASC");
mysqli_stmt_bind_param($stmt2, 'i', $kos_id);
mysqli_stmt_execute($stmt2);
$kamar_list = mysqli_fetch_all(mysqli_stmt_get_result($stmt2), MYSQLI_ASSOC);
$kamar_tersedia = array_filter($kamar_list, fn($k) => $k['status'] === 'tersedia');
$total_tersedia = count($kamar_tersedia);
$harga_min      = $total_tersedia
    ? min(array_column(array_values($kamar_tersedia), 'harga'))
    : ($kamar_list ? min(array_column($kamar_list, 'harga')) : 0);
$semua_fasilitas = [];
foreach ($kamar_list as $km) {
    if (!empty($km['fasilitas'])) {
        foreach (array_map('trim', explode(',', $km['fasilitas'])) as $f) {
            if ($f !== '') $semua_fasilitas[strtolower($f)] = ucwords(strtolower($f));
        }
    }
}
ksort($semua_fasilitas);
$icon_map = [
    'ac'                  => 'bi-snow',
    'wifi'                => 'bi-wifi',
    'kasur'               => 'bi-bookmarks-fill',
    'lemari'              => 'bi-box-seam',
    'meja'                => 'bi-columns-gap',
    'meja belajar'        => 'bi-columns-gap',
    'kursi'               => 'bi-ui-radios',
    'kamar mandi dalam'   => 'bi-droplet-fill',
    'kamar mandi luar'    => 'bi-droplet',
    'water heater'        => 'bi-thermometer-sun',
    'tv'                  => 'bi-tv-fill',
    'parkir'              => 'bi-p-circle-fill',
    'parkir motor'        => 'bi-p-circle-fill',
    'parkir mobil'        => 'bi-p-circle-fill',
    'kulkas'              => 'bi-box',
    'dapur'               => 'bi-fire',
    'dapur bersama'       => 'bi-fire',
    'laundry'             => 'bi-basket-fill',
    'jemuran'             => 'bi-wind',
    'dispenser'           => 'bi-cup-hot-fill',
    'listrik'             => 'bi-lightning-charge-fill',
    'cctv'                => 'bi-camera-video-fill',
    'security'            => 'bi-shield-check',
    'lift'                => 'bi-arrow-up-square-fill',
    'kolam renang'        => 'bi-water',
    'gym'                 => 'bi-activity',
    'mushola'             => 'bi-geo-alt-fill',
    'ruang tamu'          => 'bi-house-door-fill',
    'balkon'              => 'bi-aspect-ratio-fill',
    'jendela'             => 'bi-window',
    'meja rias'           => 'bi-eye',
    'jemuran handuk'      => 'bi-wind',
];
$foto_path = __DIR__ . '/assets/img/kos/' . ($kos['foto'] ?? '');
$has_foto  = !empty($kos['foto']) && file_exists($foto_path);
$inisial_pemilik = $pemilik ? strtoupper(substr($pemilik['nama'], 0, 1)) : 'P';
$page_title = htmlspecialchars($kos['nama_kos']);
include 'includes/header.php';
?>
<style>
.dk-page { background: var(--kk-dark); min-height: 60vh; padding: 3rem 0 5rem; }
.dk-breadcrumb { 
    font-size: 1.05rem; 
    margin-bottom: 2rem; 
    font-weight: 500;
}
.dk-breadcrumb a { color: rgba(255,255,255,.55); text-decoration: none; transition: color 0.2s; }
.dk-breadcrumb a:hover { color: var(--kk-blue); }
.dk-breadcrumb .sep { color: rgba(255,255,255,.2); margin: 0 .6rem; }
.dk-breadcrumb .cur { color: rgba(255,255,255,.85); font-weight: 600; }
.dk-hero-img {
    width: 100%; height: 440px; object-fit: cover;
    border-radius: var(--kk-radius-lg);
    display: block;
    box-shadow: var(--kk-shadow-md);
    border: 1px solid var(--kk-border);
}
.dk-hero-placeholder {
    width: 100%; height: 440px; border-radius: var(--kk-radius-lg);
    background: #f1f5f9;
    display: flex; align-items: center; justify-content: center;
    font-size: 6rem; color: rgba(255,255,255,.15);
    box-shadow: var(--kk-shadow-md);
    border: 1px solid var(--kk-border);
}
.dk-panel {
    background: var(--kk-surface) !important;
    border: 1px solid var(--kk-border);
    border-radius: var(--kk-radius-lg);
    padding: 2.25rem;
    margin-bottom: 1.75rem;
    box-shadow: var(--kk-shadow);
}
.dk-kos-name {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 2.2rem; font-weight: 800; color: #fff;
    line-height: 1.2; margin: 0 0 .75rem;
    letter-spacing: -0.03em;
}
.dk-alamat {
    font-size: 1.1rem; color: rgba(255,255,255,.6);
    display: flex; align-items: flex-start; gap: 8px;
    margin-bottom: 1.25rem;
}
.dk-alamat i { color: var(--kk-orange); flex-shrink: 0; margin-top: 3px; font-size: 1.2rem; }
.dk-desc {
    font-size: 1.1rem; color: rgba(255,255,255,.75);
    line-height: 1.8; margin: 0;
}
.dk-panel-title {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.25rem; font-weight: 800; color: #fff;
    margin: 0 0 1.5rem;
    display: flex; align-items: center; gap: 10px;
    letter-spacing: -0.01em;
}
.dk-panel-title i { color: var(--kk-blue); font-size: 1.35rem; }
.dk-fasil-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .8rem;
}
.dk-fasil-item {
    display: flex; align-items: center; gap: 10px;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.05);
    border-radius: var(--kk-radius-sm);
    padding: .8rem 1.1rem;
    font-size: 1rem; color: rgba(255,255,255,.8);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.dk-fasil-item:hover {
    background: rgba(59, 130, 246, 0.08);
    border-color: rgba(59, 130, 246, 0.2);
    color: #fff;
    transform: translateY(-1px);
}
.dk-fasil-item i { color: var(--kk-blue); font-size: 1.15rem; flex-shrink: 0; }
@media (max-width: 575px) { .dk-fasil-grid { grid-template-columns: repeat(2, 1fr); } }
.dk-sidebar {
    position: sticky; top: 90px;
}
.dk-sidebar-card {
    background: var(--kk-surface) !important;
    border: 1px solid var(--kk-border);
    border-radius: var(--kk-radius-lg);
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--kk-shadow-md);
}
.dk-price-label { font-size: 0.95rem; color: rgba(255,255,255,.45); margin-bottom: 6px; font-weight: 500; }
.dk-price-num {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 2rem; font-weight: 800; color: var(--kk-orange);
    line-height: 1;
}
.dk-price-unit { font-size: 1rem; color: rgba(255,255,255,.45); font-weight: 500; }
.dk-avail-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(22,163,74,.12);
    border: 1px solid rgba(22,163,74,.25);
    color: #4ade80; border-radius: 12px;
    font-size: 1rem; font-weight: 700;
    padding: .6rem 1.2rem; margin: 1.25rem 0;
    width: 100%; justify-content: center;
}
.dk-full-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(220,38,38,.12);
    border: 1px solid rgba(220,38,38,.25);
    color: #f87171; border-radius: 12px;
    font-size: 1rem; font-weight: 700;
    padding: .6rem 1.2rem; margin: 1.25rem 0;
    width: 100%; justify-content: center;
}
.dk-btn-pesan {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 1rem;
    background: var(--kk-blue); color: #fff;
    border: none; border-radius: var(--kk-radius-sm);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.1rem; font-weight: 800;
    text-decoration: none; cursor: pointer;
    transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--kk-shadow-blue);
}
.dk-btn-pesan:hover {
    background: var(--kk-blue-dark); color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(59,130,246,.45);
}
.dk-btn-pesan:active {
    transform: translateY(0) scale(0.97);
}
.dk-btn-pesan:disabled,
.dk-btn-pesan.disabled {
    background: rgba(255,255,255,.05) !important; color: rgba(255,255,255,.25) !important;
    cursor: not-allowed; box-shadow: none !important; transform: none !important;
    border: 1px solid rgba(255,255,255,.05) !important;
}
.dk-btn-login {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 0.9rem;
    background: transparent;
    border: 1.5px solid var(--kk-blue); color: var(--kk-blue);
    border-radius: var(--kk-radius-sm);
    font-size: 1.1rem; font-weight: 700;
    text-decoration: none;
    transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
}
.dk-btn-login:hover { background: rgba(59,130,246,.1); color: var(--kk-blue); transform: translateY(-2px); }
.dk-btn-back {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 0.85rem;
    background: transparent;
    border: 1px solid rgba(255,255,255,.12); color: rgba(255,255,255,.55);
    border-radius: var(--kk-radius-sm);
    font-size: 1.05rem; font-weight: 600;
    text-decoration: none;
    transition: all .2s;
}
.dk-btn-back:hover { border-color: rgba(255,255,255,.3); color: #fff; }
.dk-owner-avatar {
    width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--kk-blue-light), var(--kk-blue));
    display: flex; align-items: center; justify-content: center;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.15rem; font-weight: 800; color: #fff;
    box-shadow: 0 4px 12px rgba(59,130,246,0.25);
}
.dk-owner-name {
    font-size: 1.05rem; font-weight: 700; color: #fff; margin-bottom: 2px;
}
.dk-owner-role {
    font-size: 0.85rem; color: rgba(255,255,255,.45);
    text-transform: uppercase; letter-spacing: .06em;
}
.dk-kamar-card {
    background: rgba(255,255,255,.015);
    border: 1px solid rgba(255,255,255,.05);
    border-radius: var(--kk-radius-sm);
    padding: 1.25rem 1.5rem;
    display: flex; align-items: center; justify-content: space-between;
    gap: 1.25rem;
    margin-bottom: 0.85rem;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.dk-kamar-card:last-child { margin-bottom: 0; }
.dk-kamar-card:hover { 
    background: rgba(255,255,255,.04); 
    border-color: rgba(59, 130, 246, 0.3) !important;
    transform: scale(1.01) translateY(-1px);
}
.dk-kamar-num {
    font-size: 1.2rem; font-weight: 800; color: #fff;
}
.dk-kamar-tipe {
    font-size: 1rem; color: rgba(255,255,255,.5);
    margin-top: 2px;
}
.dk-kamar-harga {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.3rem; font-weight: 800; color: var(--kk-orange); white-space: nowrap;
}
.dk-kamar-harga small { font-size: 0.9rem; color: rgba(255,255,255,.4); font-weight: 500; }
.dk-kamar-penuh {
    font-size: 0.88rem; color: #f87171; font-weight: 700;
    padding: 4px 12px; background: rgba(220,38,38,.12);
    border: 1px solid rgba(220,38,38,.25);
    border-radius: 50px;
}
</style>
<div class="dk-page">
<div class="container">
    <nav class="dk-breadcrumb">
        <a href="<?= BASE_URL ?>/index.php"><i class="bi bi-house me-1"></i>Beranda</a>
        <span class="sep">›</span>
        <a href="<?= BASE_URL ?>/index.php#kos-list">Daftar Kos</a>
        <span class="sep">›</span>
        <span class="cur"><?= htmlspecialchars($kos['nama_kos']) ?></span>
    </nav>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="mb-4">
                <?php if ($has_foto): ?>
                    <img src="<?= BASE_URL ?>/assets/img/kos/<?= htmlspecialchars($kos['foto']) ?>"
                         class="dk-hero-img"
                         alt="<?= htmlspecialchars($kos['nama_kos']) ?>">
                <?php else: ?>
                    <div class="dk-hero-placeholder">
                        <i class="bi bi-buildings-fill"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="dk-panel">
                <h1 class="dk-kos-name"><?= htmlspecialchars($kos['nama_kos']) ?></h1>
                <div class="dk-alamat">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span><?= htmlspecialchars($kos['alamat']) ?></span>
                </div>
                <?php if (!empty($kos['latitude']) && !empty($kos['longitude'])): ?>
                <a href="https://maps.google.com/?q=<?= $kos['latitude'] ?>,<?= $kos['longitude'] ?>"
                   target="_blank" rel="noopener"
                   style="font-size:.82rem;color:#3b82f6;text-decoration:none;display:inline-flex;align-items:center;gap:5px">
                    <i class="bi bi-map"></i>Lihat di Google Maps
                </a>
                <?php endif; ?>
            </div>
            <?php if (!empty($kos['deskripsi'])): ?>
            <div class="dk-panel">
                <p class="dk-panel-title"><i class="bi bi-file-text-fill"></i>Deskripsi</p>
                <p class="dk-desc"><?= nl2br(htmlspecialchars($kos['deskripsi'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($semua_fasilitas)): ?>
            <div class="dk-panel">
                <p class="dk-panel-title"><i class="bi bi-stars"></i>Fasilitas</p>
                <div class="dk-fasil-grid">
                    <?php foreach ($semua_fasilitas as $key => $label):
                        $icon = $icon_map[$key] ?? 'bi-check-circle-fill';
                    ?>
                    <div class="dk-fasil-item">
                        <i class="bi <?= $icon ?>"></i>
                        <span><?= htmlspecialchars($label) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($kamar_list)): ?>
            <div class="dk-panel">
                <p class="dk-panel-title">
                    <i class="bi bi-door-open-fill"></i>Kamar Tersedia
                    <span style="font-size:.78rem;font-weight:500;color:rgba(255,255,255,.4);margin-left:auto">
                        <?= count($kamar_list) ?> kamar total
                    </span>
                </p>
                <?php foreach ($kamar_list as $kamar): ?>
                <div class="dk-kamar-card">
                    <div>
                        <div class="dk-kamar-num">Kamar <?= htmlspecialchars($kamar['nomor_kamar']) ?></div>
                        <div class="dk-kamar-tipe"><?= htmlspecialchars($kamar['nama_tipe']) ?></div>
                    </div>
                    <div class="text-end d-flex align-items-center gap-3">
                        <div>
                            <div class="dk-kamar-harga">
                                Rp <?= number_format($kamar['harga'], 0, ',', '.') ?>
                                <small>/bln</small>
                            </div>
                        </div>
                        <?php if ($kamar['status'] === 'penuh'): ?>
                            <span class="dk-kamar-penuh">Penuh</span>
                        <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'penyewa'): ?>
                            <a href="<?= BASE_URL ?>/reservasi.php?id_kamar=<?= $kamar['id'] ?>"
                               style="background:#3b82f6;color:#fff;padding:5px 14px;
                                      border-radius:8px;font-size:.78rem;font-weight:600;
                                      text-decoration:none;white-space:nowrap">
                                Pesan
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-4">
        <div class="dk-sidebar">
            <div class="dk-sidebar-card">
                <div class="dk-price-label">Harga mulai dari</div>
                <div>
                    <span class="dk-price-num">
                        Rp <?= $harga_min ? number_format($harga_min, 0, ',', '.') : '—' ?>
                    </span>
                    <span class="dk-price-unit">/bulan</span>
                </div>
                <?php if ($total_tersedia > 0): ?>
                    <div class="dk-avail-badge">
                        <i class="bi bi-check-circle-fill"></i>
                        <?= $total_tersedia ?> kamar tersedia
                    </div>
                <?php else: ?>
                    <div class="dk-full-badge">
                        <i class="bi bi-x-circle-fill"></i>Semua kamar penuh
                    </div>
                <?php endif; ?>
                <?php if ($total_tersedia > 0):
                    $first_kamar = reset($kamar_tersedia);
                ?>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'penyewa'): ?>
                        <a href="<?= BASE_URL ?>/reservasi.php?id_kamar=<?= $first_kamar['id'] ?>"
                           class="dk-btn-pesan">
                            <i class="bi bi-calendar-check-fill"></i>Pesan Sekarang
                        </a>
                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                        <a href="<?= BASE_URL ?>/auth/login.php" class="dk-btn-login">
                            <i class="bi bi-box-arrow-in-right"></i>Login untuk Pesan
                        </a>
                    <?php else: ?>
                        <button class="dk-btn-pesan disabled" disabled>
                            <i class="bi bi-slash-circle"></i>Tidak Tersedia untuk Role Ini
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="dk-btn-pesan disabled" disabled>
                        <i class="bi bi-x-circle"></i>Kamar Penuh
                    </button>
                <?php endif; ?>
            </div>
            <?php if ($pemilik): ?>
            <div class="dk-sidebar-card">
                <p class="dk-panel-title" style="margin-bottom:.9rem">
                    <i class="bi bi-person-fill"></i>Pemilik Kos
                </p>
                <div class="d-flex align-items-center gap-3">
                    <div class="dk-owner-avatar"><?= $inisial_pemilik ?></div>
                    <div>
                        <div class="dk-owner-name"><?= htmlspecialchars($pemilik['nama']) ?></div>
                        <div class="dk-owner-role"><?= htmlspecialchars($pemilik['role']) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/index.php#kos-list" class="dk-btn-back">
                Kembali ke Daftar Kos
            </a>
        </div>
        </div>
    </div>
</div>
</div>
<?php include 'includes/footer.php'; ?>
