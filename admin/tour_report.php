<?php
require '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$tour_id = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;

if (!$tour_id) {
    header('Location: manage_tours.php?err=not_found');
    exit;
}

// Fetch tour details
$stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
$stmt->execute([$tour_id]);
$tour = $stmt->fetch();

if (!$tour) {
    header('Location: manage_tours.php?err=not_found');
    exit;
}

// Fetch bookings and passengers for this tour
$sql = "SELECT 
            b.id as booking_id,
            b.booking_date,
            b.created_at as booking_time,
            u.name as user_name,
            p.name as passenger_name,
            p.age as passenger_age,
            p.gender as passenger_gender
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN passengers p ON b.id = p.booking_id
        WHERE b.tour_id = ? AND b.status = 'confirmed'
        ORDER BY b.booking_date DESC, p.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$tour_id]);
$report_data = $stmt->fetchAll();

$page_title = 'Tour Report: ' . htmlspecialchars($tour['title']);
$base_path = '../';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Tour Report</h2>
            <p class="text-muted">For: <strong><?= htmlspecialchars($tour['title']) ?></strong></p>
        </div>
        <a href="manage_tours.php" class="btn btn-outline-secondary">Back to Tours</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Confirmed Bookings Report</h5>
        </div>
        <div class="card-body">
            <?php if (empty($report_data)): ?>
                <div class="alert alert-info">No confirmed bookings found for this tour.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Passenger Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Booked By</th>
                                <th>Booking Date</th>
                                <th>Booking Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data as $i => $row): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($row['passenger_name']) ?></td>
                                    <td><?= htmlspecialchars($row['passenger_age']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($row['passenger_gender'])) ?></td>
                                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                                    <td><?= date("d M, Y", strtotime($row['booking_date'])) ?></td>
                                    <td><?= date("h:i A", strtotime($row['booking_time'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer text-end">
            <button onclick="window.print()" class="btn btn-primary">Print Report</button>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
