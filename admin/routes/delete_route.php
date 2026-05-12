<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
redirectIfNotAdmin();

$route_id = $_GET['id'];

// Check if route has bookings
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE route_id = ?");
$stmt->execute([$route_id]);
$booking_count = $stmt->fetchColumn();

if($booking_count > 0) {
    $_SESSION['error'] = "Cannot delete route with existing bookings!";
} else {
    $stmt = $pdo->prepare("DELETE FROM routes WHERE id = ?");
    $stmt->execute([$route_id]);
    $_SESSION['success'] = "Route deleted successfully!";
}

header("Location: manage_routes.php");
exit();
?>