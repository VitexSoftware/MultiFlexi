# MultiFlexi Security Enhancements Implementation

This document outlines the comprehensive security enhancements implemented for MultiFlexi as part of GDPR Phase 3.

## Overview

The security enhancements include:

1. **Security Audit Logging** - Comprehensive security event monitoring
2. **Data Encryption** - AES-256 encryption for sensitive data at rest
3. **API Rate Limiting** - Protection against brute force and DDoS attacks
4. **IP Whitelisting** - Administrative access control
5. **Two-Factor Authentication** - Enhanced authentication security
6. **Role-Based Access Control** - Granular permission management

## Components Implemented

### 1. Security Audit Logger (`MultiFlexi\Security\SecurityAuditLogger`)

Provides comprehensive logging of security-related events with varying severity levels.

#### Features:
- Event logging with severity levels (low, medium, high, critical)
- Automatic retention management (configurable)
- Query capabilities for security analysis
- Integration with other security components

#### Usage:
```php
// Initialize (done automatically in init.php)
$auditLogger = new SecurityAuditLogger($pdo);

// Log security events
$auditLogger->logEvent(
    'login_failed',
    'Failed login attempt from IP 192.168.1.100',
    'medium',
    null, // user_id
    ['ip_address' => '192.168.1.100', 'username' => 'admin']
);

// Get recent security events
$events = $auditLogger->getRecentEvents(50);

// Get security statistics
$stats = $auditLogger->getSecurityStatistics();
```

### 2. Data Encryption (`MultiFlexi\Security\DataEncryption`)

Implements AES-256-GCM and AES-256-CBC encryption for sensitive data at rest.

#### Features:
- Multiple encryption algorithms (AES-256-GCM, AES-256-CBC)
- Key rotation capabilities
- Master key encryption for key storage
- Bulk encryption operations

#### Usage:
```php
use MultiFlexi\Security\EncryptionHelpers;

// Encrypt sensitive data
$encryptedData = EncryptionHelpers::encryptData($sensitiveData);

// Decrypt data
$decryptedData = EncryptionHelpers::decryptData($encryptedData);

// Encrypt credential arrays
$credentials = ['username' => 'user', 'password' => 'secret'];
$encrypted = EncryptionHelpers::encryptCredentials($credentials);

// Decrypt credential arrays
$decrypted = EncryptionHelpers::decryptCredentials($encrypted);

// Check if encryption is available
if (EncryptionHelpers::isEncryptionAvailable()) {
    // Use encryption
}
```

### 3. API Rate Limiting (`MultiFlexi\Security\RateLimiter`)

Protects API endpoints from abuse and brute force attacks.

#### Features:
- Configurable rate limits per endpoint type
- IP-based and user-based limiting
- Automatic blocking with exponential backoff
- Statistics and monitoring

#### Default Limits:
- Login: 5 requests per 15 minutes
- API: 100 requests per hour
- Admin: 200 requests per hour
- Public: 50 requests per hour
- Upload: 10 requests per hour

#### Usage:
```php
use MultiFlexi\Security\RateLimitHelpers;

// Apply rate limiting to current request
RateLimitHelpers::applyRateLimit('api', '/api/v1/endpoint');

// Check rate limit without applying
$result = RateLimitHelpers::checkCurrentRequest('login');
if (!$result['allowed']) {
    // Handle rate limit exceeded
}

// Add custom rule
RateLimitHelpers::addRule('custom_endpoint', 50, 3600);

// Block/unblock IP addresses
RateLimitHelpers::blockIpAddress('192.168.1.100', 'all', 3600);
RateLimitHelpers::unblockIpAddress('192.168.1.100');
```

### 4. IP Whitelisting (`MultiFlexi\Security\IpWhitelist`)

Controls access to sensitive operations based on IP addresses.

#### Features:
- Support for single IPs and CIDR ranges
- Scope-based access control (admin, api, general)
- User-specific whitelists
- IPv4 and IPv6 support

#### Default Whitelist:
- 127.0.0.1 (localhost IPv4)
- ::1 (localhost IPv6)
- 10.0.0.0/8 (Private class A)
- 172.16.0.0/12 (Private class B)
- 192.168.0.0/16 (Private class C)

#### Usage:
```php
// Check if IP is whitelisted (done automatically when initialized)
$ipWhitelist = $GLOBALS['ipWhitelist'];
$isWhitelisted = $ipWhitelist->isWhitelisted('192.168.1.100', 'admin');

// Enforce whitelist for current request
$ipWhitelist->enforceWhitelist('admin', $userId, true);

// Add IP to whitelist
$ipWhitelist->addToWhitelist('203.0.113.1', null, 'Office IP', null, 'admin', $adminUserId);

// Add IP range to whitelist
$ipWhitelist->addToWhitelist(null, '203.0.113.0/24', 'Office network', null, 'admin', $adminUserId);
```

### 5. Two-Factor Authentication (`MultiFlexi\Security\TwoFactorAuth`)

Implements TOTP-based two-factor authentication.

#### Features:
- TOTP (Time-based One-Time Password) support
- QR code generation for easy setup
- Backup codes for account recovery
- Rate limiting on verification attempts

#### Usage:
```php
$twoFA = new TwoFactorAuth($pdo);

// Generate secret for user setup
$secret = $twoFA->generateSecret($userId);

// Get QR code URL for authenticator apps
$qrCodeUrl = $twoFA->getQrCodeUrl($userId, $secret, 'MultiFlexi');

// Verify TOTP code
$isValid = $twoFA->verifyTotp($userId, $totpCode);

// Enable 2FA for user
$twoFA->enableTwoFactor($userId, $secret);

// Generate backup codes
$backupCodes = $twoFA->generateBackupCodes($userId);
```

### 6. Role-Based Access Control (`MultiFlexi\Security\RoleBasedAccessControl`)

Implements granular permission management system.

#### Features:
- Hierarchical role system
- Permission-based access control
- Role inheritance
- Resource-specific permissions

#### Usage:
```php
$rbac = new RoleBasedAccessControl($pdo);

// Create role
$roleId = $rbac->createRole('editor', 'Content Editor', 'Can edit content but not system settings');

// Add permission to role
$rbac->addPermissionToRole($roleId, 'content_edit');

// Assign role to user
$rbac->assignRoleToUser($userId, $roleId);

// Check user permissions
$hasPermission = $rbac->userHasPermission($userId, 'content_edit');

// Check user role
$hasRole = $rbac->userHasRole($userId, 'editor');
```

## Configuration

### Environment Variables

Add these configuration options to your environment:

```bash
# Security Audit Logging
SECURITY_AUDIT_ENABLED=true
SECURITY_AUDIT_RETENTION_DAYS=90

# Data Encryption
DATA_ENCRYPTION_ENABLED=true
ENCRYPTION_MASTER_KEY=your-secure-master-key-here

# Rate Limiting
RATE_LIMITING_ENABLED=true

# IP Whitelisting (disabled by default)
IP_WHITELIST_ENABLED=false

# Two-Factor Authentication
TWO_FACTOR_AUTH_ENABLED=true

# Role-Based Access Control
RBAC_ENABLED=true
```

## Database Schema

The security components automatically create the following database tables:

1. `security_audit_log` - Security event logging
2. `encryption_keys` - Encryption key storage
3. `rate_limiting_rules` - Rate limiting configuration
4. `rate_limiting_attempts` - Rate limiting attempt tracking
5. `ip_whitelist` - IP whitelist entries
6. `two_factor_auth` - 2FA secrets and backup codes
7. `rbac_roles` - Role definitions
8. `rbac_permissions` - Permission definitions
9. `rbac_role_permissions` - Role-permission mappings
10. `rbac_user_roles` - User-role assignments

## Integration

All security components are automatically initialized in `src/init.php` and are available globally:

- `$GLOBALS['securityAuditLogger']`
- `$GLOBALS['dataEncryption']`
- `$GLOBALS['rateLimiter']`
- `$GLOBALS['ipWhitelist']` (if enabled)
- `$GLOBALS['twoFactorAuth']`
- `$GLOBALS['rbac']`

## Best Practices

### 1. Security Audit Logging
- Review security logs regularly
- Set up alerting for high/critical severity events
- Configure appropriate retention periods

### 2. Data Encryption
- Rotate encryption keys regularly
- Test encryption/decryption functionality
- Backup encryption keys securely

### 3. Rate Limiting
- Monitor rate limiting statistics
- Adjust limits based on usage patterns
- Implement proper error handling

### 4. IP Whitelisting
- Keep whitelist updated
- Use CIDR ranges for networks
- Document whitelist entries

### 5. Two-Factor Authentication
- Encourage users to enable 2FA
- Provide backup codes
- Test recovery procedures

### 6. Role-Based Access Control
- Follow principle of least privilege
- Regular access reviews
- Document role definitions

## Monitoring and Maintenance

### Regular Tasks

1. **Weekly**:
   - Review security audit logs
   - Check rate limiting statistics
   - Monitor blocked IPs

2. **Monthly**:
   - Rotate encryption keys
   - Review IP whitelist
   - Audit user roles and permissions

3. **Quarterly**:
   - Security audit log retention cleanup
   - Review and update rate limiting rules
   - Test backup and recovery procedures

### Alerts

Set up monitoring for:
- High severity security events
- Unusual rate limiting patterns
- IP whitelist violations
- Failed 2FA attempts
- Permission escalation attempts

## Testing

Test all security components with the included test methods:

```php
// Test encryption
$encryptionTest = EncryptionHelpers::testEncryption();

// Test rate limiting
$rateLimitTest = RateLimitHelpers::checkCurrentRequest('test');

// Test IP whitelist
$ipWhitelistTest = $GLOBALS['ipWhitelist']->isWhitelisted('127.0.0.1');

// Test 2FA
$twoFATest = $GLOBALS['twoFactorAuth']->verifyTotp($userId, '123456');
```

## Troubleshooting

### Common Issues

1. **Database connection errors**: Ensure PDO connection is properly configured
2. **Encryption errors**: Check that required PHP extensions are installed
3. **Rate limiting issues**: Verify table creation and proper IP detection
4. **IP whitelist blocking**: Check default whitelist entries and scope configuration

### Debug Mode

Enable detailed logging by setting:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Security Considerations

1. **Keep encryption keys secure** - Never store in version control
2. **Regular security updates** - Keep dependencies updated
3. **Monitor for vulnerabilities** - Subscribe to security advisories
4. **Test regularly** - Implement automated security testing
5. **Backup security data** - Include security tables in backups

This implementation provides a comprehensive security framework that can be extended and customized based on specific requirements.