<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= htmlspecialchars(generate_csrf_token()) ?>">
  <title><?= htmlspecialchars($pageTitle ?? 'Apex Custom Detailing') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap"
    rel="stylesheet">
  <!-- Ensure paths work whether included from root or subdirectories (adjust accordingly if needed) -->
  <link rel="stylesheet" href="<?= htmlspecialchars($basePath ?? '') ?>styles.css">
  <?= $extraHead ?? '' ?>
</head>
<body>
  <!-- ============ HEADER ============ -->
  <header>
    <div class="header-inner">
      <a href="<?= htmlspecialchars($basePath ?? '') ?>index.php" class="brand">
        <img src="<?= htmlspecialchars($basePath ?? '') ?>assets/svg_brand_logo.svg" alt="Apex Custom Detailing">
      </a>
      <nav class="main-nav" id="mainNav">
        <a href="<?= htmlspecialchars($basePath ?? '') ?>index.php#home" class="active">Home</a>
        <a href="<?= htmlspecialchars($basePath ?? '') ?>index.php#services">Services</a>
        <a href="<?= htmlspecialchars($basePath ?? '') ?>index.php#gallery">Gallery</a>
        <a href="<?= htmlspecialchars($basePath ?? '') ?>index.php#contact">Contact</a>
        
        <?php if (function_exists('is_admin') && is_admin()): ?>
          <a href="<?= htmlspecialchars($basePath ?? '') ?>admin/index.php" style="color:var(--navy);font-weight:bold;">Admin Dashboard</a>
          <a href="<?= htmlspecialchars($basePath ?? '') ?>logout.php" class="btn" style="border: 1px solid var(--border); padding: 10px 20px;">Logout</a>
        <?php elseif (function_exists('is_logged_in') && is_logged_in()): ?>
          <a href="#" class="open-my-bookings-modal" style="color:var(--navy);font-weight:bold;">My Bookings</a>
          <a href="<?= htmlspecialchars($basePath ?? '') ?>logout.php" class="btn" style="border: 1px solid var(--border); padding: 10px 20px;">Logout</a>
          <a href="#book" class="btn btn-navy open-book-modal">Book Now</a>
        <?php else: ?>
          <a href="<?= htmlspecialchars($basePath ?? '') ?>login.php" class="btn" style="border: 1px solid var(--border); padding: 10px 20px;">Login</a>
          <a href="<?= htmlspecialchars($basePath ?? '') ?>login.php" class="btn btn-navy">Book Now</a>
        <?php endif; ?>
      </nav>
      <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>
