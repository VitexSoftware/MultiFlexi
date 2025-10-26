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

use Ease\Html\InputHiddenTag;
use Ease\Html\InputTag;
use Ease\TWB4\SubmitButton;
use MultiFlexi\User;

/**
 * @no-named-arguments
 */
class UserForm extends SecureForm
{
    /**
     * User holder.
     */
    public User $user;

    /**
     * @param User $user
     */
    public function __construct($user)
    {
        $userID = $user->getMyKey();
        $this->user = $user;
        parent::__construct(['name' => 'user'.$userID]);

        $this->addInput(new InputTag(
            'firstname',
            $user->getDataValue('firstname'),
        ), _('Firstname'));
        $this->addInput(new InputTag(
            'lastname',
            $user->getDataValue('lastname'),
        ), _('Lastname'));
        $this->addInput(new InputTag(
            'email',
            $user->getDataValue('email'),
        ), _('Email'));
        $this->addInput(new InputTag(
            'login',
            $user->getDataValue('login'),
        ), _('Username'));

        $this->addItem(new InputHiddenTag('class', $user::class));
        //        $this->addItem(new \Ease\Html\InputHiddenTag('enquiry_id', $user->getDataValue('enquiry_id')));

        $this->addItem(new \Ease\Html\DivTag(new SubmitButton(
            _('Save'),
            'success',
        ), ['style' => 'text-align: right']));

        if (null !== $userID) {
            $this->addItem(new InputHiddenTag(
                $user->keyColumn,
                $userID,
            ));
        }
    }
}
