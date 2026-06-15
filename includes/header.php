<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/db.php';
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' | KosKita' : 'KosKita' ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/logo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top py-2 px-4 bg-white shadow-sm" id="mainNav" style="border-bottom: 1px solid #f1f5f9;">
    <div class="container-fluid position-relative align-items-center d-flex justify-content-between">
        <a class="navbar-brand d-flex align-items-center text-decoration-none m-0" href="<?= BASE_URL ?>/index.php">
            <span class="brand-icon" style="width: 32px; height: 32px; background: var(--kk-blue); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 8px;">
                <i class="bi bi-house-heart-fill text-white" style="font-size:.9rem"></i>
            </span>
            <span style="font-weight: 800; font-size: 1.25rem; color: #0f172a; font-family: 'Plus Jakarta Sans', sans-serif;">KosKita</span>
        </a>
        <button class="navbar-toggler border-0 p-1" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-label="Menu">
            <i class="bi bi-list fs-4" style="color:var(--kk-text)"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Center Links -->
            <ul class="navbar-nav nav-links-pill mx-auto nav-center-absolute mb-2 mb-lg-0 gap-1" style="z-index: 10;">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php#kos-list">Daftar Kos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php#cara-pesan">Cara Pesan</a>
                </li>
            </ul>
            <!-- Right Elements -->
            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 mt-3 mt-lg-0 ms-lg-auto" style="z-index: 5;">
                <div class="nav-search-wrap">
                    <form action="<?= BASE_URL ?>/index.php" method="GET" id="navSearchForm">
                        <div class="nav-search-inner" style="background: #f8fafc; border-radius: 50px; padding: 4px 16px; display: flex; align-items: center; gap: 8px; border: 1px solid #e2e8f0;">
                            <i class="bi bi-search" style="color: #94a3b8; font-size: 0.9rem;"></i>
                            <input type="text" name="q" id="navSearchInput"
                                   placeholder="Cari nama kos..."
                                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                                   autocomplete="off"
                                   style="border: none; background: transparent; outline: none; font-size: 0.9rem; width: 160px; color: #0f172a;">
                        </div>
                    </form>
                </div>
                <div class="d-none d-lg-block" style="width: 1px; height: 24px; background: #e2e8f0;"></div>
                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        $nav_foto      = $_SESSION['user_foto'] ?? '';
                        $nav_foto_path = __DIR__ . '/../assets/img/profil/' . $nav_foto;
                        $nav_has_foto  = !empty($nav_foto) && file_exists($nav_foto_path);
                        ?>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-0"
                               href="#" role="button" data-bs-toggle="dropdown" style="text-decoration:none;">
                                <?php if ($nav_has_foto): ?>
                                    <img src="<?= BASE_URL ?>/assets/img/profil/<?= htmlspecialchars($nav_foto) ?>"
                                         style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;" alt="">
                                <?php else: ?>
                                    <span class="brand-icon" style="width:36px;height:36px;font-size:.9rem;background:var(--kk-blue);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                        <?= strtoupper(substr($_SESSION['user_nama'],0,1)) ?>
                                    </span>
                                <?php endif; ?>
                                <span style="font-size:.9rem;font-weight:700;color:#0f172a">
                                    <?= htmlspecialchars($_SESSION['user_nama']) ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-lg-end shadow border-0" style="border-radius:16px;min-width:220px;margin-top:12px;">
                                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'pemilik'): ?>
                                    <li><a class="dropdown-item py-2 fw-semibold" href="<?= BASE_URL ?>/admin/dashboard.php">
                                        <i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard
                                    </a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item py-2 fw-semibold" href="<?= BASE_URL ?>/reservasi.php">
                                        <i class="bi bi-journal-check me-2 text-primary"></i>Reservasi Saya
                                    </a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item py-2 fw-semibold" href="<?= BASE_URL ?>/profil.php">
                                    <i class="bi bi-person-circle me-2 text-secondary"></i>Profil Saya
                                </a></li>
                                <li><hr class="dropdown-divider my-2"></li>
                                <li><a class="dropdown-item py-2 fw-bold text-danger" href="<?= BASE_URL ?>/auth/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Keluar
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a class="btn" href="<?= BASE_URL ?>/auth/login.php" style="font-weight: 700; font-size: 0.9rem; color: #64748b; padding: 8px 16px;">
                            Login
                        </a>
                        <a class="btn" href="<?= BASE_URL ?>/auth/register.php" style="font-weight: 700; font-size: 0.9rem; background: var(--kk-blue); color: #fff; padding: 8px 20px; border-radius: 50px; box-shadow: 0 4px 12px rgba(37,99,235,0.2);">
                            Daftar Gratis
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>
