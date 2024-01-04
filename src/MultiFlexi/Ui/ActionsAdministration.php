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
class ActionsAdministration extends \Ease\TWB4\Form
{
    public function __construct($properties = [])
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Action');
        $actions = \Ease\Functions::classesInNamespace('MultiFlexi\\Action');

        parent::__construct([], $properties);
        foreach ($actions as $action) {
            $actionClass = '\\MultiFlexi\\Action\\' . $action;
            $moduleRow = new \Ease\TWB4\Row();

            $moduleRow->addColumn(2, new ActionImage($action, ['height' => '50px']));
            $moduleRow->addColumn(4, [new \Ease\Html\StrongTag($actionClass::name()), new \Ease\Html\PTag(new \Ease\Html\SmallTag($actionClass::description()))]);
            $moduleRow->addColumn(4, $actionClass::configForm());

            $this->addItem(new \Ease\Html\PTag($moduleRow));
        }
        $this->addItem(new \Ease\TWB4\SubmitButton(_('Save'), 'primary btn-lg btn-block'));
    }
}
