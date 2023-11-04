<?php

/**
 * Multi Flexi  - Edit User Form  class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\User;
use Ease\Html\InputHiddenTag;
use Ease\Html\InputTag;
use Ease\TWB4\Form;
use Ease\TWB4\SubmitButton;

class UserForm extends Form
{
    /**
     * User holder
     *
     * @var User
     */
    public $user = null;

    /**
     *
     * @param User $user
     */
    public function __construct($user)
    {
        $userID = $user->getMyKey();
        $this->user = $user;
        parent::__construct(['name' => 'user' . $userID]);

        $this->addInput(new InputTag(
            'firstname',
            $user->getDataValue('firstname')
        ), _('Firstname'));
        $this->addInput(new InputTag(
            'lastname',
            $user->getDataValue('lastname')
        ), _('Lastname'));
        $this->addInput(new InputTag(
            'email',
            $user->getDataValue('email')
        ), _('Email'));
        $this->addInput(new InputTag(
            'login',
            $user->getDataValue('login')
        ), _('Username'));

        $this->addItem(new InputHiddenTag('class', get_class($user)));
        //        $this->addItem(new \Ease\Html\InputHiddenTag('enquiry_id', $user->getDataValue('enquiry_id')));

        $this->addItem(new \Ease\Html\DivTag(new SubmitButton(
            _('Save'),
            'success'
        ), ['style' => 'text-align: right']));

        if (!is_null($userID)) {
            $this->addItem(new InputHiddenTag(
                $user->keyColumn,
                $userID
            ));
        }
    }
}
