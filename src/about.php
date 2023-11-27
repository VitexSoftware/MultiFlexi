<?php

/**
 * Multi Flexi - About page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

require_once './init.php';

$oPage->addItem(new PageTop(_('About')));

$infoBlock = $oPage->container->addItem(
    new \Ease\TWB4\Panel(
        _('About Program'),
        'default',
        null,
        new \Ease\TWB4\LinkButton(
            'http://vitexsoftware.com/',
            _('Vitex Software'),
            'info'
        )
    )
);
$listing = $infoBlock->addItem(new \Ease\Html\UlTag());

if (file_exists('../README.md')) {
    $listing->addItem(implode('<br>', file('../README.md')));
} else {
    if (file_exists('/usr/share/doc/multiflexi/README.md')) {
        $listing->addItem(implode(
            '<br>',
            file('/usr/share/doc/multiflexi/README.md')
        ));
    }
}

$oPage->container->addItem(new \Ease\Html\DivTag(new \Ease\Html\ImgTag('images/openclipart/345630.svg', _('
AI and Human Relationship'), ['class' => 'mx-auto d-block']), ['style' => 'height: 80%']));


$oPage->addItem(new PageBottom());

$oPage->draw();
