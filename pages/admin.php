<?php
// pages/admin.php
if (!hasRole('admin')) {
    header("Location: ?page=home");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// Handle booking status updates
if (isset($_POST['update_booking_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['status'];
    
    $update_stmt = $db->prepare("UPDATE bookings SET status = :status WHERE id = :booking_id");
    if ($update_stmt->execute([':status' => $new_status, ':booking_id' => $booking_id])) {
        $message = "Status booking berhasil diperbarui.";
        $message_type = "success";
    } else {
        $message = "Gagal memperbarui status booking.";
        $message_type = "danger";
    }
}

// Handle kost management
if (isset($_POST['add_kost'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $description = $_POST['description'];
    $price = floatval($_POST['price']);
    $facilities = $_POST['facilities'];

    // Handle upload gambar (images)
    $images = '';
    if (isset($_FILES['images']) && $_FILES['images']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../img/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $tmp_name = $_FILES['images']['tmp_name'];
        $ext = pathinfo($_FILES['images']['name'], PATHINFO_EXTENSION);
        $new_name = 'kost_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        move_uploaded_file($tmp_name, $upload_dir . $new_name);
        $images = 'img/' . $new_name;
    }

    // Kolom owner_name, owner_phone, owner_email hanya diisi jika ada di tabel
    // Cek kolom di database
    $columns = [];
    $cols_stmt = $db->query("SHOW COLUMNS FROM kost");
    while ($col = $cols_stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $col['Field'];
    }

    $sql = "INSERT INTO kost (name, address, description, price, facilities, images, status, created_at";
    $params = [
        ':name' => $name,
        ':address' => $address,
        ':description' => $description,
        ':price' => $price,
        ':facilities' => $facilities,
        ':images' => $images,
        ':status' => 'active'
    ];

    if (in_array('owner_name', $columns)) {
        $sql .= ", owner_name";
        $params[':owner_name'] = $_POST['owner_name'];
    }
    if (in_array('owner_phone', $columns)) {
        $sql .= ", owner_phone";
        $params[':owner_phone'] = $_POST['owner_phone'];
    }
    if (in_array('owner_email', $columns)) {
        $sql .= ", owner_email";
        $params[':owner_email'] = $_POST['owner_email'];
    }
    $sql .= ") VALUES (:name, :address, :description, :price, :facilities, :images, :status, NOW()";
    if (in_array('owner_name', $columns)) $sql .= ", :owner_name";
    if (in_array('owner_phone', $columns)) $sql .= ", :owner_phone";
    if (in_array('owner_email', $columns)) $sql .= ", :owner_email";
    $sql .= ")";

    $insert_kost = $db->prepare($sql);

    if ($insert_kost->execute($params)) {
        $message = "Kost berhasil ditambahkan.";
        $message_type = "success";
    } else {
        $message = "Gagal menambahkan kost.";
        $message_type = "danger";
    }
}

// Handle update status kost
if (isset($_POST['change_status_kost'])) {
    $kost_id = intval($_POST['kost_id']);
    $new_status = $_POST['new_status'];
    $update_stmt = $db->prepare("UPDATE kost SET status = :status WHERE id = :id");
    if ($update_stmt->execute([':status' => $new_status, ':id' => $kost_id])) {
        $message = "Status kost berhasil diperbarui.";
        $message_type = "success";
    } else {
        $message = "Gagal memperbarui status kost.";
        $message_type = "danger";
    }
}

// CRUD Booking
if ($action === 'delete_booking' && isset($_GET['id'])) {
    $delete_stmt = $db->prepare("DELETE FROM bookings WHERE id = :id");
    $delete_stmt->execute([':id' => intval($_GET['id'])]);
    header("Location: ?page=admin&action=bookings");
    exit();
}

// CRUD Kost
if ($action === 'delete_kost' && isset($_GET['id'])) {
    $delete_stmt = $db->prepare("DELETE FROM kost WHERE id = :id");
    $delete_stmt->execute([':id' => intval($_GET['id'])]);
    header("Location: ?page=admin&action=kost");
    exit();
}
if ($action === 'edit_kost' && isset($_GET['id'])) {
    $kost_id = intval($_GET['id']);
    $kost_edit = $db->prepare("SELECT * FROM kost WHERE id = :id");
    $kost_edit->execute([':id' => $kost_id]);
    $kost_data = $kost_edit->fetch(PDO::FETCH_ASSOC);
    if (isset($_POST['update_kost'])) {
        $update = $db->prepare("UPDATE kost SET name=:name, address=:address, description=:description, price=:price, facilities=:facilities, images=:images, owner_name=:owner_name, owner_phone=:owner_phone, owner_email=:owner_email, status=:status WHERE id=:id");
        $update->execute([
            ':name' => $_POST['name'],
            ':address' => $_POST['address'],
            ':description' => $_POST['description'],
            ':price' => $_POST['price'],
            ':facilities' => $_POST['facilities'],
            ':images' => $_POST['images'],
            ':owner_name' => $_POST['owner_name'],
            ':owner_phone' => $_POST['owner_phone'],
            ':owner_email' => $_POST['owner_email'],
            ':status' => $_POST['status'],
            ':id' => $kost_id
        ]);
        header("Location: ?page=admin&action=kost");
        exit();
    }
}

// CRUD User
if ($action === 'delete_user' && isset($_GET['id'])) {
    $delete_stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $delete_stmt->execute([':id' => intval($_GET['id'])]);
    header("Location: ?page=admin&action=users");
    exit();
}

// Handle add user
if ($action === 'users' && isset($_POST['add_user_submit'])) {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    // Validasi sederhana
    if ($full_name && $username && $email && $role && $password) {
        // Simpan password apa adanya (TIDAK DIHASH)
        $stmt = $db->prepare("INSERT INTO users (full_name, username, email, phone, role, password, created_at) VALUES (:full_name, :username, :email, :phone, :role, :password, NOW())");
        $success = $stmt->execute([
            ':full_name' => $full_name,
            ':username' => $username,
            ':email' => $email,
            ':phone' => $phone,
            ':role' => $role,
            ':password' => $password
        ]);
        if ($success) {
            $message = "User berhasil ditambahkan.";
            $message_type = "success";
        } else {
            $message = "Gagal menambahkan user.";
            $message_type = "danger";
        }
    } else {
        $message = "Semua field wajib diisi.";
        $message_type = "danger";
    }
}

// Detail user handler
$user_detail_data = null;
if ($action === 'users' && isset($_GET['detail_id'])) {
    $detail_id = intval($_GET['detail_id']);
    $user_detail_stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
    $user_detail_stmt->execute([':id' => $detail_id]);
    $user_detail_data = $user_detail_stmt->fetch(PDO::FETCH_ASSOC);
}

// Moderasi laporan listing palsu/menyesatkan
if ($action === 'reports' && isset($_GET['review']) && isset($_GET['id'])) {
    $report_id = intval($_GET['id']);
    $status = $_GET['review'] === 'approve' ? 'reviewed' : 'dismissed';
    $db->prepare("UPDATE kost_reports SET status = :status WHERE id = :id")->execute([':status' => $status, ':id' => $report_id]);
    header("Location: ?page=admin&action=reports");
    exit();
}

// Get statistics
$stats_query = $db->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role != 'admin') as total_users,
        (SELECT COUNT(*) FROM kost WHERE status = 'active') as total_kost,
        (SELECT COUNT(*) FROM bookings WHERE status = 'pending') as pending_bookings,
        (SELECT COUNT(*) FROM bookings) as total_bookings,
        (SELECT COUNT(*) FROM kost_reports) as total_reports
");
$stats = $stats_query->fetch(PDO::FETCH_ASSOC);

// Notifikasi admin untuk verifikasi pemilik kost baru
$pending_owners = $db->query("SELECT * FROM owner_requests WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Admin Sidebar -->
        <div class="col-md-3">
            <div class="sidebar p-4 rounded-4 shadow"
                style="background: linear-gradient(135deg, #CD853F 60%, #D2B48C 100%);
                       min-height: 700px; border-radius: 24px; border: 1px solid #e0c9a6;
                       position: sticky; top: 90px; z-index: 10;">
                <div class="text-center mb-4">
                    <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 90px; height: 90px; border: 4px solid #cd853f;">
                        <i class="fas fa-user-shield fa-3x" style="color:#cd853f;"></i>
                    </div>
                    <h5 class="mt-3 fw-bold" style="color:#fff;">Admin BookKost</h5>
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
                    <a class="nav-link rounded-pill px-3 py-2 fw-semibold <?php echo $action === 'dashboard' ? 'active' : ''; ?>" href="?page=admin&action=dashboard" style="<?php echo $action === 'dashboard' ? 'background: #cd853f; color: #fff;' : 'color: #8B4513;'; ?> transition: background 0.2s;">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a class="nav-link rounded-pill px-3 py-2 fw-semibold <?php echo $action === 'bookings' ? 'active' : ''; ?>" href="?page=admin&action=bookings" style="<?php echo $action === 'bookings' ? 'background: #cd853f; color: #fff;' : 'color: #8B4513;'; ?> transition: background 0.2s;">
                        <i class="fas fa-calendar-check me-2"></i> Kelola Booking
                    </a>
                    <a class="nav-link rounded-pill px-3 py-2 fw-semibold <?php echo $action === 'kost' ? 'active' : ''; ?>" href="?page=admin&action=kost" style="<?php echo $action === 'kost' ? 'background: #cd853f; color: #fff;' : 'color: #8B4513;'; ?> transition: background 0.2s;">
                        <i class="fas fa-home me-2"></i> Kelola Kost
                    </a>
                    <a class="nav-link rounded-pill px-3 py-2 fw-semibold <?php echo $action === 'users' ? 'active' : ''; ?>" href="?page=admin&action=users" style="<?php echo $action === 'users' ? 'background: #cd853f; color: #fff;' : 'color: #8B4513;'; ?> transition: background 0.2s;">
                        <i class="fas fa-users me-2"></i> Kelola Users
                    </a>
                    <a class="nav-link rounded-pill px-3 py-2 fw-semibold <?php echo $action === 'reports' ? 'active' : ''; ?>" href="?page=admin&action=reports" style="<?php echo $action === 'reports' ? 'background: #cd853f; color: #fff;' : 'color: #8B4513;'; ?> transition: background 0.2s;">
                        <i class="fas fa-flag me-2"></i> Laporan Listing
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
        <!-- Main Content -->
        <div class="col-md-9">
            <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($action === 'dashboard'): ?>
                <!-- Admin Dashboard -->
                <h2 class="mb-4">Admin Dashboard</h2>
                <?php if (!empty($pending_owners)): ?>
                    <div class="alert alert-warning">
                        <strong>Notifikasi:</strong> Ada <?php echo count($pending_owners); ?> permintaan verifikasi pemilik kost baru.<br>
                        <a href="?page=admin&action=owner_requests" class="btn btn-warning btn-sm mt-2">Lihat & Konfirmasi Permintaan</a>
                    </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h3 class="text-primary"><?php echo $stats['total_users']; ?></h3>
                                <small>Total Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-home fa-2x text-success mb-2"></i>
                                <h3 class="text-success"><?php echo $stats['total_kost']; ?></h3>
                                <small>Total Kost</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h3 class="text-warning"><?php echo $stats['pending_bookings']; ?></h3>
                                <small>Booking Pending</small>
                            </div>
                        </div>
                    </div>
                    <!-- Card Booking Dikonfirmasi -->
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h3 class="text-success">
                                    <?php
                                    $confirmed = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn();
                                    echo $confirmed;
                                    ?>
                                </h3>
                                <small>Booking Dikonfirmasi</small>
                            </div>
                        </div>
                    </div>
                    <!-- Card Booking Selesai -->
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-star fa-2x text-info mb-2"></i>
                                <h3 class="text-info">
                                    <?php
                                    $completed = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetchColumn();
                                    echo $completed;
                                    ?>
                                </h3>
                                <small>Booking Selesai</small>
                            </div>
                        </div>
                    </div>
                    <!-- Ganti Card Total Booking menjadi Jumlah Laporan -->
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-flag fa-2x text-danger mb-2"></i>
                                <h3 class="text-danger"><?php echo $stats['total_reports']; ?></h3>
                                <small>Jumlah Laporan</small>
                            </div>
                        </div>
                    </div>
                    <!-- Card Total Rating -->
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-star fa-2x text-warning mb-2"></i>
                                <h3 class="text-warning">
                                    <?php
                                    $avg_stmt = $db->query("SELECT AVG(rating) as avg_rating FROM reviews");
                                    $avg = $avg_stmt->fetch(PDO::FETCH_ASSOC);
                                    $avg_rating = $avg && $avg['avg_rating'] ? round($avg['avg_rating'], 2) : 0;
                                    echo $avg_rating . '/5';
                                    ?>
                                </h3>
                                <small>Rata-rata Rating</small>
                            </div>
                        </div>
                    </div>
                    <!-- Card Total Pendapatan -->
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                <h3 class="text-success">
                                    <?php
                                    $income_stmt = $db->query("SELECT SUM(total_price) as total_income FROM bookings WHERE status = 'completed'");
                                    $income = $income_stmt->fetch(PDO::FETCH_ASSOC);
                                    echo 'Rp ' . number_format($income['total_income'] ?? 0, 0, ',', '.');
                                    ?>
                                </h3>
                                <small>Total Pendapatan</small>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Aktivitas Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_activity = $db->query("
                            SELECT b.*, u.full_name as user_name, k.name as kost_name 
                            FROM bookings b 
                            JOIN users u ON b.user_id = u.id 
                            JOIN kost k ON b.kost_id = k.id 
                            ORDER BY b.created_at DESC 
                            LIMIT 10
                        ");
                        ?>
                        
                        <?php if ($recent_activity->rowCount() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Kost</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($activity = $recent_activity->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activity['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['kost_name']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $activity['status']; ?>">
                                                    <?php echo ucfirst($activity['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <h5>Belum Ada Aktivitas</h5>
                                <p class="text-muted">Tidak ada aktivitas terbaru yang ditemukan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($action === 'bookings'): ?>
                <!-- Booking Management -->
                <h2 class="mb-4">Kelola Booking</h2>
                
                <?php
                $bookings_query = $db->query("
                    SELECT b.*, u.full_name as user_name, k.name as kost_name 
                    FROM bookings b 
                    JOIN users u ON b.user_id = u.id 
                    JOIN kost k ON b.kost_id = k.id 
                    ORDER BY b.created_at DESC
                ");
                ?>
                
                <div class="card">
                    <div class="card-body">
                        <?php if ($bookings_query->rowCount() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Kost</th>
                                            <th>Status</th>
                                            <th>Tanggal Booking</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($booking = $bookings_query->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['kost_name']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm" style="width:auto;display:inline-block;">
                                                        <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                        <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                    <button type="submit" name="update_booking_status" class="btn btn-primary btn-sm mt-2">
                                                        <i class="fas fa-sync"></i> Update
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                                <h5>Belum Ada Booking</h5>
                                <p class="text-muted">Tidak ada booking yang ditemukan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- End Booking Management -->
            <?php elseif ($action === 'kost'): ?>
                <!-- Kost Management -->
                <h2 class="mb-4">Kelola Kost</h2>
                <?php
                $kost_query = $db->query("SELECT * FROM kost ORDER BY created_at DESC");
                ?>

                <!-- Menu Tambah Kost -->
                <?php if (isset($_GET['add'])): ?>
                    <?php
                    if (isset($_POST['add_kost'])) {
                        $name = trim($_POST['name']);
                        $address = trim($_POST['address']);
                        $description = trim($_POST['description']);
                        $price = intval($_POST['price']);
                        $facilities = trim($_POST['facilities']);
                        $status = $_POST['status'];
                        $owner_id = intval($_POST['owner_id']);
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
                        // Ambil data owner dari tabel users
                        $owner_name = $owner_phone = $owner_email = '';
                        if ($owner_id > 0) {
                            $owner_stmt = $db->prepare("SELECT full_name, phone, email FROM users WHERE id = :id AND role = 'owner' LIMIT 1");
                            $owner_stmt->execute([':id' => $owner_id]);
                            $owner = $owner_stmt->fetch(PDO::FETCH_ASSOC);
                            if ($owner) {
                                $owner_name = $owner['full_name'];
                                $owner_phone = $owner['phone'];
                                $owner_email = $owner['email'];
                            }
                        }
                        // Hanya simpan owner_id ke tabel kost, info owner diambil dari tabel users saat ditampilkan
                        if ($name && $address && $description && $price > 0 && $facilities && $status && $owner_id > 0 && $owner_name) {
                            $insert = $db->prepare("INSERT INTO kost (name, address, description, price, facilities, images, status, owner_id, created_at) VALUES (:name, :address, :description, :price, :facilities, :images, :status, :owner_id, NOW())");
                            $insert->execute([
                                ':name' => $name,
                                ':address' => $address,
                                ':description' => $description,
                                ':price' => $price,
                                ':facilities' => $facilities,
                                ':images' => $image_name,
                                ':status' => $status,
                                ':owner_id' => $owner_id
                            ]);
                            echo '<div class="alert alert-success mt-3">Kost berhasil ditambahkan.</div>';
                            echo '<meta http-equiv="refresh" content="1;url=?page=admin&action=kost">';
                        } else {
                            echo '<div class="alert alert-danger mt-3">Semua field wajib diisi dengan benar dan ID Owner harus valid.</div>';
                        }
                    }
                    ?>
                    <div class="card shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah Kost Baru</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" id="formTambahKost">
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
                                <h6 class="mt-4 mb-3">Informasi Pemilik</h6>
                                <div class="row align-items-end">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">ID Owner</label>
                                        <input type="number" name="owner_id" id="owner_id" class="form-control" required min="1">
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <div id="owner_info" class="ps-3" style="font-size:0.98em;color:#8B4513;"></div>
                                    </div>
                                </div>
                                <button type="submit" name="add_kost" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                                <a href="?page=admin&action=kost" class="btn btn-secondary ms-2">Batal</a>
                            </form>
                        </div>
                    </div>
                    <script>
                    document.getElementById('owner_id').addEventListener('change', function() {
                        var ownerId = this.value;
                        var infoDiv = document.getElementById('owner_info');
                        infoDiv.innerHTML = 'Memuat data owner...';
                        if (ownerId > 0) {
                            fetch('ajax/get_owner_info.php?id=' + ownerId)
                                .then(response => response.json())
                                .then(data => {
                                    if (data && data.full_name) {
                                        infoDiv.innerHTML = '<b>Nama:</b> ' + data.full_name + '<br><b>Email:</b> ' + data.email + '<br><b>Telepon:</b> ' + (data.phone || '-');
                                    } else {
                                        infoDiv.innerHTML = '<span class="text-danger">Owner tidak ditemukan atau bukan role owner.</span>';
                                    }
                                })
                                .catch(() => {
                                    infoDiv.innerHTML = '<span class="text-danger">Gagal mengambil data owner.</span>';
                                });
                        } else {
                            infoDiv.innerHTML = '';
                        }
                    });
                    </script>
                <?php else: ?>
                <div class="d-flex justify-content-end mb-3">
                    <a href="?page=admin&action=kost&add=1" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Kost
                    </a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <?php if ($kost_query->rowCount() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama Kost</th>
                                            <th>Alamat</th>
                                            <th>Harga</th>
                                            <th>Status</th>
                                            <th>Tanggal Ditambahkan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($kost = $kost_query->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td>#<?php echo $kost['id']; ?></td>
                                            <td><?php echo htmlspecialchars($kost['name']); ?></td>
                                            <td><?php echo htmlspecialchars($kost['address']); ?></td>
                                            <td><?php echo number_format($kost['price'], 0, ',', '.'); ?> / bulan</td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="kost_id" value="<?php echo $kost['id']; ?>">
                                                    <select name="new_status" class="form-select form-select-sm" style="width:auto;display:inline-block;">
                                                        <option value="active" <?php echo $kost['status'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                                        <option value="inactive" <?php echo $kost['status'] === 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                                                    </select>
                                                    <button type="submit" name="change_status_kost" class="btn btn-primary btn-sm mt-2">
                                                        <i class="fas fa-sync"></i> Update
                                                    </button>
                                                </form>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($kost['created_at'])); ?></td>
                                            <td>
                                                <!-- Hapus hanya tombol Edit, pertahankan tombol Hapus -->
                                                <a href="?page=admin&action=delete_kost&id=<?php echo $kost['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin hapus kost ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-home fa-3x text-muted mb-3"></i>
                                <h5>Belum Ada Kost</h5>
                                <p class="text-muted">Belum ada kost yang ditambahkan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Inline Edit Kost Form -->
                <?php if ($action === 'edit_kost' && isset($kost_data)): ?>
                <div class="card shadow-sm rounded-4 mb-4 mt-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Kost: <?php echo htmlspecialchars($kost_data['name']); ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Kost</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($kost_data['name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Harga per Bulan</label>
                                    <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($kost_data['price']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($kost_data['address']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($kost_data['description']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fasilitas</label>
                                <input type="text" name="facilities" class="form-control" value="<?php echo htmlspecialchars($kost_data['facilities']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">URL Gambar</label>
                                <input type="url" name="images" class="form-control" value="<?php echo htmlspecialchars($kost_data['images']); ?>">
                            </div>
                            <h6 class="mt-4 mb-3">Informasi Pemilik</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nama Pemilik</label>
                                    <input type="text" name="owner_name" class="form-control" value="<?php echo htmlspecialchars($kost_data['owner_name']); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Telepon Pemilik</label>
                                    <input type="tel" name="owner_phone" class="form-control" value="<?php echo htmlspecialchars($kost_data['owner_phone']); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Email Pemilik</label>
                                    <input type="email" name="owner_email" class="form-control" value="<?php echo htmlspecialchars($kost_data['owner_email']); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?php echo $kost_data['status'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="inactive" <?php echo $kost_data['status'] === 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                                </select>
                            </div>
                            <button type="submit" name="update_kost" class="btn btn-warning"><i class="fas fa-save"></i> Simpan Perubahan</button>
                            <a href="?page=admin&action=kost" class="btn btn-secondary ms-2">Batal</a>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            <?php elseif ($action === 'users'): ?>
                <!-- Kelola Users -->
                <h2 class="mb-4">Kelola Users</h2>
                <?php
                $users_query = $db->query("SELECT * FROM users ORDER BY created_at DESC");
                ?>
                <div class="d-flex justify-content-end mb-3">
                    <a href="?page=admin&action=users&add_user=1" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Tambah User
                    </a>
                </div>
                <?php if (isset($_GET['add_user'])): ?>
                    <div class="card shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Tambah User Baru</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="full_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="phone" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select name="role" class="form-select" required>
                                        <option value="user">User</option>
                                        <option value="owner">Owner</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <button type="submit" name="add_user_submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                                <a href="?page=admin&action=users" class="btn btn-secondary ms-2">Batal</a>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Lengkap</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Telepon</th>
                                        <th>Role</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $users_query->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td>#<?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <!-- Tombol Detail User -->
                                            <a href="?page=admin&action=users&detail_id=<?php echo $user['id']; ?>" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-info-circle"></i> Detail
                                            </a>
                                            <a href="?page=admin&action=delete_user&id=<?php echo $user['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin hapus user ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Modal/Section Detail User -->
                <?php if ($user_detail_data): ?>
                <div class="modal show d-block" tabindex="-1" style="background:rgba(0,0,0,0.3);">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title"><i class="fas fa-user me-2"></i>Detail User: <?php echo htmlspecialchars($user_detail_data['full_name']); ?></h5>
                                <a href="?page=admin&action=users" class="btn-close btn-close-white"></a>
                            </div>
                            <div class="modal-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">ID</dt>
                                    <dd class="col-sm-8">#<?php echo $user_detail_data['id']; ?></dd>

                                    <dt class="col-sm-4">Nama Lengkap</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($user_detail_data['full_name']); ?></dd>

                                    <dt class="col-sm-4">Username</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($user_detail_data['username']); ?></dd>

                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($user_detail_data['email']); ?></dd>

                                    <dt class="col-sm-4">Telepon</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($user_detail_data['phone'] ?? '-'); ?></dd>

                                    <dt class="col-sm-4">Role</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($user_detail_data['role']); ?></dd>

                                    <dt class="col-sm-4">Tanggal Daftar</dt>
                                    <dd class="col-sm-8"><?php echo date('d/m/Y H:i', strtotime($user_detail_data['created_at'])); ?></dd>
                                </dl>
                            </div>
                            <div class="modal-footer">
                                <a href="?page=admin&action=users" class="btn btn-secondary">Tutup</a>
                            </div>
                        </div>
                    </div>
                </div>
                <style>
                    body { overflow: hidden; }
                </style>
                <?php endif; ?>
            <?php elseif ($action === 'reports'): ?>
                <!-- Moderasi Laporan Listing -->
                <h2 class="mb-4">Laporan Listing Palsu/Menyesatkan</h2>
                <?php
                $reports = $db->query("
                    SELECT r.*, k.name as kost_name, u.full_name as reporter
                    FROM kost_reports r
                    JOIN kost k ON r.kost_id = k.id
                    LEFT JOIN users u ON r.user_id = u.id
                    ORDER BY r.created_at DESC
                ");
                ?>
                <div class="card">
                    <div class="card-body">
                        <?php if ($reports->rowCount() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Kost</th>
                                            <th>Pelapor</th>
                                            <th>Alasan</th>
                                            <th>Detail</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($r = $reports->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($r['kost_name']); ?></td>
                                            <td><?php echo htmlspecialchars($r['reporter'] ?? 'Tamu'); ?></td>
                                            <td><?php echo htmlspecialchars($r['reason']); ?></td>
                                            <td><?php echo htmlspecialchars($r['detail']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></td>
                                            <td>
                                                <span class="badge <?php
                                                    if ($r['status'] === 'pending') echo 'bg-warning text-dark';
                                                    elseif ($r['status'] === 'reviewed') echo 'bg-success';
                                                    else echo 'bg-secondary';
                                                ?>">
                                                    <?php echo ucfirst($r['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($r['status'] === 'pending'): ?>
                                                    <a href="?page=admin&action=reports&review=approve&id=<?php echo $r['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Tandai sudah ditinjau?')">
                                                        <i class="fas fa-check"></i> Tinjau
                                                    </a>
                                                    <a href="?page=admin&action=reports&review=dismiss&id=<?php echo $r['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Abaikan laporan ini?')">
                                                        <i class="fas fa-times"></i> Abaikan
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-flag fa-3x text-muted mb-3"></i>
                                <h5>Tidak ada laporan baru.</h5>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($action === 'owner_requests'): ?>
                <h2 class="mb-4">Permintaan Daftar Pemilik Kost</h2>
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($pending_owners)): ?>
                            <div class="text-center text-muted py-4">Tidak ada permintaan baru.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama Lengkap</th>
                                            <th>Email</th>
                                            <th>No. Telepon</th>
                                            <th>KTP</th>
                                            <th>Foto Diri</th>
                                            <th>Tanggal Daftar</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_owners as $req): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($req['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($req['email']); ?></td>
                                            <td><?php echo htmlspecialchars($req['phone']); ?></td>
                                            <td>
                                                <?php if ($req['ktp_image']): ?>
                                                    <a href="<?php echo htmlspecialchars($req['ktp_image']); ?>" target="_blank">Lihat</a>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($req['selfie_image']): ?>
                                                    <a href="<?php echo htmlspecialchars($req['selfie_image']); ?>" target="_blank">Lihat</a>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($req['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="owner_request_id" value="<?php echo $req['id']; ?>">
                                                    <button type="submit" name="approve_owner" class="btn btn-success btn-sm">Konfirmasi</button>
                                                </form>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="owner_request_id" value="<?php echo $req['id']; ?>">
                                                    <button type="submit" name="reject_owner" class="btn btn-danger btn-sm">Tolak</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Kost Modal -->
<div class="modal fade" id="addKostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kost Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
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
                        <input type="text" name="facilities" class="form-control" placeholder="Contoh: WiFi, AC, Kamar Mandi Dalam, Parkir" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL Gambar</label>
                        <input type="url" name="images" class="form-control" placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <h6 class="mt-4 mb-3">Informasi Pemilik</h6>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Pemilik</label>
                            <input type="text" name="owner_name" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Telepon Pemilik</label>
                            <input type="tel" name="owner_phone" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email Pemilik</label>
                            <input type="email" name="owner_email" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_kost" class="btn btn-primary">Tambah Kost</button>
                </div>
            </form>
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