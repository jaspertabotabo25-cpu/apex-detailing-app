<?php
require_once '../config/auth.php';
require_admin();

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['portfolio_image'])) {
    $description = trim($_POST['description'] ?? '');
    $file = $_FILES['portfolio_image'];

    // 1. Basic validation
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error during file upload.';
    } else {
        // 2. Define upload directory and create if it doesn't exist
        $uploadDir = '../assets/portfolio/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 3. Validate MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mime, $allowedMimes)) {
            $error = 'Invalid file type. Only JPG, PNG, and WebP are allowed.';
        } else {
            // 4. Validate file size (e.g., max 5MB)
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                $error = 'File size exceeds the 5MB limit.';
            } else {
                // 5. Generate a unique, secure file name to prevent collision and directory traversal
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                // Fallback extension detection based on MIME if original is missing/weird
                if (empty($extension)) {
                    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                    $extension = $extMap[$mime];
                }
                $extension = strtolower($extension);
                
                // Allow only specific extensions
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($extension, $allowedExts)) {
                    $error = 'Invalid file extension.';
                } else {
                    $newFileName = uniqid('portfolio_', true) . '.' . $extension;
                    $targetPath = $uploadDir . $newFileName;

                    // 6. Move the file
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        // 7. Insert into database
                        // Store relative path for easy access from public views
                        $dbPath = 'assets/portfolio/' . $newFileName;
                        $stmt = $pdo->prepare("INSERT INTO portfolio (image_path, description) VALUES (?, ?)");
                        if ($stmt->execute([$dbPath, $description])) {
                            $msg = 'Image successfully uploaded and added to the portfolio.';
                        } else {
                            $error = 'Failed to save to database.';
                        }
                    } else {
                        $error = 'Failed to move uploaded file.';
                    }
                }
            }
        }
    }
}

// Fetch current portfolio items
$stmt = $pdo->query("SELECT * FROM portfolio ORDER BY created_at DESC");
$portfolioItems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Portfolio Uploader - Apex Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: var(--navy); color: var(--white); padding: 30px 20px; }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
        .sidebar a { display: block; color: #c7cad1; padding: 12px 15px; margin-bottom: 5px; border-radius: 4px; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: var(--white); }
        .content { flex-grow: 1; padding: 40px; background: var(--light); }
        .card { background: var(--white); padding: 25px; border-radius: var(--radius); box-shadow: 0 4px 18px rgba(0,0,0,0.04); margin-bottom: 20px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 0.95rem; }
        .form-group input[type="file"] { padding: 8px; }
        .btn-submit { padding: 12px 24px; cursor: pointer; }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .gallery-item { border-radius: 4px; overflow: hidden; background: #000; position: relative; }
        .gallery-item img { width: 100%; height: 150px; object-fit: cover; display: block; }
        .gallery-item p { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); color: white; margin: 0; padding: 8px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Apex Admin</h2>
            <a href="index.php">Dashboard Home</a>
            <a href="appointments.php">Appointments</a>
            <a href="portfolio_upload.php" class="active">Portfolio Uploader</a>
            <a href="clients.php">Client Registry</a>
            <a href="../index.php" style="margin-top: 30px;">← Back to Site</a>
            <a href="../logout.php">Logout</a>
        </div>
        <div class="content">
            <h1>Portfolio Uploader</h1>
            
            <?php if ($msg): ?>
                <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>Upload New Image</h3>
                <form method="POST" action="" enctype="multipart/form-data" style="margin-top: 20px;">
                    <div class="form-group">
                        <label for="portfolio_image">Select Image (JPG, PNG, WebP. Max 5MB)</label>
                        <input type="file" id="portfolio_image" name="portfolio_image" accept="image/jpeg, image/png, image/webp" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Image Description / Caption</label>
                        <input type="text" id="description" name="description" placeholder="E.g., Matte Black BMW M5 Full Detail">
                    </div>
                    <button type="submit" class="btn btn-navy btn-submit">Upload Image</button>
                </form>
            </div>

            <div class="card">
                <h3>Current Portfolio</h3>
                <div class="gallery-grid">
                    <?php foreach ($portfolioItems as $item): ?>
                    <div class="gallery-item">
                        <img src="../<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['description']) ?>">
                        <?php if ($item['description']): ?>
                            <p><?= htmlspecialchars($item['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($portfolioItems)): ?>
                        <div style="grid-column: 1 / -1; color: var(--gray);">No images uploaded yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
