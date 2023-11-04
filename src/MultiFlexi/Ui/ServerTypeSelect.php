<?php
declare(strict_types=1);
/**
 * Multi Flexi - 
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of EngineSelect
 *
 * @author vitex
 */
class ServerTypeSelect extends \Ease\Html\SelectTag
{

    public function __construct($name, $value = null)
    {
        parent::__construct($name, ['AbraFlexi' => 'AbraFlexi', 'Pohoda' => _('Stormware Pohoda')], $value);
    }
}
