<?php
// admin/client_profile.php
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

$pageTitle = 'Client Profile - Apex Admin';
$extraCSS = '
.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-group { margin-bottom: 15px; }
.form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit; }
.btn { padding: 10px 16px; background: var(--navy); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background 0.2s; width: 100%; }
.btn:hover { background: #1f232c; }
.alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 500; font-size: 0.9rem; }
';
require_once 'includes/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 style="color: #0f172a; font-family: 'Poppins', sans-serif; margin: 0;"><?= htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8') ?>'s Profile</h1>
    <a href="clients.php" style="color: #64748b; text-decoration: none; font-weight: 600;">&larr; Back to Clients</a>
</div>

<?php if ($message): ?>
    <div class="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="grid">
    <!-- Vehicles -->
    <div>
        <div class="card">
            <h3 style="margin-top:0; color: #0f172a; font-family: 'Poppins', sans-serif;">Registered Vehicles</h3>
            <?php if (empty($vehicles)): ?>
                <p style="color: #64748b; font-size: 0.9rem;">No vehicles registered yet.</p>
            <?php else: ?>
                <table class="table">
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
            <h3 style="margin-top:0; color: #0f172a; font-family: 'Poppins', sans-serif;">Add Vehicle</h3>
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
            <h3 style="margin-top:0; color: #0f172a; font-family: 'Poppins', sans-serif;">Service History</h3>
            <?php if (empty($appointments)): ?>
                <p style="color: #64748b; font-size: 0.9rem;">No service history.</p>
            <?php else: ?>
                <table class="table">
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

<?php require_once 'includes/admin_footer.php'; ?>
