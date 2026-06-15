<?php
require_once 'config/db.php';
$page_title = 'Beranda';
$q_area = mysqli_query($conn, "SELECT * FROM area ORDER BY nama_area ASC");
$areas  = mysqli_fetch_all($q_area, MYSQLI_ASSOC);
$q_tipe = mysqli_query($conn, "SELECT * FROM tipe_kamar ORDER BY id ASC");
$tipes  = mysqli_fetch_all($q_tipe, MYSQLI_ASSOC);
$area_id       = isset($_GET['area_id']) ? (int)$_GET['area_id'] : 0;
$selected_area = null;
$kos_list      = [];
if ($area_id > 0) {
    $r = mysqli_query($conn, "SELECT * FROM area WHERE id = $area_id LIMIT 1");
    $selected_area = mysqli_fetch_assoc($r);
}
$base_select = "
    SELECT k.*,
        COALESCE(MIN(CASE WHEN km.status='tersedia' THEN km.harga END), 0) AS harga_mulai,
        GROUP_CONCAT(DISTINCT t.nama_tipe ORDER BY t.id SEPARATOR ',')     AS tipe_tersedia,
        COUNT(DISTINCT CASE WHEN km.status='tersedia' THEN km.id END)       AS kamar_tersedia";
$base_from = "
    FROM kos k
    LEFT JOIN kamar      km ON km.id_kos = k.id
    LEFT JOIN tipe_kamar t  ON t.id      = km.id_tipe";
$base_sql = $base_select . $base_from;
if ($selected_area) {
    $lat  = (float)$selected_area['latitude'];
    $lng  = (float)$selected_area['longitude'];
    $stmt = mysqli_prepare($conn,
        $base_select . ",
        (6371 * ACOS(
            COS(RADIANS(?)) * COS(RADIANS(k.latitude)) *
            COS(RADIANS(k.longitude) - RADIANS(?)) +
            SIN(RADIANS(?)) * SIN(RADIANS(k.latitude))
        )) AS jarak_km" .
        $base_from . "
        GROUP BY k.id ORDER BY jarak_km ASC");
    mysqli_stmt_bind_param($stmt, "ddd", $lat, $lng, $lat);
    mysqli_stmt_execute($stmt);
    $kos_list = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
} else {
    $result   = mysqli_query($conn, $base_sql . " GROUP BY k.id ORDER BY k.nama_kos ASC");
    $kos_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
$search_q = trim($_GET['q'] ?? '');
if ($search_q !== '') {
    $kos_list = array_values(array_filter($kos_list, function($k) use ($search_q) {
        return stripos($k['nama_kos'], $search_q) !== false ||
               stripos($k['alamat'],   $search_q) !== false;
    }));
}
$tipe_bar = trim($_GET['tipe_bar'] ?? '');
if ($tipe_bar !== '') {
    $kos_list = array_values(array_filter($kos_list, function($k) use ($tipe_bar) {
        return stripos($k['tipe_tersedia'] ?? '', $tipe_bar) !== false;
    }));
}
$limit = 6;
$total_items = count($kos_list);
$total_pages = ceil($total_items / $limit);
if ($total_pages < 1) $total_pages = 1;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
if ($page > $total_pages) $page = $total_pages;
$start = ($page - 1) * $limit;
$kos_paginated = array_slice($kos_list, $start, $limit);
$total_kos   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM kos"))[0];
$total_avail = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM kamar WHERE status='tersedia'"))[0];
$total_area  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM area"))[0];
include 'includes/header.php';
?>
<section class="hero-section" data-theme="warm">
    <div class="hero-slider">
        <div class="hero-slide active" data-theme="warm" style="background-image: url('<?= BASE_URL ?>/assets/img/background.png');"></div>
        <div class="hero-slide" data-theme="cool" style="background-image: url('<?= BASE_URL ?>/assets/img/background2.jpg');"></div>
    </div>
    <div class="hero-center">
        <div class="hero-badge">
            <i class="bi bi-geo-alt-fill"></i>
            Yogyakarta &middot; <?= $total_avail ?> Kamar Tersedia
        </div>
        <h1 class="hero-title">
            Temukan Kos Impianmu<br>di <span class="text-accent">Yogyakarta</span>
        </h1>
        <p class="hero-subtitle">
            Cari kos berdasarkan jarak nyata ke kampus pilihanmu.<br>
            Reservasi langsung, cepat, dan aman.
        </p>
    </div>
    <div class="hero-filter-bar">
        <?php if ($selected_area): ?>
        <div class="hero-result-note">
            <i class="bi bi-check-circle-fill"></i>
            <?= count($kos_list) ?> kos ditemukan dekat <strong><?= htmlspecialchars($selected_area['nama_area']) ?></strong>
        </div>
        <?php endif; ?>
        <form method="GET" action="" class="hero-filter-form">
            <div class="hero-filter-field">
                <div class="hero-filter-label">
                    <i class="bi bi-buildings"></i> Dekat Kampus
                </div>
                <select name="area_id" class="hero-filter-select">
                    <option value="">Semua Lokasi</option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= $area_id == $a['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nama_area']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="hero-filter-field">
                <div class="hero-filter-label">
                    <i class="bi bi-grid-3x3-gap"></i> Tipe Kamar
                </div>
                <select name="tipe_bar" class="hero-filter-select">
                    <option value="">Semua Tipe</option>
                    <?php foreach ($tipes as $t): ?>
                        <option value="<?= htmlspecialchars($t['nama_tipe']) ?>">
                            <?= htmlspecialchars($t['nama_tipe']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="hero-filter-field">
                <div class="hero-filter-label">
                    <i class="bi bi-door-open"></i> Ketersediaan
                </div>
                <div class="hero-filter-value">
                    <span class="hero-filter-count"><?= $total_avail ?></span> kamar tersedia
                </div>
            </div>
            <div class="hero-filter-field">
                <div class="hero-filter-label">
                    <i class="bi bi-building"></i> Total Kos
                </div>
                <div class="hero-filter-value">
                    <span class="hero-filter-count"><?= $total_kos ?></span> kos terdaftar
                </div>
            </div>
            <div class="hero-filter-cta">
                <button type="submit" class="hero-filter-cta-btn">
                    Temukan Kos
                    <span class="btn-arrow"><i class="bi bi-arrow-right"></i></span>
                </button>
            </div>
        </form>
    </div>
</section>
<div class="trust-section">
    <div class="container">
        <div class="trust-grid">
            <div class="trust-item">
                <div class="trust-icon trust-icon-cyan">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div>
                    <div class="trust-title">Harga Terjangkau</div>
                    <div class="trust-text">Kami pastikan harga terbaik untukmu</div>
                </div>
            </div>
            <div class="trust-item">
                <div class="trust-icon trust-icon-cyan">
                    <i class="bi bi-headset"></i>
                </div>
                <div>
                    <div class="trust-title">Dukungan 24/7</div>
                    <div class="trust-text">Tim kami siap membantu kapanpun</div>
                </div>
            </div>
            <div class="trust-item">
                <div class="trust-icon trust-icon-cyan">
                    <i class="bi bi-lock-fill"></i>
                </div>
                <div>
                    <div class="trust-title">Booking Aman & Mudah</div>
                    <div class="trust-text">Reservasi terverifikasi & terpercaya</div>
                </div>
            </div>
        </div>
    </div>
</div>
<section class="container py-5" id="kos-list">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <div class="me-auto">
            <p class="section-eyebrow mb-0">
                <i class="bi bi-geo-alt me-1"></i>JELAJAHI KOS TERBAIK
            </p>
            <div class="section-heading">
                <?= $selected_area
                    ? 'Kos Terdekat dari '.htmlspecialchars($selected_area['nama_area'])
                    : 'Semua Kos Tersedia' ?>
            </div>
            <div class="section-sub"><?= count($kos_list) ?> kos ditemukan</div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <?php 
            $q_semua = $_GET;
            unset($q_semua['tipe_bar']);
            unset($q_semua['page']);
            $url_semua = '?' . http_build_query($q_semua) . '#kos-list';
            ?>
            <a href="<?= $url_semua ?>" class="text-decoration-none filter-pill <?= ($tipe_bar === '') ? 'active' : '' ?>">✦ Semua</a>
            <?php foreach ($tipes as $t): 
                $q_pill = $_GET;
                $q_pill['tipe_bar'] = $t['nama_tipe'];
                unset($q_pill['page']);
                $pill_url = '?' . http_build_query($q_pill) . '#kos-list';
            ?>
                <a href="<?= $pill_url ?>" class="text-decoration-none filter-pill <?= ($tipe_bar === $t['nama_tipe']) ? 'active' : '' ?>">
                    <?= htmlspecialchars($t['nama_tipe']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if (empty($kos_list)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-house-slash" style="font-size:3.5rem;opacity:.35"></i>
            <p class="mt-3 fw-semibold">Tidak ada kos ditemukan.</p>
        </div>
    <?php else: ?>
        <div class="row g-4" id="kosGrid">
            <?php foreach ($kos_paginated as $i => $kos):
                $foto_path = __DIR__ . '/assets/img/kos/' . ($kos['foto'] ?? '');
                $has_foto  = !empty($kos['foto']) && file_exists($foto_path);
                $tipe_arr  = !empty($kos['tipe_tersedia']) ? explode(',', $kos['tipe_tersedia']) : [];
                $tipe_str  = $kos['tipe_tersedia'] ?? '';
            ?>
            <div class="col-sm-6 col-lg-3 kos-card-anim"
                 data-nama="<?= htmlspecialchars($kos['nama_kos']) ?>"
                 data-tipe="<?= htmlspecialchars($tipe_str) ?>"
                 style="animation-delay:<?= $i * 60 ?>ms">
                <a href="<?= BASE_URL ?>/detail_kos.php?id=<?= $kos['id'] ?>" class="dest-card open-kos-modal text-decoration-none" data-kos-id="<?= $kos['id'] ?>">
                    <div class="dest-card-img">
                        <?php if ($has_foto): ?>
                            <img src="<?= BASE_URL ?>/assets/img/kos/<?= htmlspecialchars($kos['foto']) ?>" alt="Foto Kos">
                        <?php else: ?>
                            <div class="dest-img-placeholder"><i class="bi bi-image"></i></div>
                        <?php endif; ?>
                        <span class="dest-price-badge">
                                        Rp <?= number_format($kos['harga_mulai'],0,',','.') ?> <span style='font-size:0.75rem;font-weight:500;color:#64748b'>/bulan</span>
                                    </span>
                    </div>
                    <div class="dest-card-content">
                        <div class="dest-name"><?= htmlspecialchars($kos['nama_kos']) ?></div>
                        <p class="dest-addr">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span><?= htmlspecialchars($kos['alamat']) ?></span>
                                </p>
                        <div class="mt-2">
                            <div class="dest-tipe-row">
                                    <?php foreach ($tipe_arr as $t): ?>
                                        <span class="dest-tag"><?= htmlspecialchars(trim($t)) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (isset($kos['jarak_km'])): ?>
                                        <span class="dest-tag">
                                            <i class="bi bi-geo-alt-fill"></i>
                                            <?= number_format($kos['jarak_km'],1,',','.') ?> km
                                        </span>
                                    <?php endif; ?>
                                    <span class="dest-tag"><i class="bi bi-calendar3"></i> Min. 1 Bulan</span>
                                </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-5">
            <style>
            .pagination .page-item.active .page-link {
                background-color: var(--kk-blue);
                border-color: var(--kk-blue);
                color: white;
            }
            .pagination .page-link {
                color: var(--kk-text);
                border: 1px solid #e2e8f0;
                box-shadow: 0 2px 4px rgba(0,0,0,0.02);
                transition: all 0.2s ease;
            }
            .pagination .page-link:hover {
                background-color: rgba(37,99,235,0.1);
                color: var(--kk-blue);
            }
            .pagination .page-item.disabled .page-link {
                background-color: transparent;
                box-shadow: none;
                color: var(--kk-muted);
                border-color: transparent;
            }
            </style>
            <ul class="pagination justify-content-center">
                <?php
                $q_get = $_GET;
                $q_get['page'] = $page - 1;
                $prev_url = '?' . http_build_query($q_get) . '#kos-list';
                $q_get['page'] = $page + 1;
                $next_url = '?' . http_build_query($q_get) . '#kos-list';
                ?>
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill px-3 me-2 fw-medium" href="<?= $prev_url ?>">Previous</a>
                </li>
                <?php for($p = 1; $p <= $total_pages; $p++): 
                    $q_get['page'] = $p;
                    $url = '?' . http_build_query($q_get) . '#kos-list';
                ?>
                    <li class="page-item <?= ($p == $page) ? 'active' : '' ?> mx-1">
                        <a class="page-link rounded-circle text-center fw-semibold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" href="<?= $url ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill px-3 ms-2 fw-medium" href="<?= $next_url ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    <?php endif; ?>
</section>
<section class="container pb-5" id="cta-daftar">
    <div class="cta-banner">
        <div class="d-flex align-items-center gap-3 position-relative" style="z-index:1">
            <div class="cta-icon-box">
                <i class="bi bi-tag-fill"></i>
            </div>
            <div>
                <div class="cta-title">Daftar & Dapatkan Kemudahan Booking</div>
                <p class="cta-sub">Buat akun gratis dan reservasi kos favoritmu dengan mudah!</p>
            </div>
        </div>
        <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="cta-form">
            <a href="<?= BASE_URL ?>/auth/register.php" class="btn-cta">
                <i class="bi bi-person-plus-fill me-1"></i> Daftar Sekarang
            </a>
            <a href="<?= BASE_URL ?>/auth/login.php" class="btn-cta" style="background:rgba(255,255,255,.18);color:#fff;border:1.5px solid rgba(255,255,255,.35)">
                Login
            </a>
        </div>
        <?php else: ?>
        <div class="position-relative" style="z-index:1">
            <a href="<?= BASE_URL ?>/index.php#kos-list" class="btn-cta">
                <i class="bi bi-search me-1"></i> Cari Kos Sekarang
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>
<section id="cara-pesan" class="cp-section">
    <div class="container position-relative" style="z-index:2">
        <div class="text-center mb-5">
            <p class="cp-eyebrow">
                <i class="bi bi-grid-3x3-gap-fill me-1"></i>PANDUAN
            </p>
            <h2 class="cp-heading">Cara Pesan Kos di KosKita</h2>
            <p class="cp-subheading">Mudah, cepat, dan terpercaya dalam 4 langkah.</p>
        </div>
        <div class="cp-steps-row">
            <div class="cp-step-item">
                <div class="cp-step-card">
                    <div class="cp-num-circle">1</div>
                    <div class="cp-icon-box">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h3 class="cp-step-title">Daftar &amp; Login</h3>
                    <p class="cp-step-desc">Buat akun gratis dan masuk untuk mulai mencari kos impianmu.</p>
                </div>
            </div>
            <div class="cp-arrow" aria-hidden="true">
                <i class="bi bi-chevron-right"></i>
            </div>
            <div class="cp-step-item">
                <div class="cp-step-card">
                    <div class="cp-num-circle">2</div>
                    <div class="cp-icon-box">
                        <i class="bi bi-search"></i>
                    </div>
                    <h3 class="cp-step-title">Cari Kos</h3>
                    <p class="cp-step-desc">Pilih area kampus, temukan kos terdekat berdasarkan jarak nyata.</p>
                </div>
            </div>
            <div class="cp-arrow" aria-hidden="true">
                <i class="bi bi-chevron-right"></i>
            </div>
            <div class="cp-step-item">
                <div class="cp-step-card">
                    <div class="cp-num-circle">3</div>
                    <div class="cp-icon-box">
                        <i class="bi bi-house-check-fill"></i>
                    </div>
                    <h3 class="cp-step-title">Pilih &amp; Pesan</h3>
                    <p class="cp-step-desc">Lihat detail kamar, fasilitas, dan harga lalu klik Pesan Sekarang.</p>
                </div>
            </div>
            <div class="cp-arrow" aria-hidden="true">
                <i class="bi bi-chevron-right"></i>
            </div>
            <div class="cp-step-item">
                <div class="cp-step-card">
                    <div class="cp-num-circle">4</div>
                    <div class="cp-icon-box">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <h3 class="cp-step-title">Tunggu Konfirmasi</h3>
                    <p class="cp-step-desc">Admin akan memverifikasi dan mengonfirmasi reservasimu segera.</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-5">
            <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>/auth/register.php" class="cp-cta-btn">
                <i class="bi bi-rocket-takeoff-fill me-2"></i>Mulai Sekarang Gratis
            </a>
            <?php else: ?>
            <a href="<?= BASE_URL ?>/index.php#kos-list" class="cp-cta-btn">
                <i class="bi bi-search me-2"></i>Cari Kos Sekarang
            </a>
            <?php endif; ?>
            <p class="cp-cta-note">Tidak perlu kartu kredit &bull; Daftar dalam 30 detik</p>
        </div>
    </div>
</section>
<div class="modal fade" id="kosModal" tabindex="-1" aria-labelledby="kosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content kk-modal-glass">
            <div class="modal-header border-0 pb-0 pe-4 pt-4">
                <h5 class="modal-title" id="kosModalLabel" style="display:none;">Detail Kos</h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 px-md-5 pb-5 pt-2" id="kosModalBody">
                <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5 text-white preloader-spinner">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.3em;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="fw-bold mb-0 text-white-50 fs-5">Memuat Detail Kos...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if (isset($_GET['area_id']) || isset($_GET['tipe_bar'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const kosList = document.getElementById('kos-list');
            if (kosList) {
                const y = kosList.getBoundingClientRect().top + window.pageYOffset - 80;
                window.scrollTo({top: y, behavior: 'smooth'});
            }
        }, 100);
    });
</script>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
