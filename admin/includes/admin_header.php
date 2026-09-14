<?php
// admin/includes/admin_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get current file name for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Dashboard - Apex Custom Detailing') ?></title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
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
        
        /* Toast Notification */
        .toast { position: fixed; bottom: 20px; right: 20px; background: #334155; color: white; padding: 12px 24px; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-size: 0.9rem; font-weight: 500; transform: translateY(100px); opacity: 0; transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55); z-index: 1000; pointer-events: none; }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.success { border-left: 4px solid #10b981; }
        .toast.error { border-left: 4px solid #ef4444; }

        <?= $extraCSS ?? '' ?>
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Apex Admin</h2>
            <a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Dashboard Home</a>
            <a href="schedule.php" class="<?= $current_page === 'schedule.php' ? 'active' : '' ?>">Dispatch Schedule</a>
            <a href="appointments.php" class="<?= $current_page === 'appointments.php' ? 'active' : '' ?>">Appointments</a>
            <a href="services.php" class="<?= $current_page === 'services.php' ? 'active' : '' ?>">Manage Services</a>
            <a href="portfolio_upload.php" class="<?= $current_page === 'portfolio_upload.php' ? 'active' : '' ?>">Portfolio Uploader</a>
            <a href="clients.php" class="<?= $current_page === 'clients.php' ? 'active' : '' ?>">Client Registry</a>
            <a href="inventory.php" class="<?= $current_page === 'inventory.php' ? 'active' : '' ?>">Inventory</a>
            <a href="../index.php" style="margin-top: 30px;">&larr; Back to Site</a>
            <a href="../logout.php">Logout</a>
        </div>
        <div class="content">
