
<footer class="footer mt-auto py-5" style="background: linear-gradient(135deg, var(--kk-blue) 0%, var(--kk-blue-dark) 100%); color: #fff; border-top: none;">
    <div class="container">
        <div class="row gy-4">
            <div class="col-md-4">
                <a class="d-flex align-items-center gap-2 mb-3 text-decoration-none" href="<?= BASE_URL ?>/index.php">
                    <span class="brand-icon" style="background: rgba(255,255,255,0.2);"><i class="bi bi-house-heart-fill text-white" style="font-size:.85rem"></i></span>
                    <span class="text-white fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.1rem">KosKita</span>
                </a>
                <p class="text-white-50 small mb-0" style="line-height:1.75">
                    Platform pencarian dan reservasi kamar kos di Yogyakarta.
                    Temukan kos impianmu di dekat kampus dengan mudah.
                </p>
            </div>
            <div class="col-6 col-md-2">
                <p class="text-white fw-semibold mb-3" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em">Navigasi</p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="<?= BASE_URL ?>/index.php" class="text-white-50 text-decoration-none small">Beranda</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/index.php#kos-list" class="text-white-50 text-decoration-none small">Daftar Kos</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/auth/login.php" class="text-white-50 text-decoration-none small">Login</a></li>
                    <li><a href="<?= BASE_URL ?>/auth/register.php" class="text-white-50 text-decoration-none small">Daftar Akun</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-2">
                <p class="text-white fw-semibold mb-3" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em">Layanan</p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="<?= BASE_URL ?>/index.php#kos-list" class="text-white-50 text-decoration-none small">Cari Kos</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/reservasi.php" class="text-white-50 text-decoration-none small">Reservasi Online</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/index.php#kos-list" class="text-white-50 text-decoration-none small">Filter Lokasi</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php#kos-list" class="text-white-50 text-decoration-none small">Bandingkan Harga</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <p class="text-white fw-semibold mb-3" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em">Kontak</p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2 d-flex align-items-start gap-2">
                        <i class="bi bi-envelope text-white-50 mt-1" style="font-size:.85rem"></i>
                        <a href="mailto:PTkosKita@koskita.com" class="text-white-50 text-decoration-none small">PTkosKita@koskita.com</a>
                    </li>
                    <li class="mb-2 d-flex align-items-start gap-2">
                        <i class="bi bi-telephone text-white-50 mt-1" style="font-size:.85rem"></i>
                        <span class="text-white-50 small">+62 812-3456-7890</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt text-white-50 mt-1" style="font-size:.85rem"></i>
                        <span class="text-white-50 small">Yogyakarta, Daerah Istimewa Yogyakarta</span>
                    </li>
                </ul>
            </div>
        </div>
        <hr style="border-color:rgba(255,255,255,.08);margin:2rem 0 1.25rem">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <p class="text-white-50 small mb-0">
                &copy; <?= date('Y') ?> KosKita UAS Pemrograman Web
            </p>
            <p class="text-white-50 small mb-0">
                Dibuat dengan <i class="bi bi-heart-fill text-danger"></i> di Yogyakarta
            </p>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
