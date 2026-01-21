# GDPR Consent Management System

This document describes the GDPR Phase 3 consent management system implementation for MultiFlexi.

## Overview

The consent management system provides comprehensive GDPR compliance by allowing users to control how their data is processed. It includes granular consent options for cookies, analytics, marketing, and other data processing activities.

## Components

### 1. Database Schema

#### Tables Created
- **`consent`** - Stores user consent preferences with timestamps and audit information
- **`consent_log`** - Maintains audit trail of all consent actions

#### Migration File
- `db/migrations/20241020100500_consent_management.php` (in multiflexi-database project)

### 2. Backend Classes

#### ConsentManager (`src/MultiFlexi/Consent/ConsentManager.php`)
Main backend class for managing consent operations:
- Record user consent preferences
- Retrieve consent status
- Withdraw consent
- Audit trail logging
- Support for both logged-in users and anonymous sessions

#### ConsentHelper (`src/MultiFlexi/Consent/ConsentHelper.php`)
Utility functions for easy integration:
- Check consent status in templates
- Conditional content rendering based on consent
- Google Analytics and Facebook Pixel integration
- Consent-aware iframes

### 3. Frontend Components

#### JavaScript Consent Banner (`src/js/consent-banner.js`)
- User-friendly modal interface with Bootstrap 4 styling
- Granular consent options with descriptions
- Accept all / Decline all / Custom preferences
- Automatic detection of existing consent
- Integration with Google Analytics consent API
- Cookie-based consent persistence with 1-year expiration
- Automatic consent refresh after 6 months or version changes
- Consistent data structure handling between cookie and database storage

#### Consent API (`src/consent-api.php`)
AJAX endpoint for consent operations:
- Get current consent status
- Save consent preferences
- Withdraw specific consent types
- Admin statistics (if enabled)

#### Consent Preferences Page (`src/consent-preferences.php`)
Full-featured consent management interface for users:
- Detailed consent type explanations
- Toggle switches for each consent type
- Consent history display
- Withdrawal functionality

### 4. Integration

#### Automatic Integration (`src/init.php`)
- Consent manager initialization on every page load
- Automatic JavaScript inclusion (except API endpoints)
- Enhanced page functionality for consent checking

#### Navigation Integration (`src/MultiFlexi/Ui/MainMenu.php`)
- Privacy menu item added to main navigation
- Direct access to consent preferences

## Consent Types

The system supports five types of consent:

1. **Essential** - Always required, cannot be disabled
2. **Functional** - Enhanced functionality and personalization
3. **Analytics** - Usage analytics and performance monitoring
4. **Marketing** - Advertising and promotional content
5. **Personalization** - Customized content based on preferences

## Usage Examples

### Checking Consent in PHP

```php
use MultiFlexi\Consent\ConsentHelper;

// Check specific consent types
if (ConsentHelper::hasAnalyticsConsent()) {
    // Load analytics tracking
}

if (ConsentHelper::hasMarketingConsent()) {
    // Show marketing content
}

// Conditional Google Analytics
echo ConsentHelper::renderGoogleAnalytics('GA-XXXXXX-X');

// Consent-aware iframe
echo ConsentHelper::renderConsentAwareIframe(
    'https://example.com/video',
    ConsentManager::CONSENT_MARKETING,
    ['width' => '100%', 'height' => '400']
);
```

### Checking Consent in JavaScript

```javascript
// Check consent status
if (window.hasConsent('analytics')) {
    // Initialize analytics
}

// Listen for consent changes
document.addEventListener('consentApplied', function(event) {
    const consent = event.detail;
    if (consent.marketing) {
        // Enable marketing features
    }
});

// Show consent preferences
window.showConsentPreferences();
```

### Global Functions

```php
// Available globally in templates
if (hasAnalyticsConsent()) {
    // Analytics enabled
}

if (hasMarketingConsent()) {
    // Marketing enabled
}

echo renderConsentPreferencesButton('Privacy Settings', 'btn btn-primary');
```

## File Structure

```
MultiFlexi/
├── src/
│   ├── MultiFlexi/
│   │   └── Consent/
│   │       ├── ConsentManager.php     # Main consent management class
│   │       └── ConsentHelper.php      # Helper functions and utilities
│   ├── js/
│   │   └── consent-banner.js          # Frontend consent banner
│   ├── consent-api.php               # AJAX API endpoint
│   ├── consent-preferences.php       # User consent preferences page
│   ├── test-consent.php             # Testing page
│   └── init.php                     # Enhanced with consent integration

multiflexi-database/
└── db/
    └── migrations/
        └── 20241020100500_consent_management.php  # Database schema
```

## Configuration

### Analytics Configuration

MultiFlexi supports both third-party (Google Analytics) and self-hosted analytics solutions. Configure this in your `.env` file:

```bash
# Enable Google Analytics (set to true only if you want to use Google Analytics)
# When false, documentation will recommend self-hosted analytics like Matomo or AWStats
ENABLE_GOOGLE_ANALYTICS=false  # Default: false (European self-hosting friendly)
```

#### When ENABLE_GOOGLE_ANALYTICS=false (Default)
- Cookie policy shows message about self-hosted analytics
- No Google Analytics cookies are documented
- Analytics consent description mentions Matomo/AWStats
- Compliant with European privacy preferences
- Recommended for self-hosted installations

#### When ENABLE_GOOGLE_ANALYTICS=true
- Cookie policy shows Google Analytics cookie details (_ga, _ga_*, _gid)
- Standard analytics consent description is used
- Full third-party analytics documentation

### System Configuration

No additional configuration is required beyond analytics preferences. The system automatically:
- Detects user login status
- Uses session IDs for anonymous users
- Integrates with existing MultiFlexi authentication
- Respects existing database configuration
- Adapts documentation based on ENABLE_GOOGLE_ANALYTICS setting

## Testing

Use the test page to verify implementation:
```
https://your-domain.com/test-consent.php
```

The test page provides:
- Current consent status display
- Helper function verification
- Conditional content examples
- Test controls for different scenarios
- Integration with consent banner and preferences

## GDPR Compliance Features

1. **Granular Consent** - Users can choose specific types of data processing
2. **Withdrawal Rights** - Easy withdrawal of consent at any time
3. **Audit Trail** - Complete logging of consent actions with timestamps
4. **Data Minimization** - Only essential cookies enabled by default
5. **Transparency** - Clear descriptions of each consent type
6. **User Control** - Full user control over privacy preferences
7. **Session Support** - Works for both logged-in users and anonymous visitors

## API Reference

### ConsentManager Methods

- `getConsentStatus(string $type): ?bool` - Get consent status for specific type
- `getAllConsentStatuses(): array` - Get all consent preferences
- `recordConsent(string $type, bool $status, ...): bool` - Record consent decision
- `withdrawConsent(string $type): bool` - Withdraw specific consent
- `hasAnalyticsConsent(): bool` - Check analytics consent
- `hasMarketingConsent(): bool` - Check marketing consent

### ConsentHelper Methods

- `hasAnalyticsConsent(): bool` - Static analytics consent check
- `hasMarketingConsent(): bool` - Static marketing consent check
- `renderGoogleAnalytics(string $id): string` - Conditional GA code
- `renderConsentAwareIframe(...): string` - Protected iframe
- `conditionalScript(string $script, string $type): string` - Conditional JS

### JavaScript API

- `window.multiFxiConsent` - Main consent banner instance
- `window.consentStatus` - Current consent status object  
- `window.hasConsent(type)` - Check specific consent type
- `window.showConsentPreferences()` - Show preferences dialog

## Migration Guide

To apply the database changes:

1. Run the Phinx migration:
```bash
vendor/bin/phinx migrate
```

2. The system is automatically active - no additional setup required.

3. Users will see the consent banner on first visit.

4. Access consent preferences via the Privacy menu or direct URL.

## Troubleshooting

### Users Asked for Consent Repeatedly

**Symptoms:** Users report being asked for cookie consent again and again, even after accepting.

**Root Cause:** Data structure mismatch between cookie storage format and in-memory format.

**Solution (Fixed in v2.x):** The consent banner now properly normalizes data structures:
- Cookie format: `{consent: {...}, granted_at: "...", version: "1.0"}`
- Internal format: `{essential: true, analytics: true, ...}`
- The `getConsentFromCookie()` method extracts the inner `consent` object and checks expiration

**If you encounter this issue:**
1. Update to the latest version of `consent-banner.js`
2. Users may need to clear their `multiflexi_consent` cookie once
3. The issue will resolve automatically on next consent submission

### Consent Not Persisting Across Sessions

**Check:**
1. Verify the `multiflexi_consent` cookie is being set (browser DevTools → Application → Cookies)
2. Check cookie expiration date (should be 1 year from creation)
3. Verify cookie path is set to `/` and SameSite is `Lax`
4. For logged-in users, check database `consent` table for records

### Cookie Structure Verification

**Expected cookie format:**
```javascript
{
  "consent": {
    "essential": true,
    "functional": true,
    "analytics": true,
    "marketing": false,
    "personalization": true
  },
  "granted_at": "2026-01-21T12:00:00.000Z",
  "version": "1.0"
}
```

This implementation provides a complete, GDPR-compliant consent management system that integrates seamlessly with the existing MultiFlexi architecture.
