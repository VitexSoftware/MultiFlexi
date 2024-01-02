<?php

/**
 * Multi Flexi - Executor Modules Listing
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of EnvModulesListing
 *
 * @author vitex
 */
class ActionsListing extends \Ease\Html\DivTag
{
    public function __construct($content = null, $properties = [])
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Action');
        $actions = \Ease\Functions::classesInNamespace('MultiFlexi\\Action');

        parent::__construct(null, $properties);
        foreach ($actions as $action) {
            $actionClass = '\\MultiFlexi\\Action\\' . $action;
            $moduleRow = new \Ease\TWB4\Row();

//            $moduleRow->addColumn(2, new ExecutorImage($injector, ['height' => '50px']));
            $moduleRow->addColumn(4, [new \Ease\Html\StrongTag($actionClass::name()), new \Ease\Html\PTag(new \Ease\Html\SmallTag($actionClass::description()))]);

            $this->addItem(new \Ease\Html\PTag($moduleRow));
        }
    }
}
