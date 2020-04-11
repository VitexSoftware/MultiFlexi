<?php

/**
 * Multi FlexiBee Setup  - Main Menu
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

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
     * @param string $icon   Description
     *
     * @return string
     */
    protected function getMenuList($source, $icon = '') {
        $keycolumn = $source->getkeyColumn();
        $namecolumn = $source->nameColumn;
        $lister = $source->getColumnsFromSQL([$source->getkeyColumn(), $namecolumn],
                null, $namecolumn, $keycolumn);

        $itemList = [];
        if ($lister) {
            foreach ($lister as $uID => $uInfo) {
                $itemList[$source->keyword . '.php?' . $keycolumn . '=' . $uInfo[$keycolumn]] = new \Ease\TWB4\Widgets\FaIcon($icon) . '&nbsp;' . $uInfo[$namecolumn];
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
            $flexiBees = $this->getMenuList(new \FlexiPeeHP\MultiSetup\FlexiBees(), 'name');
            $customers = $this->getMenuList(new \FlexiPeeHP\MultiSetup\Customer(), 'name');
            $companys = $this->getMenuList(new \FlexiPeeHP\MultiSetup\Company(), 'name');


            $this->flexibeesMenuEnabled($nav, $flexiBees);


            if (empty($flexiBees) && empty($customers) && empty($companys)) { // All empty yet
                \FlexiPeeHP\MultiSetup\User::singleton()->addStatusMessage(_('No server registered yet. Please register one.'), 'warning');
                $this->customersMenuDisabled($nav);
                $this->companysMenuDisabled($nav);
            } else {
                if (count($flexiBees) && empty($customers) && empty($companys)) {
                    $this->customersMenuEnabled($nav, $customers);
                    $this->companysMenuDisabled($nav);
                    \FlexiPeeHP\MultiSetup\User::singleton()->addStatusMessage(_('No customer registered yet. Please register one.'), 'warning');
                } else {
                    if (count($flexiBees) && count($customers) && empty($companys)) {
                        \FlexiPeeHP\MultiSetup\User::singleton()->addStatusMessage(_('No company registered yet. Please register one.'), 'warning');
                        $this->customersMenuEnabled($nav, $customers);
                        $this->companysMenuEnabled($nav, $companys);
                    } else { // We Got All
                        $this->customersMenuEnabled($nav, $customers);
                        $this->companysMenuEnabled($nav, $companys);
                    }
                }
            }
            $nav->addMenuItem(new \Ease\Html\ATag('logout.php', _('Sign Off')), 'right');
        }
    }

    public function flexibeesMenuEnabled($nav, $flexiBees) {
        $flexiBeesMenu = ['flexibee.php' => _('Register FlexiBee Server')];

        if (!empty($flexiBees)) {
            $flexiBeesMenu['flexibees.php'] = _('Instance list');
        }

        $nav->addDropDownMenu('<img width=30 src=images/flexibee-server.svg> ' . _('Servers'),
                array_merge($flexiBeesMenu, ['' => ''], $flexiBees)
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

        if (!empty(\Ease\Shared::singleton()->getStatusMessages())) {

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
            \Ease\Shared::singleton()->cleanMessages();
            WebPage::singleton()->addCss('.dropdown-menu { overflow-y: auto } ');
            WebPage::singleton()->addJavaScript("$('.dropdown-menu').css('max-height',$(window).height()-100);",
                    null, true);
            $this->includeJavaScript('js/slideupmessages.js');
        }
    }

}
