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
 * Description of CredentialTypeCheck.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CredentialTypeCheck extends \Ease\TWB4\Well
{
    public function __construct(\MultiFlexi\CredentialType $crtype, array $properties = [])
    {
        parent::__construct(new \Ease\Html\H2Tag($crtype->getRecordName()), $properties);
        $this->addItem(new ConfigFieldsOverview($crtype->query()));
    }
}
