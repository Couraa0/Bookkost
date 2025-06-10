<?php
// pages/profile.php
if (!isLoggedIn()) {
    header("Location: ?page=home");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile update
if (isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Check if email is already taken by another user
    $check_email = $db->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
    $check_email->execute([':email' => $email, ':user_id' => $user_id]);
    
    if ($check_email->rowCount() > 0) {
        $error_message = "Email sudah digunakan oleh user lain.";
    } else {
        $update_stmt = $db->prepare("UPDATE users SET full_name = :full_name, email = :email, phone = :phone WHERE id = :user_id");
        
        if ($update_stmt->execute([
            ':full_name' => $full_name,
            ':email' => $email,
            ':phone' => $phone,
            ':user_id' => $user_id
        ])) {
            // Update session data
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $success_message = "Profil berhasil diperbarui.";
        } else {
            $error_message = "Gagal memperbarui profil.";
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current user data
    $user_stmt = $db->prepare("SELECT password FROM users WHERE id = :user_id");
    $user_stmt->execute([':user_id' => $user_id]);
    $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!password_verify($current_password, $user_data['password'])) {
        $password_error = "Password saat ini tidak benar.";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "Password baru dan konfirmasi password tidak cocok.";
    } elseif (strlen($new_password) < 6) {
        $password_error = "Password baru minimal 6 karakter.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_password = $db->prepare("UPDATE users SET password = :password WHERE id = :user_id");
        
        if ($update_password->execute([':password' => $hashed_password, ':user_id' => $user_id])) {
            $password_success = "Password berhasil diubah.";
        } else {
            $password_error = "Gagal mengubah password.";
        }
    }
}

// Handle delete account
if (isset($_POST['delete_account']) && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'YA') {
    // Hapus data user (dan opsional: booking, review, favorit, dll)
    $delete_user = $db->prepare("DELETE FROM users WHERE id = :user_id");
    $delete_user->execute([':user_id' => $user_id]);
    // Hapus session dan redirect ke home/login
    session_destroy();
    echo "<script>alert('Akun Anda berhasil dihapus.');window.location.href='?page=home';</script>";
    exit();
}

// Get current user data
$user_stmt = $db->prepare("SELECT * FROM users WHERE id = :user_id");
$user_stmt->execute([':user_id' => $user_id]);
$user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container py-5">
    <div class="row">
        <!-- Edit Profil & Ubah Password (Kiri) -->
        <div class="col-lg-8">
            <h2 class="mb-4 text-center">Edit Profil</h2>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> Informasi Pribadi
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
                                <small class="text-muted">Username tidak dapat diubah</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Telepon</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Bergabung</label>
                            <input type="text" class="form-control" value="<?php echo date('d F Y', strtotime($user_data['created_at'])); ?>" disabled>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lock"></i> Ubah Password
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($password_success)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $password_success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($password_error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $password_error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Password Saat Ini</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" minlength="6" required>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="fas fa-key"></i> Ubah Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Statistik Akun (Kanan, lebih ke bawah) -->
        <div class="col-lg-4 mb-4 d-flex align-items-start">
            <div class="w-100" style="margin-top: 65px;">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i> Riwayat Booking
                        </h5>
                    </div>
                    <div class="card-body" style="max-height:400px;overflow-y:auto;">
                        <?php
                        // Ambil 5 riwayat booking terakhir user
                        $booking_stmt = $db->prepare("
                            SELECT b.*, k.name as kost_name 
                            FROM bookings b
                            JOIN kost k ON b.kost_id = k.id
                            WHERE b.user_id = :user_id
                            ORDER BY b.created_at DESC
                            LIMIT 5
                        ");
                        $booking_stmt->execute([':user_id' => $user_id]);
                        $bookings = $booking_stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Hitung total pengeluaran dari semua booking selesai
                        $spent_stmt = $db->prepare("SELECT SUM(total_price) as total_spent FROM bookings WHERE user_id = :user_id AND status = 'completed'");
                        $spent_stmt->execute([':user_id' => $user_id]);
                        $total_spent = $spent_stmt->fetchColumn();
                        ?>
                        <?php if ($bookings && count($bookings) > 0): ?>
                            <ul class="list-group mb-3">
                                <?php foreach ($bookings as $b): ?>
                                    <li class="list-group-item d-flex flex-column align-items-start">
                                        <div class="w-100">
                                            <strong><?php echo htmlspecialchars($b['kost_name']); ?></strong>
                                            <span class="badge 
                                                <?php
                                                    if ($b['status'] == 'pending') echo 'bg-warning text-dark';
                                                    elseif ($b['status'] == 'confirmed') echo 'bg-success';
                                                    elseif ($b['status'] == 'completed') echo 'bg-info text-dark';
                                                    elseif ($b['status'] == 'cancelled') echo 'bg-danger';
                                                    else echo 'bg-secondary';
                                                ?>">
                                                <?php echo ucfirst($b['status']); ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('d M Y', strtotime($b['created_at'])); ?> | 
                                            Rp <?php echo number_format($b['total_price'], 0, ',', '.'); ?>
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center text-muted mb-3">Belum ada riwayat booking.</div>
                        <?php endif; ?>
                        <hr>
                        <div class="text-center">
                            <h6 class="text-muted mb-1">Total Pengeluaran Selesai</h6>
                            <h4 class="text-success mb-0">Rp <?php echo number_format($total_spent, 0, ',', '.'); ?></h4>
                        </div>
                    </div>
                </div>
                <!-- Hapus Akun -->
                <div class="card mt-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="fas fa-user-times"></i> Hapus Akun</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" onsubmit="return confirm('Yakin ingin menghapus akun Anda? Tindakan ini tidak dapat dibatalkan!');">
                            <div class="mb-2">
                                <label class="form-label text-danger">Ketik <b>YA</b> untuk konfirmasi hapus akun:</label>
                                <input type="text" name="confirm_delete" class="form-control" placeholder="Ketik YA untuk konfirmasi" required>
                            </div>
                            <button type="submit" name="delete_account" class="btn btn-danger w-100">
                                <i class="fas fa-user-times"></i> Hapus Akun Saya
                            </button>
                        </form>
                        <small class="text-muted d-block mt-2">Akun dan data Anda akan dihapus secara permanen.</small>
                    </div>
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