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
 * Listing of CredentialType helper classes
 *
 * @author vitex
 */
class CtHelpersListing extends \Ease\Html\DivTag
{
    public function __construct($content = null, $properties = [])
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\CredentialType');
        $helpers = \Ease\Functions::classesInNamespace('MultiFlexi\\CredentialType');

        parent::__construct(null, $properties);

        foreach ($helpers as $injector) {
            $helperClass = '\\MultiFlexi\\CredentialType\\'.$injector;
            $moduleRow = new \Ease\TWB4\Row();

            $moduleRow->addColumn(2, new \Ease\Html\ImgTag($helperClass::logo(), $helperClass::name() , ['height' => '50px']));
            $moduleRow->addColumn(4, [new \Ease\Html\StrongTag($helperClass::name()), new \Ease\Html\PTag(new \Ease\Html\SmallTag($helperClass::description()))]);

            $this->addItem(new \Ease\Html\PTag($moduleRow));
        }
    }
}
