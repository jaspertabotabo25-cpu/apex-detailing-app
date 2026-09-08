<?php
require_once '../config/auth.php';
require_admin();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: appointments.php');
    exit;
}

// Fetch appointment details
$stmt = $pdo->prepare("
    SELECT a.*, u.name as client_name, v.make, v.model, v.license_plate 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    LEFT JOIN vehicles v ON a.vehicle_id = v.id 
    WHERE a.id = ?
");
$stmt->execute([$id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    die('Appointment not found.');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }
    
    $type = $_POST['action'] === 'upload_pre' ? 'pre' : 'post';
    $notes = trim($_POST['notes'] ?? '');
    $pushPortfolio = isset($_POST['push_portfolio']) ? true : false;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $fileMime = mime_content_type($_FILES['image']['tmp_name']);
        
        if (in_array($fileMime, $allowed)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('insp_') . '.' . $ext;
            $uploadPath = '../assets/inspections/' . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $dbPath = 'assets/inspections/' . $filename;
                $stmt = $pdo->prepare("INSERT INTO inspections (appointment_id, type, image_path, notes) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $type, $dbPath, $notes]);
                
                if ($type === 'post' && $pushPortfolio) {
                    $desc = $appointment['service_type'] . ' on ' . ($appointment['make'] ? $appointment['make'].' '.$appointment['model'] : 'Vehicle');
                    $stmtPort = $pdo->prepare("INSERT INTO portfolio (image_path, description) VALUES (?, ?)");
                    $stmtPort->execute([$dbPath, $desc]);
                }
                $message = ucfirst($type) . " inspection image uploaded successfully.";
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, and WebP are allowed.";
        }
    } else {
        $error = "Please select an image to upload.";
    }
}

// Fetch existing inspections
$stmt = $pdo->prepare("SELECT * FROM inspections WHERE appointment_id = ? ORDER BY created_at ASC");
$stmt->execute([$id]);
$inspections = $stmt->fetchAll();

$preInspections = array_filter($inspections, fn($i) => $i['type'] === 'pre');
$postInspections = array_filter($inspections, fn($i) => $i['type'] === 'post');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Execution #<?= $id ?> - Apex Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .dashboard { display: flex; min-height: 100vh; font-family: 'Inter', sans-serif; }
        .sidebar { width: 260px; background: var(--navy); color: var(--white); padding: 30px 20px; flex-shrink: 0; }
        .sidebar h2 { font-family: 'Poppins', sans-serif; font-size: 1.2rem; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
        .sidebar a { display: block; color: #c7cad1; padding: 12px 15px; margin-bottom: 5px; border-radius: 4px; transition: all 0.2s; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: var(--white); }
        
        .content { flex-grow: 1; padding: 40px; background: #f8fafc; overflow-y: auto; }
        .card { background: var(--white); padding: 25px; border-radius: var(--radius); box-shadow: 0 4px 18px rgba(0,0,0,0.04); margin-bottom: 20px; border: 1px solid #e2e8f0; }
        
        h1, h2, h3 { color: #0f172a; font-family: 'Poppins', sans-serif; }
        
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .alert-success { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 500; font-size: 0.9rem; }
        .alert-error { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-weight: 500; font-size: 0.9rem; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.85rem; color: #334155; }
        .form-group input[type="file"] { width: 100%; padding: 10px; border: 1px dashed #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        .form-group textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit; resize: vertical; min-height: 80px; }
        
        .btn { padding: 10px 16px; background: var(--navy); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background 0.2s; }
        .btn:hover { background: #1f232c; }
        
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; margin-top: 15px; }
        .gallery-item { border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; position: relative; }
        .gallery-item img { width: 100%; height: 100px; object-fit: cover; display: block; }
        .gallery-item .notes { padding: 5px; font-size: 0.75rem; color: #64748b; background: #f8fafc; border-top: 1px solid #e2e8f0; }
        
        .checkbox-group { display: flex; align-items: center; gap: 8px; margin-bottom: 15px; }
        .checkbox-group input { width: auto; }
        .checkbox-group label { margin-bottom: 0; font-weight: normal; color: #475569; }
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
            <a href="inventory.php">Inventory</a>
            <a href="../index.php" style="margin-top: 30px;">← Back to Site</a>
            <a href="../logout.php">Logout</a>
        </div>
        
        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Job Execution: Appt #<?= $appointment['id'] ?></h1>
                <a href="client_profile.php?id=<?= $appointment['user_id'] ?>" style="color: #64748b; text-decoration: none; font-weight: 600;">&larr; Back to Client</a>
            </div>
            
            <p style="color: #475569; margin-bottom: 25px;">
                <strong>Client:</strong> <?= htmlspecialchars($appointment['client_name']) ?> | 
                <strong>Service:</strong> <?= htmlspecialchars($appointment['service_type']) ?> | 
                <strong>Vehicle:</strong> <?= $appointment['make'] ? htmlspecialchars($appointment['make'].' '.$appointment['model']) : 'Not Specified' ?> |
                <strong>Date:</strong> <?= date('M j, Y h:i A', strtotime($appointment['appointment_date'])) ?>
            </p>

            <?php if ($message): ?>
                <div class="alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            
            <div class="grid">
                <!-- Pre-Inspection -->
                <div class="card">
                    <h3 style="margin-top:0; color: #0284c7;">Pre-Inspection (Liability)</h3>
                    <p style="font-size:0.85rem; color:#64748b;">Log pre-existing damage (scratches, dents, tears).</p>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="upload_pre">
                        
                        <div class="form-group">
                            <label>Damage Photo</label>
                            <input type="file" name="image" required accept="image/*">
                        </div>
                        <div class="form-group">
                            <label>Notes (Optional)</label>
                            <textarea name="notes" placeholder="e.g. Scuff mark on front right bumper."></textarea>
                        </div>
                        <button type="submit" class="btn">Log Pre-Inspection</button>
                    </form>
                    
                    <?php if (!empty($preInspections)): ?>
                    <h4 style="margin-top: 25px; margin-bottom: 10px; font-size: 0.9rem;">Logged Pre-Inspections:</h4>
                    <div class="gallery">
                        <?php foreach ($preInspections as $insp): ?>
                        <div class="gallery-item">
                            <a href="../<?= htmlspecialchars($insp['image_path']) ?>" target="_blank">
                                <img src="../<?= htmlspecialchars($insp['image_path']) ?>" alt="Pre Inspection">
                            </a>
                            <?php if ($insp['notes']): ?>
                                <div class="notes"><?= htmlspecialchars($insp['notes']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Post-Detail -->
                <div class="card">
                    <h3 style="margin-top:0; color: #16a34a;">Post-Detail (Results)</h3>
                    <p style="font-size:0.85rem; color:#64748b;">Upload final "after" photos.</p>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="upload_post">
                        
                        <div class="form-group">
                            <label>Final Result Photo</label>
                            <input type="file" name="image" required accept="image/*">
                        </div>
                        <div class="form-group">
                            <label>Internal Notes (Optional)</label>
                            <textarea name="notes" placeholder="e.g. Applied 2 coats of ceramic."></textarea>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="push_portfolio" name="push_portfolio" value="1">
                            <label for="push_portfolio">Push this photo to public Portfolio Gallery</label>
                        </div>
                        
                        <button type="submit" class="btn" style="background: #16a34a;">Upload Result</button>
                    </form>

                    <?php if (!empty($postInspections)): ?>
                    <h4 style="margin-top: 25px; margin-bottom: 10px; font-size: 0.9rem;">Logged Results:</h4>
                    <div class="gallery">
                        <?php foreach ($postInspections as $insp): ?>
                        <div class="gallery-item">
                            <a href="../<?= htmlspecialchars($insp['image_path']) ?>" target="_blank">
                                <img src="../<?= htmlspecialchars($insp['image_path']) ?>" alt="Post Inspection">
                            </a>
                            <?php if ($insp['notes']): ?>
                                <div class="notes"><?= htmlspecialchars($insp['notes']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
