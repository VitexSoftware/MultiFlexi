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
class ExecutorsListing extends \Ease\Html\DivTag
{
    public function __construct($content = null, $properties = [])
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Executor');
        $executors = \Ease\Functions::classesInNamespace('MultiFlexi\\Executor');

        parent::__construct(null, $properties);

        foreach ($executors as $injector) {
            $executorClass = '\\MultiFlexi\\Executor\\'.$injector;
            $moduleRow = new \Ease\TWB4\Row();

            $moduleRow->addColumn(2, new ExecutorImage($injector, ['height' => '50px']));
            $moduleRow->addColumn(4, [new \Ease\Html\StrongTag($executorClass::name()), new \Ease\Html\PTag(new \Ease\Html\SmallTag($executorClass::description()))]);

            $this->addItem(new \Ease\Html\PTag($moduleRow));
        }
    }
}
