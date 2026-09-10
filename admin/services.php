<?php
require_once '../config/auth.php';

// Strict Role-Based Access Control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && !empty(trim($_POST['name']))) {
            $name = trim($_POST['name']);
            $stmt = $pdo->prepare("INSERT INTO services (name, is_active) VALUES (?, 1)");
            $stmt->execute([$name]);
            $message = "Service added successfully.";
        } elseif ($_POST['action'] === 'toggle' && isset($_POST['service_id'])) {
            $id = (int)$_POST['service_id'];
            $stmt = $pdo->prepare("UPDATE services SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Service status updated.";
        } elseif ($_POST['action'] === 'delete' && isset($_POST['service_id'])) {
            $id = (int)$_POST['service_id'];
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Service deleted successfully.";
        }
    }
}

// Fetch all services
$stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Services - Apex Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .dashboard { display: flex; min-height: 100vh; font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; background: var(--navy); color: var(--white); padding: 30px 20px; flex-shrink: 0; }
        .sidebar h2 { font-family: 'Poppins', sans-serif; font-size: 1.2rem; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
        .sidebar a { display: block; color: #c7cad1; padding: 12px 15px; margin-bottom: 5px; border-radius: 4px; transition: all 0.2s; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: var(--white); }
        
        .content { flex-grow: 1; padding: 40px; background: #f8fafc; overflow-y: auto; }
        .card { background: var(--white); padding: 25px; border-radius: var(--radius); box-shadow: 0 4px 18px rgba(0,0,0,0.04); margin-bottom: 20px; border: 1px solid #e2e8f0; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 14px 15px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; color: #334155; }
        th { font-weight: 600; color: #0f172a; border-bottom: 2px solid #e2e8f0; }
        
        .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .badge-active { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-inactive { background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        
        .actions-cell { display: flex; gap: 8px; align-items: center; }
        .action-form { margin: 0; }
        .btn-small { padding: 6px 12px; font-size: 0.8rem; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: 600; transition: opacity 0.2s; }
        .btn-small:hover { opacity: 0.9; }
        .btn-toggle { background: #f59e0b; }
        .btn-delete { background: #ef4444; }
        
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 500; font-size: 0.9rem; }
        
        .add-form { display: flex; gap: 10px; align-items: center; margin-bottom: 20px; }
        .add-form input { padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; width: 300px; outline: none; }
        .add-form button { padding: 10px 16px; background: var(--navy); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Apex Admin</h2>
            <a href="index.php">Dashboard Home</a>
            <a href="appointments.php">Appointments</a>
            <a href="services.php" class="active">Manage Services</a>
            <a href="portfolio_upload.php">Portfolio Uploader</a>
            <a href="clients.php">Client Registry</a>
            <a href="../index.php" style="margin-top: 30px;">← Back to Site</a>
            <a href="../logout.php">Logout</a>
        </div>
        
        <div class="content">
            <h1 style="font-family: 'Poppins', sans-serif; margin-bottom: 20px; color: #0f172a;">Manage Services</h1>
            
            <?php if ($message): ?>
                <div class="alert"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <div class="card">
                <h3>Add New Service</h3>
                <form method="POST" action="" class="add-form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="text" name="name" placeholder="e.g. Ceramic Coating" required>
                    <button type="submit">+ Add Service</button>
                </form>
            </div>
            
            <div class="card">
                <h3>Current Services</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service Name</th>
                            <th>Status</th>
                            <th>Added On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                        <tr>
                            <td>#<?= $service['id'] ?></td>
                            <td style="font-weight: 500;"><?= htmlspecialchars($service['name']) ?></td>
                            <td>
                                <span class="badge <?= $service['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= $service['is_active'] ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y', strtotime($service['created_at'])) ?></td>
                            <td>
                                <div class="actions-cell">
                                    <form method="POST" action="" class="action-form">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                        <button type="submit" class="btn-small btn-toggle">
                                            <?= $service['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="" class="action-form" onsubmit="return confirm('Delete this service permanently?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                        <button type="submit" class="btn-small btn-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($services)): ?>
                        <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 30px;">No services defined.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
