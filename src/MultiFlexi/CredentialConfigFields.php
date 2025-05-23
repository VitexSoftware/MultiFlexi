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

namespace MultiFlexi;

/**
 * Description of CredentialConfigFields.
 *
 * @author vitex
 */
class CredentialConfigFields extends ConfigFields
{
    private ?Credential $credential = null;

    public function __construct(Credential $credential)
    {
        parent::__construct('🔐 '.$credential->getRecordName());
        $this->credential = $credential;
    }

    public function addField(ConfigField $field): self
    {
        return parent::addField($field->setLogo($this->getLogo()));
    }

    /**
     * Set assigned Credential object.
     */
    public function setCredential(?Credential $credential): self
    {
        $this->credential = $credential;

        return $this;
    }

    /**
     * Get assigned Credential object.
     */
    public function getCredential(): ?Credential
    {
        return $this->credential;
    }

    public function getLogo(): string
    {
        return $this->credential->getCredentialType() ? $this->credential->getCredentialType()->getLogo() : '';
    }

    public function getCredentialName(): string
    {
        return $this->credential ? $this->credential->getRecordName() : '';
    }

    public function getCredentialType(): string
    {
        return $this->credential ? $this->credential->getType() : '';
    }
}
