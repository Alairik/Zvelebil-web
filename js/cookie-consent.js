/**
 * Cookie Consent Banner
 * GDPR compliant cookie consent management
 */

(function() {
    'use strict';

    const CONSENT_KEY = 'cookie_consent';
    const CONSENT_VERSION = '1';

    // Check if consent was already given
    function getConsent() {
        try {
            const consent = localStorage.getItem(CONSENT_KEY);
            if (consent) {
                return JSON.parse(consent);
            }
        } catch (e) {
            console.error('Error reading consent:', e);
        }
        return null;
    }

    // Save consent
    function saveConsent(analytics) {
        const consent = {
            version: CONSENT_VERSION,
            analytics: analytics,
            timestamp: new Date().toISOString()
        };
        localStorage.setItem(CONSENT_KEY, JSON.stringify(consent));
    }

    // Update GTM consent state
    function updateGTMConsent(analytics) {
        window.dataLayer = window.dataLayer || [];

        if (analytics) {
            // User accepted analytics cookies
            window.dataLayer.push({
                'event': 'consent_update',
                'analytics_storage': 'granted'
            });
        } else {
            // User declined analytics cookies
            window.dataLayer.push({
                'event': 'consent_update',
                'analytics_storage': 'denied'
            });
        }
    }

    // Set default consent state (denied until user accepts)
    function setDefaultConsent() {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': 'consent_default',
            'analytics_storage': 'denied'
        });
    }

    // Create and show cookie banner
    function showBanner() {
        const banner = document.createElement('div');
        banner.id = 'cookie-banner';
        banner.innerHTML = `
            <div class="cookie-banner-content">
                <div class="cookie-text">
                    <p><strong>Používáme cookies</strong></p>
                    <p>Tento web používá cookies pro analýzu návštěvnosti. Více informací najdete v <a href="/pages/ochrana-soukromi.html">zásadách ochrany soukromí</a>.</p>
                </div>
                <div class="cookie-buttons">
                    <button class="cookie-btn cookie-btn-reject" id="cookie-reject">Odmítnout</button>
                    <button class="cookie-btn cookie-btn-accept" id="cookie-accept">Přijmout vše</button>
                </div>
            </div>
        `;

        document.body.appendChild(banner);

        // Add event listeners
        document.getElementById('cookie-accept').addEventListener('click', function() {
            acceptCookies();
        });

        document.getElementById('cookie-reject').addEventListener('click', function() {
            rejectCookies();
        });

        // Animate in
        setTimeout(function() {
            banner.classList.add('visible');
        }, 100);
    }

    // Accept all cookies
    function acceptCookies() {
        saveConsent(true);
        updateGTMConsent(true);
        hideBanner();
    }

    // Reject analytics cookies
    function rejectCookies() {
        saveConsent(false);
        updateGTMConsent(false);
        hideBanner();
    }

    // Hide banner
    function hideBanner() {
        const banner = document.getElementById('cookie-banner');
        if (banner) {
            banner.classList.remove('visible');
            setTimeout(function() {
                banner.remove();
            }, 300);
        }
    }

    // Open cookie settings (for privacy page button)
    window.openCookieSettings = function() {
        // Remove existing consent
        localStorage.removeItem(CONSENT_KEY);
        // Show banner again
        showBanner();
    };

    // Initialize
    function init() {
        const consent = getConsent();

        if (consent && consent.version === CONSENT_VERSION) {
            // User already gave consent, apply it
            updateGTMConsent(consent.analytics);
        } else {
            // No consent yet, set default (denied) and show banner
            setDefaultConsent();

            // Show banner after DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showBanner);
            } else {
                showBanner();
            }
        }
    }

    // Run initialization
    init();
})();
