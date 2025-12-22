<?php
// index.php - Smart Entry Point
session_start();
require 'includes/db.php';

// === ROUTING LOGIC ===
if (isset($_SESSION['user_id'])) {
    // Logged-in users
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/index.php");
    }
} else {
    // Not logged in → show welcome page with login/register options
    $page_title = "Welcome to TourBooking";
    $base_path = '';
    include 'includes/header.php';
    ?>

    <div class="container mt-5 text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="display-4 mb-4" style="color:#00ff88;">Welcome to <span style="color:#00c853;">TourBooking</span></h1>
                <p class="lead mb-5">Discover amazing tours, book your adventure in seconds!</p>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100 p-4 text-center">
                            <div class="mb-3">
                                <svg width="60" height="60" fill="#00c853" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M11.354 4.646a.5.5 0 0 0-.708 0l-3.5 3.5-1.5-1.5a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l4-4a.5.5 0 0 0 0-.708z"/>
                                </svg>
                            </div>
                            <h4>Already have an account?</h4>
                            <p>Log in to book your next adventure.</p>
                            <a href="login.php" class="btn btn-primary btn-lg">Login Now</a>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 p-4 text-center">
                            <div class="mb-3">
                                <svg width="60" height="60" fill="#00c853" viewBox="0 0 16 16">
                                    <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zM4.5 7.5a.5.5 0 0 1 0-1h5.793l-2.147-2.146a.5.5 0 0 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5z"/>
                                </svg>
                            </div>
                            <h4>New here?</h4>
                            <p>Create an account and start exploring!</p>
                            <a href="register.php" class="btn btn-outline-light btn-lg">Register Free</a>
                        </div>
                    </div>
                </div>

                
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <?php
    exit;
}
?>
