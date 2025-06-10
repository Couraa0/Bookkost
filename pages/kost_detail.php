<?php
// pages/kost_detail.php
if (!isset($_GET['id'])) {
    header("Location: ?page=home");
    exit();
}

$kost_id = intval($_GET['id']);
$stmt = $db->prepare("SELECT * FROM kost WHERE id = :id AND status = 'active'");
$stmt->bindValue(':id', $kost_id);
$stmt->execute();
$kost = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kost) {
    ?>
    <div class="container py-5">
        <div class="alert alert-danger">Kost tidak ditemukan atau sudah tidak aktif.</div>
        <a href="?page=search" class="btn btn-primary">Kembali ke Pencarian</a>
    </div>
    <?php
    return;
}

$kost_image = !empty($kost['images']) ? htmlspecialchars($kost['images']) : 'https://via.placeholder.com/600x350?text=No+Image';

// Ambil review & rating
$review_stmt = $db->prepare("
    SELECT r.*, u.full_name 
    FROM reviews r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.kost_id = :kost_id
    ORDER BY r.created_at DESC
");
$review_stmt->execute([':kost_id' => $kost_id]);
$reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung rata-rata rating dan jumlah review
$rating_stmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_review FROM reviews WHERE kost_id = :kost_id");
$rating_stmt->execute([':kost_id' => $kost_id]);
$rating_data = $rating_stmt->fetch(PDO::FETCH_ASSOC);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 2) : null;
$total_review = $rating_data['total_review'];

// Handle booking request
if (isset($_POST['book_kost']) && isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $check_in = $_POST['check_in'];
    $duration = intval($_POST['duration']);
    $total_price = $kost['price'] * $duration;

    // Hitung check_out (otomatis, misal +$duration bulan dari check_in)
    $check_in_date = new DateTime($check_in);
    $check_out_date = clone $check_in_date;
    $check_out_date->modify('+' . $duration . ' months');
    $check_out = $check_out_date->format('Y-m-d');

    // Check if user already has pending booking for this kost
    $check_booking = $db->prepare("SELECT * FROM bookings WHERE user_id = :user_id AND kost_id = :kost_id AND status IN ('pending', 'confirmed', 'waiting_payment')");
    $check_booking->execute([':user_id' => $user_id, ':kost_id' => $kost_id]);

    if ($check_booking->rowCount() > 0) {
        $booking_error = "Anda sudah memiliki booking aktif untuk kost ini.";
    } else {
        // Booking status: waiting_payment
        $insert_booking = $db->prepare("INSERT INTO bookings (user_id, kost_id, check_in, check_out, total_price, status, created_at) VALUES (:user_id, :kost_id, :check_in, :check_out, :total_price, 'waiting_payment', NOW())");

        if ($insert_booking->execute([
            ':user_id' => $user_id,
            ':kost_id' => $kost_id,
            ':check_in' => $check_in,
            ':check_out' => $check_out,
            ':total_price' => $total_price
        ])) {
            $booking_id = $db->lastInsertId();
            // Redirect ke halaman pembayaran
            header("Location: ?page=payment&booking_id=" . $booking_id);
            exit();
        } else {
            $booking_error = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
}

// Cek status favorit user (jika login)
$is_favorited = false;
if (isLoggedIn() && isset($_SESSION['user_id'])) {
    $fav_stmt = $db->prepare("SELECT 1 FROM favorites WHERE user_id = :user_id AND kost_id = :kost_id");
    $fav_stmt->execute([':user_id' => $_SESSION['user_id'], ':kost_id' => $kost_id]);
    $is_favorited = $fav_stmt->fetchColumn() ? true : false;
}

// Handle aksi favorit
if (isLoggedIn() && isset($_POST['toggle_fav'])) {
    if ($is_favorited) {
        $del = $db->prepare("DELETE FROM favorites WHERE user_id = :user_id AND kost_id = :kost_id");
        $del->execute([':user_id' => $_SESSION['user_id'], ':kost_id' => $kost_id]);
        $is_favorited = false;
    } else {
        $ins = $db->prepare("INSERT INTO favorites (user_id, kost_id, created_at) VALUES (:user_id, :kost_id, NOW())");
        $ins->execute([':user_id' => $_SESSION['user_id'], ':kost_id' => $kost_id]);
        $is_favorited = true;
    }
    // Refresh untuk update status tombol
    echo "<script>location.href='?page=kost_detail&id=$kost_id';</script>";
    exit();
}

// Ambil data owner dari tabel users (berdasarkan owner_email atau owner_id jika ada)
$owner = null;
if (!empty($kost['owner_email'])) {
    $owner_stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND role = 'owner' LIMIT 1");
    $owner_stmt->execute([':email' => $kost['owner_email']]);
    $owner = $owner_stmt->fetch(PDO::FETCH_ASSOC);
} elseif (!empty($kost['owner_id'])) {
    $owner_stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND role = 'owner' LIMIT 1");
    $owner_stmt->execute([':id' => $kost['owner_id']]);
    $owner = $owner_stmt->fetch(PDO::FETCH_ASSOC);
}

// Cek apakah user bisa memberi review
$can_review = false;
$review_error = '';
$review_success = '';
if (isLoggedIn() && isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
    // Cek apakah user punya booking completed untuk kost ini
    $booking_stmt = $db->prepare("SELECT b.id FROM bookings b
        LEFT JOIN reviews r ON r.booking_id = b.id AND r.user_id = :user_id
        WHERE b.user_id = :user_id AND b.kost_id = :kost_id AND b.status = 'completed' AND r.id IS NULL
        LIMIT 1");
    $booking_stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':kost_id' => $kost_id
    ]);
    $booking_for_review = $booking_stmt->fetch(PDO::FETCH_ASSOC);
    if ($booking_for_review) {
        $can_review = true;
    }
    // Handle submit review
    if (isset($_POST['submit_review']) && $can_review) {
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);
        if ($rating < 1 || $rating > 5) {
            $review_error = "Rating harus antara 1 sampai 5.";
        } elseif (strlen($comment) < 5) {
            $review_error = "Komentar minimal 5 karakter.";
        } else {
            $ins_review = $db->prepare("INSERT INTO reviews (booking_id, user_id, kost_id, rating, comment, created_at) VALUES (:booking_id, :user_id, :kost_id, :rating, :comment, NOW())");
            if ($ins_review->execute([
                ':booking_id' => $booking_for_review['id'],
                ':user_id' => $_SESSION['user_id'],
                ':kost_id' => $kost_id,
                ':rating' => $rating,
                ':comment' => $comment
            ])) {
                // Tampilkan popup JS dan redirect ke home
                echo "<script>
                    alert('Terima kasih! Review Anda sudah terkirim.');
                    window.location.href='?page=home';
                </script>";
                exit();
            } else {
                $review_error = "Gagal menyimpan ulasan. Silakan coba lagi.";
            }
        }
    }
}

// Handle report submission
$report_success = '';
$report_error = '';
if (isset($_POST['report_listing'])) {
    $report_reason = trim($_POST['report_reason']);
    $report_detail = trim($_POST['report_detail']);
    $report_user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
    if (strlen($report_reason) < 5) {
        $report_error = "Alasan laporan harus diisi minimal 5 karakter.";
    } else {
        $ins_report = $db->prepare("INSERT INTO kost_reports (kost_id, user_id, reason, detail, created_at) VALUES (:kost_id, :user_id, :reason, :detail, NOW())");
        if ($ins_report->execute([
            ':kost_id' => $kost_id,
            ':user_id' => $report_user_id,
            ':reason' => $report_reason,
            ':detail' => $report_detail
        ])) {
            $report_success = "Laporan Anda telah dikirim. Tim kami akan meninjau Kost ini.";
        } else {
            $report_error = "Gagal mengirim laporan. Silakan coba lagi.";
        }
    }
}

// Tambahkan field virtual_tour (URL YouTube/iframe) jika ada di database
$virtual_tour = isset($kost['virtual_tour']) ? trim($kost['virtual_tour']) : '';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Main Image -->
            <div class="mb-4">
                <img src="<?php echo $kost_image; ?>" class="img-fluid rounded" alt="Gambar Kost" style="width:100%;max-height:400px;object-fit:cover;">
            </div>

            <!-- Virtual Tour -->
            <?php if (!empty($virtual_tour)): ?>
            <div class="mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-vr-cardboard"></i> Virtual Tour Kamar Kost</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        // Jika virtual_tour berupa link YouTube, embed otomatis
                        if (preg_match('/youtu\.be\/([^\?&]+)/', $virtual_tour, $yt1) || preg_match('/youtube\.com.*v=([^&]+)/', $virtual_tour, $yt2)) {
                            $yt_id = isset($yt1[1]) ? $yt1[1] : $yt2[1];
                            echo '<div class="ratio ratio-16x9"><iframe src="https://www.youtube.com/embed/' . htmlspecialchars($yt_id) . '" frameborder="0" allowfullscreen></iframe></div>';
                        } elseif (stripos($virtual_tour, '<iframe') !== false) {
                            // Jika sudah iframe, tampilkan langsung
                            echo $virtual_tour;
                        } else {
                            // Embed link lain (misal 3D tour, Google Street View, dll)
                            echo '<div class="ratio ratio-16x9"><iframe src="' . htmlspecialchars($virtual_tour) . '" frameborder="0" allowfullscreen></iframe></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Kost Information -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="card-title"><?php echo htmlspecialchars($kost['name']); ?></h2>
                            <p class="text-muted mb-3">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($kost['address']); ?>
                            </p>
                        </div>
                        <!-- Tombol Favorit -->
                        <?php if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                        <div class="text-end">
                            <form method="POST" class="ms-2 mb-2">
                                <button type="submit" name="toggle_fav" class="btn btn-<?php echo $is_favorited ? 'danger' : 'outline-danger'; ?> btn-sm" title="<?php echo $is_favorited ? 'Hapus dari Favorit' : 'Tambah ke Favorit'; ?>">
                                    <?php if ($is_favorited): ?>
                                        <i class="fas fa-heart"></i> Favorit
                                    <?php else: ?>
                                        <i class="far fa-heart"></i> Tambah Favorit
                                    <?php endif; ?>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <!-- Tambahkan tombol ketersediaan kamar -->
                    <div class="mb-3">
                        <button class="btn btn-success btn-sm" disabled>
                            <i class="fas fa-door-open"></i> Kamar Tersedia
                        </button>
                    </div>
                    
                    <!-- Tambahkan rating dan jumlah review -->
                    <div class="mb-3">
                        <?php if ($avg_rating): ?>
                            <span class="badge bg-warning text-dark" style="font-size:1.1rem;">
                                <?php echo $avg_rating; ?>/5 <i class="fas fa-star text-dark"></i>
                            </span>
                            <span class="text-muted ms-2">(<?php echo $total_review; ?> ulasan)</span>
                        <?php else: ?>
                            <span class="text-muted">Belum ada ulasan</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h4 class="text-primary">Rp <?php echo number_format($kost['price'], 0, ',', '.'); ?>/bulan</h4>
                        </div>
                        <!-- <div class="col-md-6 text-md-end">
                            <span class="badge bg-success">Tersedia</span>
                        </div> -->
                    </div>
                    
                    <h5>Deskripsi</h5>
                    <p><?php echo nl2br(htmlspecialchars($kost['description'])); ?></p>
                    
                    <h5>Fasilitas</h5>
                    <div class="row">
                        <?php 
                        $facilities = explode(',', $kost['facilities']);
                        foreach ($facilities as $facility): 
                            $facility = trim($facility);
                            if ($facility):
                        ?>
                        <div class="col-md-6 mb-2">
                            <i class="fas fa-check-circle text-success"></i>
                            <?php echo htmlspecialchars($facility); ?>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>

            <!-- Daftar Review -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-comments"></i> Ulasan Penghuni</h5>
                </div>
                <div class="card-body">
                    <?php if ($total_review > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="mb-4 border-bottom pb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <strong><?php echo htmlspecialchars($review['full_name'] ?? 'Pengguna'); ?></strong>
                                    <span class="ms-2 text-warning">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </span>
                                    <span class="ms-2 text-muted" style="font-size:0.95em;">
                                        <?php echo date('d M Y', strtotime($review['created_at'])); ?>
                                    </span>
                                </div>
                                <div>
                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">Belum ada ulasan untuk kost ini.</div>
                    <?php endif; ?>

                    <!-- Form Review -->
                    <?php if ($can_review): ?>
                        <div class="mt-4">
                            <h6 class="fw-bold mb-3">Tulis Ulasan Anda</h6>
                            <?php if ($review_error): ?>
                                <div class="alert alert-danger py-2"><?php echo $review_error; ?></div>
                            <?php endif; ?>
                            <?php if ($review_success): ?>
                                <div class="alert alert-success py-2"><?php echo $review_success; ?></div>
                            <?php endif; ?>
                            <form method="POST" class="mb-0">
                                <div class="mb-2">
                                    <label class="form-label">Rating</label>
                                    <select name="rating" class="form-select w-auto d-inline-block" required>
                                        <option value="">Pilih rating</option>
                                        <?php for ($i=5; $i>=1; $i--): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?> - <?php echo str_repeat('★', $i); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Komentar</label>
                                    <textarea name="comment" class="form-control" rows="3" required minlength="5" placeholder="Tulis ulasan Anda..."></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn btn-warning">
                                    <i class="fas fa-star"></i> Kirim Ulasan
                                </button>
                            </form>
                        </div>
                    <?php elseif (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                        <!-- Sudah review atau tidak eligible -->
                        <div class="mt-4 text-muted" style="font-size:0.95em;">
                            <?php
                            // Cek apakah user sudah pernah review untuk kost ini
                            $cek_review = $db->prepare("SELECT 1 FROM reviews WHERE user_id = :user_id AND kost_id = :kost_id");
                            $cek_review->execute([':user_id' => $_SESSION['user_id'], ':kost_id' => $kost_id]);
                            if ($cek_review->fetchColumn()) {
                                echo "Anda sudah memberikan ulasan untuk kost ini.";
                            } else {
                                echo "Anda hanya dapat memberikan ulasan setelah booking Anda selesai.";
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Laporan Listing Palsu/Menyesatkan -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-flag"></i> Laporkan Kost Ini</h5>
                </div>
                <div class="card-body">
                    <?php if ($report_success): ?>
                        <div class="alert alert-success"><?php echo $report_success; ?></div>
                    <?php endif; ?>
                    <?php if ($report_error): ?>
                        <div class="alert alert-danger"><?php echo $report_error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Alasan Laporan</label>
                            <select name="report_reason" class="form-select" required>
                                <option value="">Pilih alasan</option>
                                <option value="Listing palsu">Listing palsu</option>
                                <option value="Informasi menyesatkan">Informasi menyesatkan</option>
                                <option value="Foto tidak sesuai">Foto tidak sesuai</option>
                                <option value="Penipuan">Penipuan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Detail Laporan (opsional)</label>
                            <textarea name="report_detail" class="form-control" rows="2" placeholder="Jelaskan masalah pada listing ini"></textarea>
                        </div>
                        <button type="submit" name="report_listing" class="btn btn-danger">
                            <i class="fas fa-flag"></i> Laporkan Kost
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Booking Form + Contact Information (merged) -->
            <div class="card sticky-top bg-white" style="top: 90px; z-index: 0;">
                <div class="card-header bg-primary text-white" id="booking">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check"></i> Booking Kost
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($booking_success)): ?>
                        <div class="alert alert-success"><?php echo $booking_success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($booking_error)): ?>
                        <div class="alert alert-danger"><?php echo $booking_error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Masuk</label>
                                <input type="date" name="check_in" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Durasi (Bulan)</label>
                                <select name="duration" class="form-select" required id="duration">
                                    <option value="">Pilih Durasi</option>
                                    <option value="1">1 Bulan</option>
                                    <option value="3">3 Bulan</option>
                                    <option value="6">6 Bulan</option>
                                    <option value="12">12 Bulan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Harga</label>
                                <div class="form-control bg-light" id="total-price">Pilih durasi terlebih dahulu</div>
                            </div>
                            <button type="submit" name="book_kost" class="btn btn-primary w-100">
                                <i class="fas fa-calendar-check"></i> Booking Sekarang
                            </button>
                        </form>
                        <script>
                        document.getElementById('duration').addEventListener('change', function() {
                            const duration = parseInt(this.value);
                            const price = <?php echo $kost['price']; ?>;
                            if (duration) {
                                const total = price * duration;
                                document.getElementById('total-price').textContent = 'Rp ' + total.toLocaleString('id-ID');
                            } else {
                                document.getElementById('total-price').textContent = 'Pilih durasi terlebih dahulu';
                            }
                        });
                        </script>
                    <?php elseif (isLoggedIn()): ?>
                        <div class="alert alert-warning text-center mb-3">Hanya user yang dapat melakukan booking kost.</div>
                    <?php else: ?>
                        <p class="text-center mb-3">Silakan login untuk melakukan booking</p>
                        <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-sign-in-alt"></i> Login untuk Booking
                        </button>
                    <?php endif; ?>

                    <hr class="my-4">

                    <div>
                        <h6 class="mb-3"><i class="fas fa-phone"></i> Informasi Kontak</h6>
                        <p class="mb-2">
                            <strong>Pemilik Kost:</strong><br>
                            <?php
                            if ($owner) {
                                echo htmlspecialchars($owner['full_name']);
                            } else {
                                echo htmlspecialchars($kost['owner_name'] ?? 'Tidak tersedia');
                            }
                            ?>
                        </p>
                        <p class="mb-2">
                            <strong>Telepon:</strong><br>
                            <?php
                            if ($owner) {
                                echo htmlspecialchars($owner['phone'] ?? 'Tidak tersedia');
                            } else {
                                echo htmlspecialchars($kost['owner_phone'] ?? 'Tidak tersedia');
                            }
                            ?>
                        </p>
                        <p class="mb-0">
                            <strong>Email:</strong><br>
                            <?php
                            if ($owner) {
                                echo htmlspecialchars($owner['email']);
                            } else {
                                echo htmlspecialchars($kost['owner_email'] ?? 'Tidak tersedia');
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="mt-4">
        <a href="?page=search" class="btn" style="background: linear-gradient(135deg, #CD853F, #8B4513); color: #fff; font-weight:bold; border:none;">
            <i class="fas fa-arrow-left"></i> Kembali ke Pencarian
        </a>
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