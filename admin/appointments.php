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
.action-form { display: inline-flex; align-items: center; gap: 8px; }
.status-select:hover { background: #f1f5f9 !important; }
.status-select:focus { outline: none; border-color: #cbd5e1 !important; background: #fff !important; }
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
                    badge.className = 'badge ' + newStatus;
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

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 style="margin: 0;">Manage Appointments</h1>
</div>

<div class="card" style="padding: 0; overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <table class="table" style="margin: 0; width: 100%; min-width: 800px;">
        <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <tr>
                <th style="padding: 18px 24px; color: #64748b; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px;">Client Details</th>
                <th style="padding: 18px 24px; color: #64748b; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px;">Service Type</th>
                <th style="padding: 18px 24px; color: #64748b; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px;">Date & Time</th>
                <th style="padding: 18px 24px; color: #64748b; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px;">Status</th>
                <th style="padding: 18px 24px; color: #64748b; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $apt): ?>
            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                <td style="padding: 20px 24px; vertical-align: top;">
                    <div style="font-weight: 600; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                        <?= htmlspecialchars($apt['client_name']) ?>
                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 500; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">#<?= $apt['id'] ?></span>
                    </div>
                    <div style="font-size: 0.8rem; color: #64748b; display: flex; flex-direction: column; gap: 6px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"/></svg>
                            <?= htmlspecialchars($apt['client_email']) ?>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none" /><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.554 6.24L7.171 2.335c-.39-.45-1.105-.448-1.558.006L2.831 5.128c-.828.829-1.065 2.06-.586 3.047a29.2 29.2 0 0 0 13.561 13.58c.986.479 2.216.242 3.044-.587l2.808-2.813c.455-.455.456-1.174.002-1.564l-3.92-3.365c-.41-.352-1.047-.306-1.458.106l-1.364 1.366a.46.46 0 0 1-.553.088a14.56 14.56 0 0 1-5.36-5.367a.46.46 0 0 1 .088-.554l1.36-1.361c.412-.414.457-1.054.101-1.465" /></svg>
                            <?= htmlspecialchars($apt['phone'] ?? 'N/A') ?>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none" /><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="12" cy="10" r="3" /><path d="M12 2a8 8 0 0 0-8 8c0 1.892.402 3.13 1.5 4.5L12 22l6.5-7.5c1.098-1.37 1.5-2.608 1.5-4.5a8 8 0 0 0-8-8" /></g></svg>
                            <?= htmlspecialchars($apt['location'] ?? 'N/A') ?>
                        </div>
                    </div>
                </td>
                <td style="padding: 20px 24px; vertical-align: top;">
                    <span style="font-weight: 500; color: #334155; font-size: 0.95rem;"><?= htmlspecialchars($apt['service_type']) ?></span>
                </td>
                <td style="padding: 20px 24px; vertical-align: top;">
                    <div style="font-weight: 500; color: #0f172a; margin-bottom: 4px; font-size: 0.95rem;"><?= date('M j, Y', strtotime($apt['appointment_date'])) ?></div>
                    <div style="font-size: 0.8rem; color: #64748b;"><?= date('g:i A', strtotime($apt['appointment_date'])) ?></div>
                </td>
                <td style="padding: 20px 24px; vertical-align: top;">
                    <span class="badge <?= htmlspecialchars($apt['status']) ?>" id="badge-<?= $apt['id'] ?>">
                        <?= htmlspecialchars($apt['status']) ?>
                    </span>
                </td>
                <td style="padding: 20px 24px; vertical-align: top;">
                    <div class="action-form">
                        <select class="status-select" data-id="<?= $apt['id'] ?>" style="background: transparent; border: 1px solid transparent; padding: 6px 28px 6px 12px; font-weight: 600; color: #0f172a; cursor: pointer; border-radius: 6px; transition: all 0.2s; font-size: 0.85rem; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%230f172a%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 8px top 50%; background-size: 10px auto;">
                            <option value="pending" <?= $apt['status'] === 'pending' ? 'selected' : '' ?>>Waitlist</option>
                            <option value="approved" <?= $apt['status'] === 'approved' ? 'selected' : '' ?>>Approve</option>
                            <option value="cancelled" <?= $apt['status'] === 'cancelled' ? 'selected' : '' ?>>Cancel</option>
                        </select>
                        <span class="loader" id="loader-<?= $apt['id'] ?>" style="display:none; font-size:0.8rem; color:var(--gray);">⏳</span>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($appointments)): ?>
            <tr><td colspan="5" style="text-align: center; color: var(--gray); padding: 60px;">No appointments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
