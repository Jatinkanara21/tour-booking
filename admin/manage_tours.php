<?php
// admin/manage_tours.php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$msg = $err = '';

// === GET TOUR TO EDIT ===
$edit_tour = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$id]);
    $edit_tour = $stmt->fetch();
}

// === DELETE TOUR ===
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $tour = $pdo->query("SELECT image FROM tours WHERE id = $id")->fetch();
    if ($tour && $tour['image'] && file_exists("../assets/img/" . $tour['image'])) {
        unlink("../assets/img/" . $tour['image']);
    }
    $pdo->prepare("DELETE FROM tours WHERE id = ?")->execute([$id]);
    header("Location: manage_tours.php?msg=deleted");
    exit;
}

// === ADD/UPDATE TOUR ===
if ($_POST) {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $duration = trim($_POST['duration']);
    $current_image = $_POST['current_image'] ?? '';

    // Handle Image Upload
    $image_name = $current_image;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../assets/img/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $original_name = basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $image_name = "tour_" . time() . "." . $imageFileType;
        $target_file = $target_dir . $image_name;

        $valid_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($imageFileType, $valid_types)) {
            $err = "Invalid image format. Only JPG, PNG, GIF, WEBP allowed.";
        } elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
            $err = "Image is too large (max 5MB).";
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // New image uploaded, delete old one if it exists
                if ($current_image && file_exists($target_dir . $current_image)) {
                    unlink($target_dir . $current_image);
                }
            } else {
                $err = "Failed to upload image.";
                $image_name = $current_image; // Keep old image on failure
            }
        }
    }

    if (!$err) {
        if ($id) { // Update
            $stmt = $pdo->prepare("UPDATE tours SET title=?, description=?, price=?, duration=?, image=? WHERE id=?");
            $stmt->execute([$title, $description, $price, $duration, $image_name, $id]);
            header("Location: manage_tours.php?msg=updated");
        } else { // Add
            $stmt = $pdo->prepare("INSERT INTO tours (title, description, price, duration, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $price, $duration, $image_name]);
            header("Location: manage_tours.php?msg=added");
        }
        exit;
    }
}

// === FETCH TOURS ===
$tours = $pdo->query("SELECT * FROM tours ORDER BY created_at DESC")->fetchAll();

// Display feedback messages
if(isset($_GET['msg'])) {
    $msg_map = [
        'added' => 'Tour added successfully!',
        'updated' => 'Tour updated successfully!',
        'deleted' => 'Tour deleted!'
    ];
    $msg = $msg_map[$_GET['msg']] ?? '';
}
?>

<?php $page_title = 'Manage Tours'; $base_path = '../'; include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Manage Tours</h2>

    <?php if ($msg): ?><div class="alert alert-success alert-dismissible fade show"><?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger alert-dismissible fade show"><?= $err ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <!-- Add/Edit Tour Form -->
    <div class="card p-4 mb-5 shadow-sm">
        <h4 class="mb-3"><?= $edit_tour ? 'Edit Tour' : 'Add New Tour' ?></h4>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $edit_tour['id'] ?? '' ?>">
            <input type="hidden" name="current_image" value="<?= $edit_tour['image'] ?? '' ?>">

            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($edit_tour['title'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Price ($)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($edit_tour['price'] ?? '') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Duration</label>
                <input type="text" name="duration" class="form-control" value="<?= htmlspecialchars($edit_tour['duration'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($edit_tour['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <small class="text-muted">Max 5MB. JPG, PNG, GIF, WebP. Leave blank to keep current image.</small>
                <?php if ($edit_tour && $edit_tour['image']): ?>
                    <div class="mt-2">
                        <img src="../assets/img/<?= $edit_tour['image'] ?>" width="100" class="rounded">
                        <p class="d-inline-block ms-2 text-muted small">Current Image</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-end">
                <?php if ($edit_tour): ?>
                    <a href="manage_tours.php" class="btn btn-secondary me-2">Cancel Edit</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary"><?= $edit_tour ? 'Update Tour' : 'Add Tour' ?></button>
            </div>
        </form>
    </div>

    <!-- Tours List -->
    <h4 class="mb-3">Existing Tours</h4>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tours)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No tours found.</td></tr>
                <?php else: ?>
                    <?php foreach ($tours as $t): ?>
                        <tr>
                            <td>
                                <?php if ($t['image'] && file_exists("../assets/img/" . $t['image'])): ?>
                                    <img src="../assets/img/<?= htmlspecialchars($t['image']) ?>" width="80" height="60" class="rounded" style="object-fit: cover;">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/80x60/333/ccc?text=No+Image" width="80" height="60" class="rounded" style="object-fit: cover;">
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($t['title']) ?></strong></td>
                            <td>$<?= number_format($t['price'], 2) ?></td>
                            <td><?= htmlspecialchars($t['duration']) ?></td>
                            <td class="text-end">
                                <a href="tour_report.php?tour_id=<?= $t['id'] ?>" class="btn btn-outline-info btn-sm">Report</a>
                                <a href="?edit=<?= $t['id'] ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                <a href="?delete=<?= $t['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this tour?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>