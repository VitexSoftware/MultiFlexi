/**
 * MultiFlexi GDPR Consent Banner
 * 
 * Provides a comprehensive consent management interface for GDPR compliance.
 * Handles cookie consent, analytics, marketing, and other data processing preferences.
 * 
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2024 Vitex Software
 */

class MultiFxiConsentBanner {
    constructor() {
        this.consentVersion = '1.0';
        this.apiEndpoint = 'consent-api.php';
        this.consentTypes = {
            essential: {
                name: 'Essential',
                description: 'Required for basic site functionality',
                required: true
            },
            functional: {
                name: 'Functional',
                description: 'Enhance site functionality and personalization',
                required: false
            },
            analytics: {
                name: 'Analytics',
                description: 'Help us understand how you use our site',
                required: false
            },
            marketing: {
                name: 'Marketing',
                description: 'Used for advertising and promotional content',
                required: false
            },
            personalization: {
                name: 'Personalization',
                description: 'Customize content and experience based on your preferences',
                required: false
            }
        };
        
        this.currentConsent = {};
        this.bannerVisible = false;
        
        this.init();
    }

    async init() {
        // Check if consent already exists
        const existingConsent = await this.getExistingConsent();
        
        if (!existingConsent || this.needsConsentRefresh(existingConsent)) {
            this.showConsentBanner();
        } else {
            this.currentConsent = existingConsent;
            this.applyConsent();
        }
    }

    async getExistingConsent() {
        // First check if consent is stored in cookies (for anonymous users)
        const cookieConsent = this.getConsentFromCookie();
        if (cookieConsent && !this.needsConsentRefresh(cookieConsent)) {
            return cookieConsent;
        }
        
        // Then check server-side storage
        try {
            const response = await fetch(this.apiEndpoint + '?action=get_consent', {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.consent && Object.keys(data.consent).length > 0) {
                    return data.consent;
                }
            }
        } catch (error) {
            console.error('Error fetching consent:', error);
        }
        
        return null;
    }

    needsConsentRefresh(consent) {
        // Check if consent is older than 6 months or version changed
        if (!consent.granted_at) return true;
        
        const sixMonthsAgo = new Date();
        sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6);
        const consentDate = new Date(consent.granted_at);
        
        return consentDate < sixMonthsAgo || consent.version !== this.consentVersion;
    }

    getConsentFromCookie() {
        const cookieValue = this.getCookie('multiflexi_consent');
        if (!cookieValue) return null;
        
        try {
            const data = JSON.parse(decodeURIComponent(cookieValue));
            // Ensure the data has the expected format for needsConsentRefresh
            if (data && typeof data === 'object') {
                return data;
            }
        } catch (error) {
            console.error('Error parsing consent cookie:', error);
        }
        
        return null;
    }

    saveConsentToCookie(consent) {
        const consentData = {
            consent: consent,
            granted_at: new Date().toISOString(),
            version: this.consentVersion
        };
        
        const cookieValue = encodeURIComponent(JSON.stringify(consentData));
        // Set cookie for 1 year
        const expires = new Date();
        expires.setFullYear(expires.getFullYear() + 1);
        
        document.cookie = `multiflexi_consent=${cookieValue}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
    }

    getCookie(name) {
        const nameEQ = name + '=';
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    showConsentBanner() {
        if (this.bannerVisible) return;
        
        this.bannerVisible = true;
        const banner = this.createBannerHTML();
        document.body.appendChild(banner);
        
        // Add event listeners
        this.attachEventListeners();
        
        // Show banner with animation
        setTimeout(() => {
            banner.classList.add('show');
        }, 100);
    }

    createBannerHTML() {
        const banner = document.createElement('div');
        banner.id = 'gdpr-consent-banner';
        banner.className = 'gdpr-consent-banner';
        
        banner.innerHTML = `
            <div class="gdpr-banner-overlay"></div>
            <div class="gdpr-banner-content">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="font-awesome-icon" width="1em" height="1em" fill="currentColor"><!--! Font Awesome Pro 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2024 Fonticons, Inc. --><path d="M96 128a128 128 0 1 0 256 0A128 128 0 1 0 96 128zm320 0a128 128 0 1 0 256 0 128 128 0 1 0 -256 0zm-128 320a128 128 0 1 0 256 0 128 128 0 1 0 -256 0zM256 0c-17.7 0-32 14.3-32 32V64c0 17.7 14.3 32 32 32H416c17.7 0 32-14.3 32-32V32c0-17.7-14.3-32-32-32H256zM0 128C0 57.3 57.3 0 128 0H224c17.7 0 32 14.3 32 32s-14.3 32-32 32H128C92.7 64 64 92.7 64 128V384c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V288c0-17.7 14.3-32 32-32s32 14.3 32 32V384c0 70.7-57.3 128-128 128H128C57.3 512 0 454.7 0 384V128z"/></svg>
                                        ${this.translate('We Value Your Privacy')}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-3">
                                        ${this.translate('We use cookies and similar technologies to provide, protect, and improve our products and services. Please choose your preferences below.')}
                                    </p>
                                    
                                    <div class="consent-options">
                                        ${this.generateConsentOptions()}
                                    </div>
                                    
                                    <div class="consent-actions mt-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-outline-secondary btn-block" id="consent-decline-all">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="font-awesome-icon" width="1em" height="1em" fill="currentColor"><!--! Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc. --><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
                                                    ${this.translate('Decline All')}
                                                </button>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-success btn-block" id="consent-accept-all">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="font-awesome-icon" width="1em" height="1em" fill="currentColor"><path d="M173.898 439.404l-166.4-166.4c-10.72-10.72-10.72-28.08 0-38.8l38.8-38.8c10.72-10.72 28.08-10.72 38.8 0l117.6 117.6 204.4-204.4c10.72-10.72 28.08-10.72 38.8 0l38.8 38.8c10.72 10.72 10.72 28.08 0 38.8l-240 240c-10.72 10.72-28.08 10.72-38.8 0z"></path></svg>
                                                    ${this.translate('Accept All')}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <button type="button" class="btn btn-primary btn-block" id="consent-save-preferences">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="font-awesome-icon" width="1em" height="1em" fill="currentColor"><path d="M500.3 373.6c-10.3 21.2-22.8 40.7-37.6 58.1c-2.5 3-5.1 5.9-7.8 8.8c-15.7 17.2-33.2 32.5-52.1 45.1c-10.3 6.9-20.9 13.1-31.8 18.4c-13.5 6.6-27.4 12.1-41.6 16.4c-2.5 .8-5.1 1.6-7.6 2.3c-20.8 5.7-42.2 9.1-64 9.1c-21.8 0-43.2-3.4-64-9.1c-2.5-.7-5.1-1.5-7.6-2.3c-14.2-4.3-28.1-9.8-41.6-16.4c-10.9-5.3-21.5-11.5-31.8-18.4c-18.9-12.6-36.4-27.9-52.1-45.1c-2.7-2.9-5.3-5.8-7.8-8.8c-14.8-17.4-27.3-36.9-37.6-58.1c-6.9-14.2-12.1-29-16.1-44.2c-.7-2.5-1.4-5-2-7.6c-5.7-20.8-9.1-42.2-9.1-64s3.4-43.2 9.1-64c.6-2.6 1.3-5.1 2-7.6c4-15.2 9.2-30 16.1-44.2c10.3-21.2 22.8-40.7 37.6-58.1c2.5-3 5.1-5.9 7.8-8.8c15.7-17.2 33.2-32.5 52.1-45.1c10.3-6.9 20.9-13.1 31.8-18.4c13.5-6.6 27.4-12.1 41.6-16.4c2.5-.8 5.1-1.6 7.6-2.3c20.8-5.7 42.2-9.1 64-9.1s43.2 3.4 64 9.1c2.5 .7 5.1 1.5 7.6 2.3c14.2 4.3 28.1 9.8 41.6 16.4c10.9 5.3 21.5 11.5 31.8 18.4c18.9 12.6 36.4 27.9 52.1 45.1c2.7 2.9 5.3 5.8 7.8 8.8c14.8 17.4 27.3 36.9 37.6 58.1c6.9 14.2 12.1 29 16.1 44.2c.7 2.5 1.4 5 2 7.6c5.7 20.8 9.1 42.2 9.1 64s-3.4 43.2-9.1 64c-.6 2.6-1.3 5.1-2 7.6c-4 15.2-9.2 30-16.1 44.2zM256 320a64 64 0 1 0 0-128 64 64 0 1 0 0 128z"/></svg>
                                                    ${this.translate('Save My Preferences')}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-3 text-center">
                                            <small class="text-muted">
                                                <a href="privacy-policy.php" target="_blank">${this.translate('Privacy Policy')}</a>
                                                |
                                                <a href="cookie-policy.php" target="_blank">${this.translate('Cookie Policy')}</a>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        return banner;
    }

    generateConsentOptions() {
        let html = '';
        
        for (const [type, config] of Object.entries(this.consentTypes)) {
            const checked = config.required ? 'checked disabled' : '';
            const switchClass = config.required ? 'text-success' : '';
            
            html += `
                <div class="form-group consent-option">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="consent-info flex-grow-1">
                            <h6 class="mb-1">${this.translate(config.name)}</h6>
                            <small class="text-muted">${this.translate(config.description)}</small>
                        </div>
                        <div class="consent-toggle ml-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input consent-checkbox" 
                                       id="consent-${type}" data-consent-type="${type}" ${checked}>
                                <label class="custom-control-label ${switchClass}" for="consent-${type}">
                                    ${config.required ? this.translate('Required') : ''}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        return html;
    }

    attachEventListeners() {
        const banner = document.getElementById('gdpr-consent-banner');
        
        // Accept all button
        banner.querySelector('#consent-accept-all').addEventListener('click', () => {
            this.acceptAll();
        });
        
        // Decline all button
        banner.querySelector('#consent-decline-all').addEventListener('click', () => {
            this.declineAll();
        });
        
        // Save preferences button
        banner.querySelector('#consent-save-preferences').addEventListener('click', () => {
            this.savePreferences();
        });
        
        // Prevent banner from closing when clicking inside
        banner.querySelector('.gdpr-banner-content').addEventListener('click', (e) => {
            e.stopPropagation();
        });
        
        // Close banner when clicking overlay
        banner.querySelector('.gdpr-banner-overlay').addEventListener('click', () => {
            // Don't allow closing without making a choice
            this.showMessage('Please make a choice about your privacy preferences.', 'warning');
        });
    }

    async acceptAll() {
        const preferences = {};
        
        for (const type of Object.keys(this.consentTypes)) {
            preferences[type] = true;
        }
        
        await this.saveConsent(preferences);
    }

    async declineAll() {
        const preferences = {};
        
        for (const [type, config] of Object.entries(this.consentTypes)) {
            // Essential cookies cannot be declined
            preferences[type] = config.required;
        }
        
        await this.saveConsent(preferences);
    }

    async savePreferences() {
        const preferences = {};
        const checkboxes = document.querySelectorAll('.consent-checkbox');
        
        checkboxes.forEach(checkbox => {
            const type = checkbox.dataset.consentType;
            preferences[type] = checkbox.checked;
        });
        
        await this.saveConsent(preferences);
    }

    async saveConsent(preferences) {
        this.showLoading(true);
        
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action: 'save_consent',
                    consent: preferences,
                    version: this.consentVersion
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                
                if (result.success) {
                    this.currentConsent = preferences;
                    // Also save to cookies for persistence across sessions
                    this.saveConsentToCookie(preferences);
                    this.applyConsent();
                    this.hideBanner();
                    this.showMessage('Your privacy preferences have been saved.', 'success');
                } else {
                    throw new Error(result.message || 'Failed to save consent');
                }
            } else {
                throw new Error('Server error');
            }
        } catch (error) {
            console.error('Error saving consent:', error);
            this.showMessage('Error saving preferences. Please try again.', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    applyConsent() {
        // Apply consent settings to various services
        
        if (this.currentConsent.analytics) {
            this.enableAnalytics();
        } else {
            this.disableAnalytics();
        }
        
        if (this.currentConsent.marketing) {
            this.enableMarketing();
        } else {
            this.disableMarketing();
        }
        
        if (this.currentConsent.functional) {
            this.enableFunctional();
        } else {
            this.disableFunctional();
        }
        
        if (this.currentConsent.personalization) {
            this.enablePersonalization();
        } else {
            this.disablePersonalization();
        }
        
        // Fire custom event for other scripts to listen
        document.dispatchEvent(new CustomEvent('consentApplied', {
            detail: this.currentConsent
        }));
    }

    enableAnalytics() {
        // Enable Google Analytics, etc.
        if (typeof gtag !== 'undefined') {
            gtag('consent', 'update', {
                'analytics_storage': 'granted'
            });
        }
        
        console.log('Analytics enabled');
    }

    disableAnalytics() {
        if (typeof gtag !== 'undefined') {
            gtag('consent', 'update', {
                'analytics_storage': 'denied'
            });
        }
        
        console.log('Analytics disabled');
    }

    enableMarketing() {
        if (typeof gtag !== 'undefined') {
            gtag('consent', 'update', {
                'ad_storage': 'granted'
            });
        }
        
        console.log('Marketing enabled');
    }

    disableMarketing() {
        if (typeof gtag !== 'undefined') {
            gtag('consent', 'update', {
                'ad_storage': 'denied'
            });
        }
        
        console.log('Marketing disabled');
    }

    enableFunctional() {
        console.log('Functional cookies enabled');
    }

    disableFunctional() {
        console.log('Functional cookies disabled');
    }

    enablePersonalization() {
        console.log('Personalization enabled');
    }

    disablePersonalization() {
        console.log('Personalization disabled');
    }

    hideBanner() {
        const banner = document.getElementById('gdpr-consent-banner');
        if (banner) {
            banner.classList.remove('show');
            setTimeout(() => {
                banner.remove();
                this.bannerVisible = false;
            }, 300);
        }
    }

    showLoading(show) {
        const buttons = document.querySelectorAll('#gdpr-consent-banner button');
        buttons.forEach(button => {
            button.disabled = show;
            if (show) {
                button.innerHTML = '<svg class="fa-spinner" width="1em" height="1em" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zM108.922 108.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.51-21.491-48-48-48z" /></svg> Processing...';
            }
        });
    }

    showMessage(message, type = 'info') {
        // Create a toast notification
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${this.getIconForType(type)}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    getIconForType(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    translate(text) {
        // Basic translation function - can be extended with proper i18n
        // For now, return English text
        return text;
    }

    // Public method to show consent preferences
    showConsentPreferences() {
        this.showConsentBanner();
    }

    // Public method to get current consent status
    getConsentStatus() {
        return { ...this.currentConsent };
    }

    // Public method to check specific consent
    hasConsent(type) {
        return this.currentConsent[type] === true;
    }
}

// CSS styles for the consent banner
const consentBannerCSS = `
<style>
.gdpr-consent-banner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gdpr-consent-banner.show {
    opacity: 1;
}

.gdpr-banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
}

.gdpr-banner-content {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
}

.gdpr-banner-content .card {
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.consent-option {
    border-bottom: 1px solid #eee;
    padding: 15px 0;
}

.consent-option:last-child {
    border-bottom: none;
}

.consent-toggle .custom-control-label::before {
    transition: all 0.3s ease;
}

.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    background: white;
    border-radius: 5px;
    padding: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.toast-notification.show {
    transform: translateX(0);
}

.toast-success {
    border-left: 4px solid #28a745;
    color: #28a745;
}

.toast-error {
    border-left: 4px solid #dc3545;
    color: #dc3545;
}

.toast-warning {
    border-left: 4px solid #ffc107;
    color: #856404;
}

.toast-info {
    border-left: 4px solid #17a2b8;
    color: #17a2b8;
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

@media (max-width: 768px) {
    .gdpr-banner-content .card {
        max-height: 95vh;
        margin: 10px;
    }
    
    .consent-actions .col-md-6 {
        margin-bottom: 10px;
    }
}
</style>
`;

// Initialize the consent banner when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add CSS to page
    document.head.insertAdjacentHTML('beforeend', consentBannerCSS);
    
    // Initialize consent banner
    window.multiFxiConsent = new MultiFxiConsentBanner();
});

// Make it available globally
window.MultiFxiConsentBanner = MultiFxiConsentBanner;