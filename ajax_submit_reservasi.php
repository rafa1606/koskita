<?php
require_once 'config/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
    exit;
}
$id_user         = (int)$_SESSION['user_id'];
$post_id_kamar   = isset($_POST['id_kamar']) ? (int)$_POST['id_kamar'] : 0;
$tanggal_masuk   = isset($_POST['tanggal_masuk']) ? trim($_POST['tanggal_masuk']) : '';
$durasi          = isset($_POST['durasi']) ? (int)$_POST['durasi'] : 0;
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penyewa') {
    echo json_encode(['success' => false, 'message' => 'Hanya akun penyewa yang dapat melakukan booking.']);
    exit;
}
if ($post_id_kamar <= 0) {
    echo json_encode(['success' => false, 'message' => 'Kamar tidak valid.']);
    exit;
}
$hari_ini = date('Y-m-d');
if (empty($tanggal_masuk) || $tanggal_masuk < $hari_ini) {
    echo json_encode(['success' => false, 'message' => 'Tanggal masuk tidak boleh sebelum hari ini.']);
    exit;
}
if ($durasi < 1) {
    echo json_encode(['success' => false, 'message' => 'Durasi sewa minimal 1 bulan.']);
    exit;
}
$cek = mysqli_prepare($conn, "
    SELECT km.harga, km.status, km.id_kos
    FROM kamar km
    WHERE km.id = ? LIMIT 1
");
mysqli_stmt_bind_param($cek, 'i', $post_id_kamar);
mysqli_stmt_execute($cek);
$data_kamar = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
if (!$data_kamar || $data_kamar['status'] !== 'tersedia') {
    echo json_encode(['success' => false, 'message' => 'Kamar sudah tidak tersedia atau penuh.']);
    exit;
}
$cek_dup = mysqli_prepare($conn,
    "SELECT id FROM reservasi
     WHERE id_user = ? AND id_kamar = ? AND status IN ('pending','diterima')
     LIMIT 1");
mysqli_stmt_bind_param($cek_dup, 'ii', $id_user, $post_id_kamar);
mysqli_stmt_execute($cek_dup);
$dup = mysqli_fetch_assoc(mysqli_stmt_get_result($cek_dup));
if ($dup) {
    echo json_encode(['success' => false, 'message' => 'Anda sudah memiliki reservasi aktif (pending/diterima) untuk kamar ini.']);
    exit;
}
$ins = mysqli_prepare($conn, "
    INSERT INTO reservasi (id_user, id_kamar, tanggal_masuk, durasi, status)
    VALUES (?, ?, ?, ?, 'pending')
");
mysqli_stmt_bind_param($ins, 'iisi', $id_user, $post_id_kamar, $tanggal_masuk, $durasi);
if (mysqli_stmt_execute($ins)) {
    echo json_encode(['success' => true, 'message' => 'Reservasi berhasil diajukan! Silakan tunggu konfirmasi admin.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan reservasi. Silakan coba lagi.']);
}
exit;
