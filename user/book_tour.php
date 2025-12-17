<?php
// user/book_tour.php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Get tour ID
$tour_id = (int)($_GET['id'] ?? 0);
if ($tour_id <= 0) {
    $err = "Invalid tour ID.";
} else {
    // === FETCH TOUR SAFELY ===
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$tour_id]);
    $tour = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tour) {
        $err = "Tour not found.";
    }
}

// === PROCESS BOOKING ===
$msg = $err = '';
if ($_POST) {
    $adult_count = (int)$_POST['adult_count'];
    $child_count = (int)$_POST['child_count'];
    $booking_date = $_POST['booking_date'];
    $passengers = $_POST['passengers'] ?? [];

    $total_persons = $adult_count + $child_count;

    if ($total_persons < 1) {
        $err = "Number of persons must be at least 1.";
    } elseif (strtotime($booking_date) < strtotime('today')) {
        $err = "Booking date cannot be in the past.";
    } elseif (count($passengers) !== $total_persons) {
        $err = "Passenger details are incomplete.";
    } else {
        // Validate passenger data
        $valid = true;
        foreach ($passengers as $passenger) {
            if (empty($passenger['name']) || !isset($passenger['age']) || !isset($passenger['gender'])) {
                $valid = false;
                break;
            }
        }
        if (!$valid) {
            $err = "All passenger details must be filled.";
        } else {
            $total_price = $tour['price'] * $total_persons;

            // Insert booking
            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, tour_id, booking_date, adult_count, child_count, total_price)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $tour_id,
                $booking_date,
                $adult_count,
                $child_count,
                $total_price
            ]);
            $booking_id = $pdo->lastInsertId();

            // Insert passengers
            $stmt_passenger = $pdo->prepare("
                INSERT INTO passengers (booking_id, name, age, gender, type) 
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($passengers as $index => $passenger) {
                $type = $index < $adult_count ? 'adult' : 'child';
                $stmt_passenger->execute([
                    $booking_id,
                    $passenger['name'],
                    (int)$passenger['age'],
                    $passenger['gender'],
                    $type
                ]);
            }

            $msg = "Booking successful! <a href='my_bookings.php'>View My Bookings</a>";
        }
    }
}

// === SET DEFAULT IMAGE PATH ===
$image_path = 'https://via.placeholder.com/300x200/333/fff?text=No+Image';
if ($tour['image'] && file_exists("../assets/img/" . basename($tour['image']))) {
    $image_path = "../assets/img/" . htmlspecialchars(basename($tour['image']));
}
?>

<?php $page_title = 'Book Tour'; $base_path = '../'; include '../includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm p-4">
                <!-- Tour Image + Details -->
                <div class="row g-4 mb-4">
                    <div class="col-md-5">
                        <img src="<?= $image_path ?>" 
                             class="img-fluid rounded shadow-sm" 
                             style="height:220px; width:100%; object-fit:cover;"
                             alt="<?= htmlspecialchars($tour['title']) ?>">
                    </div>
                    <div class="col-md-7">
                        <h3 class="mb-3"><?= htmlspecialchars($tour['title']) ?></h3>
                        <p class="text-muted mb-2">
                            <strong>Duration:</strong> <?= htmlspecialchars($tour['duration']) ?>
                        </p>
                        <p class="mb-2">
                            <strong>Price per person:</strong> 
                            <span class="text-success fw-bold">$<?= number_format($tour['price'], 2) ?></span>
                        </p>
                        <p class="text-secondary small">
                            <?= nl2br(htmlspecialchars($tour['description'])) ?>
                        </p>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Messages -->
                <?php if ($msg): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $msg ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($err): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $err ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Booking Form -->
                <form method="POST" class="needs-validation" novalidate>
                    <noscript>
                        <div class="alert alert-warning">
                            JavaScript is required to fill out passenger details. Please enable it in your browser.
                        </div>
                    </noscript>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Number of Adults</label>
                            <input type="number" name="adult_count" min="0" value="1" class="form-control form-control-lg"
                                   required oninput="updateTotal(); generatePassengerFields()">
                            <div class="invalid-feedback">Please enter number of adults.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Number of Children</label>
                            <input type="number" name="child_count" min="0" value="0" class="form-control form-control-lg"
                                   required oninput="updateTotal(); generatePassengerFields()">
                            <div class="invalid-feedback">Please enter number of children.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Travel Date</label>
                            <input type="date" name="booking_date" class="form-control form-control-lg"
                                   min="<?= date('Y-m-d') ?>" required>
                            <div class="invalid-feedback">Please select a valid date.</div>
                        </div>
                    </div>

                    <!-- Passenger Details -->
                    <div id="passenger-details" class="mt-4">
                        <!-- Dynamic fields will be inserted here -->
                    </div>

                    <div class="mt-4 p-3 bg-light rounded">
                        <h5 class="mb-0">
                            Total Amount:
                            <span id="total-price" class="text-primary fw-bold">
                                $<?= number_format($tour['price'], 2) ?>
                            </span>
                        </h5>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">
                        Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Total Update and Dynamic Fields -->
<script>
function updateTotal() {
    const price = <?= $tour['price'] ?>;
    const adultCount = parseInt(document.querySelector('[name="adult_count"]').value) || 0;
    const childCount = parseInt(document.querySelector('[name="child_count"]').value) || 0;
    const totalPersons = adultCount + childCount;
    document.getElementById('total-price').textContent = '$' + (price * totalPersons).toFixed(2);
}

function generatePassengerFields() {
    const adultCount = parseInt(document.querySelector('[name="adult_count"]').value) || 0;
    const childCount = parseInt(document.querySelector('[name="child_count"]').value) || 0;
    const totalPersons = adultCount + childCount;
    const container = document.getElementById('passenger-details');
    container.innerHTML = '';

    if (totalPersons === 0) return;

    container.innerHTML = '<h5 class="mb-3">Passenger Details</h5>';

    for (let i = 0; i < totalPersons; i++) {
        const type = i < adultCount ? 'Adult' : 'Child';
        const typeClass = i < adultCount ? 'text-primary' : 'text-success';
        container.innerHTML += `
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title ${typeClass}">${type} ${i + 1}</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Name</label>
                            <input type="text" name="passengers[${i}][name]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Age</label>
                            <input type="number" name="passengers[${i}][age]" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="passengers[${i}][gender]" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

// Initialize fields on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTotal();
    generatePassengerFields();
});

// Bootstrap form validation
(() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php include '../includes/footer.php'; ?>