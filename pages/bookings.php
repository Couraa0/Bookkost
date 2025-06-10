<?php
// pages/bookings.php
if (!isLoggedIn()) {
    header("Location: ?page=home");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle booking cancellation
if (isset($_POST['cancel_booking'])) {
    $booking_id = intval($_POST['booking_id']);
    $cancel_stmt = $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = :booking_id AND user_id = :user_id AND status = 'pending'");
    if ($cancel_stmt->execute([':booking_id' => $booking_id, ':user_id' => $user_id])) {
        $message = "Booking berhasil dibatalkan.";
        $message_type = "success";
    } else {
        $message = "Gagal membatalkan booking.";
        $message_type = "danger";
    }
}

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "
    SELECT b.*, k.name as kost_name, k.address as kost_address, k.price as kost_price, k.images as kost_image 
    FROM bookings b 
    JOIN kost k ON b.kost_id = k.id 
    WHERE b.user_id = :user_id
";

$params = [':user_id' => $user_id];

if ($status_filter) {
    $query .= " AND b.status = :status";
    $params[':status'] = $status_filter;
}

$query .= " ORDER BY b.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold mb-1" style="letter-spacing:1px;">
                <i class="fas fa-calendar-check me-2" style="color:#8B4513;"></i>Booking Saya
            </h2>
            <div class="text-muted mb-2" style="font-size:1.1rem;">
                Daftar semua booking kost yang pernah Anda lakukan.
            </div>
        </div>
        <!-- HAPUS BUTTON BOOKING BARU DARI SINI -->
        <!-- <a href="?page=search" class="btn btn-primary btn-lg shadow-sm">
            <i class="fas fa-plus"></i> Booking Baru
        </a> -->
    </div>

    <!-- Messages -->
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="card mb-4 border-0 shadow-sm rounded-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="bookings">
                <div class="col-md-4 col-8">
                    <label class="form-label fw-semibold">Filter Status</label>
                    <select name="status" class="form-select rounded-pill">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Menunggu Konfirmasi</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2 col-4">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Filter</button>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="?page=search" class="btn btn-primary btn-lg shadow-sm rounded-pill">
                        <i class="fas fa-plus"></i> Booking Baru
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings List -->
    <?php if ($stmt->rowCount() > 0): ?>
        <div class="row g-4">
            <?php while ($booking = $stmt->fetch(PDO::FETCH_ASSOC)):
                $kost_image = !empty($booking['kost_image']) ? htmlspecialchars($booking['kost_image']) : 'https://via.placeholder.com/300x200?text=No+Image';
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow rounded-4 overflow-hidden">
                    <div style="height:200px;overflow:hidden;">
                        <img src="<?php echo $kost_image; ?>" class="card-img-top" alt="Kost Image" style="height:100%;object-fit:cover;">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($booking['kost_name']); ?></h5>
                        <p class="card-text text-muted small mb-2">
                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                            <?php echo htmlspecialchars($booking['kost_address']); ?>
                        </p>
                        <div class="mb-2">
                            <span class="badge bg-light text-dark border border-1 rounded-pill px-3 py-2">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php
                                if (!empty($booking['check_in'])) {
                                    echo date('d/m/Y', strtotime($booking['check_in']));
                                } elseif (!empty($booking['check_in_date'])) {
                                    echo date('d/m/Y', strtotime($booking['check_in_date']));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </span>
                            <span class="badge bg-light text-dark border border-1 rounded-pill px-3 py-2 ms-2">
                                <i class="fas fa-clock me-1"></i>
                                <?php
                                if (!empty($booking['duration_months'])) {
                                    echo intval($booking['duration_months']) . ' bulan';
                                } elseif (!empty($booking['check_in']) && !empty($booking['check_out'])) {
                                    $start = new DateTime($booking['check_in']);
                                    $end = new DateTime($booking['check_out']);
                                    $diff = $start->diff($end);
                                    $months = ($diff->y * 12) + $diff->m;
                                    echo $months > 0 ? $months . ' bulan' : '-';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="mb-2">
                            <span class="fw-bold text-primary" style="font-size:1.1rem;">
                                Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?>
                            </span>
                        </div>
                        <div class="mb-3">
                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                <?php 
                                switch($booking['status']) {
                                    case 'pending': echo 'Menunggu Konfirmasi'; break;
                                    case 'confirmed': echo 'Dikonfirmasi'; break;
                                    case 'cancelled': echo 'Dibatalkan'; break;
                                    case 'completed': echo 'Selesai'; break;
                                    default: echo ucfirst($booking['status']);
                                }
                                ?>
                            </span>
                        </div>
                        <div class="mt-auto">
                            <div class="d-flex gap-2">
                                <a href="?page=kost_detail&id=<?php echo $booking['kost_id']; ?>" class="btn btn-outline-primary btn-sm flex-fill rounded-pill">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <?php if ($booking['status'] === 'pending'): ?>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin membatalkan booking ini?')">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="cancel_booking" class="btn btn-outline-danger btn-sm rounded-pill">
                                        <i class="fas fa-times"></i> Batal
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if ($booking['status'] === 'completed'): ?>
                                <a href="?page=kost_detail&id=<?php echo $booking['kost_id']; ?>#review" class="btn btn-warning btn-sm flex-fill rounded-pill">
                                    <i class="fas fa-star"></i> Beri Ulasan
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-muted small bg-white border-0">
                        <i class="fas fa-clock me-1"></i>
                        Dibuat: <?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                <h4 class="fw-bold">Tidak Ada Booking</h4>
                <p class="text-muted">
                    <?php if ($status_filter): ?>
                        Tidak ada booking dengan status "<?php echo ucfirst($status_filter); ?>".
                    <?php else: ?>
                        Anda belum melakukan booking kost apapun.
                    <?php endif; ?>
                </p>
                <div class="mt-3">
                    <?php if ($status_filter): ?>
                        <a href="?page=bookings" class="btn btn-secondary me-2 rounded-pill">Lihat Semua Booking</a>
                    <?php endif; ?>
                    <a href="?page=search" class="btn btn-primary rounded-pill">Cari Kost Sekarang</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Jika ada sidebar/nav, tambahkan: -->
<!--
<a class="nav-link rounded-pill px-3 py-2 fw-semibold" href="?page=favorit" style="color:rgb(255, 255, 255); transition: background 0.2s;">
    <i class="fas fa-heart me-2"></i> Favorit
</a>
-->

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