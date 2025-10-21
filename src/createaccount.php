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

require_once './init.php';

if (empty(\Ease\Shared::user()->listingQuery()->count())) {
    WebPage::singleton()->addStatusMessage(_('No Administrator found'), 'warning');
} else {
    WebPage::singleton()->onlyForLogged();
}

$process = false;

$firstname = WebPage::singleton()->getRequestValue('firstname');
$lastname = WebPage::singleton()->getRequestValue('lastname');

if (WebPage::singleton()->isPosted()) {
    $process = true;

    $emailAddress = addslashes(strtolower(WebPage::singleton()->getRequestValue('email_address')));

    $login = addslashes(WebPage::singleton()->getRequestValue('login'));
    $password = addslashes(WebPage::singleton()->getRequestValue('password'));

    $confirmation = addslashes(WebPage::singleton()->getRequestValue('confirmation'));

    $error = false;

    if (!filter_var($emailAddress, \FILTER_VALIDATE_EMAIL)) {
        \Ease\Shared::user()->addStatusMessage(_('invalid mail address'), 'warning');
    } else {
        $testuser = new \MultiFlexi\User();
        $testuser->setkeyColumn('email');
        $testuser->loadFromSQL(addslashes($emailAddress));

        if ($testuser->getUserName()) {
            $error = true;
            \Ease\Shared::user()->addStatusMessage(sprintf(
                _('Mail address %s is already registered'),
                $emailAddress,
            ), 'warning');
        }

        unset($testuser);
    }

    // Validate password strength
    $passwordValidator = new \MultiFlexi\Security\PasswordValidator(
        \Ease\Shared::cfg('PASSWORD_MIN_LENGTH', 8),
        \Ease\Shared::cfg('PASSWORD_REQUIRE_UPPERCASE', true),
        \Ease\Shared::cfg('PASSWORD_REQUIRE_LOWERCASE', true),
        \Ease\Shared::cfg('PASSWORD_REQUIRE_NUMBERS', true),
        \Ease\Shared::cfg('PASSWORD_REQUIRE_SPECIAL_CHARS', true),
    );

    $passwordValidation = $passwordValidator->validate($password);

    if (!$passwordValidation['valid']) {
        $error = true;

        foreach ($passwordValidation['errors'] as $passwordError) {
            \Ease\Shared::user()->addStatusMessage($passwordError, 'warning');
        }
    } elseif ($password !== $confirmation) {
        $error = true;
        \Ease\Shared::user()->addStatusMessage(_('Password control does not match'), 'warning');
    }

    $testuser = new \MultiFlexi\User();
    $testuser->setkeyColumn('login');
    $testuser->loadFromSQL(addslashes($login));

    if ($testuser->getMyKey()) {
        $error = true;
        \Ease\Shared::user()->addStatusMessage(sprintf(
            _('Username %s is used. Please choose another one'),
            $login,
        ), 'warning');
    }

    if ($error === false) {
        $newAdmin = new \MultiFlexi\User();

        if (
            $newAdmin->dbsync([
                'email' => $emailAddress,
                'login' => $login,
                $newAdmin->passwordColumn => $newAdmin->encryptPassword($password),
                'firstname' => $firstname,
                'lastname' => $lastname,
            ])
        ) {
            if ($newAdmin->getUserID() === 1) {
                $newAdmin->setSettingValue('admin', true);
                \Ease\Shared::user()->addStatusMessage(_('Admin account created'), 'success');
                $newAdmin->setDataValue('enabled', true);
                $newAdmin->saveToSQL();
            } else {
                \Ease\Shared::user()->addStatusMessage(_('User account created'), 'success');
            }

            $newAdmin->loginSuccess();

            $email = WebPage::singleton()->addItem(new \Ease\HtmlMailer(
                $newAdmin->getDataValue('email'),
                _('Sign On info'),
            ));
            $email->setMailHeaders(['From' => \Ease\Shared::cfg('EMAIL_FROM', 'multiflexi@'.$_SERVER['SERVER_NAME'])]);
            $email->addItem(new \Ease\Html\DivTag(sprintf(_('Your new %s account:')."\n", \Ease\Shared::appName())));
            $email->addItem(new \Ease\Html\DivTag(' Login: '.$newAdmin->getUserLogin()."\n"));
            $email->addItem(new \Ease\Html\DivTag(' Password: '.$_POST['password']."\n"));

            try {
                $email->send();
            } catch (\Ease\Exception $exc) {
            }

            if (\Ease\Shared::cfg('SEND_INFO_TO', false)) {
                $email = new \Ease\HtmlMailer(
                    \Ease\Shared::cfg('SEND_INFO_TO'),
                    sprintf(
                        _('New Sign On to %s: %s'),
                        \Ease\Shared::appName(),
                        $newAdmin->getUserLogin(),
                    ),
                );
                $email->setMailHeaders(['From' => \Ease\Shared::cfg('EMAIL_FROM', 'multiflexi@'.$_SERVER['SERVER_NAME'])]);
                $email->addItem(new \Ease\Html\DivTag(_('New User').":\n"));
                $email->addItem(new \Ease\Html\DivTag(' Login: '.$newAdmin->getUserLogin()."\n"));

                try {
                    $email->send();
                } catch (\Ease\Exception $exc) {
                }
            }

            \Ease\Shared::user($newAdmin)->loginSuccess();

            WebPage::singleton()->redirect('main.php');

            exit;
        }

        \Ease\Shared::user()->addStatusMessage(_('Administrator create failed'), 'error');
    }
}

WebPage::singleton()->addItem(new PageTop(_('New Administrator')));

// Include password strength indicator JavaScript
WebPage::singleton()->includeJavaScript('js/password-strength.js');

$regFace = new \Ease\TWB4\Panel(_('Singn On'));

$regForm = $regFace->addItem(new ColumnsForm(new \MultiFlexi\User()));

if (\Ease\Shared::user()->getUserID()) {
    $regForm->addItem(new \Ease\Html\InputHiddenTag(
        'parent',
        \Ease\Shared::user()->GetUserID(),
    ));
}

$regForm->addInput(
    new \Ease\Html\InputTextTag('firstname', $firstname),
    _('Firstname'),
);
$regForm->addInput(
    new \Ease\Html\InputTextTag('lastname', $lastname),
    _('Lastname'),
);

$regForm->addInput(new \Ease\Html\InputTextTag('login'), _('User name').' *');
$regForm->addInput(
    new \Ease\Html\InputPasswordTag('password'),
    _('Password').' *',
);
$regForm->addInput(
    new \Ease\Html\InputPasswordTag('confirmation'),
    _('Password confirmation').' *',
);
$regForm->addInput(
    new \Ease\Html\InputTextTag('email_address'),
    _('eMail address').' *',
);

$regForm->addItem(new \Ease\Html\DivTag(new \Ease\Html\InputSubmitTag(
    'Register',
    _('Register'),
    ['title' => _('finish registration'), 'class' => 'btn btn-success'],
)));

if (isset($_POST)) {
    $regForm->fillUp($_POST);
}

WebPage::singleton()->container->addItem($regFace);

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
