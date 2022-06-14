<?php

/**
 * Multi Flexi  - Main Menu
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

/**
 * Description of MainMenu
 *
 * @author vitex
 */
class MainMenu extends \Ease\Html\DivTag {

    /**
     * Vytvoří hlavní menu.
     */
    public function __construct() {
        parent::__construct(null, ['id' => 'MainMenu']);
    }

    /**
     * Data source.
     *
     * @param type   $source
     * @param string $icon   Icon column
     *
     * @return string
     */
    protected function getMenuList($source, $icon = null) {
        $keycolumn = $source->getkeyColumn();
        $namecolumn = $source->nameColumn;
        $columns = [$source->getkeyColumn(), $namecolumn];
        if ($icon) {
            $columns[] = $icon;
        }
        $lister = $source->getColumnsFromSQL($columns, null, $namecolumn, $keycolumn);

        $itemList = [];
        if ($lister) {
            foreach ($lister as $uID => $uInfo) {
                if ($icon) {
                    $logo = new \Ease\Html\ImgTag($uInfo[$icon], $uInfo[$namecolumn], ['height' => 20]) . '&nbsp;';
                } else {
                    $logo = '';
                }
                $itemList[$source->keyword . '.php?' . $keycolumn . '=' . $uInfo[$keycolumn]] = $logo . $uInfo[$namecolumn];
            }
        }

        return $itemList;
    }

    /**
     * Insert menu.
     */
    public function afterAdd() {
        $nav = $this->addItem(new BootstrapMenu('main-menu', null, ['class' => 'navbar navbar-expand-lg navbar-light bg-light']));

        if (\Ease\Shared::user()->isLogged()) { //Authenticated user
            $abraflexis = $this->getMenuList(new \AbraFlexi\MultiSetup\AbraFlexis());
            $customers = $this->getMenuList(new \AbraFlexi\MultiSetup\Customer());
            $companys = $this->getMenuList(new \AbraFlexi\MultiSetup\Company(), 'logo');
            $apps = $this->getMenuList(new \AbraFlexi\MultiSetup\Application(), 'image');

            $this->abraflexisMenuEnabled($nav, $abraflexis);

            if (empty($abraflexis) && empty($customers) && empty($companys)) { // All empty yet
                \AbraFlexi\MultiSetup\User::singleton()->addStatusMessage(_('No server registered yet. Please register one.'), 'warning');
                $this->customersMenuDisabled($nav);
                $this->companysMenuDisabled($nav);
            } else {
                if (count($abraflexis) && empty($customers) && empty($companys)) {
                    $this->customersMenuEnabled($nav, $customers);
                    $this->companysMenuDisabled($nav);
                    \AbraFlexi\MultiSetup\User::singleton()->addStatusMessage(_('No customer registered yet. Please register one.'), 'warning');
                } else {
                    if (count($abraflexis) && count($customers) && empty($companys)) {
                        \AbraFlexi\MultiSetup\User::singleton()->addStatusMessage(_('No company registered yet. Please register one.'), 'warning');
                        $this->customersMenuEnabled($nav, $customers);
                        $nav->addMenuItem(new \Ease\TWB4\LinkButton('company.php', '<img width=30 src=images/company.svg> ' . _('Companies'), 'warning'), 'right');
                    } else { // We Got All
                        $this->customersMenuEnabled($nav, $customers);
                        $this->companysMenuEnabled($nav, $companys);
                    }
                }
            }



            if (empty($apps)) {
                \AbraFlexi\MultiSetup\User::singleton()->addStatusMessage(_('No application registered yet. Please register one.'), 'warning');
                $nav->addMenuItem(new \Ease\TWB4\LinkButton('app.php', '<img width=30 src=images/apps.svg> ' . _('Applications'), 'warning'), 'right');
            } else {
                $this->appsMenuEnabled($nav, $apps);
            }

            $nav->addMenuItem(new \Ease\Html\ATag('logs.php', '<img height=30 src=images/log.svg> ' . _('Logs')), 'right');
            $nav->addMenuItem(new \Ease\Html\ATag('logout.php', '<img height=30 src=images/application-exit.svg> ' . _('Sign Off')), 'right');
        }
    }

    public function appsMenuEnabled($nav, $apps) {
        $appsMenu = ['app.php' => _('Register Application')];

        if (!empty($apps)) {
            $appsMenu['apps.php'] = _('Application list');
        }

        $nav->addDropDownMenu('<img width=30 src=images/apps.svg> ' . _('Applications'),
                array_merge($appsMenu, ['' => ''], $apps)
        );
    }

    public function abraflexisMenuEnabled($nav, $abraflexis) {
        $abraflexisMenu = ['abraflexi.php' => _('Register AbraFlexi Server')];

        if (!empty($abraflexis)) {
            $abraflexisMenu['abraflexis.php'] = _('Instance list');
        }

        $nav->addDropDownMenu('<img width=30 src=images/abraflexi-server.svg> ' . _('Servers'),
                array_merge($abraflexisMenu, ['' => ''], $abraflexis)
        );
    }

    public function companysMenuEnabled($nav, $companys) {
        $nav->addDropDownMenu('<img width=30 src=images/company.svg> ' . _('Companies'),
                array_merge(['company.php' => _('New Company')], ['' => ''], $companys)
        );
    }

    public function companysMenuDisabled($nav) {
        $nav->addMenuItem(new \Ease\Html\ATag('#', '<img width=30 src=images/company.svg> ' . _('Companies'), ['class' => 'nav-link disabled']));
    }

    public function customersMenuEnabled($nav, $customers) {
        $customersMenu = ['customer.php' => _('New Customer')];
        $nav->addDropDownMenu('<img width=30 src=images/customer.svg> ' . _('Customers'),
                array_merge($customersMenu, ['' => ''], $customers)
        );
    }

    public function customersMenuDisabled($nav) {
        $nav->addMenuItem(new \Ease\Html\ATag('#', '<img width=30 src=images/customer.svg> ' . _('Customers'), ['class' => 'nav-link disabled']));
    }

    public function usersMenuEnabled($nav) {
        $nav->addDropDownMenu('<img width=30 src=images/users_150.png> ' . _('Admin'),
                array_merge([
            'createaccount.php' => _('New Admin'),
            'users.php' => new \Ease\TWB4\Widgets\FaIcon('list') . '&nbsp;' . _('Admin Overview'),
            '' => '',
                        ], $this->getMenuList(\Ease\Shared::user(), 'user'))
        );
    }

    /**
     * Přidá do stránky javascript pro skrývání oblasti stavových zpráv.
     */
    public function finalize() {

        if (!empty(\Ease\Shared::logger()->getMessages())) {

            WebPage::singleton()->addCss('
#smdrag { height: 8px; 
          background-image:  url( images/slidehandle.png ); 
          background-color: #ccc; 
          background-repeat: no-repeat; 
          background-position: top center; 
          cursor: ns-resize;
}
#smdrag:hover { background-color: #f5ad66; }

');

            $this->addItem(WebPage::singleton()->getStatusMessagesBlock(['id' => 'status-messages', 'title' => _('Click to hide messages')]));
            $this->addItem(new \Ease\Html\DivTag(null, ['id' => 'smdrag', 'style' => 'margin-bottom: 5px']));
            \Ease\Shared::logger()->cleanMessages();
            WebPage::singleton()->addCss('.dropdown-menu { overflow-y: auto } ');
            WebPage::singleton()->addJavaScript("$('.dropdown-menu').css('max-height',$(window).height()-100);",
                    null, true);
            WebPage::singleton()->includeJavaScript('js/slideupmessages.js');
        }
    }

}
