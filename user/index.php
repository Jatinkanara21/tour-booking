<?php
require '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') { header('Location: ../login.php'); exit; }

// Fetch tours with search
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM tours";
$params = [];
if ($search) {
    $sql .= " WHERE title LIKE ? OR description LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tours = $stmt->fetchAll();
?>

<?php $page_title = 'Available Tours'; $base_path = '../'; include '../includes/header.php'; ?>

<div class="container mt-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0">Explore Our Tours</h2>
        </div>
        <div class="col-md-6">
            <form method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search for tours..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($tours)): ?>
        <div class="alert alert-info text-center">
            <h4 class="alert-heading">No Tours Found</h4>
            <p>We couldn't find any tours matching your search. Try a different keyword or check back later!</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($tours as $tour): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm tour-card">
                        <?php if ($tour['image'] && file_exists("../assets/img/" . $tour['image'])): ?>
                            <img src="../assets/img/<?= htmlspecialchars($tour['image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($tour['title']) ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x200/333/ccc?text=No+Image" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($tour['title']) ?>">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($tour['title']) ?></h5>
                            <p class="card-text text-muted small flex-grow-1"><?= substr(htmlspecialchars($tour['description']), 0, 100) ?>...</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <p class="mb-0"><strong>Duration:</strong> <?= htmlspecialchars($tour['duration']) ?></p>
                                <p class="mb-0 fs-5 text-primary fw-bold">$<?= number_format($tour['price'], 2) ?></p>
                            </div>
                            <a href="book_tour.php?id=<?= $tour['id'] ?>" class="btn btn-primary w-100 stretched-link">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.tour-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.tour-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
}
</style>

<?php include '../includes/footer.php'; ?>