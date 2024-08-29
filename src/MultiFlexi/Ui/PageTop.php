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
 * Page TOP.
 */
class PageTop extends \Ease\Html\DivTag
{
    /**
     * Titulek stránky.
     */
    public ?string $pageTitle = null;

    /**
     * Nastavuje titulek.
     *
     * @param string $pageTitle
     */
    public function __construct($pageTitle = null)
    {
        parent::__construct();

        if (null !== $pageTitle) {
            WebPage::singleton()->setPageTitle($pageTitle);
        }

        WebPage::singleton()->body->addAsFirst(new MainMenu());
    }

    public function finalize()
    {
        return parent::finalize();
    }
}
