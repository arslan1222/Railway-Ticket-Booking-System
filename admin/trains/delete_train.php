<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
redirectIfNotAdmin();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No train ID specified!";
    header("Location: manage_trains.php");
    exit();
}

$train_id = $_GET['id'];

try {
    // Check if train has routes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM routes WHERE train_id = ?");
    $stmt->execute([$train_id]);
    $route_count = $stmt->fetchColumn();

    if($route_count > 0) {
        $_SESSION['error'] = "Cannot delete train with existing routes! Please delete all routes for this train first.";
        header("Location: manage_trains.php");
        exit();
    }
    
    // Check if train has bookings (through routes)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM bookings b 
        JOIN routes r ON b.route_id = r.id 
        WHERE r.train_id = ?
    ");
    $stmt->execute([$train_id]);
    $booking_count = $stmt->fetchColumn();
    
    if($booking_count > 0) {
        $_SESSION['error'] = "Cannot delete train with existing bookings!";
        header("Location: manage_trains.php");
        exit();
    }
    
    // Get train image
    $stmt = $pdo->prepare("SELECT train_image FROM trains WHERE id = ?");
    $stmt->execute([$train_id]);
    $train = $stmt->fetch();
    
    // Delete image file if exists
    if($train && $train['train_image'] && file_exists('../../assets/uploads/trains/' . $train['train_image'])) {
        unlink('../../assets/uploads/trains/' . $train['train_image']);
    }
    
    // Delete train
    $stmt = $pdo->prepare("DELETE FROM trains WHERE id = ?");
    $stmt->execute([$train_id]);
    
    $_SESSION['success'] = "Train deleted successfully!";
    
} catch(PDOException $e) {
    $_SESSION['error'] = "Failed to delete train: " . $e->getMessage();
}

header("Location: manage_trains.php");
exit();
?>