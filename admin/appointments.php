<?php
require_once '../config/auth.php';
require_admin();

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
        .action-form select { padding: 6px; border: 1px solid var(--border); border-radius: 4px; }
        
        /* Toast Notification */
        .toast { position: fixed; bottom: 20px; right: 20px; background: #334155; color: white; padding: 12px 24px; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-size: 0.9rem; font-weight: 500; transform: translateY(100px); opacity: 0; transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55); z-index: 1000; pointer-events: none; }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.success { border-left: 4px solid #10b981; }
        .toast.error { border-left: 4px solid #ef4444; }
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

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Contact & Location</th>
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
                            <td>
                                <div style="white-space: nowrap;"><small>📞 <?= htmlspecialchars($apt['phone'] ?? 'N/A') ?></small></div>
                                <div><small>📍 <?= htmlspecialchars($apt['location'] ?? 'N/A') ?></small></div>
                            </td>
                            <td><?= htmlspecialchars($apt['service_type']) ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($apt['appointment_date'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $apt['status'] ?>" id="badge-<?= $apt['id'] ?>">
                                    <?= htmlspecialchars($apt['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-form">
                                    <select class="status-select" data-id="<?= $apt['id'] ?>">
                                        <option value="pending" <?= $apt['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="approved" <?= $apt['status'] === 'approved' ? 'selected' : '' ?>>Approve</option>
                                        <option value="cancelled" <?= $apt['status'] === 'cancelled' ? 'selected' : '' ?>>Cancel</option>
                                    </select>
                                    <span class="loader" id="loader-<?= $apt['id'] ?>" style="display:none; font-size:0.8rem; color:var(--gray);">Saving...</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($appointments)): ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--gray);">No appointments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div id="toast" class="toast"></div>

    <script>
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const appointmentId = this.getAttribute('data-id');
                const newStatus = this.value;
                const loader = document.getElementById('loader-' + appointmentId);
                const badge = document.getElementById('badge-' + appointmentId);
                
                // Show saving state
                this.disabled = true;
                loader.style.display = 'inline';
                
                const formData = new FormData();
                formData.append('appointment_id', appointmentId);
                formData.append('status', newStatus);
                
                fetch('api/update_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.disabled = false;
                    loader.style.display = 'none';
                    
                    if (data.success) {
                        // Update badge
                        badge.className = 'status-badge status-' + newStatus;
                        badge.textContent = newStatus;
                        showToast('Status updated successfully!', 'success');
                    } else {
                        showToast(data.error || 'Failed to update status.', 'error');
                        // Revert selection
                        this.value = badge.textContent.trim().toLowerCase();
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.disabled = false;
                    loader.style.display = 'none';
                    this.value = badge.textContent.trim().toLowerCase();
                    showToast('A network error occurred.', 'error');
                });
            });
        });
        
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast show ' + type;
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>
