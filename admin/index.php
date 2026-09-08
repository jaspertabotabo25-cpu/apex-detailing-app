<?php
require_once '../config/auth.php';
require_admin();

// Check for low stock inventory
$stmt = $pdo->query("SELECT item_name, quantity FROM inventory WHERE quantity <= low_stock_threshold");
$lowStockItems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Apex Custom Detailing</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .dashboard { display: flex; min-height: 100vh; font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; background: var(--navy); color: var(--white); padding: 30px 20px; flex-shrink: 0; }
        .sidebar h2 { font-family: 'Poppins', sans-serif; font-size: 1.2rem; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
        .sidebar a { display: block; color: #c7cad1; padding: 12px 15px; margin-bottom: 5px; border-radius: 4px; transition: all 0.2s; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: var(--white); }
        .content { flex-grow: 1; padding: 40px; background: #f8fafc; overflow-y: auto; }
        .card { background: var(--white); padding: 25px; border-radius: var(--radius); box-shadow: 0 4px 18px rgba(0,0,0,0.04); margin-bottom: 20px; border: 1px solid #e2e8f0; }
        
        .alert-danger { background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 15px 20px; border-radius: 6px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; font-weight: 500; font-size: 0.95rem; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Apex Admin</h2>
            <a href="index.php" class="active">Dashboard Home</a>
            <a href="schedule.php">Dispatch Schedule</a>
            <a href="appointments.php">Appointments</a>
            <a href="portfolio_upload.php">Portfolio Uploader</a>
            <a href="clients.php">Client Registry</a>
            <a href="inventory.php">Inventory</a>
            <a href="../index.php" style="margin-top: 30px;">← Back to Site</a>
            <a href="../logout.php">Logout</a>
        </div>
        <div class="content">
            <h1 style="font-family: 'Poppins', sans-serif; margin-bottom: 20px; color: #0f172a;">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
            
            <?php if (!empty($lowStockItems)): ?>
            <div class="alert-danger">
                <strong>⚠️ Low Stock Alert:</strong>
                <span>
                    <?php 
                        $items = array_map(function($item) { return htmlspecialchars($item['item_name']) . " (" . $item['quantity'] . " left)"; }, $lowStockItems);
                        echo implode(", ", $items);
                    ?>
                </span>
                <a href="inventory.php" style="margin-left: auto; color: #991b1b; text-decoration: underline;">Manage Inventory</a>
            </div>
            <?php endif; ?>

            <p style="color: #64748b; margin-bottom: 30px;">Select an option from the sidebar to manage the application.</p>
            
            <div class="card">
                <h3 style="margin-top:0; color: #0f172a;">System Status</h3>
                <p style="color: #334155; margin-bottom: 0;">All core modules running normally.</p>
            </div>
        </div>
    </div>
</body>
</html>
