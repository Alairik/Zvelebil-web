/**
 * ZVELEBIL.ONLINE - Main JavaScript
 * Scroll animations, navigation, and interactivity
 */

document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    initScrollReveal();
    initSmoothScroll();
    initContactForm();
    initParallax();
    initBenefitModal();
});

/**
 * Navigation functionality
 * - Sticky navbar on scroll
 * - Mobile menu toggle
 */
function initNavigation() {
    const navbar = document.getElementById('navbar');
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    // Sticky navbar
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    });

    // Mobile menu toggle
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });

        // Close menu when clicking a link
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && !navToggle.contains(e.target)) {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            }
        });
    }
}

/**
 * Scroll Reveal Animation
 * Elements with .reveal class animate when entering viewport
 */
function initScrollReveal() {
    const revealElements = document.querySelectorAll('.reveal');

    const revealOptions = {
        root: null,
        rootMargin: '0px 0px 150px 0px',
        threshold: 0.1
    };

    const revealCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                // Optional: unobserve after revealing
                // observer.unobserve(entry.target);
            }
        });
    };

    const revealObserver = new IntersectionObserver(revealCallback, revealOptions);

    revealElements.forEach(element => {
        revealObserver.observe(element);
    });
}

/**
 * Smooth scroll for anchor links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');

            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                const navbarHeight = document.getElementById('navbar').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navbarHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Contact form handling
 * Pokud má formulář action (PHP), nechá ho odeslat normálně
 * Pouze přidá loading stav na tlačítko
 */
function initContactForm() {
    const form = document.getElementById('contactForm');

    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');

            // Validace na straně klienta
            const name = form.querySelector('#name');
            const email = form.querySelector('#email');
            const message = form.querySelector('#message');

            if (!name.value.trim() || !email.value.trim() || !message.value.trim()) {
                e.preventDefault();
                showNotification('Vyplňte prosím všechna povinná pole.', 'error');
                return;
            }

            // Základní validace emailu
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                e.preventDefault();
                showNotification('Zadejte prosím platný email.', 'error');
                return;
            }

            // Pokud má formulář action, nechá odeslat na PHP
            // Zobrazí pouze loading stav
            if (form.getAttribute('action')) {
                submitBtn.textContent = 'Odesílám...';
                submitBtn.disabled = true;
                // Formulář se odešle normálně na PHP
            }
        });
    }
}

/**
 * Show notification message
 */
function showNotification(message, type = 'success') {
    // Remove existing notification
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button class="notification-close">&times;</button>
    `;

    // Add styles dynamically
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;

    // Add animation keyframes
    if (!document.querySelector('#notification-styles')) {
        const styleSheet = document.createElement('style');
        styleSheet.id = 'notification-styles';
        styleSheet.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            .notification-close {
                background: none;
                border: none;
                color: white;
                font-size: 1.5rem;
                cursor: pointer;
                padding: 0;
                line-height: 1;
            }
        `;
        document.head.appendChild(styleSheet);
    }

    document.body.appendChild(notification);

    // Close button functionality
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => notification.remove(), 300);
    });

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

/**
 * Subtle parallax effect for hero shapes
 */
function initParallax() {
    const shapes = document.querySelectorAll('.hero-shape');

    if (shapes.length === 0) return;

    let ticking = false;

    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                const scrolled = window.pageYOffset;
                const heroSection = document.querySelector('.hero');

                if (heroSection && scrolled < heroSection.offsetHeight) {
                    shapes.forEach((shape, index) => {
                        const speed = 0.1 + (index * 0.05);
                        shape.style.transform = `translateY(${scrolled * speed}px)`;
                    });
                }

                ticking = false;
            });

            ticking = true;
        }
    });
}

/**
 * Add active class to nav links based on scroll position
 */
function initActiveNavLinks() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-menu a[href^="#"]');

    window.addEventListener('scroll', () => {
        let current = '';
        const navbarHeight = document.getElementById('navbar').offsetHeight;

        sections.forEach(section => {
            const sectionTop = section.offsetTop - navbarHeight - 100;
            const sectionHeight = section.offsetHeight;

            if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
}

// Initialize active nav links
initActiveNavLinks();

/**
 * Benefit Modal - Shows detail popup on card click
 */
function initBenefitModal() {
    const benefitCards = document.querySelectorAll('.benefit-card[data-benefit]');
    const modal = document.getElementById('benefitModal');

    if (!modal || benefitCards.length === 0) return;

    const modalNumber = document.getElementById('modalNumber');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const modalClose = modal.querySelector('.benefit-modal-close');
    const modalOverlay = modal.querySelector('.benefit-modal-overlay');

    // Benefit data - content for each card
    const benefitData = {
        1: {
            number: '01',
            title: 'SEO ready, tracking ready, funguje od startu',
            body: `
                <p>V základu Vám nastavím Google Analytics a GTM, abyste od prvního dne viděli, kdo na web chodí a co tam dělá.</p>
                <p>Struktura webu, meta tagy, rychlost načítání, mobile-first – to všechno řeším hned při vytváření. Ne až když zjistíte, že Vás Google nenajde.</p>
                <p>Tracking pro Meta nebo Google Ads? Podle domluvy. Ale základy máte pokryté.</p>
            `
        },
        2: {
            number: '02',
            title: '10+ let v oboru, ne absolvent AI kurzu',
            body: `
                <p>Začínal jsem jako projekťák webových a e-shopových projektů. Osahal jsem WordPress, Shopify, PrestaShop i lokální krabice. Pak 6 let v digitálním marketingu.</p>
                <p>Vím, kde jsou nástrahy. Vím, co se může pokazit.</p>
                <p>AI používám, abych Vám dodal web rychle a levně – ale s knowhow člověka, co v tom žije denně.</p>
            `
        },
        3: {
            number: '03',
            title: 'Poslouchám Váš byznys, ne copy-paste šablonu',
            body: `
                <p>Před každým webem se sedneme aspoň na půl hodiny (online nebo naživo, jak chcete). Ptám se na Váš byznys, co od webu čekáte, jaké weby se Vám líbí, jaké barvy preferujete.</p>
                <p>Podle toho si připravím prompt pro AI. Ne náhodnou šablonu, ale web šitý přesně Vám.</p>
                <p>Fitness trenér potřebuje jiný web než účetní kancelář. A to není jen o barvách.</p>
            `
        },
        4: {
            number: '04',
            title: 'Hotovo za týden, ne za měsíc – bez prémiové ceny',
            body: `
                <p>AI mi umožňuje vytvořit web rychleji než klasickým vývojem. Ale nejde jen o rychlost – jde o to, že neplatíte za desítky hodin kódování, které dnes AI zvládne efektivněji.</p>
                <p>Dostanete profesionální výsledek za zlomek tradiční ceny. A protože mám technické i marketingové zázemí, nemusíte pak platit dalšího specialistu na analytics nebo SEO.</p>
            `
        }
    };

    // Open modal on card click
    benefitCards.forEach(card => {
        card.addEventListener('click', () => {
            const benefitId = card.getAttribute('data-benefit');
            const data = benefitData[benefitId];

            if (data) {
                modalNumber.textContent = data.number;
                modalTitle.textContent = data.title;
                modalBody.innerHTML = data.body;
                modal.classList.add('visible');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Close modal functions
    function closeModal() {
        modal.classList.remove('visible');
        document.body.style.overflow = '';
    }

    modalClose.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', closeModal);

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('visible')) {
            closeModal();
        }
    });
}
