<?php

/**
 * Multi Flexi - Recovery password.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

require_once './init.php';

$success = false;

$emailTo = $oPage->getPostValue('Email');

if (empty($emailTo)) {
    $oUser->addStatusMessage(_('Please enter your email.'));
} else {
    $userEmail = addSlashes($emailTo);

    $controlUser = new \AbraFlexi\MultiFlexi\User();
    $controlData = $controlUser->getColumnsFromSql([$controlUser->getkeyColumn()],
            ['email' => $userEmail]);

    if (empty($controlData)) {
        \Ease\Shared::user()->addStatusMessage(sprintf(_('unknow email address %s'),
                        '<strong>' . $_REQUEST['Email'] . '</strong>'), 'warning');
    } else {

        $controlUser->loadFromSQL((int) $controlData[0][$controlUser->getkeyColumn()]);
        $userLogin = $controlUser->getUserLogin();
        $newPassword = \Ease\Functions::randomString(8);

        $controlUser->passwordChange($newPassword);

        $email = $oPage->addItem(new \Ease\HtmlMailer($userEmail,
                        constant('EASE_APPNAME') . ' -' . sprintf(_('New password for %s'),
                                $_SERVER['SERVER_NAME'])));

        $email->setMailHeaders(['From' => constant('EMAIL_FROM')]);
        $email->addItem(_('Sign On informations was changed') . ":\n");

        $email->addItem(_('Username') . ': ' . $userLogin . "\n");
        $email->addItem(_('Password') . ': ' . $newPassword . "\n");

        $email->send();

        $oUser->addStatusMessage(sprintf(_('Your new password was sent to %s'),
                        '<strong>' . $emailTo . '</strong>'));
        $success = true;
    }
}

$oPage->addItem(new PageTop(_('Lost password recovery')));

$pageRow = new \Ease\TWB4\Row();

$columnI = $pageRow->addColumn('4');
$columnII = $pageRow->addColumn('4');
$columnIII = $pageRow->addColumn('4');

$oPage->addItem($pageRow);

if (!$success) {
    $columnIII->addItem(new \Ease\TWB4\Label('info', _('Tip')));

    $columnIII->addItem(new \Ease\TWB4\Well(
                    _('Forgot your password? Enter your e-mail address you entered during the registration and we will send you a new one.')));

    $titlerow = new \Ease\TWB4\Row();
    $titlerow->addColumn(4, new \Ease\Html\ImgTag('images/password.png'));
    $titlerow->addColumn(8, new \Ease\Html\H3Tag(_('Password Recovery')));

    $loginPanel = new \Ease\TWB4\Panel(new \Ease\TWB4\Container($titlerow),
            'success', null,
            new \Ease\TWB4\SubmitButton(_('Sent New Password'), 'success'));
    $loginPanel->addItem(new \Ease\TWB4\FormGroup(_('Email'),
                    new \Ease\Html\InputTextTag('Email', $emailTo,
                            ['type' => 'email'])));
    $loginPanel->body->setTagProperties(['style' => 'margin: 20px']);

    $mailForm = $columnII->addItem(new \Ease\TWB4\Form(['name' => 'PasswordRecovery']));
    $mailForm->addItem($loginPanel);

    if ($oPage->isPosted()) {
        $mailForm->fillUp($_POST);
    }
} else {
    $columnII->addItem(new \Ease\TWB4\LinkButton('login.php',
                    _('Continue')));
    $oPage->redirect('login.php');
}

$oPage->addItem(new PageBottom());

$oPage->draw();
