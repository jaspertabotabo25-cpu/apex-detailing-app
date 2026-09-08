<?php
require_once '../config/auth.php';
require_admin();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: clients.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_vehicle') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF validation failed.');
    }
    
    $make = trim($_POST['make']);
    $model = trim($_POST['model']);
    $color = trim($_POST['color']);
    $plate = trim($_POST['license_plate']);
    
    if ($make && $model && $color) {
        $stmt = $pdo->prepare("INSERT INTO vehicles (user_id, make, model, color, license_plate) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $make, $model, $color, $plate]);
        $message = "Vehicle added successfully.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();
if (!$client) {
    die('Client not found.');
}

$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$vehicles = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date DESC");
$stmt->execute([$id]);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Profile - Apex Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .dashboard { display: flex; min-height: 100vh; font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; background: var(--navy); color: var(--white); padding: 30px 20px; flex-shrink: 0; }
        .sidebar h2 { font-family: 'Poppins', sans-serif; font-size: 1.2rem; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
        .sidebar a { display: block; color: #c7cad1; padding: 12px 15px; margin-bottom: 5px; border-radius: 4px; transition: all 0.2s; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: var(--white); }
        
        .content { flex-grow: 1; padding: 40px; background: #f8fafc; overflow-y: auto; }
        .card { background: var(--white); padding: 25px; border-radius: var(--radius); box-shadow: 0 4px 18px rgba(0,0,0,0.04); margin-bottom: 20px; border: 1px solid #e2e8f0; }
        
        h1, h2, h3 { color: #0f172a; font-family: 'Poppins', sans-serif; }
        
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 14px 15px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; color: #334155; }
        th { font-weight: 600; color: #0f172a; border-bottom: 2px solid #e2e8f0; }
        
        .form-group { margin-bottom: 15px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit; }
        .btn { padding: 10px 16px; background: var(--navy); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background 0.2s; width: 100%; }
        .btn:hover { background: #1f232c; }
        
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 500; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Apex Admin</h2>
            <a href="index.php">Dashboard Home</a>
            <a href="schedule.php">Dispatch Schedule</a>
            <a href="appointments.php">Appointments</a>
            <a href="portfolio_upload.php">Portfolio Uploader</a>
            <a href="clients.php">Client Registry</a>
            <a href="inventory.php">Inventory</a>
            <a href="../index.php" style="margin-top: 30px;">← Back to Site</a>
            <a href="../logout.php">Logout</a>
        </div>
        
        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1><?= htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8') ?>'s Profile</h1>
                <a href="clients.php" style="color: #64748b; text-decoration: none; font-weight: 600;">&larr; Back to Clients</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            
            <div class="grid">
                <!-- Vehicles -->
                <div>
                    <div class="card">
                        <h3 style="margin-top:0;">Registered Vehicles</h3>
                        <?php if (empty($vehicles)): ?>
                            <p style="color: #64748b; font-size: 0.9rem;">No vehicles registered yet.</p>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Make</th>
                                        <th>Model</th>
                                        <th>Color</th>
                                        <th>Plate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehicles as $v): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($v['make'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($v['model'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($v['color'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($v['license_plate'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card">
                        <h3 style="margin-top:0;">Add Vehicle</h3>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="add_vehicle">
                            <div class="form-group"><input type="text" name="make" placeholder="Make (e.g. Toyota)" required></div>
                            <div class="form-group"><input type="text" name="model" placeholder="Model (e.g. Camry)" required></div>
                            <div class="form-group"><input type="text" name="color" placeholder="Color" required></div>
                            <div class="form-group"><input type="text" name="license_plate" placeholder="License Plate (Optional)"></div>
                            <button type="submit" class="btn">Add Vehicle</button>
                        </form>
                    </div>
                </div>
                
                <!-- Appointments History -->
                <div>
                    <div class="card">
                        <h3 style="margin-top:0;">Service History</h3>
                        <?php if (empty($appointments)): ?>
                            <p style="color: #64748b; font-size: 0.9rem;">No service history.</p>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Job Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $app): ?>
                                    <tr>
                                        <td><?= date('M j, Y', strtotime($app['appointment_date'])) ?></td>
                                        <td><?= htmlspecialchars($app['service_type'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars(ucfirst($app['status']), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><a href="job_execution.php?id=<?= $app['id'] ?>" style="color: #0284c7; text-decoration: none; font-weight:600;">View Job</a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
