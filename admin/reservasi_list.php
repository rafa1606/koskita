<?php
require_once '../config/db.php';
requirePemilik();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservasi_id'], $_POST['action'])) {
    $id     = intval($_POST['reservasi_id']);
    $action = $_POST['action'];
    if ($id > 0) {
        if ($action === 'terima') {
            mysqli_query($conn, "UPDATE reservasi SET status = 'diterima' WHERE id = $id");
            $r = mysqli_query($conn, "SELECT id_kamar FROM reservasi WHERE id = $id LIMIT 1");
            if ($r && $row_r = mysqli_fetch_assoc($r)) {
                $id_kamar = intval($row_r['id_kamar']);
                mysqli_query($conn, "UPDATE kamar SET status = 'penuh' WHERE id = $id_kamar");
            }
        } else {
            mysqli_query($conn, "UPDATE reservasi SET status = 'ditolak' WHERE id = $id");
            $r = mysqli_query($conn, "SELECT id_kamar FROM reservasi WHERE id = $id LIMIT 1");
            if ($r && $row_r = mysqli_fetch_assoc($r)) {
                $id_kamar = intval($row_r['id_kamar']);
                mysqli_query($conn, "UPDATE kamar SET status = 'tersedia' WHERE id = $id_kamar");
            }
        }
    }
    redirect('admin/reservasi_list.php');
}
$page_title = 'Kelola Reservasi';
if (isAdmin()) {
    $sql = "SELECT r.id, u.nama AS nama_user, k2.nama_kos, k.nomor_kamar,
                   t.nama_tipe, r.tanggal_masuk, r.durasi, k.harga,
                   (r.durasi * k.harga) AS total_harga, r.status, r.tanggal_pesan
            FROM reservasi r
            JOIN user u ON r.id_user = u.id
            JOIN kamar k ON r.id_kamar = k.id
            JOIN kos k2 ON k.id_kos = k2.id
            JOIN tipe_kamar t ON k.id_tipe = t.id
            ORDER BY r.tanggal_pesan DESC";
} else {
    $uid = intval($_SESSION['user_id']);
    $sql = "SELECT r.id, u.nama AS nama_user, k2.nama_kos, k.nomor_kamar,
                   t.nama_tipe, r.tanggal_masuk, r.durasi, k.harga,
                   (r.durasi * k.harga) AS total_harga, r.status, r.tanggal_pesan
            FROM reservasi r
            JOIN user u ON r.id_user = u.id
            JOIN kamar k ON r.id_kamar = k.id
            JOIN kos k2 ON k.id_kos = k2.id
            JOIN tipe_kamar t ON k.id_tipe = t.id
            WHERE k2.id_pemilik = $uid
            ORDER BY r.tanggal_pesan DESC";
}
$result    = mysqli_query($conn, $sql);
$reservasis = mysqli_fetch_all($result, MYSQLI_ASSOC);
include '../includes/header.php';
?>
<main class="flex-grow-1 py-5" style="background:var(--kk-dark)">
    <div class="container">
        <div class="d-flex align-items-start justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0" style="font-size:.8rem">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active">Kelola Reservasi</li>
                    </ol>
                </nav>
                <h1 class="fw-bold mb-1 mt-2" style="font-size:1.6rem;font-family:'Plus Jakarta Sans',sans-serif">Kelola Reservasi</h1>
                <p class="text-muted mb-0 small"><?= count($reservasis) ?> total data reservasi</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm align-self-center" style="border-radius:10px">
                Dashboard
            </a>
        </div>
        <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden">
            <?php if (empty($reservasis)): ?>
                <div class="card-body p-5 text-center">
                    <i class="bi bi-journal-x text-muted" style="font-size:3rem"></i>
                    <p class="text-muted mt-3 mb-0">Belum ada data reservasi.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.88rem">
                        <thead style="background:#f8fafc">
                            <tr>
                                <th class="fw-semibold px-4 py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">#</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Penyewa</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Kos / Kamar</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Tipe</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Tgl Masuk</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Durasi</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Total Harga</th>
                                <th class="fw-semibold py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Status</th>
                                <th class="fw-semibold pe-4 py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--kk-muted)">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservasis as $i => $row): ?>
                            <?php
                                if ($row['status'] === 'diterima') {
                                    $badge = 'bg-success';
                                } elseif ($row['status'] === 'ditolak') {
                                    $badge = 'bg-danger';
                                } else {
                                    $badge = 'bg-warning';
                                }
                            ?>
                            <tr>
                                <td class="px-4 text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-flex align-items-center justify-content-center rounded-circle text-dark fw-bold flex-shrink-0"
                                              style="width:32px;height:32px;background:var(--kk-blue);font-size:.75rem">
                                            <?= strtoupper(substr($row['nama_user'], 0, 1)) ?>
                                        </span>
                                        <span class="fw-medium"><?= htmlspecialchars($row['nama_user']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <p class="fw-medium mb-0"><?= htmlspecialchars($row['nama_kos']) ?></p>
                                    <p class="text-muted mb-0" style="font-size:.8rem">Kamar <?= htmlspecialchars($row['nomor_kamar']) ?></p>
                                </td>
                                <td><?= htmlspecialchars($row['nama_tipe']) ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_masuk'])) ?></td>
                                <td><?= $row['durasi'] ?> bulan</td>
                                <td class="fw-semibold">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge rounded-pill <?= $badge ?> px-3 py-2">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <?php if ($row['status'] === 'pending'): ?>
                                    <div class="d-flex gap-2">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="reservasi_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="action" value="terima">
                                            <button type="submit" class="btn btn-success btn-sm px-3"
                                                    style="font-size:.8rem;border-radius:8px">
                                                <i class="bi bi-check-lg me-1"></i>Terima
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="reservasi_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="action" value="tolak">
                                            <button type="submit" class="btn btn-danger btn-sm px-3"
                                                    style="font-size:.8rem;border-radius:8px">
                                                <i class="bi bi-x-lg me-1"></i>Tolak
                                            </button>
                                        </form>
                                    </div>
                                    <?php elseif ($row['status'] === 'diterima'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="reservasi_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="action" value="tolak">
                                        <button type="submit" class="btn btn-warning btn-sm px-3"
                                                style="font-size:.8rem;border-radius:8px"
                                                onclick="return confirm('Batalkan reservasi yang sudah diterima?')">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Batalkan
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-muted" style="font-size:.8rem">—</span>
                                    <?php endif; ?>
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
