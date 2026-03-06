<?php

declare(strict_types=1);

namespace MultiFlexi\UiII;

class Sidebar extends \Ease\Html\AsideTag
{
    public function __construct($properties = [])
    {
        parent::__construct(null, ['class' => 'sidebar d-flex flex-column'] + $properties);
        $this->addItem(new \Ease\Html\H3Tag('DARAM', ['class' => 'text-danger mb-4']));

        $nav = $this->addItem(new \Ease\Html\NavTag(null, ['class' => 'nav flex-column']));
        $nav->addItem(new \Ease\Html\ATag('#', '<i class="fas fa-grip-horizontal"></i> Dashboard', ['class' => 'nav-link active']));
        $nav->addItem(new \Ease\Html\ATag('#', '<i class="fas fa-list"></i> Runs', ['class' => 'nav-link']));
        $nav->addItem(new \Ease\Html\ATag('#', '<i class="fas fa-file-alt"></i> Logs', ['class' => 'nav-link']));
        $nav->addItem(new \Ease\Html\ATag('#', '<i class="fas fa-chart-bar"></i> Statistiky', ['class' => 'nav-link']));
        $nav->addItem(new \Ease\Html\ATag('#', '<i class="fas fa-cog"></i> Nastavení', ['class' => 'nav-link']));

        $this->addItem('<div class="mt-auto pt-4 small text-muted">verze MultiFlexi • lokální</div>');
    }
}
