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

namespace MultiFlexi\Security;

/**
 * CSRF protection helper class with token generation and validation.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class CsrfProtection
{
    private SessionManager $sessionManager;
    private array $exemptRoutes;

    public function __construct(SessionManager $sessionManager, array $exemptRoutes = [])
    {
        $this->sessionManager = $sessionManager;
        $this->exemptRoutes = array_merge($exemptRoutes, [
            'api/', // API endpoints use different auth
            'login.php',
            'passwordrecovery.php',
        ]);
    }

    /**
     * Generate CSRF token for forms.
     */
    public function generateToken(): string
    {
        return $this->sessionManager->getCsrfToken();
    }

    /**
     * Create CSRF token input field for forms.
     */
    public function createTokenInput(): string
    {
        $token = $this->generateToken();

        return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars($token, \ENT_QUOTES, 'UTF-8').'">';
    }

    /**
     * Create CSRF token meta tag for JavaScript.
     */
    public function createTokenMetaTag(): string
    {
        $token = $this->generateToken();

        return '<meta name="csrf-token" content="'.htmlspecialchars($token, \ENT_QUOTES, 'UTF-8').'">';
    }

    /**
     * Validate CSRF token from request.
     */
    public function validateToken(?string $token = null): bool
    {
        if ($token === null) {
            $token = self::getTokenFromRequest();
        }

        if (empty($token)) {
            return false;
        }

        return $this->sessionManager->validateCsrfToken($token);
    }

    /**
     * Check if current route is exempt from CSRF protection.
     */
    public function isExemptRoute(): bool
    {
        $currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        foreach ($this->exemptRoutes as $route) {
            if (str_contains($currentScript, $route) || str_contains($requestUri, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate CSRF protection for current request.
     */
    public function validateRequest(): bool
    {
        // Skip validation for GET requests and exempt routes
        if ($_SERVER['REQUEST_METHOD'] === 'GET' || $this->isExemptRoute()) {
            return true;
        }

        return $this->validateToken();
    }

    /**
     * Handle CSRF validation failure.
     */
    public function handleCsrfFailure(): void
    {
        // Log security event
        if (class_exists('\\MultiFlexi\\LogToSQL')) {
            $logger = \MultiFlexi\LogToSQL::singleton();
            $logger->addStatusMessage('CSRF token validation failed', 'security');
        }

        // Send appropriate response
        if (self::isAjaxRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'CSRF token validation failed',
                'code' => 403,
            ]);
        } else {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1>';
            echo '<p>CSRF token validation failed. Please refresh the page and try again.</p>';
        }

        exit;
    }

    /**
     * Middleware to automatically validate CSRF tokens.
     */
    public function middleware(): void
    {
        if (!$this->validateRequest()) {
            $this->handleCsrfFailure();
        }
    }

    /**
     * Generate JavaScript code for CSRF protection.
     */
    public function generateJavaScript(): string
    {
        $token = $this->generateToken();

        return <<<JS
        (function() {
            // Set up CSRF token for AJAX requests
            const csrfToken = '{$token}';

            // jQuery setup
            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    beforeSend: function(xhr, settings) {
                        if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                            xhr.setRequestHeader("X-CSRF-Token", csrfToken);
                        }
                    }
                });
            }

            // Fetch API setup
            const originalFetch = window.fetch;
            window.fetch = function(url, options = {}) {
                if (!options.headers) {
                    options.headers = {};
                }

                const method = (options.method || 'GET').toUpperCase();
                if (!['GET', 'HEAD', 'OPTIONS', 'TRACE'].includes(method)) {
                    options.headers['X-CSRF-Token'] = csrfToken;
                }

                return originalFetch(url, options);
            };

            // XMLHttpRequest setup
            const originalOpen = XMLHttpRequest.prototype.open;
            const originalSend = XMLHttpRequest.prototype.send;

            XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
                this._method = method.toUpperCase();
                return originalOpen.call(this, method, url, async, user, password);
            };

            XMLHttpRequest.prototype.send = function(data) {
                if (!['GET', 'HEAD', 'OPTIONS', 'TRACE'].includes(this._method)) {
                    this.setRequestHeader('X-CSRF-Token', csrfToken);
                }
                return originalSend.call(this, data);
            };
        })();
JS;
    }

    /**
     * Get CSRF token from request (POST, header, or query).
     */
    private static function getTokenFromRequest(): ?string
    {
        // Check POST data
        if (!empty($_POST['csrf_token'])) {
            return $_POST['csrf_token'];
        }

        // Check custom header
        if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        // Check standard header
        if (!empty($_SERVER['HTTP_X_XSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_XSRF_TOKEN'];
        }

        // Check query parameter (not recommended for security, but supported)
        if (!empty($_GET['csrf_token'])) {
            return $_GET['csrf_token'];
        }

        return null;
    }

    /**
     * Check if current request is AJAX.
     */
    private static function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
               && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
