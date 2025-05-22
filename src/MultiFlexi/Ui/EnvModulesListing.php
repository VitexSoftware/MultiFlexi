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
 * Description of EnvModulesListing.
 *
 * @author vitex
 */
class EnvModulesListing extends \Ease\Html\DivTag
{
    public function __construct($content = null, $properties = [])
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Env');
        $injectors = \Ease\Functions::classesInNamespace('MultiFlexi\\Env');

        parent::__construct(null, $properties);

        foreach ($injectors as $injector) {
            $injectorClass = '\\MultiFlexi\\Env\\'.$injector;
            $moduleRow = new \Ease\TWB5\Row();

            $moduleRow->addColumn(2, [new \Ease\Html\StrongTag($injectorClass::name()), new \Ease\Html\PTag(new \Ease\Html\SmallTag($injectorClass::description()))]);
            $moduleRow->addColumn(6, implode('<br>', $injectorClass::allKeysHandled()));

            $this->addItem(new \Ease\Html\PTag($moduleRow));
        }
    }
}
