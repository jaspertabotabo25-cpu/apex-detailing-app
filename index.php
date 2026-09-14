<?php
// index.php
require_once 'config/auth.php';

// Prepare statements for secure fetching (adhering strictly to best practices)
$stmtPortfolio = $pdo->prepare("SELECT * FROM portfolio ORDER BY created_at DESC LIMIT 4");
$stmtPortfolio->execute();
$portfolioItems = $stmtPortfolio->fetchAll();

$stmtServices = $pdo->prepare("SELECT * FROM services WHERE is_active = 1 ORDER BY name ASC");
$stmtServices->execute();
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

$pageTitle = 'Apex Custom Detailing | Premium Detailing, Delivered To You';
$basePath = ''; 
require_once 'includes/header.php';
?>

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

<?php require_once 'includes/footer.php'; ?>