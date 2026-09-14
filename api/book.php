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
    // CSRF Validation
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf_token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid security token. Please refresh and try again.']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    
    // Sanitize inputs
    $name = htmlspecialchars(trim($_POST['b_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars(trim($_POST['b_phone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $service = htmlspecialchars(trim($_POST['b_service'] ?? ''), ENT_QUOTES, 'UTF-8');
    $date = trim($_POST['b_date'] ?? ''); // DateTime format, no HTML special chars needed
    $location = htmlspecialchars(trim($_POST['b_location'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    // Basic validation
    if (empty($phone) || empty($service) || empty($date) || empty($location)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        exit;
    }
    
    // Note: The global exception handler in db.php handles PDOExceptions gracefully.
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
    
    // Update user profile if phone or address is missing
    $stmtUpdate = $pdo->prepare("
        UPDATE users 
        SET phone = COALESCE(NULLIF(phone, ''), ?), 
            address = COALESCE(NULLIF(address, ''), ?) 
        WHERE id = ?
    ");
    $stmtUpdate->execute([$phone, $location, $userId]);
    
    // Update session so it autofills next time
    if (empty($_SESSION['phone'])) $_SESSION['phone'] = $phone;
    if (empty($_SESSION['address'])) $_SESSION['address'] = $location;

    echo json_encode(['success' => true]);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
}
?>
