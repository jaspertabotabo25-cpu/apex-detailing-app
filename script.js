const navToggle = document.getElementById('navToggle');
const mainNav = document.getElementById('mainNav');

navToggle.addEventListener('click', () => {
    mainNav.classList.toggle('show');
});

document.querySelectorAll('.main-nav a').forEach((link) => {
    link.addEventListener('click', () => mainNav.classList.remove('show'));
});

// FAQ accordion
document.querySelectorAll('.faq-item').forEach((item) => {
    const question = item.querySelector('.faq-question');
    const answer = item.querySelector('.faq-answer');
    
    question.addEventListener('click', () => {
        const wasOpen = item.classList.contains('open');
        
        // Close all items smoothly
        document.querySelectorAll('.faq-item').forEach((i) => {
            i.classList.remove('open');
            const ans = i.querySelector('.faq-answer');
            if (ans) ans.style.maxHeight = null;
        });
        
        // Open clicked item
        if (!wasOpen) {
            item.classList.add('open');
            answer.style.maxHeight = answer.scrollHeight + "px";
        }
    });
});