<?php
require_once '../config/auth.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $itemName = trim($_POST['item_name']);
        $qty = max(0, (int)$_POST['quantity']);
        $threshold = max(0, (int)$_POST['threshold']);
        
        if (!empty($itemName)) {
            $stmt = $pdo->prepare("INSERT INTO inventory (item_name, quantity, low_stock_threshold) VALUES (?, ?, ?)");
            $stmt->execute([$itemName, $qty, $threshold]);
            $message = "Item added successfully.";
        }
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        $qty = max(0, (int)$_POST['quantity']);
        $threshold = max(0, (int)$_POST['threshold']);
        
        $stmt = $pdo->prepare("UPDATE inventory SET quantity = ?, low_stock_threshold = ? WHERE id = ?");
        $stmt->execute([$qty, $threshold, $id]);
        $message = "Item updated successfully.";
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Item deleted successfully.";
    }
}

$stmt = $pdo->query("SELECT * FROM inventory ORDER BY item_name ASC");
$inventory = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Management - Apex Admin</title>
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
        
        .form-inline { display: flex; gap: 10px; align-items: center; }
        .form-inline input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-family: inherit; font-size: 0.9rem; }
        .btn-small { padding: 8px 14px; font-size: 0.85rem; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: 600; transition: opacity 0.2s; }
        .btn-small:hover { opacity: 0.9; }
        .btn-add { background: var(--navy); }
        .btn-update { background: #0284c7; padding: 6px 12px; }
        .btn-delete { background: #ef4444; padding: 6px 12px; }
        
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 500; font-size: 0.9rem; }
        
        .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-low { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-ok { background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
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
            <a href="inventory.php" class="active">Inventory</a>
            <a href="../index.php" style="margin-top: 30px;">← Back to Site</a>
            <a href="../logout.php">Logout</a>
        </div>
        
        <div class="content">
            <h1 style="font-family: 'Poppins', sans-serif; margin-bottom: 20px; color: #0f172a;">Inventory Management</h1>
            
            <?php if ($message): ?>
                <div class="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            
            <div class="card">
                <h3 style="margin-top:0; color: var(--navy); margin-bottom: 15px;">Add New Supply</h3>
                <form method="POST" action="" class="form-inline">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="text" name="item_name" placeholder="Item Name (e.g. Carnauba Wax)" required style="width: 250px;">
                    <input type="number" name="quantity" placeholder="Quantity" required min="0" style="width: 100px;">
                    <input type="number" name="threshold" placeholder="Low Stock Threshold" required min="0" style="width: 150px;">
                    <button type="submit" class="btn-small btn-add">Add Item</button>
                </form>
            </div>
            
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Status</th>
                            <th>Quantity</th>
                            <th>Threshold</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): ?>
                        <tr>
                            <td style="font-weight: 500;"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($item['quantity'] <= $item['low_stock_threshold']): ?>
                                    <span class="badge badge-low">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-ok">In Stock</span>
                                <?php endif; ?>
                            </td>
                            <form method="POST" action="" style="margin: 0;">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <td><input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0" style="width:70px; padding: 4px; border: 1px solid #cbd5e1; border-radius:4px;"></td>
                                <td><input type="number" name="threshold" value="<?= $item['low_stock_threshold'] ?>" min="0" style="width:70px; padding: 4px; border: 1px solid #cbd5e1; border-radius:4px;"></td>
                                <td><?= date('M j, Y H:i', strtotime($item['last_updated'])) ?></td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <button type="submit" class="btn-small btn-update">Save</button>
                            </form>
                                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this item?');" style="margin: 0;">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn-small btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($inventory)): ?>
                        <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">No inventory items found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
