<?php
require_once 'config/auth.php';
require_login();

// Fetch up to 4 recent portfolio items for the gallery
$stmtPortfolio = $pdo->query("SELECT * FROM portfolio ORDER BY created_at DESC LIMIT 4");
$portfolioItems = $stmtPortfolio->fetchAll();

// Fetch active services for the booking form
$stmtServices = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY name ASC");
$activeServices = $stmtServices->fetchAll();

// Fallback images in case the DB doesn't have 4 yet
$defaultImages = [
    ['image_path' => 'assets/work-premium-detailing.jpg', 'description' => 'Premium Detailing'],
    ['image_path' => 'assets/work-full-body-wash.jpg', 'description' => 'Full Body Wash'],
    ['image_path' => 'assets/work-custom-rim.jpg', 'description' => 'Custom Rim accent'],
    ['image_path' => 'assets/work-interior-detailing.jpg', 'description' => 'Interior Detailing'],
];

for ($i = 0; $i < 4; $i++) {
    if (!isset($portfolioItems[$i])) {
        $portfolioItems[$i] = $defaultImages[$i];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apex Custom Detailing | Premium Detailing, Delivered To You</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>

<body>

  <!-- ============ HEADER ============ -->
  <header>
    <div class="header-inner">
      <a href="#" class="brand">
        <img src="assets/svg_brand_logo.svg" alt="Apex Custom Detailing">
      </a>
      <nav class="main-nav" id="mainNav">
        <a href="#home" class="active">Home</a>
        <a href="#services">Services</a>
        <a href="#gallery">Gallery</a>
        <a href="#contact">Contact</a>
        <?php if (is_admin()): ?>
          <a href="admin/index.php" style="color:var(--navy);font-weight:bold;">Admin Dashboard</a>
        <?php else: ?>
          <a href="#" class="open-my-bookings-modal" style="color:var(--navy);font-weight:bold;">My Bookings</a>
        <?php endif; ?>
        <a href="logout.php" class="btn" style="border: 1px solid var(--border); padding: 10px 20px;">Logout</a>
        <a href="#book" class="btn btn-navy open-book-modal">Book Now</a>
      </nav>
      <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- ============ HERO ============ -->
  <section class="hero" id="home">
    <div class="hex-pattern"></div>
    <img src="assets/Logo_Apex_top.png" alt="" class="hero-apex-watermark-top" aria-hidden="true">
    <img src="assets/Logo_Apex.png" alt="" class="hero-apex-watermark-bottom" aria-hidden="true">
    <div class="hero-inner">
      <div class="hero-copy">
        <h1>PREMIUM DETAILING,<br>DELIVERED TO YOU.</h1>
        <p>Seamlessly blending premium detailing with practical efficiency in home-service vehicle cleaning.</p>
        <a href="#services" class="btn btn-gray" id="viewPackagesBtn">View Packages</a>
      </div>
      <div class="hero-visual">
        <img src="assets/Car-hero.png" alt="Detailed BMW front view">
      </div>
    </div>
  </section>

  <!-- ============ VISION ============ -->
  <section class="vision">
    <div class="wrap">
      <div class="vision-inner">
        <div>
          <h2>THE APEX VISION</h2>
          <p>We seamlessly blend premium detailing with practical efficiency in home-service vehicle cleaning. We aim to
            create a sleek, high-end automotive aesthetic that aligns entirely with your vision.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ SERVICES ============ -->
  <section class="services" id="services">
    <div class="wrap">
      <h2 class="section-heading">FEATURED SERVICES</h2>
      <div class="eyebrow-underline"></div>

      <div class="services-grid">
        <div class="service-card book-service-trigger" id="card-standard-wash" data-service="Standard Wash">
          <div class="thumb"><img src="assets/service-standard-wash.jpg" alt="Standard Wash"></div>
          <div class="body">
            <h3>Standard Wash</h3>
            <p>A thorough, efficient maintenance wash designed to restore your vehicle's daily shine and protect its
              exterior finish.</p>
          </div>
        </div>

        <div class="service-card featured book-service-trigger" id="card-custom-detail" data-service="Custom Detail">
          <div class="thumb"><img src="assets/service-custom-detail.jpg" alt="Custom Detail"></div>
          <div class="body">
            <h3>Custom Detail</h3>
            <p>Comprehensive interior and exterior restoration. We refine every inch to achieve a flawless, high-end
              aesthetic tailored to you.</p>
          </div>
        </div>

        <div class="service-card book-service-trigger" id="card-moto-detail" data-service="Moto Custom & Detail">
          <div class="thumb"><img src="assets/service-moto-detail.jpg" alt="Moto Custom & Detail"></div>
          <div class="body">
            <h3>Moto Custom &amp; Detail</h3>
            <p>Specialized detailing highlighting custom design styling, precise parts integration, and custom color
              accent preservation.</p>
          </div>
        </div>
      </div>
      
      <div style="text-align: center;">
        <button class="btn btn-outline view-all-services-btn" id="openAllServicesBtn">View All Services</button>
      </div>
    </div>
  </section>

  <!-- ============ HOW IT WORKS ============ -->
  <section class="how">
    <div class="wrap">
      <h2 class="section-heading">HOW IT WORKS</h2>

      <div class="steps" id="howItWorksSteps">
        <div class="step fade-in-step">
          <div class="step-circle">1</div>
          <div class="step-title">Book Online</div>
          <p>Schedule your detail online. We service home garages from Dumaguete City to Ronda.</p>
        </div>
        <div class="step fade-in-step">
          <div class="step-circle">2</div>
          <div class="step-title">We Arrive</div>
          <p>Our team arrives fully equipped to perform the work right at your location.</p>
        </div>
        <div class="step fade-in-step">
          <div class="step-circle">3</div>
          <div class="step-title">Flawless Finish</div>
          <p>Review the final aesthetic and enjoy your completely refreshed, custom ride.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ RECENT WORK ============ -->
  <section class="recent" id="gallery">
    <div class="wrap">
      <h2 class="section-heading left">RECENT WORK</h2>
      <div class="eyebrow-underline" style="margin-left:0;"></div>

      <div class="work-grid">
        <div class="work-card tall-card">
          <img src="<?= htmlspecialchars($portfolioItems[0]['image_path']) ?>" alt="<?= htmlspecialchars($portfolioItems[0]['description']) ?>" class="lightbox-trigger">
          <div class="work-overlay">
            <div class="work-caption"><?= htmlspecialchars($portfolioItems[0]['description']) ?></div>
          </div>
        </div>
        <div class="work-right">
          <div class="work-card wide-card">
            <img src="<?= htmlspecialchars($portfolioItems[1]['image_path']) ?>" alt="<?= htmlspecialchars($portfolioItems[1]['description']) ?>" class="lightbox-trigger">
            <div class="work-overlay">
              <div class="work-caption"><?= htmlspecialchars($portfolioItems[1]['description']) ?></div>
            </div>
          </div>
          <div class="work-pair">
            <div class="work-card small-card">
              <img src="<?= htmlspecialchars($portfolioItems[2]['image_path']) ?>" alt="<?= htmlspecialchars($portfolioItems[2]['description']) ?>" class="lightbox-trigger">
              <div class="work-overlay">
                <div class="work-caption"><?= htmlspecialchars($portfolioItems[2]['description']) ?></div>
              </div>
            </div>
            <div class="work-card small-card">
              <img src="<?= htmlspecialchars($portfolioItems[3]['image_path']) ?>" alt="<?= htmlspecialchars($portfolioItems[3]['description']) ?>" class="lightbox-trigger">
              <div class="work-overlay">
                <div class="work-caption"><?= htmlspecialchars($portfolioItems[3]['description']) ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ CLIENT REVIEWS ============ -->
  <section class="reviews">
    <div class="hex-pattern"></div>
    <div class="wrap" style="position:relative;z-index:2;">
      <h2 class="section-heading">CLIENT REVIEWS</h2>

      <div class="reviews-container">
        <div class="reviews-grid">
          <div class="review-card">
            <div class="stars">★★★★★</div>
            <p class="quote">"Absolutely flawless execution. They handled my custom wheels with perfect precision."</p>
            <div class="author">-Rogen S.</div>
          </div>
          <div class="review-card">
            <div class="stars">★★★★★</div>
            <p class="quote">"The convenience of having premium detailing done right in my own driveway is unmatched."</p>
            <div class="author">- Ian Rey F.</div>
          </div>
          <div class="review-card">
            <div class="stars">★★★★★</div>
            <p class="quote">"Incredible attention to detail. They respected the specific aesthetic I wanted for my
              motorcycle build."</p>
            <div class="author">- John Paul P.</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ FAQ ============ -->
  <section class="faq">
    <div class="wrap">
      <h2 class="section-heading">FREQUENTLY ASKED QUESTIONS</h2>

      <div class="faq-list" id="faqList">
        <div class="faq-item">
          <button class="faq-question">
            Do I need to provide a water or power source?
            <span class="icon">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              <p>No need, our team arrives fully equipped with our own water supply and power source, so we can complete
                the full service right in your driveway or garage.</p>
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question">
            How long does a Complete Custom Detail take?
            <span class="icon">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              <p>A Complete Custom Detail typically takes between 3 to 5 hours, depending on your vehicle's size and
                condition, to ensure every inch meets our flawless standard.</p>
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question">
            Do you service locations outside of Dumaguete City?
            <span class="icon">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              <p>Yes, we currently extend our home-service coverage to Ronda, Cebu and surrounding regions. Get in touch
                to confirm availability in your area.</p>
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question">
            Are specific wheel and rim polishes included?
            <span class="icon">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              <p>Standard rim cleaning is included in every package. Specialized custom rim accent polishing is available
                as part of our Custom Detail and Moto Custom & Detail services.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ CTA ============ -->
  <section class="cta" id="book">
    <div class="wrap">
      <h2>READY TO ELEVATE YOUR RIDE?</h2>
      <p>Book your home-service appointment today.</p>
      <a href="#contact" class="btn btn-navy open-book-modal">Schedule A Wash</a>
    </div>
  </section>

  <!-- ============ FOOTER ============ -->
  <footer id="contact">
    <div class="wrap">
      <div class="footer-grid">
        <div class="footer-brand">
          <img src="assets/svg_brand_logo.svg" alt="Apex Custom Detailing" class="footer-logo">
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
        © 2026 Apex Custom Detailing. All Rights Reserved.
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
        <?php foreach ($activeServices as $srv): ?>
        <div class="all-services-item">
            <span class="all-services-name"><?= htmlspecialchars($srv['name']) ?></span>
            <button class="btn btn-navy btn-sm book-specific-service" data-service="<?= htmlspecialchars($srv['name']) ?>" style="padding: 8px 16px; font-size: 0.8rem;">Book This</button>
        </div>
        <?php endforeach; ?>
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
            <?php foreach ($activeServices as $srv): ?>
                <option value="<?= htmlspecialchars($srv['name']) ?>"><?= htmlspecialchars($srv['name']) ?></option>
            <?php endforeach; ?>
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
          if (!is_admin()) {
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
          }
        ?>
      </div>
    </div>
  </dialog>

  <script src="script.js"></script>
</body>

</html>