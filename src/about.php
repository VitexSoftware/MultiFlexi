<?php

/**
 * Multi FlexiBee Setup - About page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('About')));

$infoBlock = $oPage->container->addItem(
        new \Ease\TWB4\Panel(
                _('O Programu'), 'info', null,
                new \Ease\TWB4\LinkButton(
                        'http://v.s.cz/', _('Vitex Software'), 'info'
                )
        )
);
$listing = $infoBlock->addItem(new \Ease\Html\UlTag());

if (file_exists('../README.md')) {
    $listing->addItem(implode('<br>', file('../README.md')));
} else {
    if (file_exists('/usr/share/doc/multiflexibeesetup/README.md')) {
        $listing->addItem(implode('<br>',
                        file('/usr/share/doc/multiflexibeesetup/README.md')));
    }
}

$oPage->addItem(new PageBottom());

$oPage->draw();
