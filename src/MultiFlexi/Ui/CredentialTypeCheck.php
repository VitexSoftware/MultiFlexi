<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of CredentialTypeCheck
 *
 * @author Vitex <info@vitexsoftware.cz> 
 */
class CredentialTypeCheck extends \Ease\TWB4\Well {

    public function __construct(\MultiFlexi\CredentialType $crtype, array $properties = []) {
        parent::__construct(new \Ease\Html\H2Tag($crtype->getRecordName()), $properties);
        $this->addItem(new ConfigFieldsOverview($crtype->query()));
    }
}
