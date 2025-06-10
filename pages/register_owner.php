<?php
// Halaman daftar pemilik kost
if (isLoggedIn()) {
    header("Location: ?page=dashboard");
    exit();
}

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $ktp_image = '';
    $selfie_image = '';

    // Upload KTP
    if (isset($_FILES['ktp_image']) && $_FILES['ktp_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../img/ktp/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = pathinfo($_FILES['ktp_image']['name'], PATHINFO_EXTENSION);
        $ktp_name = 'ktp_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        move_uploaded_file($_FILES['ktp_image']['tmp_name'], $upload_dir . $ktp_name);
        $ktp_image = 'img/ktp/' . $ktp_name;
    }
    // Upload selfie
    if (isset($_FILES['selfie_image']) && $_FILES['selfie_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../img/selfie/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = pathinfo($_FILES['selfie_image']['name'], PATHINFO_EXTENSION);
        $selfie_name = 'selfie_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        move_uploaded_file($_FILES['selfie_image']['tmp_name'], $upload_dir . $selfie_name);
        $selfie_image = 'img/selfie/' . $selfie_name;
    }

    // Validasi sederhana
    if ($full_name && $username && $email && $phone && $password && $ktp_image && $selfie_image) {
        // Simpan ke tabel owner_requests
        $stmt = $db->prepare("INSERT INTO owner_requests (full_name, username, email, phone, password, ktp_image, selfie_image, status, created_at) VALUES (:full_name, :username, :email, :phone, :password, :ktp_image, :selfie_image, 'pending', NOW())");
        $stmt->execute([
            ':full_name' => $full_name,
            ':username' => $username,
            ':email' => $email,
            ':phone' => $phone,
            ':password' => $password,
            ':ktp_image' => $ktp_image,
            ':selfie_image' => $selfie_image
        ]);
        $success = "Pendaftaran berhasil! Permintaan Anda akan diverifikasi oleh admin. Silakan tunggu notifikasi selanjutnya.";
    } else {
        $error = "Semua field dan dokumen wajib diisi.";
    }
}
?>
<div class="container py-5">
    <h2 class="mb-4 text-center">Daftar Sebagai Pemilik Kost</h2>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <div class="card mx-auto" style="max-width:500px;">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
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
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Foto KTP</label>
                    <input type="file" name="ktp_image" class="form-control" accept="image/*" required>
                    <small class="text-muted">Format JPG/PNG. Pastikan data KTP jelas terbaca.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Foto Diri dengan KTP</label>
                    <input type="file" name="selfie_image" class="form-control" accept="image/*" required>
                    <small class="text-muted">Foto selfie sambil memegang KTP.</small>
                </div>
                <button type="submit" class="btn btn-success w-100">Daftar & Ajukan Verifikasi</button>
            </form>
        </div>
    </div>
    <div class="mt-4 text-center text-muted" style="font-size:0.95em;">
        Setelah mendaftar, admin akan memverifikasi data Anda. Jika disetujui, Anda dapat login sebagai pemilik kost dan menambah/mengelola kost Anda.
    </div>
</div>
