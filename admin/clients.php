<?php
// admin/clients.php
require_once '../config/auth.php';
require_admin();

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    if (isset($_POST['action'], $_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];
        
        // Prevent modifying your own account to avoid lockout
        if ($userId !== (int)$_SESSION['user_id']) {
            if ($_POST['action'] === 'edit_role' && isset($_POST['new_role'])) {
                $newRole = $_POST['new_role'] === 'admin' ? 'admin' : 'user';
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$newRole, $userId]);
                $message = "User role updated successfully.";
            } elseif ($_POST['action'] === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $message = "User deleted successfully.";
            }
        } else {
            $message = "You cannot modify your own account from this view.";
        }
    }
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$params = [];
$query = "SELECT id, name, email, role, created_at FROM users";
$countQuery = "SELECT COUNT(*) FROM users";

// Incorporate search parameters
if ($search !== '') {
    $searchCond = " WHERE name LIKE ? OR email LIKE ?";
    $query .= $searchCond;
    $countQuery .= $searchCond;
    $params = ["%$search%", "%$search%"];
}

// Calculate total pages
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($params);
$totalRecords = $stmtCount->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Fetch paginated results
$query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$clients = $stmt->fetchAll();

$pageTitle = 'Client Registry - Apex Admin';
$extraCSS = '
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.search-form { display: flex; gap: 8px; }
.search-form input { padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; width: 300px; font-family: inherit; outline: none; }
.search-form input:focus { border-color: var(--navy); }
.search-form button { padding: 10px 16px; background: var(--navy); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-family: \'Inter\', sans-serif; transition: background 0.2s; }
.search-form button:hover { background: #1f232c; }
.badge-admin { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.badge-user { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.actions-cell { display: flex; gap: 10px; align-items: center; }
.action-form { display: flex; gap: 5px; align-items: center; margin: 0; }
.action-form select { padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; }
.btn-small { padding: 6px 12px; font-size: 0.8rem; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: 600; transition: opacity 0.2s; }
.btn-small:hover { opacity: 0.9; }
.btn-update { background: #0284c7; }
.btn-delete { background: #ef4444; }
.pagination { display: flex; gap: 5px; margin-top: 25px; justify-content: flex-end; }
.pagination a, .pagination span { padding: 8px 14px; border: 1px solid #cbd5e1; border-radius: 4px; text-decoration: none; color: #334155; font-size: 0.9rem; background: white; transition: all 0.2s; }
.pagination a:hover { background: #f8fafc; border-color: #94a3b8; }
.pagination .active { background: var(--navy); color: white; border-color: var(--navy); }
.alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 500; font-size: 0.9rem; }
';
require_once 'includes/admin_header.php';
?>

<h1 style="font-family: 'Poppins', sans-serif; margin-bottom: 20px; color: #0f172a;">Client Registry</h1>

<?php if ($message): ?>
    <div class="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="toolbar">
    <form method="GET" action="" class="search-form">
        <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Search</button>
        <?php if ($search): ?>
            <a href="clients.php" style="text-decoration: none; color: #64748b; margin-left: 10px; align-self: center; font-size: 0.9rem;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
            <tr>
                <td>#<?= $client['id'] ?></td>
                <!-- Sanitized output to prevent XSS -->
                <td style="font-weight: 500;"><?= htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($client['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <!-- Role Badge -->
                    <span class="badge <?= $client['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                        <?= htmlspecialchars($client['role'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
                <td><?= date('M j, Y', strtotime($client['created_at'])) ?></td>
                <td>
                    <div class="actions-cell">
                        <!-- View Profile Button -->
                        <a href="client_profile.php?id=<?= $client['id'] ?>" class="btn-small" style="background: #475569; text-decoration: none;">Profile</a>
                        
                        <!-- Edit Role Form -->
                        <form method="POST" action="" class="action-form">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="edit_role">
                            <input type="hidden" name="user_id" value="<?= $client['id'] ?>">
                            <select name="new_role">
                                <option value="user" <?= $client['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $client['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <button type="submit" class="btn-small btn-update">Update</button>
                        </form>
                        
                        <!-- Delete Form -->
                        <form method="POST" action="" class="action-form" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?= $client['id'] ?>">
                            <button type="submit" class="btn-small btn-delete">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($clients)): ?>
            <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">No clients found matching your criteria.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">&laquo; Prev</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
</div>

<?php require_once 'includes/admin_footer.php'; ?>
