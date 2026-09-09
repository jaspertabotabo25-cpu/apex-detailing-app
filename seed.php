<?php
require_once 'c:/xampp/htdocs/APEX Webpage/config/db.php';

try {
    // Add missing columns if they don't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM appointments LIKE 'duration_minutes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE appointments ADD COLUMN duration_minutes INT DEFAULT 60");
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM appointments LIKE 'buffer_minutes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE appointments ADD COLUMN buffer_minutes INT DEFAULT 30");
    }
    
    // Create a dummy user if none exists besides admin
    $pdo->exec("INSERT IGNORE INTO users (id, name, email, password_hash, role) VALUES (2, 'John Client', 'john@example.com', 'dummy', 'user')");
    $pdo->exec("INSERT IGNORE INTO users (id, name, email, password_hash, role) VALUES (3, 'Jane Customer', 'jane@example.com', 'dummy', 'user')");

    // Generate mock appointments for the next 7 days
    $services = ['Standard Wash', 'Custom Detail', 'Moto Custom & Detail'];
    $today = new DateTime('today');
    
    $insert = $pdo->prepare("INSERT INTO appointments (user_id, service_type, appointment_date, status, duration_minutes, buffer_minutes, phone, location) VALUES (?, ?, ?, 'approved', ?, ?, '1234567890', '123 Main St')");
    
    for ($i = 0; $i < 7; $i++) {
        $day = (clone $today)->modify("+$i days");
        
        // Add 2 appointments per day
        $date1 = (clone $day)->setTime(9, 0)->format('Y-m-d H:i:s');
        $insert->execute([2, $services[array_rand($services)], $date1, 90, 30]);
        
        $date2 = (clone $day)->setTime(14, 30)->format('Y-m-d H:i:s');
        $insert->execute([3, $services[array_rand($services)], $date2, 120, 45]);
    }
    
    echo "Seed successful!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
