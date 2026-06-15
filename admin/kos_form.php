<?php
require_once '../config/db.php';
requirePemilik();
$id       = isset($_GET['id']) ? intval($_GET['id']) : 0;
$is_edit  = $id > 0;
$errors   = [];
$kos      = ['nama_kos' => '', 'alamat' => '', 'latitude' => '', 'longitude' => '', 'deskripsi' => '', 'foto' => ''];
if ($is_edit) {
    $res = mysqli_query($conn, "SELECT * FROM kos WHERE id = $id");
    $row = mysqli_fetch_assoc($res);
    if (!$row) {
        redirect('admin/kos_list.php');
    }
    if (!isAdmin() && $row['id_pemilik'] != $_SESSION['user_id']) {
        redirect('admin/kos_list.php');
    }
    $kos = $row;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kos  = trim($_POST['nama_kos']  ?? '');
    $alamat    = trim($_POST['alamat']    ?? '');
    $latitude  = trim($_POST['latitude']  ?? '');
    $longitude = trim($_POST['longitude'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    if ($nama_kos === '')  $errors[] = 'Nama kos wajib diisi.';
    if ($alamat === '')    $errors[] = 'Alamat wajib diisi.';
    $nama_file = $kos['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        $file_tmp    = $_FILES['foto']['tmp_name'];
        $file_name   = $_FILES['foto']['name'];
        $file_size   = $_FILES['foto']['size'];
        $ext         = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) {
            $errors[] = 'Format foto tidak valid. Hanya jpg, png, atau webp.';
        } elseif ($file_size > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran foto maksimal 2MB.';
        } else {
            $nama_file_baru = uniqid('kos_', true) . '.' . $ext;
            $upload_dir     = __DIR__ . '/../assets/img/kos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            if (!move_uploaded_file($file_tmp, $upload_dir . $nama_file_baru)) {
                $errors[] = 'Gagal mengupload foto. Coba lagi.';
            } else {
                if ($is_edit && !empty($kos['foto'])) {
                    $foto_lama = $upload_dir . $kos['foto'];
                    if (file_exists($foto_lama)) {
                        unlink($foto_lama);
                    }
                }
                $nama_file = $nama_file_baru;
            }
        }
    }
    if (empty($errors)) {
        $nama_kos_e  = mysqli_real_escape_string($conn, $nama_kos);
        $alamat_e    = mysqli_real_escape_string($conn, $alamat);
        $latitude_e  = mysqli_real_escape_string($conn, $latitude);
        $longitude_e = mysqli_real_escape_string($conn, $longitude);
        $deskripsi_e = mysqli_real_escape_string($conn, $deskripsi);
        $foto_e      = mysqli_real_escape_string($conn, $nama_file);
        if ($is_edit) {
            mysqli_query($conn, "UPDATE kos SET
                nama_kos  = '$nama_kos_e',
                alamat    = '$alamat_e',
                latitude  = '$latitude_e',
                longitude = '$longitude_e',
                deskripsi = '$deskripsi_e',
                foto      = '$foto_e'
                WHERE id  = $id");
        } else {
            $id_pemilik = intval($_SESSION['user_id']);
            mysqli_query($conn, "INSERT INTO kos (nama_kos, alamat, latitude, longitude, deskripsi, foto, id_pemilik)
                VALUES ('$nama_kos_e', '$alamat_e', '$latitude_e', '$longitude_e', '$deskripsi_e', '$foto_e', $id_pemilik)");
        }
        redirect('admin/kos_list.php');
    }
    $kos['nama_kos']  = $nama_kos;
    $kos['alamat']    = $alamat;
    $kos['latitude']  = $latitude;
    $kos['longitude'] = $longitude;
    $kos['deskripsi'] = $deskripsi;
}
$page_title = $is_edit ? 'Edit Kos' : 'Tambah Kos';
include '../includes/header.php';
?>
<main class="flex-grow-1 py-5" style="background:var(--kk-dark)">
    <div class="container">
        <div class="d-flex align-items-start justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0" style="font-size:.8rem">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/kos_list.php" class="text-decoration-none">Kelola Kos</a></li>
                        <li class="breadcrumb-item active"><?= $is_edit ? 'Edit' : 'Tambah' ?></li>
                    </ol>
                </nav>
                <h1 class="fw-bold mb-0 mt-2" style="font-size:1.6rem;font-family:'Plus Jakarta Sans',sans-serif">
                    <?= $is_edit ? 'Edit Data Kos' : 'Tambah Kos Baru' ?>
                </h1>
            </div>
            <a href="<?= BASE_URL ?>/admin/kos_list.php"
               class="btn btn-outline-secondary btn-sm align-self-center" style="border-radius:10px">
                Kembali
            </a>
        </div>
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger border-0 rounded-3 mb-4" style="background:#fef2f2">
            <div class="d-flex gap-2">
                <i class="bi bi-exclamation-circle-fill text-danger mt-1 flex-shrink-0"></i>
                <ul class="mb-0 ps-0" style="list-style:none">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        <div class="card border-0 shadow-sm" style="border-radius:16px">
            <div class="card-body p-4 p-md-5">
                <form method="POST" enctype="multipart/form-data" novalidate id="kosForm">
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.88rem">
                            Nama Kos <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_kos" class="form-control"
                               style="border-radius:10px;font-size:.9rem"
                               placeholder="Contoh: Kos Putra Sejahtera"
                               value="<?= htmlspecialchars($kos['nama_kos']) ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.88rem">
                            Alamat <span class="text-danger">*</span>
                        </label>
                        <textarea name="alamat" class="form-control" rows="2"
                                  style="border-radius:10px;font-size:.9rem;resize:vertical"
                                  placeholder="Jl. Contoh No. 1, Kel. X, Kec. Y, Yogyakarta"
                                  required><?= htmlspecialchars($kos['alamat']) ?></textarea>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.88rem">Latitude</label>
                            <input type="text" name="latitude" class="form-control"
                                   style="border-radius:10px;font-size:.9rem"
                                   placeholder="-7.797068"
                                   value="<?= htmlspecialchars($kos['latitude']) ?>">
                            <div class="form-text">Koordinat GPS (opsional)</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.88rem">Longitude</label>
                            <input type="text" name="longitude" class="form-control"
                                   style="border-radius:10px;font-size:.9rem"
                                   placeholder="110.370529"
                                   value="<?= htmlspecialchars($kos['longitude']) ?>">
                            <div class="form-text">Koordinat GPS (opsional)</div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.88rem">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4"
                                  style="border-radius:10px;font-size:.9rem;resize:vertical"
                                  placeholder="Deskripsikan fasilitas, lokasi, dan keunggulan kos..."><?= htmlspecialchars($kos['deskripsi']) ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.88rem">Foto Kos</label>
                        <?php if ($is_edit && !empty($kos['foto'])): ?>
                        <div class="mb-2">
                            <img src="<?= BASE_URL ?>/assets/img/kos/<?= htmlspecialchars($kos['foto']) ?>"
                                 alt="Foto saat ini"
                                 style="height:100px;border-radius:10px;object-fit:cover">
                            <p class="text-muted mt-1 mb-0" style="font-size:.8rem">Foto saat ini. Upload baru untuk mengganti.</p>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="foto" id="foto" class="form-control"
                               style="border-radius:10px;font-size:.9rem"
                               accept=".jpg,.jpeg,.png,.webp">
                        <div class="form-text">Format: jpg, png, webp. Maksimal 2MB.</div>
                    </div>
                    <div id="preview-wrapper" class="mb-4 d-none">
                        <p class="fw-semibold mb-2" style="font-size:.88rem">Preview:</p>
                        <img id="foto-preview" src="#" alt="Preview"
                             style="height:120px;border-radius:10px;object-fit:cover">
                    </div>
                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn text-dark px-4"
                                style="background:var(--kk-blue);border-radius:10px;font-weight:600">
                            <i class="bi bi-<?= $is_edit ? 'floppy' : 'plus-lg' ?> me-2"></i>
                            <?= $is_edit ? 'Simpan Perubahan' : 'Tambah Kos' ?>
                        </button>
                        <a href="<?= BASE_URL ?>/admin/kos_list.php"
                           class="btn btn-outline-secondary px-4" style="border-radius:10px">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<script>
document.getElementById('foto').addEventListener('change', function () {
    var file = this.files[0];
    var wrapper = document.getElementById('preview-wrapper');
    var preview = document.getElementById('foto-preview');
    if (file) {
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            wrapper.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    } else {
        wrapper.classList.add('d-none');
    }
});
document.getElementById('kosForm').addEventListener('submit', function(e) {
    var isValid = true;
    // Bersihkan pesan error sebelumnya
    var oldErrors = document.querySelectorAll('.js-error');
    oldErrors.forEach(function(el) {
        el.remove();
    });
    function showError(inputName, message) {
        isValid = false;
        var input = document.querySelector('[name="' + inputName + '"]');
        if (input) {
            var err = document.createElement('div');
            err.className = 'text-danger mt-1 js-error fw-medium';
            err.style.fontSize = '0.85rem';
            err.innerText = message;
            input.parentNode.appendChild(err);
        }
    }
    // 1. Validasi nama_kos
    var nama = document.querySelector('[name="nama_kos"]').value.trim();
    if (nama.length < 3) {
        showError('nama_kos', 'Nama kos tidak boleh kosong dan minimal 3 karakter.');
    }
    // 2. Validasi alamat
    var alamat = document.querySelector('[name="alamat"]').value.trim();
    if (alamat.length < 10) {
        showError('alamat', 'Alamat tidak boleh kosong dan minimal 10 karakter.');
    }
    // 3. Validasi latitude
    var lat = document.querySelector('[name="latitude"]').value.trim();
    if (lat !== '') {
        var numLat = parseFloat(lat);
        if (isNaN(numLat) || numLat < -90 || numLat > 90) {
            showError('latitude', 'Latitude harus berupa angka antara -90 sampai 90.');
        }
    }
    // 4. Validasi longitude
    var lng = document.querySelector('[name="longitude"]').value.trim();
    if (lng !== '') {
        var numLng = parseFloat(lng);
        if (isNaN(numLng) || numLng < -180 || numLng > 180) {
            showError('longitude', 'Longitude harus berupa angka antara -180 sampai 180.');
        }
    }
    if (!isValid) {
        e.preventDefault();
    }
});
</script>
<?php include '../includes/footer.php'; ?>
