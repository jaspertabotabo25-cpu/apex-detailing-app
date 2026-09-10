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
    document.body.classList.add('modal-open');
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
        document.body.classList.remove('modal-open');
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

// Carousel Logic removed (converted to static grid)

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

// 8. My Bookings Modal
const myBookingsModal = document.getElementById('myBookingsModal');
const closeMyBookingsBtn = document.getElementById('closeMyBookingsBtn');

document.querySelectorAll('.open-my-bookings-modal').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        document.body.classList.add('modal-open');
        myBookingsModal.showModal();
        setTimeout(() => {
            myBookingsModal.classList.add('animating');
        }, 10);
    });
});

const closeMyBookingsModal = () => {
    myBookingsModal.classList.remove('animating');
    setTimeout(() => {
        myBookingsModal.close();
        document.body.classList.remove('modal-open');
    }, 300);
};

if (closeMyBookingsBtn) closeMyBookingsBtn.addEventListener('click', closeMyBookingsModal);
if (myBookingsModal) myBookingsModal.addEventListener('click', (e) => {
    if (e.target === myBookingsModal) closeMyBookingsModal();
});

// Cancel Booking logic
document.querySelectorAll('.cancel-booking-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Are you sure you want to cancel this booking?')) return;
        
        const bookingId = this.getAttribute('data-id');
        this.disabled = true;
        this.textContent = 'Canceling...';
        
        const formData = new FormData();
        formData.append('appointment_id', bookingId);
        
        fetch('api/cancel_booking.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById('booking-' + bookingId);
                const badge = card.querySelector('.status-badge');
                badge.className = 'status-badge status-cancelled';
                badge.textContent = 'Cancelled';
                this.remove(); // Remove cancel button
            } else {
                alert(data.error || 'Failed to cancel booking.');
                this.disabled = false;
                this.textContent = 'Cancel Booking';
            }
        })
        .catch(err => {
            console.error(err);
            alert('A network error occurred.');
            this.disabled = false;
            this.textContent = 'Cancel Booking';
        });
    });
});

// 9. All Services Modal
const allServicesModal = document.getElementById('allServicesModal');
const openAllServicesBtn = document.getElementById('openAllServicesBtn');
const closeAllServicesBtn = document.getElementById('closeAllServicesBtn');

if (openAllServicesBtn) {
    openAllServicesBtn.addEventListener('click', (e) => {
        e.preventDefault();
        document.body.classList.add('modal-open');
        allServicesModal.showModal();
        setTimeout(() => {
            allServicesModal.classList.add('animating');
        }, 10);
    });
}

const closeAllServicesModal = () => {
    if (!allServicesModal) return;
    allServicesModal.classList.remove('animating');
    setTimeout(() => {
        allServicesModal.close();
        document.body.classList.remove('modal-open');
    }, 300);
};

if (closeAllServicesBtn) closeAllServicesBtn.addEventListener('click', closeAllServicesModal);
if (allServicesModal) allServicesModal.addEventListener('click', (e) => {
    if (e.target === allServicesModal) closeAllServicesModal();
});

// Book a specific service from the All Services modal
document.querySelectorAll('.book-specific-service').forEach(btn => {
    btn.addEventListener('click', () => {
        const service = btn.getAttribute('data-service');
        closeAllServicesModal();
        setTimeout(() => {
            openModal(service);
        }, 300); // Wait for All Services modal to close before opening booking modal
    });
});