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

namespace MultiFlexi\Ui;

use Ease\Html\DivTag;
use Ease\Html\ImgTag;
use Ease\Html\InputHiddenTag;
use Ease\Html\InputPasswordTag;
use Ease\Html\InputTextTag;
use Ease\Html\PTag;
use Ease\Shared;
use Ease\TWB4\Col;
use Ease\TWB4\Form;
use Ease\TWB4\FormGroup;
use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use Ease\TWB4\SubmitButton;

require_once './init.php';

// Check if IP whitelist is enabled and user is not on whitelist
if (isset($GLOBALS['ipWhitelist']) && !$GLOBALS['ipWhitelist']->isAllowed()) {
    http_response_code(403);
    WebPage::singleton()->addItem(new PageTop(_('Access Denied')));
    WebPage::singleton()->container->addItem(new DivTag(_('Access denied from your IP address'), ['class' => 'alert alert-danger']));
    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();

    exit;
}

$shared = Shared::singleton();

// Handle session expiration message
if (isset($_GET['session_expired'])) {
    Shared::user()->addStatusMessage(_('Your session has expired. Please log in again.'), 'warning');
}

// Handle redirect parameter - store in session for post-login redirect
if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    $_SESSION['wayback'] = $_GET['redirect'];
}

// Handle logout
if (isset($_GET['logout'])) {
    if (isset($GLOBALS['securityAuditLogger'])) {
        $GLOBALS['securityAuditLogger']->logLogout(Shared::user()->getUserID());
    }

    Shared::user()->logout();
    WebPage::singleton()->redirect('login.php');

    exit;
}

try {
    $hasAdmin = false;

    if (isset($GLOBALS['rbac'])) {
        $rbac = $GLOBALS['rbac'];
        $hasAdmin = $rbac->hasRole('admin');
    }

    if (!$hasAdmin) {
        Shared::user()->addStatusMessage(_('There is no administrators in the database.'), 'warning');
        WebPage::singleton()->container->addItem(new LinkButton('createaccount.php', _('Create first Administrator Account'), 'success'));
    }
} catch (\PDOException $exc) {
    Shared::user()->addStatusMessage($exc->getMessage());
}

$login = WebPage::singleton()->getRequestValue('login');

if ($login && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token if CSRF protection is enabled
    if (Shared::cfg('CSRF_PROTECTION_ENABLED', true) && isset($GLOBALS['csrfProtection']) && !$GLOBALS['csrfProtection']->validateToken($_POST['csrf_token'] ?? '')) {
        Shared::user()->addStatusMessage(_('Invalid security token. Please clear cookies and try again.'), 'error');
    } else {
        // Rate limiting check
        if (isset($GLOBALS['rateLimiter'])) {
            $result = $GLOBALS['rateLimiter']->checkRateLimit(
                $_SERVER['REMOTE_ADDR'],
                'login_attempt',
                null,
                Shared::user()->getUserID(),
            );

            if (!$result['allowed']) {
                Shared::user()->addStatusMessage(_('Too many login attempts. Please try again later.'), 'error');
            } else {
                if (Shared::user()->tryToLogin($_POST)) {
                    // Clear rate limiting on successful login
                    $GLOBALS['rateLimiter']->clearRateLimit($_SERVER['REMOTE_ADDR'], 'login_attempt');

                    // Regenerate session ID for security
                    if (isset($GLOBALS['sessionManager'])) {
                        $GLOBALS['sessionManager']->regenerateId();
                    }

                    if (\array_key_exists('wayback', $_SESSION) && !empty($_SESSION['wayback'])) {
                        $wayback = $_SESSION['wayback'];
                        unset($_SESSION['wayback']);
                        WebPage::singleton()->redirect($wayback);
                    } else {
                        WebPage::singleton()->redirect('main.php');
                    }

                    session_write_close();

                    exit;
                }
            }
        } else {
            // Fallback without rate limiting
            if (Shared::user()->tryToLogin($_POST)) {
                if (\array_key_exists('wayback', $_SESSION) && !empty($_SESSION['wayback'])) {
                    $wayback = $_SESSION['wayback'];
                    unset($_SESSION['wayback']);
                    WebPage::singleton()->redirect($wayback);
                } else {
                    WebPage::singleton()->redirect('main.php');
                }

                session_write_close();

                exit;
            }
        }
    }
}

WebPage::singleton()->addItem(new PageTop(_('Sign In')));

$loginFace = new DivTag(null, ['id' => 'LoginFace']);

WebPage::singleton()->container->addItem($loginFace);

$loginRow = new Row();
$infoColumn = $loginRow->addItem(new Col(4));

$infoBlock = $infoColumn->addItem(new ImgTag('images/project-logo.svg', _('Logo'), ['style' => 'width: 150%']));
$infoBlock->addItem(new DivTag(_('Welcome to MultiFlexi'), ['style' => 'text-align: center;']));

$loginColumn = $loginRow->addItem(new Col(4));

$submit = new SubmitButton('🚪&nbsp;'._('Sign in'), 'success btn-lg btn-block', ['id' => 'signin']);

$submitRow = new Row();
$submitRow->addColumn(6, $submit);
$submitRow->addColumn(6, new LinkButton('passwordrecovery.php', '🔑&nbsp;'._('Password recovery'), 'warning btn-block'));

$loginPanel = new Panel(
    new ImgTag('images/project-logo.svg', 'logo', ['width' => 20]),
    'inverse',
    null,
    $submitRow,
);
$loginPanel->addItem(new FormGroup(
    _('Username'),
    new InputTextTag('login', $login),
    '',
    _('the username you chose'),
));

$loginPanel->addItem(new FormGroup(_('Password'), new InputPasswordTag('password')));

$loginPanel->body->setTagCss(['margin' => '20px']);

$loginColumn->addItem(new PTag());
$loginColumn->addItem($loginPanel);

// Add CSRF token to form if CSRF protection is enabled
$formAttributes = [];

if (\Ease\Shared::cfg('CSRF_PROTECTION_ENABLED', true) && isset($GLOBALS['csrfProtection'])) {
    $csrfToken = $GLOBALS['csrfProtection']->generateToken();
    $loginPanel->addItem(new InputHiddenTag('csrf_token', $csrfToken));
}

$loginForm = new Form(['method' => 'POST', 'action' => 'login.php'], [], $loginRow);
WebPage::singleton()->container->addItem($loginForm);

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
