<?php
require_once '../config/db.php';
requirePemilik();

$id_kos = isset($_GET['id_kos']) ? intval($_GET['id_kos']) : 0;
if ($id_kos <= 0) redirect('admin/kos_list.php');


$res_kos = mysqli_query($conn, "SELECT * FROM kos WHERE id = $id_kos LIMIT 1");
$kos     = mysqli_fetch_assoc($res_kos);
if (!$kos) redirect('admin/kos_list.php');
if (!isAdmin() && $kos['id_pemilik'] != $_SESSION['user_id']) redirect('admin/kos_list.php');

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_kamar_id'])) {
    $hid = intval($_POST['hapus_kamar_id']);
    if ($hid > 0) {
        
        $r = mysqli_query($conn, "SELECT foto FROM kamar WHERE id = $hid AND id_kos = $id_kos LIMIT 1");
        if ($r && $row_f = mysqli_fetch_assoc($r)) {
            if (!empty($row_f['foto'])) {
                $fp = __DIR__ . '/../assets/img/kos/' . $row_f['foto'];
                if (file_exists($fp)) unlink($fp);
            }
        }
        mysqli_query($conn, "DELETE FROM kamar WHERE id = $hid AND id_kos = $id_kos");
        $success = 'Kamar berhasil dihapus.';
    }
}


$edit_kamar = null;
$edit_id    = 0;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $re      = mysqli_query($conn, "SELECT * FROM kamar WHERE id = $edit_id AND id_kos = $id_kos LIMIT 1");
    $edit_kamar = mysqli_fetch_assoc($re);
    if (!$edit_kamar) $edit_id = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_kamar'])) {
    $kid         = intval($_POST['kamar_id'] ?? 0);
    $id_tipe     = intval($_POST['id_tipe'] ?? 0);
    $nomor_kamar = trim($_POST['nomor_kamar'] ?? '');
    $harga       = intval($_POST['harga'] ?? 0);
    $fasilitas   = trim($_POST['fasilitas'] ?? '');
    $status_km   = $_POST['status'] === 'penuh' ? 'penuh' : 'tersedia';

    if ($id_tipe <= 0)        $errors[] = 'Tipe kamar wajib dipilih.';
    if ($nomor_kamar === '')  $errors[] = 'Nomor kamar wajib diisi.';
    if ($harga <= 0)          $errors[] = 'Harga wajib diisi.';

    $foto_km = '';
    if ($kid > 0) {
        $rk = mysqli_query($conn, "SELECT foto FROM kamar WHERE id = $kid AND id_kos = $id_kos LIMIT 1");
        $rk_row = mysqli_fetch_assoc($rk);
        $foto_km = $rk_row['foto'] ?? '';
    }

    if (isset($_FILES['foto_kamar']) && $_FILES['foto_kamar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext     = strtolower(pathinfo($_FILES['foto_kamar']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Format foto tidak valid.';
        } elseif ($_FILES['foto_kamar']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran foto maksimal 2MB.';
        } else {
            $baru    = uniqid('km_', true) . '.' . $ext;
            $updir   = __DIR__ . '/../assets/img/kos/';
            if (!is_dir($updir)) mkdir($updir, 0755, true);
            if (move_uploaded_file($_FILES['foto_kamar']['tmp_name'], $updir . $baru)) {
                if (!empty($foto_km) && file_exists($updir . $foto_km)) unlink($updir . $foto_km);
                $foto_km = $baru;
            } else {
                $errors[] = 'Gagal upload foto.';
            }
        }
    }

    if (empty($errors)) {
        $nomor_e     = mysqli_real_escape_string($conn, $nomor_kamar);
        $fasilitas_e = mysqli_real_escape_string($conn, $fasilitas);
        $foto_e      = mysqli_real_escape_string($conn, $foto_km);

        if ($kid > 0) {
            mysqli_query($conn, "UPDATE kamar SET
                id_tipe='$id_tipe', nomor_kamar='$nomor_e', harga='$harga',
                fasilitas='$fasilitas_e', status='$status_km', foto='$foto_e'
                WHERE id=$kid AND id_kos=$id_kos");
            $success = 'Kamar berhasil diperbarui.';
        } else {
            mysqli_query($conn, "INSERT INTO kamar (id_kos, id_tipe, nomor_kamar, harga, fasilitas, status, foto)
                VALUES ($id_kos, $id_tipe, '$nomor_e', $harga, '$fasilitas_e', '$status_km', '$foto_e')");
            $success = 'Kamar berhasil ditambahkan.';
        }
        
        $edit_kamar = null;
        $edit_id    = 0;
        header("Location: kamar_list.php?id_kos=$id_kos&ok=1");
        exit;
    }
}

if (isset($_GET['ok'])) $success = 'Perubahan berhasil disimpan.';


$q_kamar = mysqli_query($conn, "
    SELECT km.*, t.nama_tipe
    FROM kamar km
    JOIN tipe_kamar t ON t.id = km.id_tipe
    WHERE km.id_kos = $id_kos
    ORDER BY km.nomor_kamar ASC");
$kamar_list = mysqli_fetch_all($q_kamar, MYSQLI_ASSOC);


$q_tipe = mysqli_query($conn, "SELECT * FROM tipe_kamar ORDER BY id ASC");
$tipe_list = mysqli_fetch_all($q_tipe, MYSQLI_ASSOC);

$page_title = 'Kelola Kamar | ' . $kos['nama_kos'];
include '../includes/header.php';
?>

<main class="flex-grow-1 py-5" style="background:#f8fafc !important;">
<div class="container">

    
    <div class="d-flex align-items-start justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-0" style="font-size:.8rem">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/kos_list.php" class="text-decoration-none">Kelola Kos</a></li>
                    <li class="breadcrumb-item active">Kelola Kamar</li>
                </ol>
            </nav>
            <h1 class="fw-bold mb-0 mt-2" style="font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif">
                Kelola Kamar
            </h1>
            <p class="text-muted mb-0 small mt-1">
                <i class="bi bi-building me-1"></i><?= htmlspecialchars($kos['nama_kos']) ?>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/admin/kos_list.php"
           class="btn btn-outline-secondary btn-sm align-self-center" style="border-radius:10px">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <?php if ($success): ?>
    <div class="alert border-0 rounded-3 mb-4 d-flex align-items-center gap-2"
         style="background:#f0fdf4;color:#166534">
        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
        <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger border-0 rounded-3 mb-4">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="row g-4">

        
        <div class="col-lg-4">
            <div class="card border-0" style="border-radius:16px;position:sticky;top:80px;box-shadow:0 12px 32px rgba(0,0,0,0.08)">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4" style="font-size:.95rem">
                        <i class="bi bi-<?= $edit_id ? 'pencil' : 'plus-circle' ?> me-2" style="color:var(--kk-blue)"></i>
                        <?= $edit_id ? 'Edit Kamar' : 'Tambah Kamar Baru' ?>
                    </h6>

                    <form method="POST" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="simpan_kamar" value="1">
                        <input type="hidden" name="kamar_id" value="<?= $edit_id ?>">

                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.82rem">
                                Nomor Kamar <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nomor_kamar" class="form-control"
                                   style="border-radius:8px;font-size:.88rem"
                                   placeholder="Contoh: A1, 101, B-02"
                                   value="<?= htmlspecialchars($edit_kamar['nomor_kamar'] ?? '') ?>">
                        </div>

                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.82rem">
                                Tipe Kamar <span class="text-danger">*</span>
                            </label>
                            <select name="id_tipe" class="form-select" style="border-radius:8px;font-size:.88rem">
                                <option value="">— Pilih tipe —</option>
                                <?php foreach ($tipe_list as $t): ?>
                                <option value="<?= $t['id'] ?>"
                                    <?= (isset($edit_kamar['id_tipe']) && $edit_kamar['id_tipe'] == $t['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nama_tipe']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.82rem">
                                Harga / Bulan <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" style="font-size:.85rem;border-radius:8px 0 0 8px">Rp</span>
                                <input type="number" name="harga" class="form-control"
                                       style="border-radius:0 8px 8px 0;font-size:.88rem"
                                       placeholder="500000" min="0"
                                       value="<?= $edit_kamar['harga'] ?? '' ?>">
                            </div>
                        </div>

                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.82rem">Fasilitas</label>
                            <input type="text" name="fasilitas" class="form-control"
                                   style="border-radius:8px;font-size:.88rem"
                                   placeholder="WiFi, Lemari, Kasur, ..."
                                   value="<?= htmlspecialchars($edit_kamar['fasilitas'] ?? '') ?>">
                            <div class="form-text">Pisahkan dengan koma.</div>
                        </div>

                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.82rem">Status</label>
                            <select name="status" class="form-select" style="border-radius:8px;font-size:.88rem">
                                <option value="tersedia" <?= (!isset($edit_kamar['status']) || $edit_kamar['status'] === 'tersedia') ? 'selected' : '' ?>>
                                    Tersedia
                                </option>
                                <option value="penuh" <?= (isset($edit_kamar['status']) && $edit_kamar['status'] === 'penuh') ? 'selected' : '' ?>>
                                    Penuh
                                </option>
                            </select>
                        </div>

                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="font-size:.82rem">Foto Kamar</label>
                            <?php if ($edit_id && !empty($edit_kamar['foto'])): ?>
                            <div class="mb-2">
                                <img src="<?= BASE_URL ?>/assets/img/kos/<?= htmlspecialchars($edit_kamar['foto']) ?>"
                                     style="height:70px;border-radius:8px;object-fit:cover" alt="Foto kamar">
                                <p class="text-muted mb-0 mt-1" style="font-size:.75rem">Upload baru untuk mengganti.</p>
                            </div>
                            <?php endif; ?>
                            <input type="file" name="foto_kamar" class="form-control"
                                   style="border-radius:8px;font-size:.85rem"
                                   accept=".jpg,.jpeg,.png,.webp">
                            <div class="form-text">Opsional. Maks 2MB.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn text-white flex-fill"
                                    style="background:var(--kk-blue);border-radius:8px;font-weight:600;font-size:.88rem">
                                <i class="bi bi-<?= $edit_id ? 'floppy' : 'plus-lg' ?> me-1"></i>
                                <?= $edit_id ? 'Simpan' : 'Tambah' ?>
                            </button>
                            <?php if ($edit_id): ?>
                            <a href="kamar_list.php?id_kos=<?= $id_kos ?>"
                               class="btn btn-outline-secondary" style="border-radius:8px;font-size:.88rem">
                                Batal
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
        <div class="col-lg-8">
            <div class="card border-0" style="border-radius:16px;overflow:hidden;box-shadow:0 12px 32px rgba(0,0,0,0.08)">
                <div class="card-header px-4 py-3 bg-white" style="border-bottom: 1px solid #f1f5f9 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fw-semibold" style="font-size:.9rem">
                            Daftar Kamar
                        </span>
                        <span class="badge rounded-pill px-3 py-2"
                              style="background:#eff6ff;color:#2563eb;font-size:.75rem">
                            <?= count($kamar_list) ?> kamar
                        </span>
                    </div>
                </div>

                <?php if (empty($kamar_list)): ?>
                <div class="card-body p-5 text-center">
                    <i class="bi bi-door-open text-muted" style="font-size:2.5rem;opacity:.4"></i>
                    <p class="text-muted mt-3 mb-0">Belum ada kamar. Tambahkan kamar di sebelah kiri.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.87rem">
                        <thead style="background:#f8fafc">
                            <tr>
                                <th class="fw-semibold px-4 py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">No.</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Kamar</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Tipe</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Harga</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Status</th>
                                <th class="fw-semibold pe-4 py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($kamar_list as $i => $km): ?>
                        <tr>
                            <td class="px-4 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($km['foto'])): ?>
                                    <img src="<?= BASE_URL ?>/assets/img/kos/<?= htmlspecialchars($km['foto']) ?>"
                                         style="width:40px;height:32px;object-fit:cover;border-radius:6px" alt="">
                                    <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center rounded-2"
                                         style="width:40px;height:32px;background:#f1f5f9;color:var(--kk-muted)">
                                        <i class="bi bi-door-closed" style="font-size:.8rem"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="fw-semibold mb-0"><?= htmlspecialchars($km['nomor_kamar']) ?></p>
                                        <?php if (!empty($km['fasilitas'])): ?>
                                        <p class="mb-0 text-muted" style="font-size:.75rem;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                            <?= htmlspecialchars($km['fasilitas']) ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($km['nama_tipe']) ?></td>
                            <td class="fw-semibold">Rp <?= number_format($km['harga'], 0, ',', '.') ?></td>
                            <td>
                                <?php if ($km['status'] === 'tersedia'): ?>
                                <span class="badge rounded-pill bg-success px-3 py-2" style="font-size:.75rem">Tersedia</span>
                                <?php else: ?>
                                <span class="badge rounded-pill bg-danger px-3 py-2" style="font-size:.75rem">Penuh</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4">
                                <div class="d-flex gap-2">
                                    <a href="kamar_list.php?id_kos=<?= $id_kos ?>&edit=<?= $km['id'] ?>"
                                       class="btn btn-sm btn-outline-primary" style="border-radius:7px;font-size:.78rem">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="hapus_kamar_id" value="<?= $km['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                style="border-radius:7px;font-size:.78rem"
                                                onclick="return confirm('Hapus kamar <?= addslashes(htmlspecialchars($km['nomor_kamar'])) ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>
