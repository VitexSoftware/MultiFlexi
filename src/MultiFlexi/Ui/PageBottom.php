<?php

/**
 * Multi Flexi  - Shared page bottom class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Page Bottom
 *
 * @package    VitexSoftware
 * @author     Vitex <vitex@hippy.cz>
 */
class PageBottom extends \Ease\Html\FooterTag
{
    public const BUILD = '';

    /**
     * Zobrazí přehled právě přihlášených a spodek stránky
     */
    public function finalize()
    {
        $this->includeCSS('https://use.fontawesome.com/releases/v5.3.1/css/all.css');
        $container = $this->setTagID('footer');
        //        if (\Ease\Shared::user()->getUserID()) {
        //        $this->addItem(new \Ease\ui\BrowsingHistory());
        //        }
        $this->addItem('<hr>');
        $footrow = new \Ease\TWB4\Row();

        if (method_exists('Composer\InstalledVersions', 'getRootPackage')) {
            $composer = \Composer\InstalledVersions::getRootPackage()['install_path'] . '/composer.lock';
        } else {
            $composer = '../composer.lock';
        }

        $author = 'Multi Flexi v.: ' . \Ease\Shared::appVersion() . (empty(self::BUILD) ? '' : '&nbsp;' . _('build') . ' #' . self::BUILD) . '<br>' . _('age') . '&nbsp;' . new \Ease\ui\LiveAge(filemtime($composer)) . '&nbsp;&nbsp; &copy; 2020-2023 <a href="https://vitexsoftware.cz/">Vitex Software</a>';
        $footrow->addColumn(6, [$author]);
        $this->addItem(new \Ease\TWB4\Container($footrow));
    }
}
