<?php
// api/cancel_booking.php
require_once '../config/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'You must be logged in.']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $appointmentId = $_POST['appointment_id'] ?? '';
    
    if (empty($appointmentId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Appointment ID is required.']);
        exit;
    }
    
    try {
        // Only allow cancelling if it belongs to the user and is still pending
        $stmt = $pdo->prepare("
            UPDATE appointments 
            SET status = 'cancelled' 
            WHERE id = ? AND user_id = ? AND status = 'pending'
        ");
        
        $stmt->execute([$appointmentId, $userId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Booking could not be cancelled. It may have already been approved or it does not exist.']);
        }
        
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'A database error occurred.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
}
?>
