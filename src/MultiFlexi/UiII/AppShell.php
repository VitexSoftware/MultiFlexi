<?php

declare(strict_types=1);

namespace MultiFlexi\UiII;

class AppShell extends \Ease\Html\DivTag
{
    /**
     * @var Sidebar Postranní panel.
     */
    public Sidebar $sidebar;

    /**
     * @var \Ease\Html\MainTag Hlavní obsahová oblast.
     */
    public \Ease\Html\MainTag $contentArea;

    /**
     * @var \Ease\Html\DivTag Kontejner s maximální šířkou pro obsah.
     */
    public \Ease\Html\DivTag $pageMax;

    public function __construct($properties = [])
    {
        parent::__construct(null, ['class' => 'app-shell'] + $properties);

        // Vytvoření postranního panelu
        $this->sidebar = new Sidebar();
        $this->addItem($this->sidebar);

        // Vytvoření hlavní obsahové oblasti
        $this->contentArea = $this->addItem(new \Ease\Html\MainTag(null, ['class' => 'content-area']));
        $this->pageMax = $this->contentArea->addItem(new \Ease\Html\DivTag(null, ['class' => 'page-max']));
    }

    /**
     * Přidá obsah do hlavní části stránky.
     *
     * @param mixed $content Obsah k přidání.
     *
     * @return mixed
     */
    public function addContent($content)
    {
        return $this->pageMax->addItem($content);
    }
}
