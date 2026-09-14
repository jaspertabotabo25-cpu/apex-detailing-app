<?php
// includes/footer.php
?>
  <!-- ============ FOOTER ============ -->
  <footer id="contact">
    <div class="wrap">
      <div class="footer-grid">
        <div class="footer-brand">
          <img src="<?= htmlspecialchars($basePath ?? '') ?>assets/svg_brand_logo.svg" alt="Apex Custom Detailing" class="footer-logo">
          <p>Sleek. Premium. Efficient.</p>
        </div>
        <div>
          <h4>COMPANY</h4>
          <ul>
            <li><a href="#">About Us</a></li>
            <li><a href="#services">Our Services</a></li>
            <li><a href="#gallery">Gallery</a></li>
          </ul>
        </div>
        <div>
          <h4>SERVICE AREAS</h4>
          <ul>
            <li><a href="#">Dumaguete City</a></li>
            <li><a href="#">Ronda, Cebu</a></li>
            <li><a href="#">Surrounding Regions</a></li>
          </ul>
        </div>
        <div>
          <h4>CONTACT</h4>
          <ul>
            <li><a href="mailto:booking@apexcustomdetail.ph">booking@apexcustomdetail.ph</a></li>
            <li><a href="tel:+639123456789">+63 912 345 6789</a></li>
            <li><a href="#">Instagram</a></li>
            <li><a href="#">Facebook</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        &copy; <?= date('Y') ?> Apex Custom Detailing. All Rights Reserved.
      </div>
    </div>
  </footer>

  <!-- ============ MODALS & OVERLAYS ============ -->
  
  <!-- All Services Modal -->
  <dialog id="allServicesModal" class="modal">
    <div class="modal-content">
      <button class="modal-close" id="closeAllServicesBtn" aria-label="Close modal">&times;</button>
      <h2>All Services</h2>
      <p>Browse our complete list of detailing packages.</p>
      
      <div class="all-services-list">
        <?php if (!empty($activeServices)): ?>
          <?php foreach ($activeServices as $srv): ?>
          <div class="all-services-item">
              <span class="all-services-name"><?= htmlspecialchars($srv['name']) ?></span>
              <button class="btn btn-navy btn-sm book-specific-service" data-service="<?= htmlspecialchars($srv['name']) ?>" style="padding: 8px 16px; font-size: 0.8rem;">Book This</button>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No services currently available.</p>
        <?php endif; ?>
      </div>
    </div>
  </dialog>

  <!-- Booking Modal -->
  <dialog id="bookingModal" class="modal">
    <div class="modal-content">
      <button class="modal-close" id="closeModalBtn" aria-label="Close modal">&times;</button>
      <h2>Book Your Service</h2>
      <p id="bookingFormDesc">Fill out the details below and we'll confirm your appointment.</p>
      
      <div id="bookingSuccessMessage" style="display: none; text-align: center; padding: 30px 10px;">
        <h3 style="color: #155724; margin-bottom: 15px; font-family: 'Poppins', sans-serif;">Appointment Requested!</h3>
        <p style="margin-bottom: 25px;">Your appointment has been successfully requested. We will contact you shortly to confirm the details.</p>
        <button type="button" class="btn btn-navy" id="successCloseBtn">Close</button>
      </div>

      <form id="bookingForm" class="booking-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
        <div class="form-group">
          <label for="b_name">Full Name</label>
          <input type="text" id="b_name" name="b_name" value="<?= htmlspecialchars($_SESSION['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label for="b_phone">Phone Number</label>
          <input type="tel" id="b_phone" name="b_phone" value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label for="b_service">Select Service</label>
          <select id="b_service" name="b_service" required>
            <option value="">Choose a service...</option>
            <?php if (!empty($activeServices)): ?>
                <?php foreach ($activeServices as $srv): ?>
                    <option value="<?= htmlspecialchars($srv['name']) ?>"><?= htmlspecialchars($srv['name']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="b_date">Preferred Date & Time</label>
          <input type="datetime-local" id="b_date" name="b_date" required>
        </div>
        <div class="form-group">
          <label for="b_location">Service Location (Address)</label>
          <input type="text" id="b_location" name="b_location" placeholder="e.g. 123 Main St, Dumaguete City" value="<?= htmlspecialchars($_SESSION['address'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn btn-navy w-100" id="submitBookingBtn">Confirm Booking</button>
      </form>
    </div>
  </dialog>

  <div id="lightboxOverlay" class="lightbox-overlay">
    <button class="lightbox-close" id="closeLightboxBtn" aria-label="Close lightbox">&times;</button>
    <div class="lightbox-content">
      <img src="" alt="" id="lightboxImg">
    </div>
  </div>

  <dialog id="myBookingsModal" class="modal">
    <div class="modal-content">
      <button class="modal-close" id="closeMyBookingsBtn" aria-label="Close modal">&times;</button>
      <h2>My Bookings</h2>
      <p>View and manage your appointments below.</p>
      
      <div id="myBookingsContainer" style="margin-top: 20px;">
        <?php
          if (function_exists('is_logged_in') && is_logged_in() && !is_admin()) {
              if (!isset($pdo)) {
                  require_once __DIR__ . '/../config/db.php';
              }
              try {
                  $stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date DESC");
                  $stmt->execute([$_SESSION['user_id']]);
                  $appointments = $stmt->fetchAll();
                  if (count($appointments) > 0) {
                      echo '<div class="bookings-list">';
                      foreach ($appointments as $apt) {
                          $statusClass = 'status-' . strtolower($apt['status']);
                          $dateFormatted = date('M j, Y, g:i A', strtotime($apt['appointment_date']));
                          echo '<div class="booking-card" id="booking-'.$apt['id'].'">';
                          echo '<h4>' . htmlspecialchars($apt['service_type']) . '</h4>';
                          echo '<p style="margin-bottom:4px; font-size: 0.9rem;"><strong>Date:</strong> ' . $dateFormatted . '</p>';
                          echo '<p style="margin-bottom:8px; font-size: 0.9rem;"><strong>Status:</strong> <span class="status-badge '.$statusClass.'">' . ucfirst(htmlspecialchars($apt['status'])) . '</span></p>';
                          if ($apt['status'] === 'pending') {
                              echo '<button class="btn btn-gray cancel-booking-btn" data-id="'.$apt['id'].'" style="padding: 8px 16px; margin-top: 6px; font-size: 0.75rem;">Cancel Booking</button>';
                          }
                          echo '</div>';
                      }
                      echo '</div>';
                  } else {
                      echo '<p style="text-align:center; padding: 20px 0; color: var(--gray);">You have no appointments yet.</p>';
                  }
              } catch (\Exception $e) {
                  echo '<p style="text-align:center; padding: 20px 0; color: var(--gray);">Unable to fetch bookings.</p>';
              }
          }
        ?>
      </div>
    </div>
  </dialog>

  <script src="<?= htmlspecialchars($basePath ?? '') ?>script.js"></script>
</body>
</html>
