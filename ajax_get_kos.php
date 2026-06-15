<?php
require_once 'config/db.php';
$kos_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($kos_id <= 0) {
    echo '<div class="alert text-danger bg-danger-subtle p-3 border-danger-subtle rounded-3">ID Kos tidak valid.</div>';
    exit;
}
$stmt = mysqli_prepare($conn, "SELECT * FROM kos WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $kos_id);
mysqli_stmt_execute($stmt);
$kos = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$kos) {
    echo '<div class="alert text-danger bg-danger-subtle p-3 border-danger-subtle rounded-3">Kos tidak ditemukan.</div>';
    exit;
}
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
?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="mb-4">
            <?php if ($has_foto): ?>
                <img src="<?= BASE_URL ?>/assets/img/kos/<?= htmlspecialchars($kos['foto']) ?>"
                     class="w-100 object-fit-cover rounded-4"
                     style="height: 280px; box-shadow: 0 12px 30px rgba(0,0,0,0.4); border: 1px solid #e2e8f0;"
                     alt="<?= htmlspecialchars($kos['nama_kos']) ?>">
            <?php else: ?>
                <div class="w-100 rounded-4 d-flex align-items-center justify-content-center text-secondary"
                     style="height: 280px; background: #f1f5f9; font-size: 4.5rem; border: 1px solid #e2e8f0;">
                    <i class="bi bi-buildings-fill"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="mb-4">
            <h2 class="fw-extrabold text-dark mb-2" style="font-size: 1.8rem; letter-spacing: -0.01em;">
                <?= htmlspecialchars($kos['nama_kos']) ?>
            </h2>
            <div class="text-dark-50 d-flex align-items-start gap-2" style="font-size: 0.95rem;">
                <i class="bi bi-geo-alt-fill text-accent-orange mt-1"></i>
                <span><?= htmlspecialchars($kos['alamat']) ?></span>
            </div>
        </div>
        <?php if (!empty($kos['deskripsi'])): ?>
            <div class="mb-4 p-3 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <p class="text-dark fw-bold mb-2 d-flex align-items-center gap-2" style="font-size: 1rem;">
                    <i class="bi bi-file-text-fill text-primary"></i> Deskripsi
                </p>
                <p class="text-dark-75 mb-0" style="font-size: 0.92rem; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($kos['deskripsi'])) ?>
                </p>
            </div>
        <?php endif; ?>
        <?php if (!empty($semua_fasilitas)): ?>
            <div class="mb-4">
                <p class="text-dark fw-bold mb-3 d-flex align-items-center gap-2" style="font-size: 1rem;">
                    <i class="bi bi-stars text-primary"></i> Fasilitas Bersama & Kamar
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($semua_fasilitas as $key => $label):
                        $icon = $icon_map[$key] ?? 'bi-check-circle-fill';
                    ?>
                        <span class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 text-dark-75" 
                              style="background: #f1f5f9; border: 1px solid rgba(255,255,255,0.06); font-size: 0.88rem;">
                            <i class="bi <?= $icon ?> text-primary" style="font-size: 1rem;"></i>
                            <span><?= htmlspecialchars($label) ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($kos['latitude']) && !empty($kos['longitude'])): ?>
            <div class="mb-4 p-3 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <p class="text-dark fw-bold mb-3 d-flex align-items-center gap-2" style="font-size: 1rem;">
                    <i class="bi bi-map text-primary"></i> Lokasi
                </p>
                <iframe src="https://maps.google.com/maps?q=<?= $kos['latitude'] ?>,<?= $kos['longitude'] ?>&z=15&output=embed" width="100%" height="350px" style="border:0; border-radius: 12px;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        <?php endif; ?>
        <?php if (!empty($kamar_list)): ?>
            <div class="mb-4">
                <p class="text-dark fw-bold mb-3 d-flex align-items-center justify-content-between" style="font-size: 1rem;">
                    <span><i class="bi bi-door-open-fill text-primary"></i> Pilih Kamar</span>
                    <span class="text-dark-50" style="font-size: 0.8rem; font-weight: 500;">
                        <?= count($kamar_list) ?> Kamar
                    </span>
                </p>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($kamar_list as $kamar): ?>
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-4 transition-all modal-kamar-row"
                             style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 1.05rem;">
                                    Kamar <?= htmlspecialchars($kamar['nomor_kamar']) ?>
                                </div>
                                <div class="text-dark-50" style="font-size: 0.88rem; margin-top: 1px;">
                                    <?= htmlspecialchars($kamar['nama_tipe']) ?>
                                </div>
                            </div>
                            <div class="text-end d-flex align-items-center gap-3">
                                <div>
                                    <div class="fw-extrabold text-accent-orange" style="font-size: 1.15rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                                        Rp <?= number_format($kamar['harga'], 0, ',', '.') ?>
                                        <small class="text-dark-50" style="font-size: 0.75rem; font-weight: 500;">/bln</small>
                                    </div>
                                </div>
                                <?php if ($kamar['status'] === 'penuh'): ?>
                                    <span class="badge text-danger-emphasis bg-danger-subtle border border-danger-subtle rounded-pill px-3 py-1.5" style="font-size: 0.8rem; font-weight: 700;">
                                        Penuh
                                    </span>
                                <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'penyewa'): ?>
                                    <button class="btn btn-primary btn-sm btn-pesan-kamar px-3 py-1.5 fw-bold text-dark rounded-3"
                                            data-id-kamar="<?= $kamar['id'] ?>"
                                            data-nomor-kamar="<?= htmlspecialchars($kamar['nomor_kamar']) ?>"
                                            data-tipe-kamar="<?= htmlspecialchars($kamar['nama_tipe']) ?>"
                                            data-harga="<?= $kamar['harga'] ?>"
                                            data-fasilitas="<?= htmlspecialchars($kamar['fasilitas'] ?? '') ?>"
                                            style="font-size: 0.8rem; box-shadow: 0 4px 12px rgba(37,99,235,0.25);">
                                        Pesan
                                    </button>
                                <?php elseif (!isset($_SESSION['user_id'])): ?>
                                    <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline-primary btn-sm px-3 py-1.5 fw-bold rounded-3" style="font-size: 0.8rem;">
                                        Pesan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-5">
        <div id="modalActionContainer" class="position-sticky" style="top: 10px;">
            <div id="modalOverviewPanel" class="modal-panel-view active">
                <div class="p-4 rounded-4" style="background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 12px 30px rgba(0,0,0,0.3);">
                    <div class="mb-3 text-dark-50" style="font-size: 0.88rem; font-weight: 500;">Harga mulai dari</div>
                    <div class="mb-4">
                        <span class="text-accent-orange fw-extrabold" style="font-size: 2rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                            Rp <?= $harga_min ? number_format($harga_min, 0, ',', '.') : '—' ?>
                        </span>
                        <span class="text-dark-50" style="font-size: 0.95rem; font-weight: 500;">/bulan</span>
                    </div>
                    <?php if ($total_tersedia > 0): ?>
                        <div class="d-inline-flex align-items-center gap-2 text-primary bg-primary-subtle border border-primary-subtle rounded-3 px-3 py-2 mb-4 w-100" style="font-size: 0.92rem; font-weight: 700;">
                            <i class="bi bi-check-circle-fill"></i>
                            <span><?= $total_tersedia ?> kamar tersedia saat ini</span>
                        </div>
                    <?php else: ?>
                        <div class="d-inline-flex align-items-center gap-2 text-danger bg-danger-subtle border border-danger-subtle rounded-3 px-3 py-2 mb-4 w-100" style="font-size: 0.92rem; font-weight: 700;">
                            <i class="bi bi-x-circle-fill"></i>
                            <span>Semua kamar penuh</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($pemilik): ?>
                        <div class="pt-3 border-top border-secondary-subtle mb-4">
                            <p class="text-dark-50 fw-bold mb-3" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                Pemilik Kos
                            </p>
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center text-dark fw-bold rounded-circle" 
                                     style="width: 42px; height: 42px; background: linear-gradient(135deg, #3b82f6, #2563eb); font-size: 1rem; box-shadow: 0 4px 10px rgba(37,99,235,0.2);">
                                    <?= $inisial_pemilik ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.98rem;"><?= htmlspecialchars($pemilik['nama']) ?></div>
                                    <div class="text-dark-50 text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.05em;"><?= htmlspecialchars($pemilik['role']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="text-dark-50" style="font-size: 0.88rem; line-height: 1.5;">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        Silakan pilih salah satu kamar yang tersedia di sebelah kiri untuk melanjutkan proses booking.
                    </div>
                </div>
            </div>
            <div id="modalBookingPanel" class="modal-panel-view d-none">
                <div class="p-4 rounded-4" style="background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 12px 30px rgba(0,0,0,0.3);">
                    <button type="button" id="btnBackToOverview" class="btn text-dark-50 p-0 mb-4 border-0 bg-transparent fw-bold d-flex align-items-center gap-2" style="font-size: 0.92rem;">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </button>
                    <div class="p-3 rounded-3 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <p class="text-primary-emphasis fw-bold text-uppercase mb-2" style="font-size: 0.78rem; letter-spacing: 0.05em;">Kamar Pilihan</p>
                        <div class="fw-bold text-dark fs-5" id="selRoomName">Kamar -</div>
                        <div class="text-dark-50 mb-2" style="font-size: 0.88rem;" id="selRoomTipe">-</div>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary-subtle">
                            <span class="text-dark-50 small">Harga / bln</span>
                            <span class="text-accent-orange fw-extrabold fs-5" id="selRoomHarga" data-raw-harga="0">Rp -</span>
                        </div>
                    </div>
                    <form id="modalFormReservasi" novalidate>
                        <input type="hidden" id="modal_id_kamar" name="id_kamar">
                        <input type="hidden" id="modal_tanggal_masuk" name="tanggal_masuk" required>
                        <div class="mb-4">
                            <label class="form-label text-dark fw-bold d-flex align-items-center gap-2 mb-2" style="font-size: 0.95rem;">
                                <i class="bi bi-calendar3 text-primary"></i> Tanggal Masuk
                            </label>
                            <div class="p-3 rounded-4 border border-light-subtle bg-light">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <button type="button" id="mCalPrev" class="btn btn-sm text-dark border-secondary-subtle d-flex align-items-center justify-content-center" style="width:30px; height:30px; border-radius:8px; background: #e2e8f0;">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <span id="mCalMonthLabel" class="text-dark fw-extrabold" style="font-size: 0.95rem;"></span>
                                    <button type="button" id="mCalNext" class="btn btn-sm text-dark border-secondary-subtle d-flex align-items-center justify-content-center" style="width:30px; height:30px; border-radius:8px; background: #e2e8f0;">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                                <div id="mCalDayHeaders" style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px; margin-bottom:4px;" class="text-center"></div>
                                <div id="mCalGrid" style="display:grid; grid-template-columns:repeat(7,1fr); gap:4px;"></div>
                                <div id="mCalSelectedDisplay" class="mt-3 p-2.5 rounded-3 align-items-center gap-2 border border-primary-subtle text-primary bg-primary-subtle bg-opacity-10" style="display:none; font-size: 0.88rem; font-weight: 700;">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span id="mCalSelectedText"></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-dark fw-bold mb-2" for="modal_durasi" style="font-size: 0.95rem;">
                                <i class="bi bi-clock text-primary me-1"></i> Durasi Sewa (bulan)
                            </label>
                            <input type="number" id="modal_durasi" name="durasi"
                                   class="form-control text-dark border-secondary-subtle rounded-3 px-3 py-2.5"
                                   style="background: #ffffff; font-size: 0.95rem;"
                                   min="1" max="24" placeholder="Contoh: 6" required>
                        </div>
                        <div id="mEstimasiTotal" class="text-primary fw-extrabold mb-4 fs-6" style="min-height: 1.5rem;"></div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-3 text-dark rounded-3 shadow-lg" style="font-size: 1.02rem;">
                            <i class="bi bi-send-check me-1.5"></i> Ajukan Reservasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
