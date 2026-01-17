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

/**
 * Description of MainMenu.
 *
 * @author vitex
 *
 * @no-named-arguments
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
     * Insert menu.
     */
    public function afterAdd(): void
    {
        $nav = $this->addItem(new BootstrapMenu('main-menu', null, ['class' => 'navbar navbar-expand-lg navbar-light bg-light']));

        if (\Ease\Shared::user()->isLogged()) { // Authenticated user
            $oPage = WebPage::singleton();

            // Add Home link
            $nav->addMenuItem(new \Ease\Html\ATag('home.php', new \Ease\TWB4\Widgets\FaIcon('home').' '._('Home')));

            $customers = $this->getMenuList(new \MultiFlexi\Customer(), null, WebPage::singleton()->customer);
            $companies = $this->getMenuList(new \MultiFlexi\Company(), 'logo');
            $apps = $this->getMenuList(new \MultiFlexi\Application(), 'image');
            $this->credentialsMenuEnabled($nav);

            if (empty($companies)) {
                \MultiFlexi\User::singleton()->addStatusMessage('🏭 '._('No company registered yet. Please register one.'), 'warning');
            }

            if (empty($servers) && empty($customers) && empty($companies)) { // All empty yet
                //                $this->customersMenuDisabled($nav);
                $this->companiesMenuDisabled($nav);
            } else {
                if (empty($customers) && empty($companies)) {
                    //                    $this->customersMenuEnabled($nav, $customers);
                    $this->companiesMenuDisabled($nav);
                    \MultiFlexi\User::singleton()->addStatusMessage(_('No customer registered yet. Please register one.'), 'warning');
                } else {
                    if (\count($customers) && empty($companies)) {
                        \MultiFlexi\User::singleton()->addStatusMessage(_('No company registered yet. Please register one.'), 'warning');
                        $this->customersMenuEnabled($nav, $customers);
                        $nav->addMenuItem(new \Ease\TWB4\LinkButton('companysetup.php', '🏭 '._('Companies'), 'warning'), 'right');
                    } else { // We Got All
                        //                        $this->customersMenuEnabled($nav, $customers);
                        $this->companiesMenuEnabled($nav, $companies);
                    }
                }
            }

            if (empty($apps)) {
                \MultiFlexi\User::singleton()->addStatusMessage(_('No application registered yet. Please register one.'), 'warning');
                $nav->addMenuItem(new \Ease\TWB4\LinkButton('app.php', '<img width=30 src=images/apps.svg> '._('Applications'), 'warning'), 'right');
            } else {
                $this->appsMenuEnabled($nav, $apps);
            }

            $this->adminMenuEnabled($nav);
            // $nav->addMenuItem(new \Ease\Html\ATag('logs.php', '<img height=30 src=images/log.svg> ' . _('Logs')), 'right');

            $nav->addDropDownMenu('<img height=30 src=images/log.svg> '._('Logs'), [
                'logs.php' => '📖&nbsp;'._('System'),
                'joblist.php' => '🏁&nbsp;'._('Jobs'),
                'queue.php' => '⏳&nbsp;'._('Job queue'),
                'dashboard.php' => '📋&nbsp;'._('Dashboard'),
            ]);

            // Privacy menu with dropdown
            $privacyMenu = [
                'consent-preferences.php' => '<i class="fas fa-user-shield"></i> '._('Privacy Preferences'),
                'data-export-page.php' => '<i class="fas fa-download"></i> '._('Export My Data'),
                'gdpr-user-deletion-request.php' => '<i class="fas fa-user-times"></i> '._('Request Account Deletion'),
                '' => '',
                'privacy-policy.php' => '<i class="fas fa-shield-alt"></i> '._('Privacy Policy'),
                'cookie-policy.php' => '<i class="fas fa-cookie-bite"></i> '._('Cookie Policy'),
            ];
            $nav->addDropDownMenu('<i class="fas fa-user-shield"></i> '._('Privacy'), $privacyMenu);

            $nav->addMenuItem(new \Ease\Html\ATag('logout.php', '<img height=30 src=images/application-exit.svg> '._('Sign Off')), 'right');

            if (\MultiFlexi\Runner::isServiceActive('multiflexi-scheduler.service') === false) {
                WebPage::singleton()->addStatusMessage(_('My Scheduler systemd service is not running. Consider `systemctl start multiflexi-scheduler`'), 'warning');
            }

            if (\MultiFlexi\Runner::isServiceActive('multiflexi-executor.service') === false) {
                WebPage::singleton()->addStatusMessage(_('My Task Launcher systemd service is not running. Consider `systemctl start multiflexi-executor`'), 'warning');
            }

            // Add search form outside collapsible menu to keep it always visible
            $nav->addItem($this->searchFrom());
        }

        // Add language selector for all users (logged in or not) - placed last to appear on far right
        $nav->addMenuItem(new \Ease\TWB4\Widgets\LangSelect('locale'), 'right');
    }

    /**
     * @param string $nav
     * @param array  $apps
     */
    public function appsMenuEnabled($nav, $apps): void
    {
        $appsMenu = [
            'app.php' => '🧩'._('Register Application'),
        ];

        if (!empty($apps)) {
            $appsMenu['apps.php'] = '🧩'._('Application list');
        }

        $appsMenu['activation-wizard.php'] = '🧙'._('Activation Wizard');
        $appsMenu['runtemplates.php'] = '⚗️'._('RunTemplates');

        $nav->addDropDownMenu(
            '<img width=30 src=images/apps.svg> '._('Applications'),
            $appsMenu,
        );
    }

    /**
     * Credential menu.
     *
     * @param \Ease\Html\NavTag $nav
     */
    public function credentialsMenuEnabled($nav): void
    {
        $credentialsMenu = ['credential.php' => '🔏 '._('Register new Credential')];
        $credentialsMenu['credentials.php'] = '🔒 '._('Credentials Listing');
        $credentialsMenu['credentialtype.php'] = '🔏 '._('Register new Credential Type');
        $credentialsMenu['credentialtypes.php'] = '🔒 '._('Credential types listing');
        $credentialsMenu['credentialprototype.php'] = '📋 '._('New Credential Prototype');
        $credentialsMenu['credentialprototypes.php'] = '📋 '._('Credential Prototypes');
        $credentialsMenu['credential-wizard.php'] = '🧙'._('Credential Wizard');
        $nav->addDropDownMenu('<img width=30 src=images/vault.svg> '._('Credentials'), $credentialsMenu);
    }

    /**
     * Company Menu.
     *
     * @param \Ease\Html\NavTag $nav
     * @param array             $companies
     */
    public function companiesMenuEnabled($nav, $companies): void
    {
        $nav->addDropDownMenu(
            '<img width=30 src=images/company.svg> '._('Companies'),
            array_merge(['companysetup.php' => _('New Company')], ['' => ''], ['companies.php' => _('Listing')], $companies),
        );
    }

    /**
     * @param \Ease\Html\NavTag $nav
     */
    public function companiesMenuDisabled($nav): void
    {
        $nav->addMenuItem(new \Ease\TWB4\LinkButton('companysetup.php', '<img width=30 src=images/company.svg> '._('Companies'), 'warning', ['class' => 'nav-link']));
    }

    /**
     * @param \Ease\Html\NavTag $nav
     * @param array             $customers
     */
    public function customersMenuEnabled($nav, $customers): void
    {
        $customersMenu = ['customer.php' => _('New Customer')];
        $nav->addDropDownMenu(
            '<img width=30 src=images/customer.svg> '._('Customers'),
            array_merge($customersMenu, ['' => ''], $customers),
        );
    }

    /**
     * @param \Ease\Html\NavTag $nav
     */
    public function customersMenuDisabled($nav): void
    {
        $nav->addMenuItem(new \Ease\Html\ATag('#', '<img width=30 src=images/customer.svg> '._('Customers'), ['class' => 'nav-link disabled']));
    }

    /**
     * @param \Ease\Html\NavTag $nav
     */
    public function adminMenuEnabled($nav): void
    {
        $nav->addDropDownMenu(
            '<img width=30 src=images/system-users.svg> '._('Admin'),
            array_merge([
                'profile.php' => new \Ease\TWB4\Widgets\FaIcon('user-circle').'&nbsp;'._('My Profile'),
                'createaccount.php' => '🤬&nbsp;'._('New Admin'),
                'createuser.php' => '👤&nbsp;'._('New User Account'),
                'envmods.php' => '🌦️&nbsp;'._('Environment Modules'),
                'actionmodules.php' => '🤖&nbsp;'._('Actions'),
                'executors.php' => '🚀&nbsp;'._('Executors'),
                'credtypes.php' => '🔐&nbsp;'._('Credential Type helpers'),
                'data-retention-admin.php' => '🗂️&nbsp;'._('Data Retention Management'),
                'admin-deletion-requests.php' => '🗑️&nbsp;'._('Deletion Requests'),
                'admin-data-corrections.php' => '✏️&nbsp;'._('Data Corrections'),
                '' => '',
                'users.php' => new \Ease\TWB4\Widgets\FaIcon('list').'&nbsp;'._('Users'),
            ], $this->getMenuList(\Ease\Shared::user())),
        );
    }

    /**
     * Přidá do stránky javascript pro skrývání oblasti stavových zpráv.
     */
    public function finalize(): void
    {
        //        if (\Ease\Shared::user()->isLogged()) { //Authenticated user
        //            $this->addItem(new Breadcrumb());
        //        }
        if (!empty(\Ease\Shared::logger()->getMessages())) {
            WebPage::singleton()->addCss(<<<'EOD'

#smdrag { height: 8px;
          background-image:  url( images/slidehandle.png );
          background-color: #ccc;
          background-repeat: no-repeat;
          background-position: top center;
          cursor: ns-resize;
}
#smdrag:hover { background-color: #f5ad66; }


EOD);
            $this->addItem(WebPage::singleton()->getStatusMessagesBlock(['id' => 'status-messages', 'title' => _('Click to hide messages')]));
            $this->addItem(new \Ease\Html\DivTag(null, ['id' => 'smdrag', 'style' => 'margin-bottom: 5px']));
            \Ease\Shared::logger()->cleanMessages();
            WebPage::singleton()->addCss('.dropdown-menu { overflow-y: auto } ');
            WebPage::singleton()->addJavaScript(
                "$('.dropdown-menu').css('max-height',$(window).height()-100);",
                null,
                true,
            );
            WebPage::singleton()->includeJavaScript('js/slideupmessages.js');
        }

        parent::finalize();
    }

    public function searchFrom()
    {
        $search = \Ease\WebPage::getRequestValue('search');
        $what = \Ease\WebPage::getRequestValue('what');

        if (empty($search) && (empty($what) || $what === 'RunTemplate' || $what === 'Job')) {
            $search = '#';
        }

        $searchForm = new SecureForm(['class' => 'form-inline my-2 my-lg-0', 'action' => 'search.php', 'style' => 'flex-wrap: nowrap;']);
        $searchForm->addItem(new \Ease\Html\InputTextTag('search', $search, ['aria-label' => _('Search'), 'class' => 'form-control mr-1', 'type' => 'search', 'placeholder' => _('Search'), 'title' => _('#number to jump on record'), 'style' => 'width: 100px;']));
        $searchForm->addItem(new SearchSelect('what', [], $search));
        $searchForm->addItem(new \Ease\Html\ButtonTag(_('Search'), ['class' => 'btn btn-outline-success ml-1', 'type' => 'submit', 'title' => _('Search in selected area'), 'id' => 'mainmenusearchbutton']));

        return $searchForm;
    }

    /**
     * Data source.
     *
     * @param \Ease\Engine       $source
     * @param string             $icon   Icon column
     * @param \MultiFlexi\Engine $nest   Object place
     *
     * @return string
     */
    protected function getMenuList($source, $icon = null, $nest = null)
    {
        $keycolumn = $source->getkeyColumn();
        $namecolumn = $source->getNameColumn();
        $columns = [$source->getkeyColumn(), $namecolumn];

        if ($icon) {
            $columns[] = $icon;
        }

        $lister = $source->getColumnsFromSQL($columns, '', $namecolumn, $keycolumn);
        $itemList = [];

        if ($lister) {
            foreach ($lister as $uID => $uInfo) {
                if (null !== $nest && ($nest->getMyKey() === $uID)) {
                    $uInfo[$namecolumn] .= ' ✓';
                }

                if (empty($icon)) {
                    $logo = '';
                } else {
                    $logo = new \Ease\Html\ImgTag($uInfo[$icon], (string) $uInfo[$namecolumn], ['height' => 20]).'&nbsp;';
                }

                $itemList[$source->keyword.'.php?'.$keycolumn.'='.$uInfo[$keycolumn]] = $logo._(\array_key_exists($namecolumn, $uInfo) ? (string) ($uInfo[$namecolumn]).' ' : 'n/a');
            }
        }

        return $itemList;
    }
}
