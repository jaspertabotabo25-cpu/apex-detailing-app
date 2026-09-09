<?php
require_once 'c:/xampp/htdocs/APEX Webpage/config/db.php';

try {
    // Delete the mock users I created (IDs 2 and 3). 
    // This will automatically delete their mock appointments due to ON DELETE CASCADE.
    $pdo->exec("DELETE FROM users WHERE id IN (2, 3)");
    
    echo "Mock users and their appointments deleted successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
