<?php
// admin/services.php
require_once '../config/auth.php';
require_admin();

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

// Fetch all services using prepared statement
$stmt = $pdo->prepare("SELECT * FROM services ORDER BY created_at DESC");
$stmt->execute();
$services = $stmt->fetchAll();

$pageTitle = 'Manage Services - Apex Admin';
$extraCSS = '
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
';
require_once 'includes/admin_header.php';
?>

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

<?php require_once 'includes/admin_footer.php'; ?>
