<?php
// Pastikan user sudah login
if (!isLoggedIn() || !isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil daftar favorit user
$fav_stmt = $db->prepare("
    SELECT k.* FROM favorites f
    JOIN kost k ON f.kost_id = k.id
    WHERE f.user_id = :user_id
    ORDER BY f.created_at DESC
");
$fav_stmt->execute([':user_id' => $user_id]);
$favorites = $fav_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-5">
    <h2 class="mb-4">Kost Favorit Saya</h2>
    <div class="row">
        <?php if ($favorites): ?>
            <?php foreach ($favorites as $kost):
                $kost_image = !empty($kost['images']) ? htmlspecialchars($kost['images']) : 'https://via.placeholder.com/400x250?text=No+Image';
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="<?php echo $kost_image; ?>" class="card-img-top" alt="Gambar Kost" style="height:200px;object-fit:cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($kost['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars(substr($kost['description'], 0, 100)); ?>...</p>
                        <a href="?page=kost_detail&id=<?php echo $kost['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                        <!-- Tombol booking jika user login -->
                        <?php if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                        <a href="?page=kost_detail&id=<?php echo $kost['id']; ?>#booking" class="btn btn-success btn-sm ms-1">
                            <i class="fas fa-calendar-check"></i> Booking
                        </a>
                        <?php endif; ?>
                        <!-- Tombol favorit/unfavorit -->
                        <form method="POST" action="?page=favorit" class="d-inline">
                            <input type="hidden" name="remove_fav" value="<?php echo $kost['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus dari favorit?')">
                                <i class="fas fa-heart-broken"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="fas fa-heart-broken fa-3x mb-3"></i>
                <h5>Belum ada kost favorit.</h5>
                <p>Tambahkan kost ke favorit untuk memudahkan pencarianmu.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Handle hapus favorit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_fav'])) {
    $kost_id = intval($_POST['remove_fav']);
    $del_stmt = $db->prepare("DELETE FROM favorites WHERE user_id = :user_id AND kost_id = :kost_id");
    $del_stmt->execute([':user_id' => $user_id, ':kost_id' => $kost_id]);
    echo "<script>location.href='?page=favorit';</script>";
    exit();
}
?>

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