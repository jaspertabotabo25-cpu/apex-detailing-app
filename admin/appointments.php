<?php
require_once '../config/auth.php';
require_admin();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['status'])) {
    $valid_statuses = ['pending', 'approved', 'cancelled'];
    if (in_array($_POST['status'], $valid_statuses)) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], (int)$_POST['appointment_id']]);
        header('Location: appointments.php?msg=updated');
        exit;
    }
}

// Fetch all appointments
$stmt = $pdo->query("
    SELECT a.*, u.name as client_name, u.email as client_email 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.appointment_date DESC
");
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Appointments - Apex Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: var(--navy); color: var(--white); padding: 30px 20px; }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
        .sidebar a { display: block; color: #c7cad1; padding: 12px 15px; margin-bottom: 5px; border-radius: 4px; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: var(--white); }
        .content { flex-grow: 1; padding: 40px; background: var(--light); }
        .card { background: var(--white); padding: 25px; border-radius: var(--radius); box-shadow: 0 4px 18px rgba(0,0,0,0.04); margin-bottom: 20px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 15px; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
        th { font-weight: 600; color: var(--navy); }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .action-form { display: inline-flex; gap: 8px; }
        .action-form select { padding: 6px; border: 1px solid var(--border); border-radius: 4px; }
        .action-form button { padding: 6px 12px; background: var(--navy); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Apex Admin</h2>
            <a href="index.php">Dashboard Home</a>
            <a href="appointments.php" class="active">Appointments</a>
            <a href="portfolio_upload.php">Portfolio Uploader</a>
            <a href="clients.php">Client Registry</a>
            <a href="../index.php" style="margin-top: 30px;">← Back to Site</a>
            <a href="../logout.php">Logout</a>
        </div>
        <div class="content">
            <h1>Manage Appointments</h1>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">Appointment status updated successfully.</div>
            <?php endif; ?>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Service Type</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $apt): ?>
                        <tr>
                            <td>#<?= $apt['id'] ?></td>
                            <td><?= htmlspecialchars($apt['client_name']) ?><br><small style="color:var(--gray)"><?= htmlspecialchars($apt['client_email']) ?></small></td>
                            <td><?= htmlspecialchars($apt['service_type']) ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($apt['appointment_date'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $apt['status'] ?>">
                                    <?= htmlspecialchars($apt['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="" class="action-form">
                                    <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                    <select name="status">
                                        <option value="pending" <?= $apt['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="approved" <?= $apt['status'] === 'approved' ? 'selected' : '' ?>>Approve</option>
                                        <option value="cancelled" <?= $apt['status'] === 'cancelled' ? 'selected' : '' ?>>Cancel</option>
                                    </select>
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($appointments)): ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--gray);">No appointments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
