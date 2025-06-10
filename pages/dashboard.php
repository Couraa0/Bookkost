<?php
// pages/dashboard.php
if (!isLoggedIn()) {
    header("Location: ?page=home");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user stats
$stats_query = $db->prepare("
    SELECT 
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings,
        COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_bookings,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
        COUNT(*) as total_bookings
    FROM bookings 
    WHERE user_id = :user_id
");
$stats_query->execute([':user_id' => $user_id]);
$stats = $stats_query->fetch(PDO::FETCH_ASSOC);

// Get recent bookings
$recent_bookings = $db->prepare("
    SELECT b.*, k.name as kost_name, k.address as kost_address, k.price as kost_price 
    FROM bookings b 
    JOIN kost k ON b.kost_id = k.id 
    WHERE b.user_id = :user_id 
    ORDER BY b.created_at DESC 
    LIMIT 5
");
$recent_bookings->execute([':user_id' => $user_id]);

// Jika role owner, tampilkan data booking untuk kost miliknya dan data user (penghuni)
$is_owner = isset($_SESSION['role']) && $_SESSION['role'] === 'owner';

if ($is_owner) {
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
        header("Location: ?page=dashboard");
        exit();
    }
    if (isset($_POST['cancel_booking']) && isset($_POST['booking_id'])) {
        $booking_id = intval($_POST['booking_id']);
        $update = $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = :id");
        $update->execute([':id' => $booking_id]);
        header("Location: ?page=dashboard");
        exit();
    }
}

// Hitung ulang statistik booking untuk owner (berdasarkan semua kost miliknya)
if ($is_owner && !empty($owner_kost_ids_in)) {
    $stats_owner = [
        'pending_bookings' => 0,
        'confirmed_bookings' => 0,
        'completed_bookings' => 0,
        'total_bookings' => 0
    ];
    $stat_stmt = $db->query("
        SELECT status, COUNT(*) as jumlah
        FROM bookings
        WHERE kost_id IN ($owner_kost_ids_in)
        GROUP BY status
    ");
    while ($row = $stat_stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['status'] === 'pending') $stats_owner['pending_bookings'] = $row['jumlah'];
        if ($row['status'] === 'confirmed') $stats_owner['confirmed_bookings'] = $row['jumlah'];
        if ($row['status'] === 'completed') $stats_owner['completed_bookings'] = $row['jumlah'];
        $stats_owner['total_bookings'] += $row['jumlah'];
    }
}
?>
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="sidebar p-4 rounded-4 shadow" style="background: linear-gradient(135deg, #CD853F 60%, #D2B48C 100%); min-height: 600px; border-radius: 24px; border: 1px solid #e0c9a6; position: sticky; top: 24px; z-index: 10;">
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
                    <a class="nav-link active rounded-pill px-3 py-2 fw-semibold" href="?page=dashboard" style="background: #cd853f; color: #fff; transition: background 0.2s;">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <?php if ($is_owner): ?>
                    <a class="nav-link rounded-pill px-3 py-2 fw-semibold" href="?page=owner_manage" style="color:rgb(255, 255, 255); transition: background 0.2s;">
                        <i class="fas fa-calendar-check me-2"></i> Kelola Kost Anda
                    </a>
                    <?php endif; ?>
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
        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Welcome Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4>Selamat Datang, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h4>
                    <p class="text-muted">Kelola booking kost Anda dengan mudah melalui dashboard ini.</p>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                            <h3 class="text-warning">
                                <?php
                                if ($is_owner && !empty($owner_kost_ids_in)) {
                                    echo $stats_owner['pending_bookings'];
                                } else {
                                    echo $stats['pending_bookings'];
                                }
                                ?>
                            </h3>
                            <small>Menunggu Dikonfirmasi</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h3 class="text-success">
                                <?php
                                if ($is_owner && !empty($owner_kost_ids_in)) {
                                    echo $stats_owner['confirmed_bookings'];
                                } else {
                                    echo $stats['confirmed_bookings'];
                                }
                                ?>
                            </h3>
                            <small>Dikonfirmasi</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-star fa-2x text-info mb-2"></i>
                            <h3 class="text-info">
                                <?php
                                if ($is_owner && !empty($owner_kost_ids_in)) {
                                    echo $stats_owner['completed_bookings'];
                                } else {
                                    echo $stats['completed_bookings'];
                                }
                                ?>
                            </h3>
                            <small>Selesai</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-money-bill-wave fa-2x text-primary mb-2"></i>
                            <h3 class="text-primary">
                                <?php
                                // Total pendapatan owner dari seluruh kost (status completed)
                                if ($is_owner && !empty($owner_kost_ids_in)) {
                                    $income_stmt = $db->query("SELECT SUM(total_price) as total_income FROM bookings WHERE kost_id IN ($owner_kost_ids_in) AND status = 'completed'");
                                    $income = $income_stmt->fetch(PDO::FETCH_ASSOC);
                                    echo 'Rp ' . number_format($income['total_income'] ?? 0, 0, ',', '.');
                                } else {
                                    echo '-';
                                }
                                ?>
                            </h3>
                            <small>Total Pendapatan</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Daftar Kost Milik Owner (CRUD) -->
            <?php if ($is_owner): ?>
            <div class="card mb-4 shadow-sm rounded-4">
                <div class="card-header bg-gradient" style="background:linear-gradient(135deg,#CD853F,#8B4513);color:#fff;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <span style="color:#222;">Daftar Kost yang Anda Kelola</span>
                        </h5>
                        <a href="?page=dashboard&show=kost&add=1" class="btn btn-warning btn-sm rounded-pill">
                            <i class="fas fa-plus"></i> Tambah Kost
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $kosts_stmt = $db->prepare("SELECT * FROM kost WHERE owner_id = :owner_id");
                    $kosts_stmt->execute([':owner_id' => $_SESSION['user_id']]);
                    $kosts = $kosts_stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (count($kosts) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Nama Kost</th>
                                        <th>Alamat</th>
                                        <th>Harga</th>
                                        <th>Status</th>
                                        <th>Tanggal Ditambahkan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kosts as $kost): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($kost['name']); ?></td>
                                        <td><?php echo htmlspecialchars($kost['address']); ?></td>
                                        <td>Rp <?php echo number_format($kost['price'], 0, ',', '.'); ?>/bulan</td>
                                        <td>
                                            <span class="badge <?php echo $kost['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo ucfirst($kost['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($kost['created_at'])); ?></td>
                                        <td>
                                            <a href="?page=dashboard&show=kost&edit_kost=<?php echo $kost['id']; ?>" class="btn btn-outline-warning btn-sm rounded-pill">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="?page=dashboard&show=kost&delete_kost=<?php echo $kost['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill" onclick="return confirm('Yakin hapus kost ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-home fa-3x text-muted mb-3"></i>
                            <h5>Belum Ada Kost</h5>
                            <p class="text-muted">Anda belum mendaftarkan kost apapun.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            // CRUD Kost: Tambah/Edit/Hapus
            // Tambah Kost
            if (isset($_GET['add'])):
                if (isset($_POST['add_kost'])) {
                    // Validasi sederhana
                    $name = trim($_POST['name']);
                    $address = trim($_POST['address']);
                    $description = trim($_POST['description']);
                    $price = intval($_POST['price']);
                    $facilities = trim($_POST['facilities']);
                    $status = $_POST['status'];
                    $image_name = '';
                    // Handle upload gambar jika ada
                    if (isset($_FILES['images']) && $_FILES['images']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = __DIR__ . '/../img/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        $tmp_name = $_FILES['images']['tmp_name'];
                        $ext = pathinfo($_FILES['images']['name'], PATHINFO_EXTENSION);
                        $new_name = 'kost_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                        move_uploaded_file($tmp_name, $upload_dir . $new_name);
                        $image_name = 'img/' . $new_name;
                    }
                    if ($name && $address && $description && $price > 0 && $facilities && $status) {
                        $insert = $db->prepare("INSERT INTO kost (name, address, description, price, facilities, images, status, owner_id, created_at) VALUES (:name, :address, :description, :price, :facilities, :images, :status, :owner_id, NOW())");
                        $insert->execute([
                            ':name' => $name,
                            ':address' => $address,
                            ':description' => $description,
                            ':price' => $price,
                            ':facilities' => $facilities,
                            ':images' => $image_name,
                            ':status' => $status,
                            ':owner_id' => $_SESSION['user_id']
                        ]);
                        echo '<div class="alert alert-success mt-3">Kost berhasil ditambahkan.</div>';
                        echo '<meta http-equiv="refresh" content="1;url=?page=dashboard&show=kost">';
                    } else {
                        echo '<div class="alert alert-danger mt-3">Semua field wajib diisi dengan benar.</div>';
                    }
                }
            ?>
            <div class="card shadow-sm rounded-4 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah Kost Baru</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Kost</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga per Bulan</label>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fasilitas</label>
                            <input type="text" name="facilities" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Kost</label>
                            <input type="file" name="images" class="form-control" accept="image/*" required>
                            <small class="text-muted">Upload gambar kost (jpg/png/jpeg).</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                        <button type="submit" name="add_kost" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                        <a href="?page=dashboard&show=kost" class="btn btn-secondary ms-2">Batal</a>
                    </form>
                </div>
            </div>
            <?php endif; // <-- ini menutup if (isset($_GET['add'])) ?>
            <?php
            // Edit Kost
            if (isset($_GET['edit_kost'])):
                $kost_id = intval($_GET['edit_kost']);
                $kost_stmt = $db->prepare("SELECT * FROM kost WHERE id = :id AND owner_id = :owner_id");
                $kost_stmt->execute([':id' => $kost_id, ':owner_id' => $_SESSION['user_id']]);
                $kost = $kost_stmt->fetch(PDO::FETCH_ASSOC);
                if ($kost):
                    if (isset($_POST['update_kost'])) {
                        $name = trim($_POST['name']);
                        $address = trim($_POST['address']);
                        $description = trim($_POST['description']);
                        $price = intval($_POST['price']);
                        $facilities = trim($_POST['facilities']);
                        $status = $_POST['status'];
                        $image_name = $kost['images'];
                        // Handle upload gambar jika ada
                        if (isset($_FILES['images']) && $_FILES['images']['error'] === UPLOAD_ERR_OK) {
                            $upload_dir = __DIR__ . '/../img/';
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }
                            $tmp_name = $_FILES['images']['tmp_name'];
                            $ext = pathinfo($_FILES['images']['name'], PATHINFO_EXTENSION);
                            $new_name = 'kost_' . $kost_id . '_' . time() . '.' . $ext;
                            move_uploaded_file($tmp_name, $upload_dir . $new_name);
                            $image_name = 'img/' . $new_name;
                        }
                        if ($name && $address && $description && $price > 0 && $facilities && $status) {
                            $update = $db->prepare("UPDATE kost SET name=:name, address=:address, description=:description, price=:price, facilities=:facilities, images=:images, status=:status WHERE id=:id AND owner_id=:owner_id");
                            $update->execute([
                                ':name' => $name,
                                ':address' => $address,
                                ':description' => $description,
                                ':price' => $price,
                                ':facilities' => $facilities,
                                ':images' => $image_name,
                                ':status' => $status,
                                ':id' => $kost_id,
                                ':owner_id' => $_SESSION['user_id']
                            ]);
                            echo '<div class="alert alert-success mt-3">Data kost berhasil diperbarui.</div>';
                            echo '<meta http-equiv="refresh" content="1;url=?page=dashboard&show=kost">';
                        } else {
                            echo '<div class="alert alert-danger mt-3">Semua field wajib diisi dengan benar.</div>';
                        }
                    }
            ?>
            <div class="card shadow-sm rounded-4 mb-4">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Kost: <?php echo htmlspecialchars($kost['name']); ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Kost</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($kost['name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga per Bulan</label>
                                <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($kost['price']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($kost['address']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($kost['description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fasilitas</label>
                            <input type="text" name="facilities" class="form-control" value="<?php echo htmlspecialchars($kost['facilities']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Kost</label>
                            <?php if (!empty($kost['images'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($kost['images']); ?>" alt="Gambar Kost" style="max-width:120px;max-height:90px;border-radius:8px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="images" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo $kost['status'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="inactive" <?php echo $kost['status'] === 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                        <button type="submit" name="update_kost" class="btn btn-warning"><i class="fas fa-save"></i> Simpan Perubahan</button>
                        <a href="?page=dashboard&show=kost" class="btn btn-secondary ms-2">Batal</a>
                    </form>
                </div>
            </div>
            <?php endif; endif; // <-- ini menutup if ($kost) dan if (isset($_GET['edit_kost'])) ?>
            <?php
            // Hapus Kost
            if (isset($_GET['delete_kost'])):
                $kost_id = intval($_GET['delete_kost']);
                $delete = $db->prepare("DELETE FROM kost WHERE id = :id AND owner_id = :owner_id");
                $delete->execute([':id' => $kost_id, ':owner_id' => $_SESSION['user_id']]);
                echo '<div class="alert alert-success mt-3">Kost berhasil dihapus.</div>';
                echo '<meta http-equiv="refresh" content="1;url=?page=dashboard&show=kost">';
            endif;
            ?>
            <!-- ...existing code for non-owner or other dashboard content... -->
        </div>
    </div>
</div>
<?php endif; // <-- TAMBAHKAN BARIS INI untuk menutup if ($is_owner): ?>

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