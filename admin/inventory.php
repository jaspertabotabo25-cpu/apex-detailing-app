<?php
// admin/inventory.php
require_once '../config/auth.php';
require_admin();

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

$stmt = $pdo->prepare("SELECT * FROM inventory ORDER BY item_name ASC");
$stmt->execute();
$inventory = $stmt->fetchAll();

$pageTitle = 'Inventory Management - Apex Admin';
$extraCSS = '
.form-inline { display: flex; gap: 10px; align-items: center; }
.form-inline input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-family: inherit; font-size: 0.9rem; }
.btn-small { padding: 8px 14px; font-size: 0.85rem; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: 600; transition: opacity 0.2s; }
.btn-small:hover { opacity: 0.9; }
.btn-add { background: var(--navy); }
.btn-update { background: #0284c7; padding: 6px 12px; }
.btn-delete { background: #ef4444; padding: 6px 12px; }
.badge-low { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
.badge-ok { background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 500; font-size: 0.9rem; }
';
require_once 'includes/admin_header.php';
?>

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

<?php require_once 'includes/admin_footer.php'; ?>
