<?php
// admin/index.php
require_once '../config/auth.php';
require_admin();

// Check for low stock inventory
$stmt = $pdo->prepare("SELECT item_name, quantity FROM inventory WHERE quantity <= low_stock_threshold");
$stmt->execute();
$lowStockItems = $stmt->fetchAll();

// KPI: Appointments Today
$today = date('Y-m-d');
$stmtToday = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = ?");
$stmtToday->execute([$today]);
$appointmentsToday = $stmtToday->fetchColumn();

// KPI: Pending Dispatches
$stmtPending = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
$stmtPending->execute();
$pendingDispatches = $stmtPending->fetchColumn();

// KPI: Total Clients
$stmtClients = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user'");
$stmtClients->execute();
$totalClients = $stmtClients->fetchColumn();

// Upcoming Appointments
$stmtUpcoming = $pdo->prepare("SELECT a.id, u.name as client_name, a.service_type, a.appointment_date, a.status 
                             FROM appointments a 
                             JOIN users u ON a.user_id = u.id 
                             WHERE a.appointment_date >= NOW() 
                             ORDER BY a.appointment_date ASC 
                             LIMIT 5");
$stmtUpcoming->execute();
$upcomingAppointments = $stmtUpcoming->fetchAll();

// Analytics: Service Popularity
$stmtAnalytics = $pdo->prepare("SELECT service_type, COUNT(*) as count FROM appointments GROUP BY service_type ORDER BY count DESC");
$stmtAnalytics->execute();
$analyticsData = $stmtAnalytics->fetchAll();
$maxCount = 0;
foreach ($analyticsData as $data) {
    if ($data['count'] > $maxCount) $maxCount = $data['count'];
}

$pageTitle = 'Admin Dashboard - Apex Custom Detailing';
require_once 'includes/admin_header.php';
?>

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

<?php require_once 'includes/admin_footer.php'; ?>
