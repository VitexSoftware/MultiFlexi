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
        $this->container = $this->addItem(new \Ease\TWB4\Container());
        $this->container->setTagClass('container-fluid');
        $this->addCSS(<<<'EOD'


EOD);
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
}
