<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

require_once './init.php';

if (empty($oUser->listingQuery()->count())) {
    $oPage->addStatusMessage(_('No Administrator found'), 'warning');
} else {
    $oPage->onlyForLogged();
}


$process = false;

$firstname = $oPage->getRequestValue('firstname');
$lastname = $oPage->getRequestValue('lastname');

if ($oPage->isPosted()) {
    $process = true;

    $emailAddress = addslashes(strtolower($oPage->getRequestValue('email_address')));

    $login = addslashes($oPage->getRequestValue('login'));
    $password = addslashes($oPage->getRequestValue('password'));

    $confirmation = addslashes($oPage->getRequestValue('confirmation'));

    $error = false;

    if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
        $oUser->addStatusMessage(_('invalid mail address'), 'warning');
    } else {
        $testuser = new \AbraFlexi\MultiFlexi\User();
        $testuser->setkeyColumn('email');
        $testuser->loadFromSQL(addSlashes($emailAddress));
        if ($testuser->getUserName()) {
            $error = true;
            $oUser->addStatusMessage(sprintf(_('Mail address %s is already registered'),
                            $emailAddress), 'warning');
        }
        unset($testuser);
    }

    if (strlen($password) < 5) {
        $error = true;
        $oUser->addStatusMessage(_('password is too short'), 'warning');
    } elseif ($password != $confirmation) {
        $error = true;
        $oUser->addStatusMessage(_('Password control does not match'), 'warning');
    }

    $testuser = new \AbraFlexi\MultiFlexi\User();
    $testuser->setkeyColumn('login');
    $testuser->loadFromSQL(addslashes($login));

    if ($testuser->getMyKey()) {
        $error = true;
        $oUser->addStatusMessage(sprintf(_('Username %s is used. Please choose another one'),
                        $login), 'warning');
    }

    if ($error == false) {
        $newAdmin = new \AbraFlexi\MultiFlexi\User();

        if ($newAdmin->dbsync([
                    'email' => $emailAddress,
                    'login' => $login,
                    $newAdmin->passwordColumn => $newAdmin->encryptPassword($password),
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                ])) {

            if ($newAdmin->getUserID() == 1) {
                $newAdmin->setSettingValue('admin', true);
                $oUser->addStatusMessage(_('Admin account created'), 'success');
                $newAdmin->setDataValue('enabled', true);
                $newAdmin->saveToSQL();
            } else {
                $oUser->addStatusMessage(_('User account created'), 'success');
            }

            $newAdmin->loginSuccess();

            $email = $oPage->addItem(new \Ease\HtmlMailer($newAdmin->getDataValue('email'),
                            _('Sign On info')));
            $email->setMailHeaders(['From' => \Ease\Functions::cfg('EMAIL_FROM')]);
            $email->addItem(new \Ease\Html\DivTag(sprintf(_("Your new %s account:") . "\n", \Ease\Shared::appName())));
            $email->addItem(new \Ease\Html\DivTag(' Login: ' . $newAdmin->getUserLogin() . "\n"));
            $email->addItem(new \Ease\Html\DivTag(' Password: ' . $_POST['password'] . "\n"));
            $email->send();

            $email = $oPage->addItem(new \Ease\HtmlMailer(\Ease\Functions::cfg('SEND_INFO_TO'),
                            sprintf(_('New Sign On to %s: %s'),
                                    \Ease\Shared::appName(), $newAdmin->getUserLogin())));
            $email->setMailHeaders(['From' => \Ease\Functions::cfg('EMAIL_FROM')]);
            $email->addItem(new \Ease\Html\DivTag(_("New User").":\n"));
            $email->addItem(new \Ease\Html\DivTag(' Login: ' . $newAdmin->getUserLogin() . "\n"));
            $email->send();

            \Ease\Shared::user($newAdmin)->loginSuccess();

            $oPage->redirect('main.php');
            exit;
        } else {
            $oUser->addStatusMessage(_('Administrator create failed'), 'error');
        }
    }
}

$oPage->addItem(new PageTop(_('New Administrator')));

$regFace = $oPage->container->addItem(new \Ease\TWB4\Panel(_('Singn On')));

$regForm = $regFace->addItem(new ColumnsForm(new \AbraFlexi\MultiFlexi\User()));
if ($oUser->getUserID()) {
    $regForm->addItem(new \Ease\Html\InputHiddenTag('parent',
                    $oUser->GetUserID()));
}

$regForm->addInput(new \Ease\Html\InputTextTag('firstname', $firstname),
        _('Firstname'));
$regForm->addInput(new \Ease\Html\InputTextTag('lastname', $lastname),
        _('Lastname'));

$regForm->addInput(new \Ease\Html\InputTextTag('login'), _('User name') . ' *');
$regForm->addInput(new \Ease\Html\InputPasswordTag('password'),
        _('Password') . ' *');
$regForm->addInput(new \Ease\Html\InputPasswordTag('confirmation'),
        _('Password confirmation') . ' *');
$regForm->addInput(new \Ease\Html\InputTextTag('email_address'),
        _('eMail address') . ' *');

$regForm->addItem(new \Ease\Html\DivTag(new \Ease\Html\InputSubmitTag('Register',
                        _('Register'),
                        ['title' => _('finish registration'), 'class' => 'btn btn-success'])));

if (isset($_POST)) {
    $regForm->fillUp($_POST);
}

$oPage->addItem(new PageBottom());
$oPage->draw();
