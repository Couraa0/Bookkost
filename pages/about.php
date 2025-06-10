<?php
// pages/about.php
?>
<section class="py-5" style="background: linear-gradient(200deg, #F5F5DC 100%); min-height:100vh;">
    <div class="container">
        <div class="row mb-5 align-items-center justify-content-center">
            <div class="col-lg-6 mb-4 mb-lg-0 d-flex justify-content-center align-items-center position-relative" style="min-height:320px;">
                <!-- Card besar di belakang gambar -->
                <div class="card shadow-lg position-relative p-0 overflow-hidden" style="background:#CD853F;border-radius:30px;z-index:1;width:520px;min-height:380px;display:flex;align-items:center;justify-content:center;">
                    <img src="img/Logo.png" alt="BookKost Logo" style="width:100%;height:100%;object-fit:cover;display:block;">
                </div>
                <!-- Hiasan lingkaran transparan seperti home.php -->
                <div style="position:absolute;top:10%;left:-10px;width:120px;height:120px;background:rgba(205,133,63,0.10);border-radius:50%;z-index:2;"></div>
                <div style="position:absolute;bottom:10%;right:0px;width:80px;height:80px;background:rgba(255, 135, 50, 0.08);border-radius:50%;z-index:2;"></div>
            </div>
            <div class="col-lg-6">
                <h1 class="fw-bold mb-3" style="color:#4B2C20;letter-spacing:1px;">
                    Tentang <span style="color:#CD853F;">BookKost</span>
                </h1>
                <p class="lead" style="color:#3E1F14;">
                    <strong>BookKost</strong> adalah platform digital yang memudahkan Anda dalam mencari, membandingkan, dan memesan kost secara online di seluruh Indonesia. Dengan fitur pencarian canggih, ulasan penghuni asli, dan sistem pembayaran yang aman, BookKost hadir untuk memberikan pengalaman mencari kost yang <span class="fw-bold" style="color:#8B4513;">mudah, cepat, dan terpercaya</span>.
                </p>
                <ul class="list-unstyled mt-3">
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Kost terverifikasi & terpercaya</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Ulasan asli dari penghuni</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Booking & pembayaran online</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Dukungan pelanggan 24/7</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <b>Ingin menambah kost?</b> Daftar sebagai pemilik, lengkapi data & upload dokumen, lalu admin akan memverifikasi sebelum Anda bisa menambah kost.</li>
                </ul>
            </div>
        </div>

        <div class="text-center mb-5">
            <h2 class="fw-bold" style="color:#4B2C20;letter-spacing:1px;text-shadow:0 2px 8px #cd853f33;">Tim Kami</h2>
            <p class="text-black mb-4">BookKost dikembangkan oleh tim muda yang berdedikasi untuk memberikan solusi terbaik bagi pencari kost di Indonesia.</p>
        </div>
        <div class="row justify-content-center g-4">
            <!-- Tim 1 -->
            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-lg text-center h-100" style="background:linear-gradient(135deg,#CD853F 60%,#8B4513 100%);color:#fff;overflow:hidden;position:relative;">
                    <div class="card-body position-relative">
                        <div style="position:absolute;top:-30px;right:-30px;width:60px;height:60px;background:rgba(255,255,255,0.08);border-radius:50%;z-index:0;"></div>
                        <img src="img/Alifia.jpg" alt="Tim 1" class="rounded-circle mb-3 shadow" style="width:110px;height:110px;object-fit:cover;border:5px solid #F5F5DC;z-index:1;position:relative;">
                        <h6 class="fw-bold mb-1">Alifia Nur Huda</h6>
                        <div class="text-light mb-1" style="font-size:0.95em;">Chief Executive Officer</div>
                        <span class="badge bg-warning text-dark">CEO</span>
                    </div>
                </div>
            </div>
            <!-- Tim 2 -->
            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-lg text-center h-100" style="background:linear-gradient(135deg,#CD853F 60%,#8B4513 100%);color:#fff;overflow:hidden;position:relative;">
                    <div class="card-body position-relative">
                        <div style="position:absolute;bottom:-30px;left:-30px;width:60px;height:60px;background:rgba(255,255,255,0.08);border-radius:50%;z-index:0;"></div>
                        <img src="img/Rakha.jpg" alt="Tim 2" class="rounded-circle mb-3 shadow" style="width:110px;height:110px;object-fit:cover;border:5px solid #F5F5DC;z-index:1;position:relative;">
                        <h6 class="fw-bold mb-1">M Rakha Syamputra</h6>
                        <div class="text-light mb-1" style="font-size:0.95egy Om;">Chief Technology Officer</div>
                        <span class="badge bg-black">CTO</span>
                    </div>
                </div>
            </div>
            <!-- Tim 3 -->
            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-lg text-center h-100" style="background:linear-gradient(135deg,#CD853F 60%,#8B4513 100%);color:#fff;overflow:hidden;position:relative;">
                    <div class="card-body position-relative">
                        <div style="position:absolute;top:-30px;left:-30px;width:60px;height:60px;background:rgba(255,255,255,0.08);border-radius:50%;z-index:0;"></div>
                        <img src="img/Rizky.jpg" alt="Tim 3" class="rounded-circle mb-3 shadow" style="width:110px;height:110px;object-fit:cover;border:5px solid #F5F5DC;z-index:1;position:relative;">
                        <h6 class="fw-bold mb-1">Rizky Azhari Putra</h6>
                        <div class="text-light mb-1" style="font-size:0.95em;">Chief Technology Officer</div>
                        <span class="badge bg-black">CTO</span>
                    </div>
                </div>
            </div>
            <!-- Tim 4 -->
            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-lg text-center h-100" style="background:linear-gradient(135deg,#CD853F 60%,#8B4513 100%);color:#fff;overflow:hidden;position:relative;">
                    <div class="card-body position-relative">
                        <div style="position:absolute;bottom:-30px;right:-30px;width:60px;height:60px;background:rgba(255,255,255,0.08);border-radius:50%;z-index:0;"></div>
                        <img src="img/Yuuka.jpg" alt="Tim 4" class="rounded-circle mb-3 shadow" style="width:110px;height:110px;object-fit:cover;border:5px solid #F5F5DC;z-index:1;position:relative;">
                        <h6 class="fw-bold mb-1">Yuuka Natasya Aji</h6>
                        <div class="text-light mb-1" style="font-size:0.95em;">Chief Operating Officer</div>
                        <span class="badge bg-primary">COO</span>
                    </div>
                </div>
            </div>
            <!-- Tim 5 -->
            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-lg text-center h-100" style="background:linear-gradient(135deg,#CD853F 60%,#8B4513 100%);color:#fff;overflow:hidden;position:relative;">
                    <div class="card-body position-relative">
                        <div style="position:absolute;top:-30px;right:-30px;width:60px;height:60px;background:rgba(255,255,255,0.08);border-radius:50%;z-index:0;"></div>
                        <img src="img/Farhan.jpg" alt="Tim 5" class="rounded-circle mb-3 shadow" style="width:110px;height:110px;object-fit:cover;border:5px solid #F5F5DC;z-index:1;position:relative;">
                        <h6 class="fw-bold mb-1">Farhan Ramadhan</h6>
                        <div class="text-light mb-1" style="font-size:0.95em;">Chief Marketing Officer</div>
                        <span class="badge bg-success">CMO</span>
                    </div>
                </div>
            </div>
            <!-- Tim 6 -->
            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-lg text-center h-100" style="background:linear-gradient(135deg,#CD853F 60%,#8B4513 100%);color:#fff;overflow:hidden;position:relative;">
                    <div class="card-body position-relative">
                        <div style="position:absolute;bottom:-30px;left:-30px;width:60px;height:60px;background:rgba(255,255,255,0.08);border-radius:50%;z-index:0;"></div>
                        <img src="img/Rizka.jpg" alt="Tim 6" class="rounded-circle mb-3 shadow" style="width:110px;height:110px;object-fit:cover;border:5px solid #F5F5DC;z-index:1;position:relative;">
                        <h6 class="fw-bold mb-1">Rizka Amaniah</h6>
                        <div class="text-light mb-1" style="font-size:0.95em;">Chief Financial Officer</div>
                        <span class="badge bg-danger">CFO</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 text-center">
            <h4 class="fw-bold mb-3" style="color:#4B2C20;letter-spacing:1px;text-shadow:0 2px 8px #cd853f33;">Visi & Misi</h4>
            <p class="lead" style="color:#3E1F14;">
                Menjadi platform pencarian kost nomor satu di Indonesia dengan mengedepankan <span class="fw-bold" style="color:#8B4513;">keamanan, kenyamanan, dan transparansi</span> bagi seluruh pengguna.
            </p>
            <div class="row justify-content-center mt-4">
                <div class="col-md-4 mb-3">
                    <div class="p-4 shadow-lg" style="background:#fff;color:#4B2C20;position:relative;overflow:hidden;border-radius:2rem 2rem 2rem 2rem;border:2px solid #8B4513;">
                        <i class="fas fa-bullseye fa-2x mb-2" style="color:#8B4513;"></i>
                        <div class="fw-bold mb-1">Mudah & Cepat</div>
                        <div>Proses pencarian dan booking kost yang praktis dan efisien.</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="p-4 shadow-lg" style="background:#fff;color:#4B2C20;position:relative;overflow:hidden;border-radius:2rem 2rem 2rem 2rem;border:2px solid #8B4513;">
                        <i class="fas fa-users fa-2x mb-2" style="color:#8B4513;"></i>
                        <div class="fw-bold mb-1">Komunitas Terpercaya</div>
                        <div>Menghubungkan pencari dan pemilik kost secara aman.</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="p-4 shadow-lg" style="background:#fff;color:#4B2C20;position:relative;overflow:hidden;border-radius:2rem 2rem 2rem 2rem;border:2px solid #8B4513;">
                        <i class="fas fa-star fa-2x mb-2" style="color:#8B4513;"></i>
                        <div class="fw-bold mb-1">Ulasan Asli</div>
                        <div>Review jujur dari penghuni untuk membantu keputusan Anda.</div>
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