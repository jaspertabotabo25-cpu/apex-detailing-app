<?php
// admin/api/update_status.php
require_once '../../config/auth.php';

// Strict Role-Based Access Control (RBAC)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = $_POST['appointment_id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    $valid_statuses = ['pending', 'approved', 'cancelled'];
    
    if (empty($appointmentId) || empty($status)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
        exit;
    }
    
    if (!in_array($status, $valid_statuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid status.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$status, (int)$appointmentId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            // It could be that the status was already set to the target value
            echo json_encode(['success' => true, 'message' => 'No changes made or appointment not found.']);
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
