<?php
require_once '../config/auth.php';
require_admin();

// Check for low stock inventory
$stmt = $pdo->query("SELECT item_name, quantity FROM inventory WHERE quantity <= low_stock_threshold");
$lowStockItems = $stmt->fetchAll();

// KPI: Appointments Today
$today = date('Y-m-d');
$stmtToday = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = ?");
$stmtToday->execute([$today]);
$appointmentsToday = $stmtToday->fetchColumn();

// KPI: Pending Dispatches
$stmtPending = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
$pendingDispatches = $stmtPending->fetchColumn();

// KPI: Total Clients
$stmtClients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$totalClients = $stmtClients->fetchColumn();

// Upcoming Appointments
$stmtUpcoming = $pdo->query("SELECT a.id, u.name as client_name, a.service_type, a.appointment_date, a.status 
                             FROM appointments a 
                             JOIN users u ON a.user_id = u.id 
                             WHERE a.appointment_date >= NOW() 
                             ORDER BY a.appointment_date ASC 
                             LIMIT 5");
$upcomingAppointments = $stmtUpcoming->fetchAll();

// Analytics: Service Popularity
$stmtAnalytics = $pdo->query("SELECT service_type, COUNT(*) as count FROM appointments GROUP BY service_type ORDER BY count DESC");
$analyticsData = $stmtAnalytics->fetchAll();
$maxCount = 0;
foreach ($analyticsData as $data) {
    if ($data['count'] > $maxCount) $maxCount = $data['count'];
}
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
        
        /* Dashboard specific styles */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .kpi-card { background: var(--white); padding: 20px; border-radius: var(--radius); box-shadow: 0 2px 10px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; display: flex; flex-direction: column; }
        .kpi-card .value { font-size: 2rem; font-weight: 700; color: var(--navy); margin-bottom: 5px; font-family: 'Poppins', sans-serif; }
        .kpi-card .label { color: #64748b; font-size: 0.9rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .dashboard-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        @media (max-width: 992px) { .dashboard-layout { grid-template-columns: 1fr; } }
        
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .table th { background: #f8fafc; font-weight: 600; color: #475569; font-size: 0.85rem; text-transform: uppercase; }
        .table tr:last-child td { border-bottom: none; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .badge.approved { background: #dcfce7; color: #166534; }
        .badge.cancelled { background: #fee2e2; color: #991b1b; }
        
        .action-buttons { display: flex; flex-direction: column; gap: 10px; }
        .btn-action { display: flex; align-items: center; justify-content: center; padding: 12px; background: var(--navy); color: white; border-radius: 6px; text-decoration: none; font-weight: 500; transition: background 0.2s; }
        .btn-action:hover { background: #1e293b; color: white; }
        .btn-outline { background: transparent; border: 1px solid var(--navy); color: var(--navy); }
        .btn-outline:hover { background: #f8fafc; color: var(--navy); }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Apex Admin</h2>
            <a href="index.php" class="active">Dashboard Home</a>
            <a href="schedule.php">Dispatch Schedule</a>
            <a href="appointments.php">Appointments</a>
            <a href="services.php">Manage Services</a>
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

            <!-- KPI Grid -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <span class="value"><?= $appointmentsToday ?></span>
                    <span class="label">Appointments Today</span>
                </div>
                <div class="kpi-card">
                    <span class="value"><?= $pendingDispatches ?></span>
                    <span class="label">Pending Dispatches</span>
                </div>
                <div class="kpi-card">
                    <span class="value"><?= $totalClients ?></span>
                    <span class="label">Total Clients</span>
                </div>
                <div class="kpi-card" style="<?= count($lowStockItems) > 0 ? 'border-color: #fecaca; background-color: #fef2f2;' : '' ?>">
                    <span class="value" style="<?= count($lowStockItems) > 0 ? 'color: #991b1b;' : '' ?>"><?= count($lowStockItems) ?></span>
                    <span class="label" style="<?= count($lowStockItems) > 0 ? 'color: #991b1b;' : '' ?>">Low Stock Alerts</span>
                </div>
            </div>
            
            <div class="dashboard-layout">
                <!-- Upcoming Appointments -->
                <div class="card" style="margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0; color: #0f172a;">Upcoming Appointments</h3>
                        <a href="appointments.php" style="color: var(--navy); text-decoration: none; font-size: 0.9rem; font-weight: 500;">View All &rarr;</a>
                    </div>
                    
                    <?php if (empty($upcomingAppointments)): ?>
                        <p style="color: #64748b; margin: 0; padding: 20px 0; text-align: center;">No upcoming appointments found.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Service</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingAppointments as $apt): ?>
                                <tr>
                                    <td><?= htmlspecialchars($apt['client_name']) ?></td>
                                    <td><?= htmlspecialchars($apt['service_type']) ?></td>
                                    <td><?= date('M j, Y g:i A', strtotime($apt['appointment_date'])) ?></td>
                                    <td><span class="badge <?= htmlspecialchars($apt['status']) ?>"><?= htmlspecialchars($apt['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Right Column -->
                <div>
                    <!-- Service Analytics (CSS Chart) -->
                    <div class="card" style="margin-bottom: 20px;">
                        <h3 style="margin-top: 0; margin-bottom: 20px; color: #0f172a;">Service Popularity</h3>
                        <?php if (empty($analyticsData)): ?>
                            <p style="color: #64748b;">No data available yet.</p>
                        <?php else: ?>
                            <div class="chart-container">
                                <?php foreach ($analyticsData as $data): ?>
                                    <?php 
                                    $percentage = $maxCount > 0 ? ($data['count'] / $maxCount) * 100 : 0; 
                                    // Make sure even small values have a tiny sliver of width
                                    if ($percentage > 0 && $percentage < 2) $percentage = 2;
                                    ?>
                                    <div class="chart-row">
                                        <div class="chart-label" title="<?= htmlspecialchars($data['service_type']) ?>">
                                            <?= htmlspecialchars($data['service_type']) ?>
                                        </div>
                                        <div class="chart-bar-bg">
                                            <div class="chart-bar-fill" style="width: <?= $percentage ?>%;"></div>
                                        </div>
                                        <div class="chart-value"><?= $data['count'] ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card" style="margin-bottom: 0;">
                        <h3 style="margin-top: 0; margin-bottom: 20px; color: #0f172a;">Quick Actions</h3>
                        <div class="action-buttons">
                            <a href="appointments.php" class="btn-action">+ New Appointment</a>
                            <a href="services.php" class="btn-action btn-outline">+ Manage Services</a>
                            <a href="clients.php" class="btn-action btn-outline">+ Add Client</a>
                            <a href="inventory.php" class="btn-action btn-outline">Update Inventory</a>
                            <a href="portfolio_upload.php" class="btn-action btn-outline">Upload to Portfolio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
