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
 * Description of CredentialClassSelect.
 *
 * @author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class CredentialTypeClassSelect extends \Ease\Html\SelectTag
{
    public array $credentialTypeClasses = [];

    /**
     *  Executor Select.
     *
     * @param string $name
     * @param array  $items
     * @param string $defaultValue
     * @param array  $properties
     */
    public function __construct($name, $items = [], $defaultValue = 'BuiltIn', $properties = [])
    {
        parent::__construct($name, $items, $defaultValue, $properties);
    }

    /**
     * Executors list.
     */
    public function loadItems(): array
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\CredentialType');
        $credTypeHelpers = \Ease\Functions::classesInNamespace('MultiFlexi\\CredentialType');
        $items = ['' => _('No CredentialType helper used')];

        foreach ($credTypeHelpers as $credTypeHelper) {
            $credTypeHelperClass = '\\MultiFlexi\\CredentialType\\'.$credTypeHelper;
            $items[$credTypeHelper] = $credTypeHelperClass::description();
            $this->credentialTypeClasses[$credTypeHelper] = $credTypeHelperClass;
        }

        return $items;
    }
}
