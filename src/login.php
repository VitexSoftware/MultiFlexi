<?php

/**
 * Multi Flexi - Login page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Html\DivTag;
use Ease\Html\ImgTag;
use Ease\Html\InputPasswordTag;
use Ease\Html\InputTextTag;
use Ease\Shared;
use Ease\TWB4\Card;
use Ease\TWB4\Col;
use Ease\TWB4\Form;
use Ease\TWB4\FormGroup;
use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use Ease\TWB4\SubmitButton;

require_once './init.php';

$shared = Shared::singleton();

$login = $oPage->getRequestValue('login');
if ($login) {
//    try {
//        $oUser = Shared::user(new User());
//    } catch (PDOException $e) {
//        echo 'Caught exception: ', $e->getMessage(), "\n";
//    }
    if ($oUser->tryToLogin($_POST)) {
        $oPage->redirect('main.php');
        session_write_close();
        exit;
    }
}

$oPage->addItem(new PageTop(_('Sign In')));

$loginFace = new DivTag(null, ['id' => 'LoginFace']);

$oPage->container->addItem($loginFace);

$loginRow = new Row();
$infoColumn = $loginRow->addItem(new Col(4));

$infoBlock = $infoColumn->addItem(new Card(new ImgTag('images/project-logo.svg')));
$infoBlock->addItem(new DivTag(_('Welcome to Multi Flexi'), ['style' => 'text-align: center;']));

$loginColumn = $loginRow->addItem(new Col(4));

$submit = new SubmitButton(_('Sign in'), 'success btn-lg', ['id' => 'signin']);

$loginPanel = new Panel(new ImgTag('images/project-logo.svg', 'logo', ['width' => 20]),
        'secondary', null, $submit);
$loginPanel->addItem(new FormGroup(_('Username'),
                new InputTextTag('login', $login),
                '', _('the username you chose')));

$loginPanel->addItem(new FormGroup(_('Password'), new InputPasswordTag('password', $login)));

$loginPanel->body->setTagCss(['margin' => '20px']);

$loginColumn->addItem($loginPanel);

$passRecoveryColumn = $loginRow->addItem(new Col(4,
                new LinkButton('passwordrecovery.php', '<i class="fa fa-key"></i>
' . _('Lost password recovery'), 'warning')));

$oPage->container->addItem(new Form([], [], $loginRow));

$oPage->addItem(new PageBottom());

$oPage->draw();
