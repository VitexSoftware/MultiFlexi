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
class ConfigFieldsView extends \Ease\Html\DivTag
{
    public function __construct(\MultiFlexi\ConfigFields $fieldsInfo)
    {
        parent::__construct();

        foreach ($fieldsInfo as $fieldInfo) {
            $this->addItem(self::confInfo($fieldInfo));
        }
    }

    public static function confInfo(\MultiFlexi\ConfigField $fieldInfo)
    {
        $container = new \Ease\TWB4\Container($fieldInfo->getType());
        $container->addItem(new \Ease\Html\H3Tag($fieldInfo->getCode()));
        $container->addItem($fieldInfo->getDescription());

        $dl = new \Ease\Html\DlTag();
        $dl->addDef(_('Default value'), $fieldInfo->getDefaultValue());
        $dl->addDef(_('Requied'), $fieldInfo->isRequired() ? '✅' : '❎');
        $container->addItem($dl);

        return new \Ease\TWB4\Card($container);
    }
}
