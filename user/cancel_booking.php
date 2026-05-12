<?php
require_once '../config/database.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

$booking_id = $_GET['id'];

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, r.route_id, r.available_seats 
    FROM bookings b
    JOIN routes r ON b.route_id = r.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if ($booking && $booking['status'] == 'confirmed') {
    try {
        $pdo->beginTransaction();
        
        // Update booking status
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled', cancellation_date = NOW(), refund_amount = total_fare * 0.8 WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        // Restore seat
        $stmt = $pdo->prepare("UPDATE routes SET available_seats = available_seats + 1 WHERE id = ?");
        $stmt->execute([$booking['route_id']]);
        
        $pdo->commit();
        $_SESSION['message'] = "Booking cancelled successfully! 80% amount will be refunded.";
    } catch(Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Cancellation failed!";
    }
}

header("Location: my_bookings.php");
exit();
?>