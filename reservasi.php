<?php
require_once 'config/db.php';
requireLogin();
$page_title = 'Reservasi';
$pesan_sukses = '';
$pesan_error  = '';
$id_kamar = isset($_GET['id_kamar']) ? (int)$_GET['id_kamar'] : 0;
$kamar    = null;
if ($id_kamar > 0) {
    $stmt = mysqli_prepare($conn, "
        SELECT
            km.id,
            km.nomor_kamar,
            km.harga,
            km.status,
            km.fasilitas,
            km.id_kos,
            k.nama_kos,
            k.alamat,
            t.nama_tipe,
            t.deskripsi AS tipe_deskripsi
        FROM kamar km
        JOIN kos         k ON k.id  = km.id_kos
        JOIN tipe_kamar  t ON t.id  = km.id_tipe
        WHERE km.id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, 'i', $id_kamar);
    mysqli_stmt_execute($stmt);
    $kamar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$kamar || $kamar['status'] !== 'tersedia') {
        $kamar = null;
        $pesan_error = 'Kamar tidak ditemukan atau sudah tidak tersedia.';
        $id_kamar = 0;
    }
    if ($kamar && isset($_SESSION['user_id'])) {
        $uid_cek   = (int)$_SESSION['user_id'];
        $cek_aktif = mysqli_prepare($conn,
            "SELECT id FROM reservasi
             WHERE id_user = ? AND id_kamar = ? AND status IN ('pending','diterima')
             LIMIT 1");
        mysqli_stmt_bind_param($cek_aktif, 'ii', $uid_cek, $id_kamar);
        mysqli_stmt_execute($cek_aktif);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($cek_aktif))) {
            $kamar    = null;
            $id_kamar = 0;
            $pesan_error = 'Kamu sudah memiliki reservasi aktif untuk kamar ini.';
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_kamar'])) {
    $post_id_kamar   = (int)$_POST['id_kamar'];
    $tanggal_masuk   = trim($_POST['tanggal_masuk'] ?? '');
    $durasi          = (int)($_POST['durasi'] ?? 0);
    $id_user         = (int)$_SESSION['user_id']; 
    $hari_ini = date('Y-m-d');
    if (empty($tanggal_masuk) || $tanggal_masuk < $hari_ini) {
        $pesan_error = 'Tanggal masuk tidak boleh sebelum hari ini.';
    } elseif ($durasi < 1) {
        $pesan_error = 'Durasi sewa minimal 1 bulan.';
    } else {
        $cek = mysqli_prepare($conn, "
            SELECT km.harga, km.status, km.id_kos
            FROM kamar km
            WHERE km.id = ? LIMIT 1
        ");
        mysqli_stmt_bind_param($cek, 'i', $post_id_kamar);
        mysqli_stmt_execute($cek);
        $data_kamar = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
        if (!$data_kamar || $data_kamar['status'] !== 'tersedia') {
            $pesan_error = 'Kamar sudah tidak tersedia saat diproses.';
        } else {
            $cek_dup = mysqli_prepare($conn,
                "SELECT id FROM reservasi
                 WHERE id_user = ? AND id_kamar = ? AND status IN ('pending','diterima')
                 LIMIT 1");
            mysqli_stmt_bind_param($cek_dup, 'ii', $id_user, $post_id_kamar);
            mysqli_stmt_execute($cek_dup);
            $dup = mysqli_fetch_assoc(mysqli_stmt_get_result($cek_dup));
            if ($dup) {
                $pesan_error = 'Kamu sudah memiliki reservasi aktif untuk kamar ini.';
            } else {
            $total_harga = $data_kamar['harga'] * $durasi;
            $ins = mysqli_prepare($conn, "
                INSERT INTO reservasi (id_user, id_kamar, tanggal_masuk, durasi, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            mysqli_stmt_bind_param($ins, 'iisi',
                $id_user, $post_id_kamar, $tanggal_masuk, $durasi);
            if (mysqli_stmt_execute($ins)) {
                $kamar    = null;
                $id_kamar = 0;
                $pesan_sukses = 'Reservasi berhasil diajukan! Silakan tunggu konfirmasi dari admin.';
            } else {
                $pesan_error = 'Gagal menyimpan reservasi. Coba lagi.';
            }
            } 
        }
    }
}
$uid    = (int)$_SESSION['user_id'];
$stmt_r = mysqli_prepare($conn, "
    SELECT
        r.id,
        r.tanggal_masuk,
        r.durasi,
        r.status,
        r.tanggal_pesan,
        km.nomor_kamar,
        km.harga,
        (r.durasi * km.harga) AS total_harga,
        k.nama_kos,
        k.id             AS kos_id,
        t.nama_tipe
    FROM reservasi r
    JOIN kamar      km ON km.id  = r.id_kamar
    JOIN kos        k  ON k.id   = km.id_kos
    JOIN tipe_kamar t  ON t.id   = km.id_tipe
    WHERE r.id_user = ?
    ORDER BY r.tanggal_pesan DESC
");
mysqli_stmt_bind_param($stmt_r, 'i', $uid);
mysqli_stmt_execute($stmt_r);
$riwayat = mysqli_fetch_all(mysqli_stmt_get_result($stmt_r), MYSQLI_ASSOC);
include 'includes/header.php';
?>
<style>
body { background: var(--kk-dark) !important; }
.rv-page { background: var(--kk-dark); min-height: 100vh; }
.rv-alert-ok  { 
    background: rgba(22,163,74,.12) !important; 
    border: 1px solid rgba(22,163,74,.3) !important; 
    color: #6ee7a0 !important; 
    border-radius: var(--kk-radius); 
    font-size: 1.05rem;
    padding: 1rem 1.25rem;
}
.rv-alert-err { 
    background: rgba(220,38,38,.12) !important; 
    border: 1px solid rgba(220,38,38,.3) !important; 
    color: #fca5a5 !important; 
    border-radius: var(--kk-radius); 
    font-size: 1.05rem;
    padding: 1rem 1.25rem;
}
.rv-card {
    background: var(--kk-surface) !important;
    border: 1px solid var(--kk-border) !important;
    border-radius: var(--kk-radius-lg) !important; 
    overflow: hidden;
    box-shadow: var(--kk-shadow-md) !important;
}
.rv-info-box {
    background: #f8fafc !important;
    border: 1px solid var(--kk-border) !important;
    border-radius: var(--kk-radius);
    padding: 1.75rem !important;
    position: relative;
    overflow: hidden;
}
.rv-input {
    background: #ffffff !important;
    border: 1px solid var(--kk-border) !important;
    color: var(--kk-text) !important;
    border-radius: var(--kk-radius-sm);
    font-size: 1.05rem;
    padding: 0.85rem 1.1rem !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.rv-input:focus {
    border-color: var(--kk-blue) !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2) !important;
    background: #ffffff !important;
}
.rv-input::placeholder { color: rgba(255,255,255,.28) !important; }
.rv-calendar {
    background: #ffffff !important;
    border: 1.5px solid var(--kk-border) !important;
    border-radius: var(--kk-radius);
    padding: 20px;
    box-shadow: var(--kk-shadow);
}
.rv-cal-nav {
    background: #f1f5f9; 
    border: 1px solid #e2e8f0; 
    cursor: pointer;
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: var(--kk-text); transition: all .2s;
}
.rv-cal-nav:hover { 
    background: var(--kk-blue) !important; 
    border-color: var(--kk-blue-light) !important;
    color: var(--kk-text) !important;
    transform: translateY(-1px) scale(1.05);
}
.rv-cal-label { 
    font-size: 1.15rem; 
    font-weight: 800; 
    color: var(--kk-text); 
    letter-spacing: -.02em; 
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.rv-section-eyebrow { 
    font-size: .85rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: var(--kk-blue);
    margin-bottom: .4rem; 
}
.rv-section-title { 
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--kk-text);
    margin: 0; 
    font-family: 'Plus Jakarta Sans', sans-serif;
    letter-spacing: -0.02em;
}
.rv-empty {
    background: var(--kk-surface) !important;
    border: 1px solid var(--kk-border) !important;
    border-radius: var(--kk-radius-lg);
    padding: 3rem 2rem !important;
}
.rv-tabel-card {
    background: var(--kk-surface) !important;
    border: 1px solid var(--kk-border) !important;
    border-radius: var(--kk-radius); 
    overflow: hidden;
    box-shadow: var(--kk-shadow);
}
.rv-tabel-card .table { 
    color: var(--kk-text) !important; 
    font-size: 1.02rem !important;
}
.rv-tabel-card .table th {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 700;
    letter-spacing: 0.02em;
    font-size: 0.95rem;
    text-transform: uppercase;
}
.rv-tabel-card .table th, .rv-tabel-card .table td {
    border-color: var(--kk-border) !important;
    vertical-align: middle;
}
.rv-tabel-card .table-hover > tbody > tr:hover > * {
    background: rgba(255,255,255,.03) !important;
    color: var(--kk-text) !important;
}
.rv-tabel-card .table > :not(caption) > * > * { background: transparent !important; }
.rv-mobile-card {
    background: var(--kk-surface) !important;
    border: 1px solid var(--kk-border) !important;
    border-radius: var(--kk-radius);
    box-shadow: var(--kk-shadow);
    transition: all 0.25s ease;
}
.rv-mobile-card:hover {
    transform: translateY(-2px);
    border-color: rgba(59, 130, 246, 0.15) !important;
    box-shadow: var(--kk-shadow-md);
}
.rv-label { 
    font-size: 1.05rem;
    color: var(--kk-text);
    font-weight: 700; 
}
.rv-note { 
    font-size: 0.9rem;
    color: rgba(255,255,255,.45); 
}
.rv-info-hr { border-color: var(--kk-border) !important; }
#calGrid > div[data-past="0"]:hover {
    transform: scale(1.08);
}
</style>
<div class="container py-4" style="max-width:1080px">
    <?php if ($pesan_sukses): ?>
        <div class="alert rv-alert-ok d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <span><?= htmlspecialchars($pesan_sukses) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($pesan_error): ?>
        <div class="alert rv-alert-err d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-circle-fill fs-5"></i>
            <span><?= htmlspecialchars($pesan_error) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($kamar): ?>
    <div class="rv-card mb-5 fade-in-up">
        <div class="p-4 py-4" style="background:#f8fafc">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0" style="font-size:.95rem">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/index.php"
                           style="color:rgba(255,255,255,.9);text-decoration:none;font-weight:500">Beranda</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/detail_kos.php?id=<?= $kamar['id_kos'] ?>"
                           style="color:rgba(255,255,255,.9);text-decoration:none;font-weight:500">
                            <?= htmlspecialchars($kamar['nama_kos']) ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color:var(--kk-text);font-weight:600">Form Reservasi</li>
                </ol>
            </nav>
            <h3 class="text-white fw-extrabold mb-0 fs-3">
                <i class="bi bi-calendar-check-fill me-2"></i>Form Reservasi Kamar
            </h3>
        </div>
        <div class="p-4 p-md-5">
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="rv-info-box p-4 h-100">
                        <p style="font-size:.9rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:#3b82f6;margin-bottom:0.75rem">
                            Detail Kamar
                        </p>
                        <h4 class="fw-extrabold mb-2" style="color:var(--kk-text);font-size:1.45rem">
                            <?= htmlspecialchars($kamar['nama_kos']) ?>
                        </h4>
                        <p class="mb-4" style="font-size:1.02rem;color:var(--kk-muted)">
                            <i class="bi bi-geo-alt-fill me-1" style="color:#3b82f6"></i>
                            <?= htmlspecialchars($kamar['alamat']) ?>
                        </p>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-weight:700;padding:8px 14px;border-radius:8px;font-size:0.95rem">
                                <i class="bi bi-door-open me-1"></i>Kamar <?= htmlspecialchars($kamar['nomor_kamar']) ?>
                            </span>
                            <span class="badge" style="background:rgba(249,115,22,.15);color:#fb923c;font-weight:700;padding:8px 14px;border-radius:8px;border:1px solid rgba(249,115,22,.25);font-size:0.95rem">
                                <?= htmlspecialchars($kamar['nama_tipe']) ?>
                            </span>
                        </div>
                        <?php if (!empty($kamar['fasilitas'])): ?>
                        <p style="font-size:1rem;color:var(--kk-muted);margin-bottom:1.5rem;line-height:1.6">
                            <i class="bi bi-stars me-1" style="color:#f59e0b"></i>
                            <?= htmlspecialchars($kamar['fasilitas']) ?>
                        </p>
                        <?php endif; ?>
                        <hr class="rv-info-hr my-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="font-size:.95rem;color:var(--kk-muted);font-weight:500">Harga per bulan</span>
                            <div id="hargaPerBulan"
                                 data-harga="<?= $kamar['harga'] ?>"
                                 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.6rem;font-weight:800;color:var(--kk-text)">
                                Rp <?= number_format($kamar['harga'], 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <form method="POST" action="reservasi.php?id_kamar=<?= $kamar['id'] ?>"
                          id="formReservasi" novalidate>
                        <input type="hidden" name="id_kamar" value="<?= $kamar['id'] ?>">
                        <input type="hidden" id="tanggal_masuk" name="tanggal_masuk" required>
                        <div class="mb-4">
                            <label class="form-label fw-bold d-flex align-items-center gap-2 mb-2"
                                   style="font-size:1.05rem;color:var(--kk-text)">
                                <i class="bi bi-calendar3 text-primary"></i> Tanggal Masuk
                            </label>
                            <div id="customCalendar" class="rv-calendar">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <button type="button" id="calPrev" class="rv-cal-nav">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                                    </button>
                                    <span id="calMonthLabel" class="rv-cal-label"></span>
                                    <button type="button" id="calNext" class="rv-cal-nav">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                    </button>
                                </div>
                                <div id="calDayHeaders" style="
                                    display:grid;
                                    grid-template-columns:repeat(7,1fr);
                                    gap:2px;
                                    margin-bottom:6px;
                                "></div>
                                <div id="calGrid" style="
                                    display:grid;
                                    grid-template-columns:repeat(7,1fr);
                                    gap:6px;
                                "></div>
                                <div id="calSelectedDisplay"
                                     style="margin-top:16px;padding:12px 16px;
                                            background:rgba(22,163,74,.15);border-radius:12px;
                                            font-size:1rem;color:#6ee7a0;font-weight:700;
                                            display:none;align-items:center;gap:8px;
                                            border:1px solid rgba(22,163,74,.25)">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                    <span id="calSelectedText"></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2" for="durasi"
                                   style="font-size:1.05rem;color:var(--kk-text)">
                                <i class="bi bi-clock me-1 text-primary"></i>Durasi Sewa (bulan)
                            </label>
                            <input type="number" id="durasi" name="durasi"
                                   class="form-control rv-input"
                                   min="1" max="24" placeholder="Contoh: 6"
                                   required>
                        </div>
                        <div id="estimasiTotal"
                             style="font-size:1.2rem;color:#3b82f6;font-weight:800;
                                    min-height:1.8rem;margin-bottom:1.25rem">
                        </div>
                        <button type="submit" class="btn w-100 fw-bold py-3"
                                style="background:#2563eb;color:var(--kk-text);border-radius:12px;
                                       box-shadow:0 4px 16px rgba(37,99,235,.3);font-size:1.1rem;
                                       transition:all .2s ease">
                            <i class="bi bi-send-check me-2"></i>Ajukan Reservasi
                        </button>
                        <p class="text-center mt-3 mb-0" style="font-size:0.9rem;color:var(--kk-muted)">
                            <i class="bi bi-info-circle me-1"></i>
                            Reservasi akan berstatus <strong>pending</strong> hingga dikonfirmasi admin.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <p class="rv-section-eyebrow"><i class="bi bi-journal-check me-1"></i>RIWAYAT SAYA</p>
            <h3 class="rv-section-title">Reservasi Saya</h3>
        </div>
        <span class="badge" style="background:rgba(37,99,235,.2);color:#93c5fd;font-size:0.95rem;padding:8px 16px;border-radius:50px;border:1px solid rgba(37,99,235,.3)">
            <?= count($riwayat) ?> reservasi
        </span>
    </div>
    <?php if (empty($riwayat)): ?>
        <div class="rv-empty text-center py-5">
            <i class="bi bi-journal-x" style="font-size:3.5rem;color:var(--kk-muted)"></i>
            <p class="mt-3 fw-bold fs-5" style="color:var(--kk-muted)">Belum ada riwayat reservasi.</p>
            <a href="<?= BASE_URL ?>/index.php" class="btn mt-2 px-4 py-2 fw-bold"
               style="background:#2563eb;color:var(--kk-text);border-radius:10px;font-size:1rem">
                <i class="bi bi-search me-1"></i>Cari Kos Sekarang
            </a>
        </div>
    <?php else: ?>
        <div class="d-none d-md-block">
            <div class="rv-tabel-card">
                <table class="table table-hover mb-0">
                    <thead style="background:#f8fafc">
                        <tr style="color:var(--kk-text)">
                            <th class="py-3 ps-4">Kos / Kamar</th>
                            <th>Tipe</th>
                            <th>Tanggal Masuk</th>
                            <th class="text-center">Durasi</th>
                            <th class="text-end">Total Harga</th>
                            <th class="text-center pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat as $r): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold fs-5" style="color:var(--kk-text)">
                                    <?= htmlspecialchars($r['nama_kos']) ?>
                                </div>
                                <div style="font-size:.9rem;color:var(--kk-muted);margin-top:2px">
                                    Kamar <?= htmlspecialchars($r['nomor_kamar']) ?>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="badge" style="background:rgba(249,115,22,.15);color:#fb923c;font-weight:700;padding:6px 12px;border:1px solid rgba(249,115,22,.25);font-size:0.88rem">
                                    <?= htmlspecialchars($r['nama_tipe']) ?>
                                </span>
                            </td>
                            <td class="py-3 fw-medium" style="color:var(--kk-text)">
                                <?= date('d M Y', strtotime($r['tanggal_masuk'])) ?>
                            </td>
                            <td class="text-center py-3 fw-medium" style="color:var(--kk-text)">
                                <?= $r['durasi'] ?> bln
                            </td>
                            <td class="text-end py-3 fw-bold fs-5" style="color:var(--kk-text)">
                                Rp <?= number_format($r['total_harga'], 0, ',', '.') ?>
                            </td>
                            <td class="text-center py-3 pe-4">
                                <?php
                                [$bg, $color, $icon] = match($r['status']) {
                                    'diterima' => ['rgba(22,163,74,.15)','#6ee7a0','bi-check-circle-fill'],
                                    'ditolak'  => ['rgba(220,38,38,.15)','#fca5a5','bi-x-circle-fill'],
                                    default    => ['rgba(202,138,4,.15)','#fcd34d','bi-hourglass-split'],
                                };
                                ?>
                                <span style="background:<?= $bg ?>;color:<?= $color ?>;
                                             font-size:.88rem;font-weight:700;
                                             padding:6px 14px;border-radius:50px;
                                             display:inline-flex;align-items:center;gap:6px">
                                    <i class="bi <?= $icon ?>"></i>
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-md-none d-flex flex-column gap-3">
            <?php foreach ($riwayat as $r):
                [$bg, $color, $icon] = match($r['status']) {
                    'diterima' => ['rgba(22,163,74,.15)','#6ee7a0','bi-check-circle-fill'],
                    'ditolak'  => ['rgba(220,38,38,.15)','#fca5a5','bi-x-circle-fill'],
                    default    => ['rgba(202,138,4,.15)','#fcd34d','bi-hourglass-split'],
                };
            ?>
            <div class="rv-mobile-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="fw-bold" style="color:var(--kk-text);font-size:1.15rem;line-height:1.2">
                            <?= htmlspecialchars($r['nama_kos']) ?>
                        </div>
                        <div style="font-size:.92rem;color:var(--kk-muted);margin-top:4px">
                            Kamar <?= htmlspecialchars($r['nomor_kamar']) ?> &middot;
                            <?= htmlspecialchars($r['nama_tipe']) ?>
                        </div>
                    </div>
                    <span style="background:<?= $bg ?>;color:<?= $color ?>;
                                 font-size:.85rem;font-weight:700;
                                 padding:4px 10px;border-radius:50px;
                                 display:inline-flex;align-items:center;gap:4px;white-space:nowrap">
                        <i class="bi <?= $icon ?>"></i><?= ucfirst($r['status']) ?>
                    </span>
                </div>
                <div class="row g-2 pt-2 border-top border-secondary-subtle" style="font-size:.95rem;color:var(--kk-muted)">
                    <div class="col-6">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        <?= date('d M Y', strtotime($r['tanggal_masuk'])) ?>
                    </div>
                    <div class="col-6">
                        <i class="bi bi-clock me-1 text-primary"></i><?= $r['durasi'] ?> bulan
                    </div>
                    <div class="col-12 mt-2">
                        <span class="fw-bold" style="color:var(--kk-text);font-size:1.1rem">
                            Rp <?= number_format($r['total_harga'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="mt-5">
        <a href="<?= BASE_URL ?>/index.php"
           style="font-size:1.05rem;color:var(--kk-muted);text-decoration:none;font-weight:600;transition:color .2s"
           onmouseover="this.style.color='var(--kk-blue)'"
           onmouseout="this.style.color='var(--kk-muted)'">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
        </a>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
