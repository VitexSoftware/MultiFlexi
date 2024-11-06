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
     * Where to look for bootstrap stylesheet.
     *
     * @var string path or url
     */
    public string $bootstrapCSS = 'css/bootstrap.min.css';

    /**
     * Put page contents here.
     */
    public ?\Ease\TWB4\Container $container = null;

    /**
     * Current Customer.
     */
    public ?\MultiFlexi\Customer $customer = null;

    public function __construct(string $pageTitle = '')
    {
        parent::__construct($pageTitle);
        $this->container = $this->addItem(new \Ease\TWB4\Container());
        $this->container->setTagClass('container-fluid');
        $this->addCSS(<<<'EOD'


EOD);
    }
}
