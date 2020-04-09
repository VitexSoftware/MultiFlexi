<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
            $nav->addDropDownMenu('<img width=30 src=images/flexibee-server.svg> ' . _('Servers'),
                    array_merge([
                'flexibee.php' => _('Register FlexiBee Server'),
                'flexibees.php' => _('Instance list'),
                '' => '',
                            ], $this->getMenuList(new \FlexiPeeHP\MultiSetup\FlexiBees(), 'name'))
            );

            $nav->addDropDownMenu('<img width=30 src=images/customer.svg> ' . _('Customers'),
                    array_merge([
                'customer.php' => _('New Customer'),
//                'customers.php' => \Ease\TWB4\Part::GlyphIcon('list').'&nbsp;'._('Customers list'),
                '' => '',
                            ], $this->getMenuList(new \FlexiPeeHP\MultiSetup\Customer(), 'name'))
            );

            $nav->addDropDownMenu('<img width=30 src=images/company.svg> ' . _('Companies'),
                    array_merge([
                'company.php' => _('New Company'),
//                'customers.php' => \Ease\TWB4\Part::GlyphIcon('list').'&nbsp;'._('Customers list'),
                '' => '',
                            ], $this->getMenuList(new \FlexiPeeHP\MultiSetup\Company(), 'name'))
            );

            if (\Ease\Shared::user()->getSettingValue('admin') == true) {
                $nav->addDropDownMenu('<img width=30 src=images/users_150.png> ' . _('Admin'),
                        array_merge([
                    'createaccount.php' => _('New Admin'),
                    'users.php' => new \Ease\TWB4\Widgets\FaIcon('list') . '&nbsp;' . _('Admin Overview'),
                    '' => '',
                                ], $this->getMenuList(\Ease\Shared::user(), 'user'))
                );
            }
            
            $nav->addMenuItem( new \Ease\Html\ATag('logout.php', _('Sign Off')),'right');
        }
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
