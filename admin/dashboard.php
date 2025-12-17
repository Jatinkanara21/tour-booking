<?php
require '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit; }

// Fetch statistics
$total_tours = $pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
?>

<?php $page_title = 'Admin Dashboard'; $base_path = '../'; include '../includes/header.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Admin Dashboard</h2>
        <span class="text-muted">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</span>
    </div>

    <!-- Dashboard Stats -->
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-center p-3 h-100">
                <h4 class="display-4"><?= $total_tours ?></h4>
                <p class="text-muted mb-0">Total Tours</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3 h-100">
                <h4 class="display-4"><?= $total_bookings ?></h4>
                <p class="text-muted mb-0">Total Bookings</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3 h-100">
                <h4 class="display-4"><?= $pending_bookings ?></h4>
                <p class="text-muted mb-0">Pending Bookings</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3 h-100">
                <h4 class="display-4"><?= $total_users ?></h4>
                <p class="text-muted mb-0">Registered Users</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-5 text-center">
        <h4 class="mb-3">Quick Actions</h4>
        <a href="manage_tours.php" class="btn btn-primary btn-lg me-2">Manage Tours</a>
        <a href="manage_bookings.php" class="btn btn-warning btn-lg">Manage Bookings</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>