<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi\Consent;

/**
 * GDPR Consent Helper Functions.
 *
 * Provides convenient functions for checking consent status in templates and pages.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
 */
class ConsentHelper
{
    private static ?ConsentManager $consentManager = null;

    /**
     * Check if user has given consent for analytics.
     *
     * @return bool True if analytics consent is granted
     */
    public static function hasAnalyticsConsent(): bool
    {
        return self::getConsentManager()->hasAnalyticsConsent();
    }

    /**
     * Check if user has given consent for marketing.
     *
     * @return bool True if marketing consent is granted
     */
    public static function hasMarketingConsent(): bool
    {
        return self::getConsentManager()->hasMarketingConsent();
    }

    /**
     * Check if user has given consent for functional cookies.
     *
     * @return bool True if functional consent is granted
     */
    public static function hasFunctionalConsent(): bool
    {
        return self::getConsentManager()->getConsentStatus(ConsentManager::CONSENT_FUNCTIONAL) === true;
    }

    /**
     * Check if user has given consent for personalization.
     *
     * @return bool True if personalization consent is granted
     */
    public static function hasPersonalizationConsent(): bool
    {
        return self::getConsentManager()->getConsentStatus(ConsentManager::CONSENT_PERSONALIZATION) === true;
    }

    /**
     * Get all consent statuses as an associative array.
     *
     * @return array Consent statuses keyed by type
     */
    public static function getAllConsentStatuses(): array
    {
        return self::getConsentManager()->getAllConsentStatuses();
    }

    /**
     * Check if user has given any consent (useful for determining if consent banner was shown).
     *
     * @return bool True if any consent has been recorded
     */
    public static function hasAnyConsent(): bool
    {
        $consents = self::getAllConsentStatuses();

        return !empty($consents);
    }

    /**
     * Render Google Analytics tracking code only if consent is given.
     *
     * @param string $trackingId Google Analytics tracking ID
     *
     * @return string HTML/JavaScript code or empty string
     */
    public static function renderGoogleAnalytics(string $trackingId): string
    {
        if (!self::hasAnalyticsConsent()) {
            return '';
        }

        return <<<HTML
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$trackingId}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$trackingId}');
</script>
HTML;
    }

    /**
     * Render Facebook Pixel code only if consent is given.
     *
     * @param string $pixelId Facebook Pixel ID
     *
     * @return string HTML/JavaScript code or empty string
     */
    public static function renderFacebookPixel(string $pixelId): string
    {
        if (!self::hasMarketingConsent()) {
            return '';
        }

        return <<<HTML
<!-- Facebook Pixel -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$pixelId}');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={$pixelId}&ev=PageView&noscript=1"
/></noscript>
HTML;
    }

    /**
     * Conditionally include external JavaScript based on consent.
     *
     * @param string $script      The JavaScript code to include
     * @param string $consentType The type of consent required
     *
     * @return string The script or empty string
     */
    public static function conditionalScript(string $script, string $consentType): string
    {
        $hasConsent = match ($consentType) {
            ConsentManager::CONSENT_ANALYTICS => self::hasAnalyticsConsent(),
            ConsentManager::CONSENT_MARKETING => self::hasMarketingConsent(),
            ConsentManager::CONSENT_FUNCTIONAL => self::hasFunctionalConsent(),
            ConsentManager::CONSENT_PERSONALIZATION => self::hasPersonalizationConsent(),
            ConsentManager::CONSENT_ESSENTIAL => true, // Always allowed
            default => false,
        };

        return $hasConsent ? $script : '';
    }

    /**
     * Generate consent status JavaScript object for use in frontend.
     *
     * @return string JavaScript code defining window.consentStatus
     */
    public static function renderConsentStatusJS(): string
    {
        $consents = self::getAllConsentStatuses();
        $jsObject = json_encode($consents, \JSON_PRETTY_PRINT);

        return <<<JS
window.consentStatus = {$jsObject};
window.hasConsent = function(type) {
    return window.consentStatus[type] && window.consentStatus[type].status === true;
};
JS;
    }

    /**
     * Render a consent-aware iframe that only loads if consent is given.
     *
     * @param string $src         The iframe source URL
     * @param string $consentType Required consent type
     * @param array  $attributes  Additional iframe attributes
     *
     * @return string HTML for the iframe or placeholder
     */
    public static function renderConsentAwareIframe(string $src, string $consentType, array $attributes = []): string
    {
        $hasConsent = self::conditionalScript('true', $consentType) !== '';

        if (!$hasConsent) {
            $message = _('This content requires your consent. Please update your privacy preferences.');

            return <<<HTML
<div class="consent-blocked-content">
    <div class="alert alert-info">
        <i class="fas fa-shield-alt"></i>
        {$message}
        <br>
        <a href="consent-preferences.php" class="btn btn-primary btn-sm mt-2">
            <i class="fas fa-cog"></i> {$_('Privacy Settings')}
        </a>
    </div>
</div>
HTML;
        }

        $attrString = '';

        foreach ($attributes as $key => $value) {
            $attrString .= ' '.htmlspecialchars($key).'="'.htmlspecialchars($value).'"';
        }

        return '<iframe src="'.htmlspecialchars($src).'"'.$attrString.'></iframe>';
    }

    /**
     * Create a consent preference button for easy access.
     *
     * @param string $text  Button text
     * @param string $class CSS classes
     *
     * @return string HTML button
     */
    public static function renderConsentPreferencesButton(?string $text = null, string $class = 'btn btn-outline-secondary'): string
    {
        $text ??= _('Cookie Settings');

        return <<<HTML
<button type="button" class="{$class}" onclick="if(window.multiFxiConsent) { window.multiFxiConsent.showConsentPreferences(); } else { window.location.href='consent-preferences.php'; }">
    <i class="fas fa-cookie-bite"></i> {$text}
</button>
HTML;
    }

    /**
     * Add consent checking to existing WebPage instance.
     *
     * @param \MultiFlexi\Ui\WebPage $page The page to add consent checking to
     */
    public static function enhancePageWithConsent(\MultiFlexi\Ui\WebPage $page): void
    {
        // Add consent status as JavaScript
        $page->addJavaScript(self::renderConsentStatusJS());

        // Add global consent checking function
        $page->addJavaScript(<<<'JS'
// Global function to check if we should load external content
window.shouldLoadExternalContent = function(type) {
    return window.hasConsent && window.hasConsent(type);
};

// Function to show consent preferences
window.showConsentPreferences = function() {
    if (window.multiFxiConsent) {
        window.multiFxiConsent.showConsentPreferences();
    } else {
        window.location.href = 'consent-preferences.php';
    }
};
JS);
    }

    /**
     * Get consent status for specific type.
     *
     * @param string $consentType The consent type to check
     *
     * @return null|bool True if granted, false if denied, null if not set
     */
    public static function getConsentStatus(string $consentType): ?bool
    {
        return self::getConsentManager()->getConsentStatus($consentType);
    }

    /**
     * Get the consent manager instance.
     */
    private static function getConsentManager(): ConsentManager
    {
        if (self::$consentManager === null) {
            self::$consentManager = new ConsentManager();
        }

        return self::$consentManager;
    }
}

// Global convenience functions for use in templates
if (!\function_exists('hasAnalyticsConsent')) {
    function hasAnalyticsConsent(): bool
    {
        return ConsentHelper::hasAnalyticsConsent();
    }
}

if (!\function_exists('hasMarketingConsent')) {
    function hasMarketingConsent(): bool
    {
        return ConsentHelper::hasMarketingConsent();
    }
}

if (!\function_exists('renderConsentPreferencesButton')) {
    function renderConsentPreferencesButton(?string $text = null, string $class = 'btn btn-outline-secondary'): string
    {
        return ConsentHelper::renderConsentPreferencesButton($text, $class);
    }
}
