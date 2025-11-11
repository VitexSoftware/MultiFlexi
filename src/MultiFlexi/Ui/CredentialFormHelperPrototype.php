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
 * Description of CredentialTypePrototype.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CredentialFormHelperPrototype extends \Ease\Html\DivTag
{
    public \MultiFlexi\Credential $credential;

    public function __construct(\MultiFlexi\Credential $credential)
    {
        $this->credential = $credential;
        parent::__construct();
    }
}
