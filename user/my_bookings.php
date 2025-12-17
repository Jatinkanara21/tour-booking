<?php
// user/my_bookings.php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT b.*, t.title as tour_title, t.image as tour_image
    FROM bookings b
    JOIN tours t ON b.tour_id = t.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php $page_title = 'My Bookings'; $base_path = '../'; include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">My Bookings</h2>

    <?php if (empty($bookings)): ?>
        <div class="alert alert-info text-center">
            <h4 class="alert-heading">No Bookings Yet!</h4>
            <p>You haven't booked any tours. It's time for a new adventure!</p>
            <a href="index.php" class="btn btn-primary">Explore Tours</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($bookings as $b): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <?php if ($b['tour_image'] && file_exists("../assets/img/" . $b['tour_image'])): ?>
                                    <img src="../assets/img/<?= htmlspecialchars($b['tour_image']) ?>" class="img-fluid rounded-start h-100" style="object-fit: cover;" alt="Tour Image">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/150x180/333/ccc?text=No+Image" class="img-fluid rounded-start h-100" style="object-fit: cover;" alt="Tour Image">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0"><?= htmlspecialchars($b['tour_title']) ?></h5>
                                        <?php
                                            $status_map = [
                                                'pending' => 'warning',
                                                'confirmed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $status_color = $status_map[$b['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $status_color ?> fs-6"><?= ucfirst($b['status']) ?></span>
                                    </div>
                                    <p class="card-text mb-1"><strong>Travel Date:</strong> <?= date('d M, Y', strtotime($b['booking_date'])) ?></p>
                                    <p class="card-text mb-1"><strong>Persons:</strong> <?= $b['adult_count'] + $b['child_count'] ?></p>
                                    <p class="card-text mb-3"><strong>Total:</strong> <span class="fw-bold text-primary">$<?= number_format($b['total_price'], 2) ?></span></p>
                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Booked on: <?= date('d M, Y', strtotime($b['created_at'])) ?></small>
                                        <?php if ($b['status'] !== 'cancelled' && $b['status'] !== 'confirmed'): ?>
                                            <a href="cancel_booking.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>