/**
 * Cookie Consent s GTM Consent Mode v2
 * Kategorie: necessary, analytics, marketing
 */

(function() {
    'use strict';

    const CONSENT_KEY = 'zvelebil_cookie_consent';
    const CONSENT_VERSION = '2';

    // Výchozí stav - vše zakázáno kromě nutných cookies
    const defaultConsent = {
        necessary: true,      // Vždy povoleno
        analytics: false,     // Google Analytics, GTM analytics
        marketing: false,     // Meta Pixel, remarketing
        version: CONSENT_VERSION
    };

    // Inicializace GTM Consent Mode v2 - MUSÍ být před GTM!
    function initGTMConsent() {
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }

        // Nastavení výchozího stavu souhlasu
        gtag('consent', 'default', {
            'ad_storage': 'denied',
            'ad_user_data': 'denied',
            'ad_personalization': 'denied',
            'analytics_storage': 'denied',
            'functionality_storage': 'granted',
            'personalization_storage': 'denied',
            'security_storage': 'granted',
            'wait_for_update': 500
        });

        // Informace o regionu (EU)
        gtag('set', 'ads_data_redaction', true);
        gtag('set', 'url_passthrough', true);
    }

    // Aktualizace GTM Consent podle uživatelských preferencí
    function updateGTMConsent(consent) {
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }

        gtag('consent', 'update', {
            'ad_storage': consent.marketing ? 'granted' : 'denied',
            'ad_user_data': consent.marketing ? 'granted' : 'denied',
            'ad_personalization': consent.marketing ? 'granted' : 'denied',
            'analytics_storage': consent.analytics ? 'granted' : 'denied',
            'personalization_storage': consent.analytics ? 'granted' : 'denied'
        });

        // Push event pro GTM
        dataLayer.push({
            'event': 'consent_update',
            'consent_analytics': consent.analytics,
            'consent_marketing': consent.marketing
        });
    }

    // Uložení souhlasu do localStorage
    function saveConsent(consent) {
        consent.version = CONSENT_VERSION;
        consent.timestamp = new Date().toISOString();
        localStorage.setItem(CONSENT_KEY, JSON.stringify(consent));
    }

    // Načtení souhlasu z localStorage
    function loadConsent() {
        try {
            const stored = localStorage.getItem(CONSENT_KEY);
            if (stored) {
                const consent = JSON.parse(stored);
                // Kontrola verze - pokud se změní, znovu zobrazit banner
                if (consent.version === CONSENT_VERSION) {
                    return consent;
                }
            }
        } catch (e) {
            console.error('Chyba při načítání cookie souhlasu:', e);
        }
        return null;
    }

    // Vytvoření HTML cookie banneru
    function createBanner() {
        const banner = document.createElement('div');
        banner.id = 'cookie-banner';
        banner.innerHTML = `
            <div class="cookie-banner-content">
                <div class="cookie-main">
                    <div class="cookie-text">
                        <p class="cookie-title">Nastavení cookies</p>
                        <p>Používáme cookies pro analýzu návštěvnosti a personalizaci obsahu.
                           <a href="/pages/ochrana-soukromi.html">Více informací</a></p>
                    </div>
                    <div class="cookie-buttons">
                        <button class="cookie-btn cookie-btn-settings" id="cookie-toggle-settings">Nastavení</button>
                        <button class="cookie-btn cookie-btn-reject" id="cookie-reject">Odmítnout vše</button>
                        <button class="cookie-btn cookie-btn-accept" id="cookie-accept">Přijmout vše</button>
                    </div>
                </div>
                <div class="cookie-settings" id="cookie-settings-panel">
                    <div class="cookie-category">
                        <div class="cookie-category-header">
                            <div class="cookie-category-info">
                                <strong>Nezbytné cookies</strong>
                                <p>Nutné pro fungování webu. Nelze vypnout.</p>
                            </div>
                            <label class="cookie-toggle disabled">
                                <input type="checkbox" checked disabled>
                                <span class="cookie-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="cookie-category">
                        <div class="cookie-category-header">
                            <div class="cookie-category-info">
                                <strong>Analytické cookies</strong>
                                <p>Pomáhají nám pochopit, jak web používáte (Google Analytics).</p>
                            </div>
                            <label class="cookie-toggle">
                                <input type="checkbox" id="consent-analytics">
                                <span class="cookie-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="cookie-category">
                        <div class="cookie-category-header">
                            <div class="cookie-category-info">
                                <strong>Marketingové cookies</strong>
                                <p>Používané pro cílenou reklamu (Meta Pixel, Google Ads).</p>
                            </div>
                            <label class="cookie-toggle">
                                <input type="checkbox" id="consent-marketing">
                                <span class="cookie-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="cookie-settings-buttons">
                        <button class="cookie-btn cookie-btn-save" id="cookie-save">Uložit nastavení</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(banner);
        return banner;
    }

    // Zobrazení banneru
    function showBanner() {
        const banner = document.getElementById('cookie-banner') || createBanner();
        requestAnimationFrame(() => {
            banner.classList.add('visible');
        });
        setupEventListeners();
    }

    // Skrytí banneru
    function hideBanner() {
        const banner = document.getElementById('cookie-banner');
        if (banner) {
            banner.classList.remove('visible');
        }
    }

    // Nastavení event listenerů
    function setupEventListeners() {
        const acceptBtn = document.getElementById('cookie-accept');
        const rejectBtn = document.getElementById('cookie-reject');
        const saveBtn = document.getElementById('cookie-save');
        const toggleSettingsBtn = document.getElementById('cookie-toggle-settings');
        const settingsPanel = document.getElementById('cookie-settings-panel');

        if (acceptBtn) {
            acceptBtn.addEventListener('click', function() {
                const consent = {
                    necessary: true,
                    analytics: true,
                    marketing: true
                };
                saveConsent(consent);
                updateGTMConsent(consent);
                hideBanner();
            });
        }

        if (rejectBtn) {
            rejectBtn.addEventListener('click', function() {
                const consent = {
                    necessary: true,
                    analytics: false,
                    marketing: false
                };
                saveConsent(consent);
                updateGTMConsent(consent);
                hideBanner();
            });
        }

        if (toggleSettingsBtn && settingsPanel) {
            toggleSettingsBtn.addEventListener('click', function() {
                settingsPanel.classList.toggle('visible');
                this.textContent = settingsPanel.classList.contains('visible') ? 'Skrýt' : 'Nastavení';
            });
        }

        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                const analyticsCheckbox = document.getElementById('consent-analytics');
                const marketingCheckbox = document.getElementById('consent-marketing');

                const consent = {
                    necessary: true,
                    analytics: analyticsCheckbox ? analyticsCheckbox.checked : false,
                    marketing: marketingCheckbox ? marketingCheckbox.checked : false
                };
                saveConsent(consent);
                updateGTMConsent(consent);
                hideBanner();
            });
        }
    }

    // Otevření nastavení (pro odkaz z privacy policy)
    window.openCookieSettings = function() {
        const banner = document.getElementById('cookie-banner') || createBanner();
        const settingsPanel = document.getElementById('cookie-settings-panel');
        const toggleBtn = document.getElementById('cookie-toggle-settings');

        // Načíst aktuální nastavení do checkboxů
        const consent = loadConsent() || defaultConsent;
        const analyticsCheckbox = document.getElementById('consent-analytics');
        const marketingCheckbox = document.getElementById('consent-marketing');

        if (analyticsCheckbox) analyticsCheckbox.checked = consent.analytics;
        if (marketingCheckbox) marketingCheckbox.checked = consent.marketing;

        // Zobrazit banner a panel nastavení
        banner.classList.add('visible');
        if (settingsPanel) settingsPanel.classList.add('visible');
        if (toggleBtn) toggleBtn.textContent = 'Skrýt';

        setupEventListeners();
    };

    // Hlavní inicializace
    function init() {
        // Nejprve inicializovat GTM Consent Mode
        initGTMConsent();

        // Kontrola existujícího souhlasu
        const existingConsent = loadConsent();

        if (existingConsent) {
            // Uživatel už dal souhlas - aplikovat jeho nastavení
            updateGTMConsent(existingConsent);
        } else {
            // Nový uživatel - zobrazit banner
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showBanner);
            } else {
                showBanner();
            }
        }
    }

    // Spustit IHNED (před GTM!)
    init();

})();
