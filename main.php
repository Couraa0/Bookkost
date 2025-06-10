<?php
ob_start(); // Tambahkan ini di baris paling atas sebelum output apapun
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Handle login, register, logout
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookKost - Sistem Booking Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --cream: #F5F5DC;
            --light-brown: #D2B48C;
            --medium-brown: #CD853F;
            --dark-brown: #8B4513;
            --darker-brown: #654321;
        }

        body {
            background-color: var(--cream);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            /* Gradien warna lebih modern dan efek glassmorphism */
            background: linear-gradient(90deg, #8B4513 0%, #CD853F 60%, #D2B48C 100%);
            box-shadow: 0 4px 24px rgba(139,69,19,0.10), 0 1.5px 0 rgba(205,133,63,0.08);
            backdrop-filter: blur(6px);
            border-bottom: 2px solid rgba(255,255,255,0.08);
        }
        .navbar .nav-link, .navbar .navbar-brand {
            color: #fff !important;
            text-shadow: 0 1px 6px rgba(0,0,0,0.10);
            letter-spacing: 0.5px;
            transition: color 0.2s;
        }
        .navbar .nav-link:hover, .navbar .nav-link.active {
            color: #ffe4b5 !important;
            text-shadow: 0 2px 8px rgba(139,69,19,0.15);
        }
        .navbar .dropdown-menu {
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(139,69,19,0.13);
            border: none;
            background: rgba(255,255,255,0.97);
        }
        .navbar .dropdown-item {
            color: #654321;
            font-weight: 500;
            border-radius: 8px;
            transition: background 0.2s, color 0.2s;
        }
        .navbar .dropdown-item:hover, .navbar .dropdown-item:focus {
            background: #CD853F;
            color: #fff;
        }
        .navbar .navbar-brand img {
            filter: drop-shadow(0 2px 8px rgba(139,69,19,0.10));
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--medium-brown), var(--dark-brown));
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--dark-brown), var(--darker-brown));
            transform: translateY(-2px);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--light-brown), var(--medium-brown));
            border-radius: 15px 15px 0 0 !important;
            color: white;
            font-weight: bold;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--medium-brown), var(--dark-brown));
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }

        .search-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .kost-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .kost-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .price-tag {
            background: var(--dark-brown);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--medium-brown), var(--dark-brown));
            color: white;
        }

        .form-control:focus {
            border-color: var(--medium-brown);
            box-shadow: 0 0 0 0.2rem rgba(205, 133, 63, 0.25);
        }

        .table-striped > tbody > tr:nth-of-type(odd) > td {
            background-color: rgba(245, 245, 220, 0.5);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-pending { background-color: #ffc107; color: #000; }
        .status-confirmed { background-color: #28a745; color: #fff; }
        .status-cancelled { background-color: #dc3545; color: #fff; }
        .status-completed { background-color: #17a2b8; color: #fff; }

        .sidebar {
            background: linear-gradient(180deg, var(--light-brown), var(--medium-brown));
            min-height: 100vh;
            padding-top: 20px;
        }

        .sidebar .nav-link {
            color: var(--darker-brown);
            font-weight: 500;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--dark-brown);
            color: white;
            transform: translateX(5px);
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid"><!-- ubah dari .container ke .container-fluid -->
        <a class="navbar-brand d-flex align-items-center" href="?page=home">
            <img src="img/Logo1.png" alt="BookKost Logo" style="height:60px;vertical-align:middle;">
            <span class="ms-2 fw-bold" style="font-size:1.5rem;letter-spacing:1px;">BookKost</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="?page=home" style="color:#fff;font-size:1rem;font-weight:bold;">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=search" style="color:#fff;font-size:1rem;font-weight:bold;">Cari Kost</a>
                </li>
                <?php if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="?page=favorit" style="color:#fff;font-size:1rem;font-weight:bold;">
                        Favorit
                    </a>
                </li>
                <?php endif; ?>
                <?php if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="?page=bookings" style="color:#fff;font-size:1rem;font-weight:bold;">Booking Saya</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="?page=about" style="color:#fff;font-size:1rem;font-weight:bold;">Tentang Kami</a>
                </li>
                <?php if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'owner'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="?page=dashboard" style="color:#fff;font-size:1rem;font-weight:bold;">Dashboard</a>
                </li>
                <?php endif; ?>
                <?php if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="?page=admin" style="color:#fff;font-size:1rem;font-weight:bold;">Admin</a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" style="color:#fff;font-size:1rem;font-weight:bold;">
                            <i class="fas fa-user"></i> <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Profil'; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?page=profile">Profil</a></li>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                                <li><a class="dropdown-item" href="?page=bookings">Booking Saya</a></li>
                            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="?page=admin">Admin</a></li>
                            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'owner'): ?>
                                <li><a class="dropdown-item" href="?page=dashboard">Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="?logout=1">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item me-2">
                        <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#loginModal" style="font-weight:bold;">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#registerModal" style="font-weight:bold;">
                            <i class="fas fa-user-plus"></i> Daftar
                        </button>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php
// Route to appropriate page
switch($page) {
    case 'home':
        include 'pages/home.php';
        break;
    case 'search':
        include 'pages/search.php';
        break;
    case 'favorit':
        // Hanya user yang bisa akses favorit
        if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
            include 'pages/favorit.php';
        } else {
            header("Location: ?page=login");
            exit();
        }
        break;
    case 'dashboard':
        // Hanya owner yang bisa akses dashboard
        if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'owner') {
            include 'pages/dashboard.php';
        } else {
            header("Location: ?page=home");
            exit();
        }
        break;
    case 'admin':
        // Hanya admin yang bisa akses halaman admin
        if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            include 'pages/admin.php';
        } else {
            header("Location: ?page=home");
            exit();
        }
        break;
    case 'bookings':
        // Hanya user (bukan owner/admin) yang bisa booking
        if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
            include 'pages/bookings.php';
        } else {
            header("Location: ?page=home");
            exit();
        }
        break;
    case 'profile':
        if (isLoggedIn()) {
            include 'pages/profil.php'; // ubah dari 'pages/profile.php' ke 'pages/profil.php'
        } else {
            header("Location: ?page=home");
            exit();
        }
        break;
    case 'kost_detail':
        include 'pages/kost_detail.php';
        break;
    case 'owner_manage':
        // Hanya owner yang bisa akses halaman ini
        if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'owner') {
            include 'pages/owner_manage.php';
        } else {
            header("Location: ?page=home");
            exit();
        }
        break;
    case 'about':
        include 'pages/about.php';
        break;
    case 'payment':
        include 'pages/payment.php';
        break;
    case 'privacy_policy':
        include 'pages/privacy_policy.php';
        break;
    default:
        include 'pages/home.php';
}
ob_end_flush(); // Tambahkan ini sebelum tag </html> atau di akhir file
?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Login</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php if (isset($login_error)): ?>
                        <div class="alert alert-danger"><?php echo $login_error; ?></div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Username atau Email</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column gap-2">
                    <button type="submit" name="login" class="btn btn-primary w-100 mb-1">Login</button>
                    <button type="button" class="btn w-100 mb-1" style="background: linear-gradient(135deg, #CD853F, #8B4513); color: #fff;" disabled>
                        <i class="fab fa-google"></i> Login dengan Google
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Akun Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php if (isset($register_error)): ?>
                        <div class="alert alert-danger"><?php echo $register_error; ?></div>
                    <?php endif; ?>
                    <?php if (isset($register_success)): ?>
                        <div class="alert alert-success"><?php echo $register_success; ?></div>
                    <?php endif; ?>
                    
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
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="register" class="btn btn-primary">Daftar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>