<?php
if (!isset($_GET['booking_id']) || !isLoggedIn()) {
    header("Location: ?page=home");
    exit();
}

$booking_id = intval($_GET['booking_id']);
$stmt = $db->prepare("SELECT b.*, k.name AS kost_name, k.price AS kost_price, k.owner_id FROM bookings b JOIN kost k ON b.kost_id = k.id WHERE b.id = :id AND b.user_id = :user_id");
$stmt->execute([':id' => $booking_id, ':user_id' => $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil email owner jika diperlukan
$owner_email = null;
if ($booking && !empty($booking['owner_id'])) {
    $owner_stmt = $db->prepare("SELECT email FROM users WHERE id = :id AND role = 'owner' LIMIT 1");
    $owner_stmt->execute([':id' => $booking['owner_id']]);
    $owner_email = $owner_stmt->fetchColumn();
}

if (!$booking) {
    echo '<div class="container py-5"><div class="alert alert-danger">Data booking tidak ditemukan.</div></div>';
    return;
}

$payment_success = false;
if (isset($_POST['pay_now'])) {
    $payment_method = $_POST['payment_method'];
    // Update status booking ke pending dan payment_status ke paid (tanpa updated_at)
    $update = $db->prepare("UPDATE bookings SET status = 'pending', payment_method = :payment_method, payment_status = 'paid' WHERE id = :id AND user_id = :user_id");
    if ($update->execute([
        ':payment_method' => $payment_method,
        ':id' => $booking_id,
        ':user_id' => $_SESSION['user_id']
    ])) {
        $payment_success = true;
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-money-check-alt"></i> Pembayaran Booking Kost</h5>
                </div>
                <div class="card-body">
                    <?php if ($payment_success): ?>
                        <div class="alert alert-success">
                            Pembayaran berhasil! Notifikasi telah dikirim ke pemilik kost.<br>
                            Silakan tunggu konfirmasi dari pemilik.
                        </div>
                        <a href="?page=bookings" class="btn btn-primary">Lihat Booking Saya</a>
                    <?php else: ?>
                        <h6>Kost: <strong><?php echo htmlspecialchars($booking['kost_name']); ?></strong></h6>
                        <p>
                            Tanggal Masuk: <strong><?php echo htmlspecialchars($booking['check_in']); ?></strong><br>
                            Tanggal Keluar: <strong><?php echo htmlspecialchars($booking['check_out']); ?></strong><br>
                            Durasi: <strong>
                                <?php
                                $start = new DateTime($booking['check_in']);
                                $end = new DateTime($booking['check_out']);
                                $interval = $start->diff($end);
                                echo $interval->m + ($interval->y * 12);
                                ?>
                                bulan
                            </strong>
                        </p>
                        <div class="mb-3">
                            <h5>Total yang harus dibayar:</h5>
                            <div class="display-6 fw-bold text-success mb-2">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></div>
                        </div>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Metode Pembayaran</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="">Pilih Metode</option>
                                    <option value="bank_transfer">Transfer Bank</option>
                                    <option value="ewallet">E-Wallet (OVO, GoPay, DANA)</option>
                                    <option value="qris">QRIS</option>
                                    <option value="minimarket">Minimarket (Alfamart/Indomaret)</option>
                                </select>
                            </div>
                            <button type="submit" name="pay_now" class="btn btn-success w-100">
                                <i class="fas fa-money-bill-wave"></i> Bayar Sekarang
                            </button>
                        </form>
                        <a href="?page=kost_detail&id=<?php echo $booking['kost_id']; ?>" class="btn" style="background: #8B4513; color: #fff; font-weight:bold; border-radius:2rem; margin-top:1rem;">
                            <i class="fas fa-arrow-left"></i> Kembali ke Detail Kost
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="mt-5 pt-5 pb-4" style="background: linear-gradient(135deg, #CD853F, #8B4513); color: #fff;">
    <div class="container">
        <div class="row text-center text-md-start">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="fw-bold mb-3">BookKost</h5>
                <p>Platform terpercaya untuk mencari, membandingkan, dan memesan kost dengan mudah dan aman di seluruh Indonesia.</p>
                <div class="d-flex gap-3 justify-content-center justify-content-md-start mt-3">
                    <a href="https://www.facebook.com/profile.php?id=61555856880836" target="_blank" class="text-white fs-4" title="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="https://instagram.com/couraa0" target="_blank" class="text-white fs-4" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://twitter.com/Couraa0" target="_blank" class="text-white fs-4" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="https://wa.me/6287871310560" target="_blank" class="text-white fs-4" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <h6 class="fw-bold mb-3">Navigasi</h6>
                <ul class="list-unstyled">
                    <li><a href="?page=home" class="text-white text-decoration-none">Beranda</a></li>
                    <li><a href="?page=search" class="text-white text-decoration-none">Cari Kost</a></li>
                    <li><a href="?page=about" class="text-white text-decoration-none">Tentang Kami</a></li>
                    <li><a href="pages/privacy_policy.php" class="text-white text-decoration-none">Kebijakan & Privasi</a></li>
                    <li><a href="https://wa.me/6287871310560" class="text-white text-decoration-none">Kontak</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Kontak Kami</h6>
                <ul class="list-unstyled mb-2">
                    <li><i class="fas fa-envelope me-2"></i> <a href="mailto:info@BookKost.com" class="text-white text-decoration-none">info@BookKost.com</a></li>
                    <li><i class="fas fa-phone me-2"></i> <a href="tel:+6287871310560" class="text-white text-decoration-none">+62 878-7131-0560</a></li>
                    <li><i class="fas fa-map-marker-alt me-2"></i> Jl. Kost Impian No. 123, Jakarta</li>
                </ul>
                <small>Dukungan pelanggan 24/7 siap membantu Anda.</small>
            </div>
        </div>
        <hr class="my-4" style="border-color:rgba(255,255,255,0.2);">
        <div class="text-center">
            <small>
                <strong>BookKost</strong> &copy; <?php echo date('Y'); ?>. All rights reserved.<br>
                Dibuat dengan <i class="fas fa-heart text-light"></i> untuk kemudahan mencari kost.
            </small>
        </div>
    </div>
</footer>