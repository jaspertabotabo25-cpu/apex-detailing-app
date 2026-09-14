<?php
// config/db.php

// Global Exception Handler to prevent exposing raw PHP errors
set_exception_handler(function ($e) {
    // Log the error securely (In production, ensure error_log writes to a secure file)
    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    // Check if the request is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'An internal server error occurred. Please try again later.']);
        exit;
    }

    // Display user-friendly HTML error message
    http_response_code(500);
    echo "<!DOCTYPE html><html lang='en'><head><title>System Error</title><style>body{font-family:'Inter', sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;background:#f8fafc;color:#334155;margin:0;} .error-box{background:white;padding:40px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.08);text-align:center;max-width:400px;} h1{color:#ef4444;margin-top:0;font-size:1.5rem;} p{color:#64748b;line-height:1.5;margin-bottom:20px;}</style></head><body><div class='error-box'><h1>Oops! Something went wrong.</h1><p>We're experiencing technical difficulties. Our team has been notified.</p><a href='/APEX Webpage/index.php' style='display:inline-block;padding:10px 20px;background:#0f172a;color:white;text-decoration:none;border-radius:6px;font-weight:600;'>Return to Homepage</a></div></body></html>";
    exit;
});

$host = '127.0.0.1'; // Use IP to avoid DNS resolution delays on localhost
$db   = 'apex_detailing';
$user = 'root';      // Default XAMPP MySQL user
$pass = '';          // Default XAMPP MySQL password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Configure PDO for strict error handling and security
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements to prevent injection
];

// No try-catch needed here because the global exception handler will catch it
$pdo = new PDO($dsn, $user, $pass, $options);
?>
