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
    public $modConf;
    private $actions;

    public function __construct(\MultiFlexi\ModConfig $modConf, $properties = [])
    {
        $this->modConf = $modConf;
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Action');
        $this->actions = \Ease\Functions::classesInNamespace('MultiFlexi\\Action');

        parent::__construct([], $properties);
        foreach ($this->actions as $action) {
            $actionClass = '\\MultiFlexi\\Action\\' . $action;
            $moduleRow = new \Ease\TWB4\Row();

            $moduleRow->addColumn(2, new ActionImage($action, ['height' => '50px']));
            $moduleRow->addColumn(4, [new \Ease\Html\StrongTag($actionClass::name()), new \Ease\Html\PTag(new \Ease\Html\SmallTag($actionClass::description()))]);
            $moduleRow->addColumn(4, $actionClass::configForm());

            $this->addItem(new \Ease\Html\PTag($moduleRow));
        }
        $this->addItem(new \Ease\TWB4\SubmitButton(_('Save'), 'primary btn-lg btn-block'));
    }

    public function finalize()
    {
        $this->fillUp(self::fixFieldNames($this->modConf->getConfigForModules($this->actions)));
        parent::finalize();
    }

    public static function fixFieldNames($configurationsRaw)
    {
        $configurations = [];
        foreach ($configurationsRaw as $module => $cfgs) {
            foreach ($cfgs as $key => $value) {
                $configurations[$module . '[' . $key . ']'] = $value;
            }
        }
        return $configurations;
    }
}