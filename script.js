const navToggle = document.getElementById('navToggle');
const mainNav = document.getElementById('mainNav');

navToggle.addEventListener('click', () => {
    mainNav.classList.toggle('show');
});

document.querySelectorAll('.main-nav a').forEach((link) => {
    link.addEventListener('click', () => mainNav.classList.remove('show'));
});

// 1. Sticky Header
const header = document.querySelector('header');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// 2. View Packages smooth scroll
const viewPackagesBtn = document.getElementById('viewPackagesBtn');
if (viewPackagesBtn) {
    viewPackagesBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const target = document.getElementById('services');
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
}

// 3. Modal Logic
const bookingModal = document.getElementById('bookingModal');
const closeModalBtn = document.getElementById('closeModalBtn');
const serviceSelect = document.getElementById('b_service');

let openModal = (serviceName = '') => {
    if (serviceName && serviceSelect) {
        serviceSelect.value = serviceName;
    }
    bookingModal.showModal();
    // Use setTimeout to allow CSS transition after showModal
    setTimeout(() => {
        bookingModal.classList.add('animating');
    }, 10);
};

const closeModal = () => {
    bookingModal.classList.remove('animating');
    setTimeout(() => {
        bookingModal.close();
    }, 300); // match transition duration
};

document.querySelectorAll('.open-book-modal').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        openModal();
    });
});

document.querySelectorAll('.book-service-trigger').forEach(card => {
    card.addEventListener('click', () => {
        const service = card.getAttribute('data-service');
        openModal(service);
    });
});

if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
if (bookingModal) bookingModal.addEventListener('click', (e) => {
    if (e.target === bookingModal) closeModal();
});

const bookingForm = document.getElementById('bookingForm');
const bookingSuccessMessage = document.getElementById('bookingSuccessMessage');
const bookingFormDesc = document.getElementById('bookingFormDesc');
const submitBookingBtn = document.getElementById('submitBookingBtn');
const successCloseBtn = document.getElementById('successCloseBtn');

if (successCloseBtn) {
    successCloseBtn.addEventListener('click', closeModal);
}

if (bookingForm) {
    bookingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        if (submitBookingBtn) {
            submitBookingBtn.disabled = true;
            submitBookingBtn.textContent = 'Submitting...';
        }
        
        const formData = new FormData(bookingForm);
        
        fetch('api/book.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bookingForm.style.display = 'none';
                if (bookingFormDesc) bookingFormDesc.style.display = 'none';
                if (bookingSuccessMessage) bookingSuccessMessage.style.display = 'block';
                bookingForm.reset();
            } else {
                alert(data.error || 'An error occurred while booking. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('A network error occurred. Please try again.');
        })
        .finally(() => {
            if (submitBookingBtn) {
                submitBookingBtn.disabled = false;
                submitBookingBtn.textContent = 'Confirm Booking';
            }
        });
    });
}

// Reset modal state when opening
const originalOpenModal = openModal;
openModal = (serviceName = '') => {
    if (bookingForm) bookingForm.style.display = 'block';
    if (bookingFormDesc) bookingFormDesc.style.display = 'block';
    if (bookingSuccessMessage) bookingSuccessMessage.style.display = 'none';
    originalOpenModal(serviceName);
};

// 4. Intersection Observer for 'How It Works'
const observerOptions = {
    threshold: 0.2,
    rootMargin: "0px 0px -50px 0px"
};

const fadeObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            setTimeout(() => {
                entry.target.classList.add('visible');
            }, index * 150); // Staggered fade in
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

document.querySelectorAll('.fade-in-step').forEach(step => {
    fadeObserver.observe(step);
});

// 5. Lightbox Logic
const lightboxOverlay = document.getElementById('lightboxOverlay');
const lightboxImg = document.getElementById('lightboxImg');
const closeLightboxBtn = document.getElementById('closeLightboxBtn');

document.querySelectorAll('.lightbox-trigger').forEach(img => {
    img.addEventListener('click', () => {
        if(lightboxImg) {
            lightboxImg.src = img.src;
            lightboxImg.alt = img.alt;
        }
        if(lightboxOverlay) lightboxOverlay.classList.add('active');
    });
});

const closeLightbox = () => {
    if(lightboxOverlay) lightboxOverlay.classList.remove('active');
    setTimeout(() => { if(lightboxImg) lightboxImg.src = ''; }, 300);
};

if(closeLightboxBtn) closeLightboxBtn.addEventListener('click', closeLightbox);
if(lightboxOverlay) lightboxOverlay.addEventListener('click', (e) => {
    if (e.target === lightboxOverlay) closeLightbox();
});

// 6. Carousel Logic
const track = document.querySelector('.carousel-track');
if (track) {
    // Clone items to make it seamless
    const items = track.innerHTML;
    track.innerHTML += items; // Double the content
    track.classList.add('scrolling');
}

// 7. FAQ logic with smooth animation
document.querySelectorAll('.faq-item .faq-question').forEach(button => {
    button.addEventListener('click', () => {
        const currentItem = button.closest('.faq-item');
        const isActive = currentItem.classList.contains('active');

        // Close all other items
        document.querySelectorAll('.faq-item').forEach(item => {
            item.classList.remove('active');
        });

        // Toggle current item
        if (!isActive) {
            currentItem.classList.add('active');
        }
    });
});