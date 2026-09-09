<?php
// api/book.php
require_once '../config/auth.php';

// Ensure the user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'You must be logged in to book an appointment.']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    
    // In a real application, you might use the submitted name to update the user's profile,
    // but here we just need phone, service, date, and location for the appointment.
    $name = $_POST['b_name'] ?? '';
    $phone = $_POST['b_phone'] ?? '';
    $service = $_POST['b_service'] ?? '';
    $date = $_POST['b_date'] ?? '';
    $location = $_POST['b_location'] ?? '';
    
    // Basic validation
    if (empty($phone) || empty($service) || empty($date) || empty($location)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        exit;
    }
    
    try {
        // Secure PDO insertion
        $stmt = $pdo->prepare("
            INSERT INTO appointments (user_id, service_type, appointment_date, phone, location, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $userId,
            $service,
            $date,
            $phone,
            $location
        ]);
        
        echo json_encode(['success' => true]);
        
    } catch (\PDOException $e) {
        http_response_code(500);
        // Do not expose database details in production
        echo json_encode(['success' => false, 'error' => 'A database error occurred while saving your appointment.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
}
?>
