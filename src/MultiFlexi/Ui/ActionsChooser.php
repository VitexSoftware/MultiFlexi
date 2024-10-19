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
class ActionsChooser extends \Ease\Html\DivTag
{
    public function __construct($prefix, \MultiFlexi\Application $app, $toggles = [], $properties = [])
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Action');
        $actions = \Ease\Functions::classesInNamespace('MultiFlexi\\Action');

        parent::__construct(null, $properties);

        foreach ($actions as $action) {
            $actionClass = '\\MultiFlexi\\Action\\'.$action;

            if ($actionClass::usableForApp($app)) {
                $moduleRow = new \Ease\TWB4\Row();

                $moduleRow->addColumn(1, new ActionImage($action, ['height' => '50px']));
                $moduleRow->addColumn(1, new \Ease\TWB4\Widgets\Toggle($prefix.'actionSwitch['.$action.']', \array_key_exists($action, $toggles) && $toggles[$action], '', ['data-size' => 'lg']));
                $moduleRow->addColumn(4, [new \Ease\Html\StrongTag($actionClass::name()), new \Ease\Html\PTag(new \Ease\Html\SmallTag($actionClass::description()))]);
                $moduleRow->addColumn(4, $actionClass::inputs($prefix));
                $this->addItem(new \Ease\Html\PTag($moduleRow));
            }
        }
    }

    /**
     * @return array
     */
    public static function toggles(string $prefix)
    {
        $toggles = [];
        $updates = \Ease\WebPage::getRequestValue($prefix.'actionSwitch') ?: [];
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Action');
        $actions = \Ease\Functions::classesInNamespace('MultiFlexi\\Action');

        foreach ($actions as $action) {
            $toggles[$action] = \array_key_exists($action, $updates);
        }

        return $toggles;
    }

    /**
     * @return array
     */
    public static function formModuleCofig(string $prefix)
    {
        return \Ease\WebPage::getRequestValue($prefix) ? \Ease\WebPage::getRequestValue($prefix) : [];
    }

    /**
     * SQL To Form Data.
     *
     * @param \Envms\FluentPDO\Queries\Select $query
     *
     * @return array
     */
    public static function sqlToForm($query)
    {
        $formData = [];

        foreach ($query as $field) {
            $formData[$field['mode'].'['.$field['module'].']['.$field['keyname'].']'] = $field['value'];
        }

        return $formData;
    }
}
