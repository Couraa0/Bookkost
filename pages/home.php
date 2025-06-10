<?php
// pages/home.php
?>
<!-- Hero Section -->
<section class="hero-section" style="background: linear-gradient(135deg, #CD853F 60%, #8B4513 100%); position:relative; overflow:hidden;">
    <div class="container position-relative" style="z-index:2;">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.15);">
                    <span style="color:#fff;">Temukan Kost Impian Anda</span>
                </h1>
                <p class="lead mb-4" style="color:#ffe;">Platform terpercaya untuk mencari dan memesan kost dengan mudah dan aman.<br>
                <span style="font-size:1.1rem;color:#ffe;">Dapatkan promo menarik & review penghuni asli!</span></p>
                <a href="?page=search" class="btn btn-light btn-lg shadow" style="font-weight:bold;">
                    <i class="fas fa-search"></i> Mulai Cari Kostmu!
                </a>
                <div class="mt-4 d-flex gap-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-warning me-2"></i>
                        <span style="color:#fff;">Kost Terverifikasi</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-star text-info me-2"></i>
                        <span style="color:#fff;">Rating & Ulasan Asli</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="position-relative" style="min-height:320px;">
                    <img src="img/Logo.png" alt="KostBook Hero" class="img-fluid rounded shadow-lg" style="max-width:90%; position:absolute; bottom:0; right:0;">
                    <div style="position:absolute;top:10%;left:-30px;width:120px;height:120px;background:rgba(255,255,255,0.15);border-radius:50%;"></div>
                    <div style="position:absolute;bottom:10%;right:-40px;width:80px;height:80px;background:rgba(255,255,255,0.10);border-radius:50%;"></div>
                </div>
            </div>
        </div>
    </div>
    <svg style="position:absolute;bottom:0;left:0;width:100%;height:60px;" viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <path fill="#F5F5DC" fill-opacity="1" d="M0,32L80,37.3C160,43,320,53,480,53.3C640,53,800,43,960,37.3C1120,32,1280,32,1360,32L1440,32L1440,60L1360,60C1280,60,1120,60,960,60C800,60,640,60,480,60C320,60,160,60,80,60L0,60Z"></path>
    </svg>
</section>

<!-- Featured Kost -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Kost Pilihan</h2>
        <div class="row">
            <?php
            // Ambil 6 kost dengan rata-rata rating tertinggi (dan minimal ada 1 review)
            $query = "
                SELECT k.*, AVG(r.rating) as avg_rating, COUNT(r.id) as total_review
                FROM kost k
                LEFT JOIN reviews r ON r.kost_id = k.id
                WHERE k.status = 'active'
                GROUP BY k.id
                HAVING total_review > 0
                ORDER BY avg_rating DESC, total_review DESC
                LIMIT 6
            ";
            $stmt = $db->prepare($query);
            $stmt->execute();

            while ($kost = $stmt->fetch(PDO::FETCH_ASSOC)):
                $kost_image = !empty($kost['images']) ? htmlspecialchars($kost['images']) : 'https://via.placeholder.com/400x250?text=No+Image';
                $avg_rating = $kost['avg_rating'] ? round($kost['avg_rating'], 2) : null;
                $total_review = $kost['total_review'];
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card kost-card h-100">
                    <img src="<?php echo $kost_image; ?>" class="card-img-top" alt="Gambar Kost" style="height:200px;object-fit:cover;">
                    <div class="card-body">
                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($kost['name']); ?></h5>
                        <!-- Tampilkan rating -->
                        <div class="mb-2">
                            <?php if ($avg_rating): ?>
                                <span class="badge bg-warning text-dark" style="font-size:1em;">
                                    <?php echo $avg_rating; ?>/5 <i class="fas fa-star text-dark"></i>
                                </span>
                                <span class="text-muted ms-1" style="font-size:0.95em;">(<?php echo $total_review; ?> ulasan)</span>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:0.95em;">Belum ada ulasan</span>
                            <?php endif; ?>
                        </div>
                        <p class="card-text text-muted"><?php echo htmlspecialchars(substr($kost['description'], 0, 100)); ?>...</p>
                        <p class="card-text">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <?php echo htmlspecialchars(substr($kost['address'], 0, 50)); ?>...
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price-tag">Rp <?php echo number_format($kost['price'], 0, ',', '.'); ?>/bulan</span>
                            <div>
                                <a href="?page=kost_detail&id=<?php echo $kost['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                                <?php if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                                <a href="?page=kost_detail&id=<?php echo $kost['id']; ?>#booking" class="btn btn-success btn-sm ms-1">
                                    <i class="fas fa-calendar-check"></i> Booking
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-5" style="background: var(--cream);">
    <div class="container">
        <h2 class="text-center mb-5">Mengapa Memilih BookKost?</h2>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-center h-100" style="background: #fff; color: var(--darker-brown); border: 1px solid var(--light-brown);">
                    <div class="card-body">
                        <i class="fas fa-search fa-3x mb-3" style="color: var(--dark-brown);"></i>
                        <h5>Mudah Dicari</h5>
                        <p>Sistem pencarian yang canggih untuk menemukan kost sesuai kebutuhan Anda.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-center h-100" style="background: #fff; color: var(--darker-brown); border: 1px solid var(--light-brown);">
                    <div class="card-body">
                        <i class="fas fa-shield-alt fa-3x mb-3" style="color: var(--dark-brown);"></i>
                        <h5>Aman & Terpercaya</h5>
                        <p>Semua kost telah diverifikasi untuk menjamin keamanan dan kenyamanan Anda.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-center h-100" style="background: #fff; color: var(--darker-brown); border: 1px solid var(--light-brown);">
                    <div class="card-body">
                        <i class="fas fa-credit-card fa-3x mb-3" style="color: var(--dark-brown);"></i>
                        <h5>Pembayaran Mudah</h5>
                        <p>Berbagai metode pembayaran yang aman dan mudah untuk kemudahan transaksi.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-center h-100" style="background: #fff; color: var(--darker-brown); border: 1px solid var(--light-brown);">
                    <div class="card-body">
                        <i class="fas fa-comments fa-3x mb-3" style="color: var(--dark-brown);"></i>
                        <h5>Dukungan Pelanggan</h5>
                        <p>Tim dukungan kami siap membantu Anda 24/7 untuk segala pertanyaan dan masalah.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-center h-100" style="background: #fff; color: var(--darker-brown); border: 1px solid var(--light-brown);">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x mb-3" style="color: var(--dark-brown);"></i>
                        <h5>Komunitas Aktif</h5>
                        <p>Bergabung dengan komunitas kami untuk berbagi pengalaman dan tips seputar kost.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-center h-100" style="background: #fff; color: var(--darker-brown); border: 1px solid var(--light-brown);">
                    <div class="card-body">
                        <i class="fas fa-star fa-3x mb-3" style="color: var(--dark-brown);"></i>
                        <h5>Ulasan & Rating</h5>
                        <p>Baca ulasan dari pengguna lain untuk memilih kost terbaik sesuai preferensi Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Daftar Pemilik Kost CTA -->
<section class="py-5" style="background: #F5F5DC;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0" style="border-radius: 2rem; background:rgba(255,255,255,0.97);">
                    <div class="card-body text-center py-5">
                        <h3 class="fw-bold mb-3" style="color:#8B4513;">
                            <i class="fas fa-home-user me-2"></i>
                            Ingin Mendaftarkan Kost Anda di BookKost?
                        </h3>
                        <p class="mb-4" style="color:#4B2C20;font-size:1.15rem;">
                            Daftarkan kost Anda dan jangkau ribuan pencari kost di seluruh Indonesia!<br>
                            <span class="text-muted" style="font-size:1rem;">Proses mudah, aman, dan diverifikasi oleh admin BookKost.</span>
                        </p>
                        <a href="https://wa.me/6285311803904?text=Halo%20admin%2C%20saya%20ingin%20daftar%20sebagai%20pemilik%20kost%20di%20BookKost.%20Berikut%20data%20dan%20dokumen%20yang%20akan%20saya%20kirimkan%3A%0A- Nama%20Lengkap%0A- Username%20yang%20diinginkan%0A- Email%20aktif%0A- No.%20Telepon%0A- Password%20yang%20diinginkan%0A- Foto%20KTP%20(upload%20file)%0A- Foto%20diri%20sambil%20memegang%20KTP%20(upload%20file)%0AMohon%20petunjuk%20selanjutnya."
                           class="btn btn-success btn-lg px-4 py-2 mb-3" target="_blank" rel="noopener" style="font-weight:bold;font-size:1.1rem;border-radius:2rem;">
                            <i class="fab fa-whatsapp me-2"></i> Daftar via WhatsApp Admin
                        </a>
                        <div class="mt-3 text-start mx-auto" style="max-width:400px;">
                            <div class="fw-semibold mb-2" style="color:#8B4513;">Persyaratan yang perlu dikirimkan:</div>
                            <ul class="mb-0" style="color:#4B2C20;">
                                <li>Nama Lengkap</li>
                                <li>Username yang diinginkan</li>
                                <li>Email aktif</li>
                                <li>No. Telepon</li>
                                <li>Password yang diinginkan</li>
                                <li>Foto KTP (upload file)</li>
                                <li>Foto diri sambil memegang KTP (upload file)</li>
                            </ul>
                        </div>
                        <div class="alert alert-warning mt-4 mb-0" style="background:linear-gradient(90deg,#FFF5E1 80%,#FFE4B5);color:#8B4513;border:none;">
                            Setelah mengirimkan data dan dokumen ke admin, silakan tunggu proses verifikasi. Admin akan menghubungi Anda jika akun pemilik kost sudah aktif.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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