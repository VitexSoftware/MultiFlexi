# Security Features Quick Reference

## GDPR Phase 3 Security Enhancements

This document provides a quick reference for developers working with MultiFlexi's security features.

## Security Components Available

### 1. Security Audit Logger ✅
**Global Access**: `$GLOBALS['securityAuditLogger']`
```php
// Log a security event
$GLOBALS['securityAuditLogger']->logEvent(
    'user_login_failed', 
    'Login failed for user@example.com', 
    'medium', 
    $userId, 
    ['ip_address' => $ip, 'user_agent' => $userAgent]
);

// Get recent security events
$events = $GLOBALS['securityAuditLogger']->getRecentEvents(50);
```

### 2. Data Encryption ✅
**Helper Class**: `MultiFlexi\Security\EncryptionHelpers`
```php
use MultiFlexi\Security\EncryptionHelpers;

// Encrypt sensitive data
$encrypted = EncryptionHelpers::encryptData($sensitiveData);

// Decrypt data
$decrypted = EncryptionHelpers::decryptData($encrypted);

// Encrypt credentials array
$credentials = ['username' => 'user', 'password' => 'secret'];
$encryptedCreds = EncryptionHelpers::encryptCredentials($credentials);
```

### 3. API Rate Limiting ✅
**Helper Class**: `MultiFlexi\Security\RateLimitHelpers`
```php
use MultiFlexi\Security\RateLimitHelpers;

// Apply rate limiting to current request
RateLimitHelpers::applyRateLimit('api', '/api/endpoint');

// Check rate limit without applying
$result = RateLimitHelpers::checkCurrentRequest('login');
if (!$result['allowed']) {
    // Handle rate limit exceeded
}
```

### 4. IP Whitelisting ✅
**Global Access**: `$GLOBALS['ipWhitelist']` (if enabled)
```php
// Check if IP is whitelisted
$isAllowed = $GLOBALS['ipWhitelist']->isWhitelisted('192.168.1.100', 'admin');

// Enforce whitelist for current request
$GLOBALS['ipWhitelist']->enforceWhitelist('admin', $userId);
```

## Configuration

Set these environment variables in your `.env` or configuration:

```bash
# Enable security features
SECURITY_AUDIT_ENABLED=true
DATA_ENCRYPTION_ENABLED=true
RATE_LIMITING_ENABLED=true
IP_WHITELIST_ENABLED=false

# Required for encryption
ENCRYPTION_MASTER_KEY=your-secure-master-key-here

# Optional retention settings
SECURITY_AUDIT_RETENTION_DAYS=90
```

## Common Usage Patterns

### Protecting an API Endpoint
```php
// At the beginning of your API endpoint
use MultiFlexi\Security\RateLimitHelpers;

RateLimitHelpers::applyRateLimit('api', $_SERVER['REQUEST_URI']);

// Your API logic here
```

### Securing Administrative Functions
```php
// For admin-only actions
if (isset($GLOBALS['ipWhitelist'])) {
    $GLOBALS['ipWhitelist']->enforceWhitelist('admin', $currentUserId);
}

// Log the administrative action
if (isset($GLOBALS['securityAuditLogger'])) {
    $GLOBALS['securityAuditLogger']->logEvent(
        'admin_config_changed',
        'Configuration updated by admin',
        'medium',
        $currentUserId
    );
}
```

### Encrypting Sensitive Form Data
```php
use MultiFlexi\Security\EncryptionHelpers;

// Before saving to database
if (EncryptionHelpers::isEncryptionAvailable()) {
    $formData['sensitive_field'] = EncryptionHelpers::encryptData($formData['sensitive_field']);
}

// When retrieving from database
if (EncryptionHelpers::isEncryptionAvailable()) {
    $formData['sensitive_field'] = EncryptionHelpers::decryptData($formData['sensitive_field']);
}
```

## Testing Security Components

```bash
# Test all security components
php -r "
use MultiFlexi\Security\EncryptionHelpers;
use MultiFlexi\Security\RateLimitHelpers;

echo 'Encryption: ' . (EncryptionHelpers::testEncryption() ? 'PASS' : 'FAIL') . PHP_EOL;
echo 'Rate Limiting: ' . (RateLimitHelpers::isRateLimitingAvailable() ? 'Available' : 'Disabled') . PHP_EOL;
echo 'Audit Logger: ' . (isset(\$GLOBALS['securityAuditLogger']) ? 'Available' : 'Not initialized') . PHP_EOL;
"
```

## Database Tables Created

The security system automatically creates these tables:
- `security_audit_log` - Security events
- `encryption_keys` - Encryption key management
- `rate_limiting_rules` - Rate limit configuration
- `rate_limiting_attempts` - Rate limit tracking
- `ip_whitelist` - IP address whitelist

## Error Handling

All security components include error handling that logs failures without breaking the application:

```php
try {
    $encrypted = EncryptionHelpers::encryptData($data);
} catch (\Exception $e) {
    error_log('Encryption failed: ' . $e->getMessage());
    // Handle gracefully - perhaps store unencrypted with warning
}
```

## Best Practices

1. **Always check availability** before using security components
2. **Log security events** for audit trails
3. **Handle errors gracefully** to maintain application functionality
4. **Test security features** in development environment
5. **Monitor security logs** regularly in production

## Getting Help

- See `SECURITY_IMPLEMENTATION.md` for comprehensive documentation
- Check `GDPR_PHASE3_SECURITY.md` for compliance details
- Review source code in `src/MultiFlexi/Security/` directory

## Status Overview

- ✅ Security Audit Logger - **COMPLETED**
- ✅ Data Encryption - **COMPLETED**
- ✅ API Rate Limiting - **COMPLETED**
- ✅ IP Whitelisting - **COMPLETED**
- 🚧 Two-Factor Authentication - **IN PROGRESS**
- 🚧 Role-Based Access Control - **IN PROGRESS**