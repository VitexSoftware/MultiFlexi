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
 *
 * @no-named-arguments
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
            // First try to use the UI-specific class, then fallback to core
            $uiActionClass = '\\MultiFlexi\\Ui\\Action\\'.$action;
            
            $actionClass = new $uiActionClass(new \MultiFlexi\RunTemplate());
            
            $moduleRow = new \Ease\TWB4\Row();

            $moduleRow->addColumn(2, new ActionImage($action, ['height' => '50px']));
            $moduleRow->addColumn(4, [new \Ease\Html\StrongTag($uiActionClass::name()), new \Ease\Html\PTag(new \Ease\Html\SmallTag($uiActionClass::description()))]);
           

            $this->addItem(new \Ease\Html\PTag($moduleRow));
        }

        $this->addItem(new \Ease\TWB4\SubmitButton(_('Save'), 'primary btn-lg btn-block'));
    }

    public function finalize(): void
    {
        $this->fillUp(self::fixFieldNames($this->modConf->getConfigForModules($this->actions)));
        parent::finalize();
    }

    public static function fixFieldNames($configurationsRaw)
    {
        $configurations = [];

        foreach ($configurationsRaw as $module => $cfgs) {
            foreach ($cfgs as $key => $value) {
                $configurations[$module.'['.$key.']'] = $value;
            }
        }

        return $configurations;
    }
}
