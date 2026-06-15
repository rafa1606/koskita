<?php
require_once '../config/db.php';
requirePemilik();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    $id = intval($_POST['hapus_id']);
    if ($id > 0) {
        $res = mysqli_query($conn, "SELECT foto FROM kos WHERE id = $id");
        $row = mysqli_fetch_assoc($res);
        if ($row && !empty($row['foto'])) {
            $foto_path = __DIR__ . '/../assets/img/kos/' . $row['foto'];
            if (file_exists($foto_path)) {
                unlink($foto_path);
            }
        }
        mysqli_query($conn, "DELETE FROM kos WHERE id = $id");
    }
    redirect('admin/kos_list.php');
}
$page_title = 'Kelola Data Kos';
$search_q = '';
$where_clauses = [];
if (!isAdmin()) {
    $uid = intval($_SESSION['user_id']);
    $where_clauses[] = "id_pemilik = $uid";
}
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $search_q = mysqli_real_escape_string($conn, trim($_GET['q']));
    $where_clauses[] = "(nama_kos LIKE '%$search_q%' OR alamat LIKE '%$search_q%')";
}
$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(' AND ', $where_clauses);
}
$result = mysqli_query($conn, "SELECT * FROM kos $where_sql ORDER BY created_at DESC");
$kos_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
include '../includes/header.php';
?>
<main class="flex-grow-1 py-5" style="background:var(--kk-dark)">
    <div class="container">
        <div class="d-flex align-items-start justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0" style="font-size:.8rem">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active">Kelola Kos</li>
                    </ol>
                </nav>
                <h1 class="fw-bold mb-1 mt-2" style="font-size:1.6rem;font-family:'Plus Jakarta Sans',sans-serif">Kelola Data Kos</h1>
                <?php if ($search_q !== ''): ?>
                    <p class="text-muted mb-0 small"><?= count($kos_list) ?> hasil pencarian untuk "<?= htmlspecialchars($_GET['q']) ?>"</p>
                <?php else: ?>
                    <p class="text-muted mb-0 small"><?= count($kos_list) ?> kos terdaftar</p>
                <?php endif; ?>
            </div>
            <a href="<?= BASE_URL ?>/admin/kos_form.php" class="btn btn-sm text-white align-self-center"
               style="background:var(--kk-blue);border-radius:10px;padding:.45rem 1rem;font-weight:600">
                <i class="bi bi-plus-lg me-1"></i>Tambah Kos
            </a>
        </div>
        <div class="mb-4">
            <form method="GET" action="" class="d-flex" style="max-width: 400px;">
                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0 text-muted px-3"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-0 px-2 py-2" name="q" placeholder="Cari nama kos atau alamat..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="box-shadow: none;">
                    <button class="btn btn-primary px-4 fw-medium" type="submit" style="background: var(--kk-blue); border: none;">Cari</button>
                </div>
            </form>
        </div>
        <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden">
            <?php if (empty($kos_list)): ?>
                <div class="card-body p-5 text-center">
                    <?php if ($search_q !== ''): ?>
                        <i class="bi bi-search text-muted" style="font-size:3rem"></i>
                        <p class="text-muted mt-3 mb-0">Tidak ada kos yang cocok dengan pencarian "<?= htmlspecialchars($_GET['q']) ?>".</p>
                        <a href="<?= BASE_URL ?>/admin/kos_list.php" class="btn btn-sm btn-outline-secondary mt-3" style="border-radius:10px">Reset Pencarian</a>
                    <?php else: ?>
                        <i class="bi bi-building text-muted" style="font-size:3rem"></i>
                        <p class="text-muted mt-3 mb-3">Belum ada data kos.</p>
                        <a href="<?= BASE_URL ?>/admin/kos_form.php" class="btn btn-sm text-white"
                           style="background:var(--kk-blue);border-radius:10px">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Kos Pertama
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.88rem">
                        <thead style="background:#f8fafc">
                            <tr>
                                <th class="fw-semibold px-4 py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">#</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Foto</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Nama Kos</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Alamat</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Koordinat</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Tgl Daftar</th>
                                <th class="fw-semibold pe-4 py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kos_list as $i => $kos): ?>
                            <tr>
                                <td class="px-4 text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($kos['foto'])): ?>
                                        <img src="<?= BASE_URL ?>/assets/img/kos/<?= htmlspecialchars($kos['foto']) ?>"
                                             alt="<?= htmlspecialchars($kos['nama_kos']) ?>"
                                             style="width:60px;height:48px;object-fit:cover;border-radius:8px">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center rounded-2"
                                             style="width:60px;height:48px;background:#f1f5f9;color:var(--kk-muted)">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <p class="fw-semibold mb-0"><?= htmlspecialchars($kos['nama_kos']) ?></p>
                                </td>
                                <td>
                                    <p class="mb-0 text-muted" style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        <?= htmlspecialchars($kos['alamat']) ?>
                                    </p>
                                </td>
                                <td class="text-muted" style="font-size:.8rem">
                                    <?= $kos['latitude'] ?>, <?= $kos['longitude'] ?>
                                </td>
                                <td class="text-muted"><?= date('d M Y', strtotime($kos['created_at'])) ?></td>
                                <td class="pe-4">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="<?= BASE_URL ?>/admin/kamar_list.php?id_kos=<?= $kos['id'] ?>"
                                           class="btn btn-sm text-white" style="background:var(--kk-blue);border-radius:8px;font-size:.8rem">
                                            <i class="bi bi-door-open me-1"></i>Kamar
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/kos_form.php?id=<?= $kos['id'] ?>"
                                           class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.8rem">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="hapus_id" value="<?= $kos['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    style="border-radius:8px;font-size:.8rem"
                                                    onclick="return confirm('Hapus kos &quot;<?= addslashes(htmlspecialchars($kos['nama_kos'])) ?>&quot;?\nTindakan ini tidak dapat dibatalkan.')">
                                                <i class="bi bi-trash me-1"></i>Hapus
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
</main>
<?php include '../includes/footer.php'; ?>
