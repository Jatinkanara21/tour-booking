<?php
// user/cancel_booking.php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: my_bookings.php");
    exit;
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if the booking belongs to the current user
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

if ($booking) {
    // Update the booking status to 'cancelled'
    $update_stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $update_stmt->execute([$booking_id]);
}

header("Location: my_bookings.php");
exit;
?>