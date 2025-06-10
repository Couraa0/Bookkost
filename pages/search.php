<?php
// pages/search.php
$location = isset($_GET['location']) ? $_GET['location'] : '';
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$facility = isset($_GET['facility']) ? $_GET['facility'] : [];
$rating = isset($_GET['rating']) ? $_GET['rating'] : '';

$query = "SELECT k.*, 
    (SELECT AVG(r.rating) FROM reviews r WHERE r.kost_id = k.id) as avg_rating 
    FROM kost k WHERE k.status = 'active'";
$params = [];

// Filter lokasi
if ($location) {
    $query .= " AND (k.name LIKE :location OR k.address LIKE :location)";
    $params[':location'] = "%$location%";
}

// Filter harga
if ($price_range) {
    $range = explode('-', $price_range);
    if (count($range) == 2) {
        $query .= " AND k.price BETWEEN :min_price AND :max_price";
        $params[':min_price'] = $range[0];
        $params[':max_price'] = $range[1];
    }
}

// Filter fasilitas (AND logic: semua fasilitas harus ada)
if (!empty($facility)) {
    foreach ($facility as $idx => $f) {
        $key = ":facility$idx";
        $query .= " AND k.facilities LIKE $key";
        $params[$key] = "%$f%";
    }
}

// Filter rating
if ($rating !== '') {
    $query .= " HAVING avg_rating >= :rating";
    $params[':rating'] = (float)$rating;
}

$query .= " ORDER BY k.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();

// Tambahkan array lokasi penting (contoh kampus, stasiun, dll)
$important_places = [
    [
        'name' => 'Universitas Indonesia',
        'lat' => -6.362796,
        'lng' => 106.824001
    ],
    [
        'name' => 'Stasiun Gambir',
        'lat' => -6.177430,
        'lng' => 106.830620
    ],
    [
        'name' => 'Universitas Gadjah Mada',
        'lat' => -7.769975,
        'lng' => 110.378543
    ],
    // Tambahkan lokasi penting lain sesuai kebutuhan
];
?>

<div class="container py-5">
    <h2 class="mb-4">Temukan Kost Impianmu!</h2>
    <div class="row">
        <!-- Sidebar Search Form -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0" style="position:sticky; top:90px; z-index:1;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Pencarian</h5>
                </div>
                <div class="card-body">
                    <form method="GET" id="searchForm">
                        <input type="hidden" name="page" value="search">
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="fas fa-map-marker-alt"></i> Lokasi</label>
                            <input type="text" name="location" id="locationInput" class="form-control" placeholder="Masukkan lokasi atau klik 'Lokasi Saya'" value="<?php echo htmlspecialchars($location); ?>">
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="detectLocationBtn">
                                <i class="fas fa-crosshairs"></i> Lokasi Saya
                            </button>
                            <input type="hidden" name="user_lat" id="user_lat">
                            <input type="hidden" name="user_lng" id="user_lng">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="fas fa-money-bill-wave"></i> Harga</label>
                            <select name="price_range" class="form-select">
                                <option value="">Semua Harga</option>
                                <option value="0-1000000" <?php echo $price_range === '0-1000000' ? 'selected' : ''; ?>>< Rp 1.000.000</option>
                                <option value="1000000-2000000" <?php echo $price_range === '1000000-2000000' ? 'selected' : ''; ?>>Rp 1.000.000 - 2.000.000</option>
                                <option value="2000000-99999999" <?php echo $price_range === '2000000-99999999' ? 'selected' : ''; ?>>> Rp 2.000.000</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="fas fa-cogs"></i> Fasilitas</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php
                                $facility_options = ['AC', 'WiFi', 'Kamar Mandi Dalam', 'Dapur', 'Parkir', 'Kasur'];
                                foreach ($facility_options as $f): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="facility[]" value="<?php echo $f; ?>" id="facility_<?php echo $f; ?>"
                                            <?php echo in_array($f, $facility) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="facility_<?php echo $f; ?>"><?php echo $f; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="fas fa-star"></i> Rating</label>
                            <select name="rating" class="form-select">
                                <option value="">Semua Rating</option>
                                <option value="4" <?php echo $rating === '4' ? 'selected' : ''; ?>>&#9733; 4 ke atas</option>
                                <option value="3" <?php echo $rating === '3' ? 'selected' : ''; ?>>&#9733; 3 ke atas</option>
                                <option value="2" <?php echo $rating === '2' ? 'selected' : ''; ?>>&#9733; 2 ke atas</option>
                                <option value="1" <?php echo $rating === '1' ? 'selected' : ''; ?>>&#9733; 1 ke atas</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Cari Kost
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Results -->
        <div class="col-lg-8">
            <div class="row" id="kostResults">
                <?php if ($stmt->rowCount() > 0): ?>
                    <?php while ($kost = $stmt->fetch(PDO::FETCH_ASSOC)):
                        $kost_image = !empty($kost['images']) ? htmlspecialchars($kost['images']) : 'https://via.placeholder.com/400x250?text=No+Image';
                        $avg_rating = isset($kost['avg_rating']) ? round($kost['avg_rating'], 1) : null;
                        // Ambil lat/lng kost jika ada (pastikan field latitude & longitude ada di tabel kost)
                        $kost_lat = isset($kost['latitude']) ? $kost['latitude'] : null;
                        $kost_lng = isset($kost['longitude']) ? $kost['longitude'] : null;
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card kost-card h-100 shadow-sm border-0">
                            <img src="<?php echo $kost_image; ?>" class="card-img-top" alt="Gambar Kost" style="height:200px;object-fit:cover;">
                            <div class="card-body">
                                <h5 class="card-title d-flex align-items-center justify-content-between">
                                    <?php echo htmlspecialchars($kost['name']); ?>
                                    <?php if ($avg_rating): ?>
                                        <span class="ms-2 badge bg-warning text-dark" title="Rating: <?php echo $avg_rating; ?>">
                                            <?php
                                            $full = floor($avg_rating);
                                            $half = ($avg_rating - $full) >= 0.5 ? 1 : 0;
                                            for ($i = 0; $i < $full; $i++) echo '<i class="fas fa-star text-dark"></i>';
                                            if ($half) echo '<i class="fas fa-star-half-alt text-dark"></i>';
                                            for ($i = $full + $half; $i < 5; $i++) echo '<i class="far fa-star text-dark"></i>';
                                            ?>
                                            <?php echo $avg_rating; ?>
                                        </span>
                                    <?php endif; ?>
                                </h5>
                                <!-- Estimasi jarak ke tempat penting -->
                                <?php if ($kost_lat && $kost_lng): ?>
                                    <div class="mb-2" id="distance-info-<?php echo $kost['id']; ?>">
                                        <small class="text-muted"><i class="fas fa-location-arrow"></i> Estimasi jarak:</small>
                                        <ul class="mb-1" style="font-size:0.95em;">
                                            <?php foreach ($important_places as $place): ?>
                                                <li>
                                                    <span class="fw-semibold"><?php echo htmlspecialchars($place['name']); ?>:</span>
                                                    <span class="distance-value" data-kost-lat="<?php echo $kost_lat; ?>" data-kost-lng="<?php echo $kost_lng; ?>" data-place-lat="<?php echo $place['lat']; ?>" data-place-lng="<?php echo $place['lng']; ?>">-</span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                <p class="card-text text-muted"><?php echo htmlspecialchars(substr($kost['description'], 0, 100)); ?>...</p>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <?php echo htmlspecialchars($kost['address']); ?>
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <?php echo htmlspecialchars($kost['facilities']); ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-tag">Rp <?php echo number_format($kost['price'], 0, ',', '.'); ?>/bulan</span>
                                    <a href="?page=kost_detail&id=<?php echo $kost['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-5x text-muted mb-3"></i>
                            <h4>Tidak ada kost yang ditemukan</h4>
                            <p class="text-muted">Coba ubah kriteria pencarian Anda</p>
                        </div>
                    </div>
                <?php endif; ?>
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

<script>
// Geolocation untuk deteksi lokasi user
document.getElementById('detectLocationBtn').addEventListener('click', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('user_lat').value = position.coords.latitude;
            document.getElementById('user_lng').value = position.coords.longitude;
            document.getElementById('locationInput').value = position.coords.latitude + ',' + position.coords.longitude;
        }, function() {
            alert('Gagal mendapatkan lokasi Anda.');
        });
    } else {
        alert('Browser Anda tidak mendukung geolokasi.');
    }
});

// Fungsi hitung jarak Haversine
function haversine(lat1, lon1, lat2, lon2) {
    function toRad(x) { return x * Math.PI / 180; }
    var R = 6371; // km
    var dLat = toRad(lat2-lat1);
    var dLon = toRad(lon2-lon1);
    var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Estimasi jarak ke tempat penting (hanya jika ada lat/lng kost)
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.distance-value').forEach(function(span) {
        var kostLat = parseFloat(span.getAttribute('data-kost-lat'));
        var kostLng = parseFloat(span.getAttribute('data-kost-lng'));
        var placeLat = parseFloat(span.getAttribute('data-place-lat'));
        var placeLng = parseFloat(span.getAttribute('data-place-lng'));
        if (!isNaN(kostLat) && !isNaN(kostLng) && !isNaN(placeLat) && !isNaN(placeLng)) {
            var dist = haversine(kostLat, kostLng, placeLat, placeLng);
            span.textContent = dist.toFixed(2) + ' km';
        }
    });
});

</script>});
</script>