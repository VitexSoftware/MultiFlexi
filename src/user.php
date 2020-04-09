<?php

namespace TaxTorro;

/**
 * TaxTorro - Stránka uživatele.
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015 Vitex Software
 */
require_once 'includes/Init.php';

$oPage->onlyForLogged();

$user_id = $oPage->getRequestValue('id', 'int');

//$user = Engine::doThings($oPage);
//if (is_null($user)) {
    $user = new User($user_id);
//}

if ($oPage->getGetValue('delete', 'bool') == 'true') {
    if ($user->delete()) {
        $oPage->redirect('users.php');
        exit;
    }
}

$oPage->addItem(new ui\PageTop(_('Uživatel')));

switch ($oPage->getRequestValue('action')) {
    case 'delete':

        $confirmBlock = new \Ease\TWB\Well();

        $confirmBlock->addItem($user);

        $confirmator = $confirmBlock->addItem(new \Ease\TWB\Panel(_('Opravdu smazat ?')), 'danger');
        $confirmator->addItem(new \Ease\TWB\LinkButton('user.php?id='.$user->getId(), _('Ne').' '.\Ease\TWB\Part::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?delete=true&'.$user->keyColumn.'='.$user->getID(), _('Ano').' '.\Ease\TWB\Part::glyphIcon('remove'), 'danger'));

        $oPage->container->addItem(new \Ease\TWB\Panel('<strong>'.$user->getUserName().'</strong>', 'info', $confirmBlock));

        break;
    default :

//        $operationsMenu = $user->operationsMenu();
//        $operationsMenu->setTagCss(['float' => 'right']);
//        $operationsMenu->dropdown->addTagClass('pull-right');

        $oPage->container->addItem(new \Ease\TWB\Panel(['<strong>'.$user->getUserName().'</strong>', /*$operationsMenu*/], 'info', new UserForm($user)));
        break;
}

$oPage->addItem(new ui\PageBottom());

$oPage->draw();
