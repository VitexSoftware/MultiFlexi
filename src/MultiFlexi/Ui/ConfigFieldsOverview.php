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
 * Description of ConfiguredFieldBadges.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class ConfigFieldsOverview extends \Ease\Html\DivTag
{
    public function __construct(\MultiFlexi\ConfigFields $fields)
    {
        parent::__construct();

        foreach ($fields as $field) {
            $this->addItem(self::confInfo($field));
        }
    }

    public static function confInfo(\MultiFlexi\ConfigField $field): \Ease\TWB4\Row
    {
        $confInfoRow = new \Ease\TWB4\Row();
        $confInfoRow->addColumn(2, [$field->getType(), new \Ease\Html\H3Tag($field->getName())]);
        $confInfoRow->addColumn(2, $field->getDescription());
        $confInfoRow->addColumn(2, _('Note').': '.$field->getNote());
        $confInfoRow->addColumn(2, _('Source').': '.$field->getSource());

        if ($field->isSecret()) {
            $confInfoRow->addColumn(1, _('Value').': '.empty($field->getValue()) ? '⁉️' : '✅');
        } else {
            $confInfoRow->addColumn(1, _('Value').': '.new \Ease\Html\StrongTag($field->getValue()));
        }

        $confInfoRow->addColumn(2, _('Default').': '.$field->getDefaultValue());
        $confInfoRow->addColumn(1, _('Requied').': '.($field->isRequired() ? _('Yes') : _('No')));

        return $confInfoRow;
    }
}
