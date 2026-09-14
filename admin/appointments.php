<?php
// admin/appointments.php
require_once '../config/auth.php';
require_admin();

// Fetch all appointments securely
$stmt = $pdo->prepare("
    SELECT a.*, u.name as client_name, u.email as client_email 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.appointment_date DESC
");
$stmt->execute();
$appointments = $stmt->fetchAll();

$pageTitle = 'Manage Appointments - Apex Admin';
$extraCSS = '
.action-form { display: inline-flex; gap: 8px; }
.action-form select { padding: 6px; border: 1px solid var(--border); border-radius: 4px; }
';
$extraScripts = "
<script>
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const appointmentId = select.getAttribute('data-id');
            const newStatus = select.value;
            const loader = document.getElementById('loader-' + appointmentId);
            const badge = document.getElementById('badge-' + appointmentId);
            
            // Show saving state
            select.disabled = true;
            loader.style.display = 'inline';
            
            const formData = new FormData();
            formData.append('appointment_id', appointmentId);
            formData.append('status', newStatus);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) formData.append('csrf_token', csrfToken);
            
            fetch('api/update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                select.disabled = false;
                loader.style.display = 'none';
                
                if (data.success) {
                    // Update badge
                    badge.className = 'status-badge status-' + newStatus;
                    badge.textContent = newStatus;
                    showToast('Status updated successfully!', 'success');
                } else {
                    showToast(data.error || 'Failed to update status.', 'error');
                    // Revert selection
                    select.value = badge.textContent.trim().toLowerCase();
                }
            })
            .catch(err => {
                console.error(err);
                select.disabled = false;
                loader.style.display = 'none';
                select.value = badge.textContent.trim().toLowerCase();
                showToast('A network error occurred.', 'error');
            });
        });
    });
</script>
";

require_once 'includes/admin_header.php';
?>

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
                    <span class="status-badge status-<?= htmlspecialchars($apt['status']) ?>" id="badge-<?= $apt['id'] ?>">
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

<?php require_once 'includes/admin_footer.php'; ?>
