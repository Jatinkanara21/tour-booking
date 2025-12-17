<?php
require '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit; }

// Update booking status
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    $allowed_actions = ['confirm', 'cancel'];

    if (in_array($action, $allowed_actions)) {
        $status = ($action === 'confirm') ? 'confirmed' : 'cancelled';
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        header("Location: manage_bookings.php?msg=$action");
        exit;
    }
}

// Fetch bookings with search and filter
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? 'all';

$sql = "SELECT b.*, u.name as user_name, t.title as tour_title 
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN tours t ON b.tour_id = t.id";

$params = [];
$where_clauses = [];

if ($search) {
    $where_clauses[] = "(u.name LIKE ? OR t.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_status !== 'all') {
    $where_clauses[] = "b.status = ?";
    $params[] = $filter_status;
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Feedback messages
$msg = '';
if(isset($_GET['msg'])) {
    $msg_map = ['confirmed' => 'Booking confirmed!', 'cancelled' => 'Booking cancelled!'];
    $msg = $msg_map[$_GET['msg']] ?? '';
}
?>

<?php $page_title = 'Manage Bookings'; $base_path = '../'; include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Manage Bookings</h2>

    <?php if ($msg): ?><div class="alert alert-success alert-dismissible fade show"><?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <!-- Search and Filter Form -->
    <div class="card p-3 mb-4 shadow-sm">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search by user or tour..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="all" <?= ($filter_status == 'all') ? 'selected' : '' ?>>All Statuses</option>
                    <option value="pending" <?= ($filter_status == 'pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= ($filter_status == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                    <option value="cancelled" <?= ($filter_status == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>

    <!-- Bookings List -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Tour Title</th>
                <th>User Name</th>
                <th>Passengers</th>
                <th>Booking Date</th>
                <th>Persons</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Booked On</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="10" class="text-center">No bookings found matching your criteria.</td>
                </tr>
            <?php else: ?>
                <?php foreach($bookings as $b): ?>
                    <?php
                    // Fetch passengers for this booking
                    $stmt_passengers = $pdo->prepare("SELECT * FROM passengers WHERE booking_id = ?");
                    $stmt_passengers->execute([$b['id']]);
                    $passengers = $stmt_passengers->fetchAll();
                    ?>
                    <tr>
                        
                        <td><a href="tour_report.php?tour_id=<?= $b['tour_id'] ?>"><?= htmlspecialchars($b['tour_title']) ?></a></td>
                        <td><?= htmlspecialchars($b['user_name']) ?></td>
                        <td>
                            <?php if (empty($passengers)): ?>
                                <span class="text-muted">No details</span>
                            <?php else: ?>
                                <?php foreach ($passengers as $p): ?>
                                    <div class="small">
                                        - <strong><?= htmlspecialchars($p['name']) ?></strong> 
                                        (<?= (int)$p['age'] ?>, <?= htmlspecialchars(ucfirst($p['gender'])) ?>)
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td><?= date("d M, Y", strtotime($b['booking_date'])) ?></td>
                        <td><?= $b['adult_count'] + $b['child_count'] ?></td>
                        <td>$<?= number_format($b['total_price'], 2) ?></td>
                        <td>
                            <?php
                                $status_map = [
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $status_color = $status_map[$b['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $status_color ?>"><?= ucfirst($b['status']) ?></span>
                        </td>
                        <td>
                            <?php if($b['status'] === 'pending' || $b['status'] === 'confirmed'): ?>
                                <?php if($b['status'] === 'pending'): ?>
                                    <a href="?action=confirm&id=<?= $b['id'] ?>" class="btn btn-success btn-sm me-2">Confirm</a>
                                <?php endif; ?>
                                <a href="?action=cancel&id=<?= $b['id'] ?>" class="btn btn-danger btn-sm">Cancel</a>
                            <?php endif; ?>
                        </td>
                        <td><?= date("d M, Y, h:i A", strtotime($b['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>