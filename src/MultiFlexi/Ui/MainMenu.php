<?php

/**
 * Multi Flexi  - Main Menu
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of MainMenu
 *
 * @author vitex
 */
class MainMenu extends \Ease\Html\DivTag
{
    /**
     * Vytvoří hlavní menu.
     */
    public function __construct()
    {
        parent::__construct(null, ['id' => 'MainMenu']);
    }

    /**
     * Data source.
     *
     * @param \Ease\Engine       $source
     * @param string $icon       Icon column
     * @param \MultiFlexi\Engine $nest Object place
     *
     * @return string
     */
    protected function getMenuList($source, $icon = null, $nest = null)
    {
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
                if (!is_null($nest) && ($nest->getMyKey() == $uID)) {
                    $uInfo[$namecolumn] .= ' ✓';
                }

                if (empty($icon)) {
                    $logo = '';
                } else {
                    $logo = new \Ease\Html\ImgTag($uInfo[$icon], $uInfo[$namecolumn], ['height' => 20]) . '&nbsp;';
                }
                $itemList[$source->keyword . '.php?' . $keycolumn . '=' . $uInfo[$keycolumn]] = $logo . $uInfo[$namecolumn];
            }
        }

        return $itemList;
    }

    /**
     * Insert menu.
     */
    public function afterAdd()
    {
        $nav = $this->addItem(new BootstrapMenu('main-menu', null, ['class' => 'navbar navbar-expand-lg navbar-light bg-light']));
        if (\Ease\Shared::user()->isLogged()) { //Authenticated user
            $oPage = WebPage::singleton();
            $servers = $this->getMenuList(new \MultiFlexi\Servers());
            $customers = $this->getMenuList(new \MultiFlexi\Customer(), null, $oPage->customer);
            $companys = $this->getMenuList(new \MultiFlexi\Company(), 'logo');
            $apps = $this->getMenuList(new \MultiFlexi\Application(), 'image');
            $this->serversMenuEnabled($nav, $servers);
            if (empty($servers) && empty($customers) && empty($companys)) { // All empty yet
                \MultiFlexi\User::singleton()->addStatusMessage(_('No server registered yet. Please register one.'), 'warning');
                $this->customersMenuDisabled($nav);
                $this->companysMenuDisabled($nav);
            } else {
                if (count($servers) && empty($customers) && empty($companys)) {
                    $this->customersMenuEnabled($nav, $customers);
                    $this->companysMenuDisabled($nav);
                    \MultiFlexi\User::singleton()->addStatusMessage(_('No customer registered yet. Please register one.'), 'warning');
                } else {
                    if (count($servers) && count($customers) && empty($companys)) {
                        \MultiFlexi\User::singleton()->addStatusMessage(_('No company registered yet. Please register one.'), 'warning');
                        $this->customersMenuEnabled($nav, $customers);
                        $nav->addMenuItem(new \Ease\TWB4\LinkButton('companysetup.php', '<img width=30 src=images/company.svg> ' . _('Companies'), 'warning'), 'right');
                    } else { // We Got All
                        $this->customersMenuEnabled($nav, $customers);
                        $this->companysMenuEnabled($nav, $companys);
                    }
                }
            }

            if (empty($apps)) {
                \MultiFlexi\User::singleton()->addStatusMessage(_('No application registered yet. Please register one.'), 'warning');
                $nav->addMenuItem(new \Ease\TWB4\LinkButton('app.php', '<img width=30 src=images/apps.svg> ' . _('Applications'), 'warning'), 'right');
            } else {
                $this->appsMenuEnabled($nav, $apps);
            }

            $this->usersMenuEnabled($nav);
            //$nav->addMenuItem(new \Ease\Html\ATag('logs.php', '<img height=30 src=images/log.svg> ' . _('Logs')), 'right');

            $nav->addDropDownMenu('<img height=30 src=images/log.svg> ' . _('Logs'), ['logs.php' => _('System'), 'joblist.php' => _('Jobs')]);
            $nav->addMenuItem(new \Ease\Html\ATag('logout.php', '<img height=30 src=images/application-exit.svg> ' . _('Sign Off')), 'right');
        }
    }

    /**
     *
     * @param type $nav
     * @param type $apps
     */
    public function appsMenuEnabled($nav, $apps)
    {
        $appsMenu = ['app.php' => _('Register Application')];
        if (!empty($apps)) {
            $appsMenu['apps.php'] = _('Application list');
        }

        $nav->addDropDownMenu(
            '<img width=30 src=images/apps.svg> ' . _('Applications'),
            array_merge($appsMenu, ['' => ''], $apps)
        );
    }

    /**
     * Servers menu
     * 
     * @param \Ease\Html\NavTag $nav
     * @param array $servers
     */
    public function serversMenuEnabled($nav, $servers)
    {
        $serversMenu = ['server.php' => _('Register new Server')];
        if (!empty($servers)) {
            $serversMenu['servers.php'] = _('Instance list');
        }

        $nav->addDropDownMenu(
            '<img width=30 src=images/server.svg> ' . _('Servers'),
            array_merge($serversMenu, ['' => ''], $servers)
        );
    }

    /**
     * Company Menu
     * 
     * @param \Ease\Html\NavTag $nav
     * @param array $companys
     */
    public function companysMenuEnabled($nav, $companys)
    {
        $nav->addDropDownMenu(
            '<img width=30 src=images/company.svg> ' . _('Companies'),
            array_merge(['companysetup.php' => _('New Company')], ['' => ''], $companys)
        );
    }

    /**
     *
     * @param \Ease\Html\NavTag $nav
     */
    public function companysMenuDisabled($nav)
    {
        $nav->addMenuItem(new \Ease\Html\ATag('#', '<img width=30 src=images/company.svg> ' . _('Companies'), ['class' => 'nav-link disabled']));
    }

    /**
     *
     * @param type $nav
     * @param type $customers
     */
    public function customersMenuEnabled($nav, $customers)
    {
        $customersMenu = ['customer.php' => _('New Customer')];
        $nav->addDropDownMenu(
            '<img width=30 src=images/customer.svg> ' . _('Customers'),
            array_merge($customersMenu, ['' => ''], $customers)
        );
    }

    /**
     *
     * @param type $nav
     */
    public function customersMenuDisabled($nav)
    {
        $nav->addMenuItem(new \Ease\Html\ATag('#', '<img width=30 src=images/customer.svg> ' . _('Customers'), ['class' => 'nav-link disabled']));
    }

    /**
     *
     * @param type $nav
     */
    public function usersMenuEnabled($nav)
    {
        $nav->addDropDownMenu(
            '<img width=30 src=images/system-users.svg> ' . _('Admin'),
            array_merge([
            'createaccount.php' => _('New Admin'),
            //            'users.php' => new \Ease\TWB4\Widgets\FaIcon('list') . '&nbsp;' . _('Admin Overview'),
            '' => '',
                        ], $this->getMenuList(\Ease\Shared::user(), 'login'))
        );
    }

    /**
     * Přidá do stránky javascript pro skrývání oblasti stavových zpráv.
     */
    public function finalize()
    {
        if (\Ease\Shared::user()->isLogged()) { //Authenticated user
            $this->addItem(new Breadcrumb());
        }
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
            WebPage::singleton()->addJavaScript(
                "$('.dropdown-menu').css('max-height',$(window).height()-100);",
                null,
                true
            );
            WebPage::singleton()->includeJavaScript('js/slideupmessages.js');
        }
    }
}