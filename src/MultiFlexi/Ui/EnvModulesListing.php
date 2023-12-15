<?php

/**
 * Multi Flexi - Environment Modules Listing
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
class EnvModulesListing extends \Ease\Html\DivTag
{
    public function __construct($content = null, $properties = [])
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Env');
        $injectors = \Ease\Functions::classesInNamespace('MultiFlexi\\Env');

        parent::__construct(null, $properties);
        foreach ($injectors as $injector) {
            $injectorClass = '\\MultiFlexi\\Env\\' . $injector;
            $moduleRow = new \Ease\TWB4\Row();

            $moduleRow->addColumn(2, [new \Ease\Html\StrongTag($injectorClass::name()), new \Ease\Html\PTag(new \Ease\Html\SmallTag($injectorClass::description())) ]);
            $moduleRow->addColumn(6, implode('<br>', $injectorClass::allKeysHandled()));

            $this->addItem(new \Ease\Html\PTag($moduleRow));
        }
    }
}
