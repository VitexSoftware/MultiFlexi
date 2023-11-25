<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MultiFlexi\Ui;

/**
 * Description of ConfiguredFieldBadges
 *
 * @author vitex
 */
class ConfigFieldsView extends \Ease\Html\DivTag
{
    /**
     *
     * @param array $fieldsInfo
     */
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
        $container->addItem(_($fieldInfo['description']));

        $dl = new \Ease\Html\DlTag();
        $dl->addDef(_('Default value'), $fieldInfo['defval']);
        $dl->addDef(_('Requied'), $fieldInfo['required'] ? '✅' : '❎');
        $container->addItem($dl);
        return new \Ease\TWB4\Card($container);
    }
}
