<?php

declare(strict_types=1);

namespace MultiFlexi\UiII;

class WebPage extends \Ease\TWB4\WebPage
{
    /**
     * @var AppShell Vnější obálka stránky.
     */
    public AppShell $appShell;

    /**
     * Sestaví základní strukturu stránky.
     *
     * @param string $pageTitle Titulek stránky.
     */
    public function __construct($pageTitle = null)
    {
        parent::__construct($pageTitle);

        // Přidání základních CSS
        $this->addCss('https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/css/bootstrap.min.css');
        $this->addCss('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'); // Font Awesome 6
        $this->addCss(
            '
            html, body { height: 100%; }
            .app-shell { min-height: 100vh; display: flex; background: #f7f8fa; }
            .sidebar {
                width: 220px;
                background: #fff;
                border-right: 1px solid #e6e9ef;
                padding: 1.25rem 0.75rem;
                position: sticky;
                top: 0;
                height: 100vh;
                transition: margin-left 0.3s, width 0.3s;
            }
            .sidebar.collapsed { margin-left: -220px; }
            .content-area {
                flex: 1;
                padding: 1.25rem;
                display: flex;
                justify-content: center;
                transition: margin-left 0.3s;
            }
            .content-area.expanded {
                margin-left: 0;
            }
            .page-max { width: 100%; max-width: 1200px; }
            .card-header-actions { display:flex; gap: 0.5rem; align-items:center; }
            @media (max-width: 768px) {
                .sidebar { display:none; }
            }
        '
        );

        // Vytvoření hlavní obálky
        $this->appShell = new AppShell();
        $this->body->addItem($this->appShell);
    }

    /**
     * Přidá do stránky základní JavaScript.
     */
    public function finalize()
    {
        // Přidání základních JS souborů na konec <body>
        $this->addJavaScript('https://code.jquery.com/jquery-3.5.1.slim.min.js', null, true);
        $this->addJavaScript('https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js', null, true);
        $this->addJavaScript('https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.min.js', null, true);
        $this->addJavaScript('https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js', null, true);

        parent::finalize();
    }
}
