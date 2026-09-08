<?php
require_once '../config/auth.php';
require_admin();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get the next 7 days
$days = [];
$today = new DateTime('today');
for ($i = 0; $i < 7; $i++) {
    $days[] = (clone $today)->modify("+$i days")->format('Y-m-d');
}

$startDate = $days[0];
$endDate = $days[6];

// Fetch appointments within this range
$stmt = $pdo->prepare("
    SELECT a.*, u.name as client_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    WHERE DATE(a.appointment_date) BETWEEN ? AND ?
    ORDER BY a.appointment_date ASC
");
$stmt->execute([$startDate, $endDate]);
$appointments = $stmt->fetchAll();

// Group by date
$schedule = [];
foreach ($days as $day) {
    $schedule[$day] = [];
}
foreach ($appointments as $app) {
    $date = date('Y-m-d', strtotime($app['appointment_date']));
    if (isset($schedule[$date])) {
        $schedule[$date][] = $app;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dispatch Schedule - Apex Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .dashboard { display: flex; min-height: 100vh; font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; background: var(--navy); color: var(--white); padding: 30px 20px; flex-shrink: 0; }
        .sidebar h2 { font-family: 'Poppins', sans-serif; font-size: 1.2rem; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
        .sidebar a { display: block; color: #c7cad1; padding: 12px 15px; margin-bottom: 5px; border-radius: 4px; transition: all 0.2s; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: var(--white); }
        
        .content { flex-grow: 1; padding: 40px; background: #f8fafc; overflow-y: auto; height: 100vh; box-sizing: border-box; }
        h1 { color: #0f172a; font-family: 'Poppins', sans-serif; margin-top: 0; margin-bottom: 20px; }
        
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 15px; }
        .day-col { background: var(--white); border: 1px solid #e2e8f0; border-radius: 8px; min-height: 500px; display: flex; flex-direction: column; }
        .day-header { padding: 15px; border-bottom: 1px solid #e2e8f0; background: #f1f5f9; border-radius: 8px 8px 0 0; text-align: center; }
        .day-name { font-weight: 700; color: #334155; font-size: 0.9rem; text-transform: uppercase; }
        .day-date { color: #64748b; font-size: 0.8rem; margin-top: 5px; }
        
        .dropzone { flex-grow: 1; padding: 10px; display: flex; flex-direction: column; gap: 10px; transition: background 0.2s; }
        .dropzone.drag-over { background: #e0f2fe; }
        
        .appt-card { background: var(--navy); color: white; padding: 12px; border-radius: 6px; cursor: grab; box-shadow: 0 2px 5px rgba(0,0,0,0.1); font-size: 0.85rem; user-select: none; }
        .appt-card:active { cursor: grabbing; }
        .appt-card strong { display: block; font-size: 0.95rem; margin-bottom: 5px; }
        .appt-card .meta { color: #cbd5e1; font-size: 0.75rem; margin-bottom: 5px; }
        
        .buffer-block { background: repeating-linear-gradient(45deg, #f1f5f9, #f1f5f9 10px, #e2e8f0 10px, #e2e8f0 20px); border: 1px dashed #cbd5e1; border-radius: 6px; padding: 10px; text-align: center; font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: -5px; display: flex; align-items: center; justify-content: center; }
        
        .toast { position: fixed; bottom: 20px; right: 20px; background: #16a34a; color: white; padding: 12px 20px; border-radius: 6px; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.15); opacity: 0; transform: translateY(20px); transition: all 0.3s; z-index: 1000; pointer-events: none; }
        .toast.show { opacity: 1; transform: translateY(0); }
        .toast.error { background: #dc2626; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Apex Admin</h2>
            <a href="index.php">Dashboard Home</a>
            <a href="schedule.php" class="active">Dispatch Schedule</a>
            <a href="appointments.php">Appointments</a>
            <a href="portfolio_upload.php">Portfolio Uploader</a>
            <a href="clients.php">Client Registry</a>
            <a href="inventory.php">Inventory</a>
            <a href="../index.php" style="margin-top: 30px;">← Back to Site</a>
            <a href="../logout.php">Logout</a>
        </div>
        
        <div class="content">
            <h1>Dispatch Schedule (7-Day View)</h1>
            <p style="color: #64748b; margin-bottom: 30px;">Drag and drop appointments across days to reschedule them. Buffer times are automatically appended to account for travel routing.</p>
            
            <div class="calendar-grid">
                <?php foreach ($days as $day): ?>
                <div class="day-col">
                    <div class="day-header">
                        <div class="day-name"><?= date('l', strtotime($day)) ?></div>
                        <div class="day-date"><?= date('M j', strtotime($day)) ?></div>
                    </div>
                    <div class="dropzone" data-date="<?= $day ?>">
                        <?php foreach ($schedule[$day] as $app): ?>
                            <div class="appt-card" draggable="true" data-id="<?= $app['id'] ?>" style="min-height: <?= max(50, $app['duration_minutes']) ?>px;">
                                <strong><?= htmlspecialchars($app['client_name']) ?></strong>
                                <div class="meta"><?= date('h:i A', strtotime($app['appointment_date'])) ?></div>
                                <div><?= htmlspecialchars($app['service_type']) ?></div>
                            </div>
                            <?php if ($app['buffer_minutes'] > 0): ?>
                                <div class="buffer-block" data-buffer-for="<?= $app['id'] ?>" style="min-height: <?= max(30, $app['buffer_minutes']) ?>px;">
                                    Travel Buffer (<?= $app['buffer_minutes'] ?>m)
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div id="toast" class="toast">Schedule Updated!</div>
    
    <script>
        const csrfToken = "<?= $_SESSION['csrf_token'] ?>";
        let draggedItem = null;
        let draggedBuffer = null;

        document.querySelectorAll('.appt-card').forEach(card => {
            card.addEventListener('dragstart', function(e) {
                draggedItem = this;
                const id = this.getAttribute('data-id');
                draggedBuffer = document.querySelector(`.buffer-block[data-buffer-for="${id}"]`);
                setTimeout(() => {
                    this.style.display = 'none';
                    if(draggedBuffer) draggedBuffer.style.display = 'none';
                }, 0);
            });
            
            card.addEventListener('dragend', function(e) {
                setTimeout(() => {
                    this.style.display = 'block';
                    if(draggedBuffer) draggedBuffer.style.display = 'block';
                    draggedItem = null;
                    draggedBuffer = null;
                }, 0);
            });
        });

        document.querySelectorAll('.dropzone').forEach(zone => {
            zone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });
            
            zone.addEventListener('dragleave', function(e) {
                this.classList.remove('drag-over');
            });
            
            zone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                if (draggedItem) {
                    this.appendChild(draggedItem);
                    if(draggedBuffer) {
                        this.appendChild(draggedBuffer);
                    }
                    
                    const newDate = this.getAttribute('data-date');
                    const appId = draggedItem.getAttribute('data-id');
                    const newDateTime = newDate + ' 09:00:00'; // Defaulting dropped items to 9AM for demo
                    
                    updateAppointment(appId, newDateTime);
                }
            });
        });

        function updateAppointment(id, newDate) {
            fetch('api/update_schedule.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    new_date: newDate,
                    csrf_token: csrfToken
                })
            })
            .then(res => res.json())
            .then(data => {
                const toast = document.getElementById('toast');
                if (data.success) {
                    toast.textContent = "Schedule Updated Successfully!";
                    toast.className = 'toast show';
                } else {
                    toast.textContent = data.error || "Update Failed";
                    toast.className = 'toast show error';
                }
                setTimeout(() => { toast.classList.remove('show'); }, 3000);
            })
            .catch(err => {
                const toast = document.getElementById('toast');
                toast.textContent = "Network Error";
                toast.className = 'toast show error';
                setTimeout(() => { toast.classList.remove('show'); }, 3000);
            });
        }
    </script>
</body>
</html>
