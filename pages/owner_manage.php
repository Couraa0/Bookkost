<?php
// pages/owner_manage.php
if (!isLoggedIn() || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: ?page=home");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil semua kost milik owner ini
$kosts_stmt = $db->prepare("SELECT id, name FROM kost WHERE owner_id = :owner_id");
$kosts_stmt->execute([':owner_id' => $user_id]);
$kosts = $kosts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua booking untuk kost milik owner ini
$owner_kost_ids = array_column($kosts, 'id');
$owner_kost_ids_in = implode(',', array_map('intval', $owner_kost_ids));
$bookings = [];
if ($owner_kost_ids_in) {
    $bookings_stmt = $db->query("
        SELECT b.*, u.full_name, u.username, u.email, u.phone, k.name as kost_name
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN kost k ON b.kost_id = k.id
        WHERE b.kost_id IN ($owner_kost_ids_in)
        ORDER BY b.created_at DESC
    ");
    $bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Ambil semua user yang pernah booking di kost owner ini (penghuni)
$penghuni = [];
if ($owner_kost_ids_in) {
    $penghuni_stmt = $db->query("
        SELECT DISTINCT u.*
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.kost_id IN ($owner_kost_ids_in)
    ");
    $penghuni = $penghuni_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle konfirmasi booking
if (isset($_POST['confirm_booking']) && isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    $update = $db->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = :id");
    $update->execute([':id' => $booking_id]);
    header("Location: ?page=owner_manage");
    exit();
}
if (isset($_POST['cancel_booking']) && isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    $update = $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = :id");
    $update->execute([':id' => $booking_id]);
    header("Location: ?page=owner_manage");
    exit();
}
?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="sidebar p-4 rounded-4 shadow" style="background: linear-gradient(135deg,#CD853F 60%, #D2B48C 100%); min-height: 600px; border-radius: 24px; border: 1px solid #e0c9a6; position: sticky; top: 24px; z-index: 10;">
                <div class="text-center mb-4">
                    <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 90px; height: 90px; border: 4px solid #cd853f;">
                        <i class="fas fa-user fa-3x" style="color:#cd853f;"></i>
                    </div>
                    <h5 class="mt-3 fw-bold" style="color:white;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h5>
                    <small class="text-white">
                        <?php
                        // Ambil email user dari database jika belum ada di session
                        if (!empty($_SESSION['email'])) {
                            echo htmlspecialchars($_SESSION['email']);
                        } else {
                            $user_email = '';
                            if (isset($db) && isset($_SESSION['user_id'])) {
                                $stmt_email = $db->prepare("SELECT email FROM users WHERE id = :id LIMIT 1");
                                $stmt_email->execute([':id' => $_SESSION['user_id']]);
                                $row_email = $stmt_email->fetch(PDO::FETCH_ASSOC);
                                if ($row_email && isset($row_email['email'])) {
                                    $user_email = $row_email['email'];
                                    // Simpan ke session agar tidak query ulang
                                    $_SESSION['email'] = $user_email;
                                }
                            }
                            echo htmlspecialchars($user_email);
                        }
                        ?>
                    </small>
                </div>
                <nav class="nav flex-column gap-2">
                    <a class="nav-link rounded-pill px-3 py-2 fw-semibold" href="?page=dashboard" style="color:rgb(255, 255, 255); transition: background 0.2s;">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a class="nav-link active rounded-pill px-3 py-2 fw-semibold" href="?page=owner_manage" style="background: #cd853f; color: #fff; transition: background 0.2s;">
                        <i class="fas fa-calendar-check me-2"></i> Kelola Kost Anda
                    </a>
                </nav>
                <style>
                    .sidebar .nav-link:not(.active):hover {
                        background: #ffe4c4;
                        color: #cd853f !important;
                        box-shadow: 0 2px 8px rgba(205,133,63,0.07);
                    }
                    .sidebar .nav-link.active {
                        box-shadow: 0 2px 8px rgba(205,133,63,0.13);
                    }
                </style>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card mb-4" id="kelola-booking">
                <div class="card-header">
                    <h5 class="mb-0">Kelola Booking Kost Anda</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($bookings)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kost</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Telepon</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Tanggal Keluar</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($b['kost_name']); ?></td>
                                        <td><?php echo htmlspecialchars($b['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($b['email']); ?></td>
                                        <td><?php echo htmlspecialchars($b['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($b['check_in']); ?></td>
                                        <td><?php echo htmlspecialchars($b['check_out']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $b['status']; ?>">
                                                <?php echo ucfirst($b['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($b['status'] === 'pending'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                    <button type="submit" name="confirm_booking" class="btn btn-success btn-sm" onclick="return confirm('Konfirmasi booking ini?')">
                                                        <i class="fas fa-check"></i> Konfirmasi
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                    <button type="submit" name="cancel_booking" class="btn btn-danger btn-sm" onclick="return confirm('Batalkan booking ini?')">
                                                        <i class="fas fa-times"></i> Batalkan
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5>Belum Ada Booking Untuk Kost Anda</h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card mb-4" id="data-penghuni">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Review Kost Anda</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Ambil semua review untuk kost milik owner
                    $reviews = [];
                    if (!empty($owner_kost_ids_in)) {
                        $review_stmt = $db->query("
                            SELECT r.*, k.name as kost_name, u.full_name as reviewer_name
                            FROM reviews r
                            JOIN kost k ON r.kost_id = k.id
                            JOIN users u ON r.user_id = u.id
                            WHERE r.kost_id IN ($owner_kost_ids_in)
                            ORDER BY r.created_at DESC
                        ");
                        $reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                    ?>
                    <?php if (!empty($reviews)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Kost</th>
                                        <th>Nama Lengkap</th>
                                        <th>Rating</th>
                                        <th>Komentar</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reviews as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['kost_name']); ?></td>
                                        <td><?php echo htmlspecialchars($r['reviewer_name']); ?></td>
                                        <td>
                                            <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($r['rating']); ?> / 5</span>
                                        </td>
                                        <td><?php echo htmlspecialchars($r['comment']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($r['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <h5>Belum Ada Review Untuk Kost Anda</h5>
                        </div>
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
