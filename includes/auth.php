<?php
// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $query = "SELECT id, username, email, password, full_name, role FROM users WHERE username = :username OR email = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Tanpa hash, bandingkan langsung
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            // Redirect admin ke halaman admin, owner ke dashboard, user ke bookings/dashboard
            if ($user['role'] === 'admin') {
                header("Location: ?page=admin");
            } elseif ($user['role'] === 'owner') {
                header("Location: ?page=dashboard");
            } else {
                header("Location: ?page=bookings");
            }
            exit();
        }
    }
    $login_error = "Username atau password salah!";
}

// Handle register
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Simpan langsung tanpa hash
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $query = "INSERT INTO users (username, email, password, full_name, phone) VALUES (:username, :email, :password, :full_name, :phone)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':phone', $phone);
    if ($stmt->execute()) {
        $register_success = "Registrasi berhasil! Silakan login.";
    } else {
        $register_error = "Registrasi gagal! Username atau email sudah digunakan.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ?page=home");
    exit();
}
