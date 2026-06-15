<?php
require_once 'config/db.php';
$page_title = 'Halaman Tidak Ditemukan';
include 'includes/header.php';
?>
<style>
.err-page {
    background: #0d1117;
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    padding: 4rem 0;
}
.err-panel {
    background: #161b22;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 24px;
    padding: 4rem 2.5rem;
    text-align: center;
    max-width: 600px;
    width: 100%;
    box-shadow: 0 16px 48px rgba(0,0,0,.45);
    position: relative;
    overflow: hidden;
}
.err-panel::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--kk-blue) 0%, var(--kk-orange) 100%);
}
.eyes-container {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 2rem;
}
.eye {
    width: 120px;
    height: 120px;
    background: #ffffff;
    border-radius: 50%;
    position: relative;
    box-shadow: 0 8px 24px rgba(0,0,0,0.35), inset 0 -6px 16px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}
.pupil {
    width: 48px;
    height: 48px;
    background: #0f172a;
    border-radius: 50%;
    position: absolute;
    transition: transform 0.05s ease-out;
    border: 4px solid var(--kk-blue-light);
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}
.err-title {
    font-size: 6rem;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 0.5rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: linear-gradient(135deg, #fff 30%, rgba(255,255,255,0.6) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -0.04em;
}
.err-subtitle {
    font-size: 1.6rem;
    font-weight: 800;
    color: #e6edf3;
    margin-bottom: 1.25rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.err-text {
    font-size: 0.95rem;
    color: rgba(255,255,255,.6);
    line-height: 1.75;
    max-width: 460px;
    margin: 0 auto;
}
.btn-err-primary {
    background: linear-gradient(135deg, var(--kk-blue) 0%, var(--kk-blue-dark) 100%);
    color: #fff !important;
    border: none;
    border-radius: 50px;
    padding: 11px 28px;
    font-size: 0.9rem;
    font-weight: 700;
    transition: all 0.22s ease;
    box-shadow: var(--kk-shadow-blue);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-err-primary:hover {
    background: linear-gradient(135deg, var(--kk-blue-light) 0%, var(--kk-blue) 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(37,99,235,.45);
}
.btn-err-secondary {
    background: transparent;
    color: rgba(255,255,255,0.7) !important;
    border: 1.5px solid rgba(255,255,255,0.15);
    border-radius: 50px;
    padding: 10px 28px;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.22s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-err-secondary:hover {
    border-color: rgba(255,255,255,0.35);
    color: #fff !important;
    transform: translateY(-2px);
}
@media (max-width: 576px) {
    .err-panel {
        padding: 3rem 1.5rem;
    }
    .eye {
        width: 90px;
        height: 90px;
    }
    .pupil {
        width: 36px;
        height: 36px;
    }
    .err-title {
        font-size: 4.5rem;
    }
    .err-subtitle {
        font-size: 1.35rem;
    }
}
</style>
<div class="err-page">
    <div class="container d-flex justify-content-center">
        <div class="err-panel">
            <div class="eyes-container">
                <div class="eye">
                    <div class="pupil"></div>
                </div>
                <div class="eye">
                    <div class="pupil"></div>
                </div>
            </div>
            <h1 class="err-title">404</h1>
            <h2 class="err-subtitle">Halaman Tidak Ditemukan</h2>
            <p class="err-text">
                Waduh! Sepertinya kamu tersesat di lorong kos yang salah. Halaman yang kamu cari tidak dapat ditemukan atau sudah dipindahkan.
            </p>
            <div class="err-actions mt-4 d-flex justify-content-center gap-3 flex-wrap">
                <a href="<?= BASE_URL ?>/index.php" class="btn-err-primary">
                    <i class="bi bi-house-door-fill"></i>Kembali ke Beranda
                </a>
                <a href="<?= BASE_URL ?>/index.php#kos-list" class="btn-err-secondary">
                    <i class="bi bi-search"></i>Cari Kos Lainnya
                </a>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('mousemove', (e) => {
    const eyes = document.querySelectorAll('.eye');
    eyes.forEach(eye => {
        const pupil = eye.querySelector('.pupil');
        const rect = eye.getBoundingClientRect();
        const eyeX = rect.left + rect.width / 2;
        const eyeY = rect.top + rect.height / 2;
        const angle = Math.atan2(e.clientY - eyeY, e.clientX - eyeX);
        const pupilRect = pupil.getBoundingClientRect();
        const maxDistance = (rect.width - pupilRect.width) / 2 - 4;
        const mouseDistance = Math.hypot(e.clientX - eyeX, e.clientY - eyeY);
        const distance = Math.min(maxDistance, mouseDistance / 15);
        const targetX = Math.cos(angle) * distance;
        const targetY = Math.sin(angle) * distance;
        pupil.style.transform = `translate(${targetX}px, ${targetY}px)`;
    });
});
</script>
<?php include 'includes/footer.php'; ?>
