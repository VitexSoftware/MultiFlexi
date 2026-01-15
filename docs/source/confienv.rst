Configuration Environment Variables
===================================

The following environment variables are used to configure MultiFlexi via ``Shared::cfg()``.

Database
--------

- **DB_CONNECTION**: Database driver (e.g., ``mysql``, ``pgsql``).
- **DB_HOST**: Database host address.
- **DB_PORT**: Database port (default: ``3306``).
- **DB_DATABASE**: Database name.
- **DB_USERNAME**: Database user name.
- **DB_PASSWORD**: Database password.

Security
--------

- **ENCRYPTION_MASTER_KEY**: Master key used for data encryption.
- **CSRF_PROTECTION_ENABLED**: Enable Cross-Site Request Forgery protection (default: ``true``).
- **BRUTE_FORCE_PROTECTION_ENABLED**: Enable protection against brute force attacks (default: ``true``).
- **BRUTE_FORCE_MAX_ATTEMPTS**: Maximum number of failed attempts allowed (default: ``5``).
- **BRUTE_FORCE_LOCKOUT_DURATION**: Duration in seconds to lock out an IP after max attempts (default: ``900``).
- **BRUTE_FORCE_TIME_WINDOW**: Time window in seconds to count attempts (default: ``300``).
- **BRUTE_FORCE_IP_LIMITING**: Enable IP-based limiting for brute force protection (default: ``true``).
- **SECURITY_LOGGING_ENABLED**: Enable security audit logging (default: ``true``).
- **DATA_ENCRYPTION_ENABLED**: Enable data encryption features (default: ``true``).
- **RATE_LIMITING_ENABLED**: Enable general rate limiting (default: ``true``).
- **IP_WHITELIST_ENABLED**: Enable IP whitelisting (default: ``false``).
- **TWO_FACTOR_AUTH_ENABLED**: Enable Two-Factor Authentication (default: ``true``).
- **RBAC_ENABLED**: Enable Role-Based Access Control (default: ``true``).
- **PASSWORD_MIN_LENGTH**: Minimum password length (default: ``8``).
- **PASSWORD_REQUIRE_UPPERCASE**: Require uppercase characters in password (default: ``true``).
- **PASSWORD_REQUIRE_LOWERCASE**: Require lowercase characters in password (default: ``true``).
- **PASSWORD_REQUIRE_NUMBERS**: Require numbers in password (default: ``true``).
- **PASSWORD_REQUIRE_SPECIAL_CHARS**: Require special characters in password (default: ``true``).

Session
-------

- **SESSION_TIMEOUT**: Session timeout in seconds (default: ``3600``).
- **SESSION_REGENERATION_INTERVAL**: Interval in seconds to regenerate session ID (default: ``300``).
- **SESSION_STRICT_USER_AGENT**: Enforce strict User-Agent checking for sessions (default: ``true``).
- **SESSION_STRICT_IP_ADDRESS**: Enforce strict IP address checking for sessions (default: ``false``).

API
---

- **API_DEBUG**: Enable API debug mode (default: ``false``).
- **API_RATE_LIMITING_ENABLED**: Enable API-specific rate limiting (default: ``true``).
- **API_RATE_LIMIT_REQUESTS**: Max API requests per window (default: ``100``).
- **API_RATE_LIMIT_WINDOW**: API rate limit window in seconds (default: ``3600``).

Email
-----

- **EMAIL_FROM**: Default sender address for emails (default: ``multiflexi@<SERVER_NAME>``).
- **SEND_INFO_TO**: Email address to send informational notifications to (default: ``false``).

Logging & Telemetry
-------------------

- **LOG_DIRECTORY**: Directory for log files (default: ``/var/log/multiflexi``).
- **ZABBIX_SERVER**: Zabbix server address for logging to Zabbix.
- **ENABLE_GOOGLE_ANALYTICS**: Enable Google Analytics (default: ``false``).
- **LIVE_OUTPUT_SOCKET**: WebSocket URI for live output (e.g., ``ws://localhost:8080``).

Other
-----
- **APP_NAME**: Application name.
- **APP_DEBUG**: Application debug mode.
- **APP_URL**: Application URL.
