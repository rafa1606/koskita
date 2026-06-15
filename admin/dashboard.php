<?php
require_once '../config/db.php';
requirePemilik();
$page_title = 'Dashboard Admin';
$total_kos     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM kos"))[0];
$total_kamar   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM kamar"))[0];
$total_penyewa = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM user WHERE role = 'penyewa'"))[0];
$total_pending = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM reservasi WHERE status = 'pending'"))[0];
include '../includes/header.php';
?>
<main class="flex-grow-1 py-5" style="background:var(--kk-dark)">
    <div class="container">
        <div class="d-flex align-items-start justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0" style="font-size:.8rem">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item active">Dashboard Admin</li>
                    </ol>
                </nav>
                <h1 class="fw-bold mb-1 mt-2" style="font-size:1.6rem;font-family:'Plus Jakarta Sans',sans-serif">Dashboard Admin</h1>
                <p class="text-muted mb-0 small">Selamat datang kembali, <strong><?= htmlspecialchars($_SESSION['user_nama']) ?></strong></p>
            </div>
        </div>
        <div class="row g-4 mb-5">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3"
                                 style="width:48px;height:48px;background:rgba(37,99,235,.1)">
                                <i class="bi bi-building-fill" style="font-size:1.25rem;color:var(--kk-blue)"></i>
                            </div>
                            <span class="badge rounded-pill px-3 py-2" style="background:rgba(37,99,235,.1);color:var(--kk-blue);font-size:.75rem">Kos</span>
                        </div>
                        <p class="text-muted small mb-1 fw-medium">Total Kos</p>
                        <h2 class="fw-bold mb-0" style="font-size:2.2rem;color:var(--kk-blue)"><?= $total_kos ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3"
                                 style="width:48px;height:48px;background:rgba(37,99,235,.1)">
                                <i class="bi bi-door-open-fill" style="font-size:1.25rem;color:var(--kk-blue)"></i>
                            </div>
                            <span class="badge rounded-pill px-3 py-2" style="background:rgba(37,99,235,.1);color:var(--kk-blue);font-size:.75rem">Kamar</span>
                        </div>
                        <p class="text-muted small mb-1 fw-medium">Total Kamar</p>
                        <h2 class="fw-bold mb-0" style="font-size:2.2rem;color:var(--kk-blue)"><?= $total_kamar ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3"
                                 style="width:48px;height:48px;background:rgba(37,99,235,.1)">
                                <i class="bi bi-people-fill" style="font-size:1.25rem;color:var(--kk-blue)"></i>
                            </div>
                            <span class="badge rounded-pill px-3 py-2" style="background:rgba(37,99,235,.1);color:var(--kk-blue);font-size:.75rem">User</span>
                        </div>
                        <p class="text-muted small mb-1 fw-medium">User Penyewa</p>
                        <h2 class="fw-bold mb-0" style="font-size:2.2rem;color:var(--kk-blue)"><?= $total_penyewa ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3"
                                 style="width:48px;height:48px;background:rgba(37,99,235,.1)">
                                <i class="bi bi-hourglass-split" style="font-size:1.25rem;color:var(--kk-blue)"></i>
                            </div>
                            <span class="badge rounded-pill px-3 py-2" style="background:rgba(37,99,235,.1);color:var(--kk-blue);font-size:.75rem">Pending</span>
                        </div>
                        <p class="text-muted small mb-1 fw-medium">Reservasi Pending</p>
                        <h2 class="fw-bold mb-0" style="font-size:2.2rem;color:var(--kk-blue)"><?= $total_pending ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <h5 class="fw-bold mb-3" style="font-family:'Plus Jakarta Sans',sans-serif">Menu Kelola</h5>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/admin/reservasi_list.php"
                   class="card border-0 shadow-sm text-decoration-none h-100" style="border-radius:16px;transition:transform .2s,box-shadow .2s"
                   onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.1)'"
                   onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                             style="width:48px;height:48px;background:rgba(37,99,235,.1)">
                            <i class="bi bi-journal-check" style="font-size:1.25rem;color:var(--kk-blue)"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="fw-semibold mb-0 text-dark" style="font-size:.95rem">Kelola Reservasi</p>
                            <p class="text-muted mb-0" style="font-size:.8rem">Terima atau tolak reservasi masuk</p>
                        </div>
                        <i class="bi bi-chevron-right text-muted flex-shrink-0"></i>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="<?= BASE_URL ?>/admin/kos_list.php"
                   class="card border-0 shadow-sm text-decoration-none h-100" style="border-radius:16px;transition:transform .2s,box-shadow .2s"
                   onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.1)'"
                   onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                             style="width:48px;height:48px;background:rgba(37,99,235,.1)">
                            <i class="bi bi-building" style="font-size:1.25rem;color:var(--kk-blue)"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="fw-semibold mb-0 text-dark" style="font-size:.95rem">Kelola Data Kos</p>
                            <p class="text-muted mb-0" style="font-size:.8rem">Tambah, edit, dan hapus data kos</p>
                        </div>
                        <i class="bi bi-chevron-right text-muted flex-shrink-0"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
