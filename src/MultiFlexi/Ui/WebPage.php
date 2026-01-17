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

/**
 * Description of WebPage.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class WebPage extends \Ease\TWB4\WebPage
{
    /**
     * Where to look for bootstrap style sheet.
     *
     * @var string path or url
     */
    public string $bootstrapCSS = 'css/bootstrap.min.css';

    /**
     * Put page contents here.
     */
    public \Ease\TWB4\Container $container;

    /**
     * Current Customer.
     */
    public ?\MultiFlexi\Customer $customer = null;

    /**
     * Saves object instance (singleton...).
     */
    private static $instance;

    public function __construct(string $pageTitle = '')
    {
        parent::__construct($pageTitle);
        $this->container = new \Ease\TWB4\Container();
        $this->addItem($this->container);
        $this->container->setTagClass('container-fluid');

        $this->head->addItem('<link rel="icon" type="image/svg+xml" href="images/project-logo.svg">');
        $this->head->addItem('<link rel="icon" type="image/png" href="images/project-logo.png">');
        $this->head->addItem('<link rel="icon" type="image/x-icon" href="favicon.ico">');

        // Include Font Awesome globally for all icons
        $this->includeCss('css/font-awesome.min.css');

        // Add CSRF protection
        $this->addCsrfProtection();

        $this->addCSS(<<<'CSS'
            body { background-color: #f8f9fa; color: #343a40; font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
            .card { transition: all 0.3s cubic-bezier(.25,.8,.25,1); border-radius: 8px; border: none; }
            .card:hover { box-shadow: 0 14px 28px rgba(0,0,0,0.1), 0 10px 10px rgba(0,0,0,0.1) !important; }
            .img-thumbnail { border-radius: 12px; transition: transform 0.3s ease; }
            .img-thumbnail:hover { transform: scale(1.05); }
            .nav-tabs { border-bottom: 2px solid #dee2e6; margin-bottom: 20px; }
            .nav-link { font-weight: 500; color: #6c757d; border: none !important; padding: 12px 20px; }
            .nav-link.active { color: #007bff !important; border-bottom: 3px solid #007bff !important; background: transparent !important; }
            .badge-primary { background-color: #007bff; }
            .btn { border-radius: 6px; font-weight: 500; transition: all 0.2s; }
            .btn-outline-danger:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(220, 53, 69, 0.2); }
            .application-metadata h3 { color: #343a40; font-weight: 700; }
            .table thead th { border-top: none; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; color: #8898aa; border-bottom: 1px solid #e9ecef; }
            .table td { vertical-align: middle; }
            .shadow-sm { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important; }
CSS);
    }

    public function onlyForLogged($loginPage = 'login.php', $message = null)
    {
        if (parent::onlyForLogged($loginPage, $message)) {
            $_SESSION['wayback'] = self::getUri();

            return true;
        }

        return false;
    }

    public static function singleton($webPage = null): self
    {
        if (!isset(self::$instance)) {
            self::$instance = \is_object($webPage) ? $webPage : new self();
            \Ease\Document::singleton()->registerItem(self::$instance);
        }

        return self::$instance;
    }

    /**
     * Add CSRF protection to the page.
     */
    private function addCsrfProtection(): void
    {
        if (isset($GLOBALS['csrfProtection'])) {
            $csrfProtection = $GLOBALS['csrfProtection'];

            // Add meta tag for JavaScript
            $this->head->addItem($csrfProtection->createTokenMetaTag());

            // Add JavaScript for automatic CSRF token handling
            $this->addJavaScript($csrfProtection->generateJavaScript(), null, true);
        }
    }
}
