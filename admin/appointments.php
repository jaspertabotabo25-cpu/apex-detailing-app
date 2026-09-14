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
$extraScripts = <<<HTML
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
HTML;

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
                    <div style="white-space: nowrap; display: flex; align-items: center; gap: 4px; color: var(--gray);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none" /><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.554 6.24L7.171 2.335c-.39-.45-1.105-.448-1.558.006L2.831 5.128c-.828.829-1.065 2.06-.586 3.047a29.2 29.2 0 0 0 13.561 13.58c.986.479 2.216.242 3.044-.587l2.808-2.813c.455-.455.456-1.174.002-1.564l-3.92-3.365c-.41-.352-1.047-.306-1.458.106l-1.364 1.366a.46.46 0 0 1-.553.088a14.56 14.56 0 0 1-5.36-5.367a.46.46 0 0 1 .088-.554l1.36-1.361c.412-.414.457-1.054.101-1.465" /></svg>
                        <small style="color: #000;"><?= htmlspecialchars($apt['phone'] ?? 'N/A') ?></small>
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px; color: var(--gray);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none" /><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="12" cy="10" r="3" /><path d="M12 2a8 8 0 0 0-8 8c0 1.892.402 3.13 1.5 4.5L12 22l6.5-7.5c1.098-1.37 1.5-2.608 1.5-4.5a8 8 0 0 0-8-8" /></g></svg>
                        <small style="color: #000;"><?= htmlspecialchars($apt['location'] ?? 'N/A') ?></small>
                    </div>
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
