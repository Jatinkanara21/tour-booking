<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Simplified base path. Assumes a flat structure within /admin and /user.
$base_path = isset($base_path) ? $base_path : './';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Tour Booking' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= $base_path ?>index.php">
            <svg width="24" height="24" fill="currentColor" class="bi bi-geo-alt-fill me-2" viewBox="0 0 16 16">
                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
            </svg>
            TourBooking
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><span class="navbar-text me-3">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</span></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/manage_tours.php">Manage Tours</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>admin/manage_bookings.php">Manage Bookings</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>user/index.php">Tours</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>user/my_bookings.php">My Bookings</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path . ($_SESSION['role'] === 'admin' ? 'admin' : 'user') ?>/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="py-4">