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

require_once './init.php';

WebPage::singleton()->addItem(new PageTop(_('About')));

$infoBlock = WebPage::singleton()->container->addItem(
    new \Ease\TWB4\Panel(
        _('About Program'),
        'default',
        null,
        new \Ease\TWB4\LinkButton(
            'http://vitexsoftware.com/',
            _('Vitex Software'),
            'info',
        ),
    ),
);
$listing = $infoBlock->addItem(new \Ease\Html\UlTag());

if (file_exists('../README.md')) {
    $listing->addItem(implode('<br>', file('../README.md')));
} else {
    if (file_exists('/usr/share/doc/multiflexi/README.md')) {
        $listing->addItem(implode(
            '<br>',
            file('/usr/share/doc/multiflexi/README.md'),
        ));
    }
}

WebPage::singleton()->container->addItem(new \Ease\Html\DivTag(new \Ease\Html\ImgTag('images/openclipart/345630.svg', _(<<<'EOD'

AI and Human Relationship
EOD), ['class' => 'mx-auto d-block']), ['style' => 'height: 80%']));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
