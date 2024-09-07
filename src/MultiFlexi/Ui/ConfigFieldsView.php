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
 */
class ConfigFieldsView extends \Ease\Html\DivTag
{
    public function __construct(array $fieldsInfo)
    {
        parent::__construct();

        foreach ($fieldsInfo as $key => $fieldInfo) {
            $this->addItem(self::confInfo($fieldInfo));
        }
    }

    public static function confInfo($fieldInfo)
    {
        $container = new \Ease\TWB4\Container($fieldInfo['type']);
        $container->addItem(new \Ease\Html\H3Tag($fieldInfo['keyname']));
        $container->addItem( empty($fieldInfo['description']) ? '' : _($fieldInfo['description']));

        $dl = new \Ease\Html\DlTag();
        $dl->addDef(_('Default value'), $fieldInfo['defval']);
        $dl->addDef(_('Requied'), $fieldInfo['required'] ? '✅' : '❎');
        $container->addItem($dl);

        return new \Ease\TWB4\Card($container);
    }
}
