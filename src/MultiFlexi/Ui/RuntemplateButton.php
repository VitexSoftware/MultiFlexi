<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of RuntemplateButton
 *
 * @author Vitex <info@vitexsoftware.cz> 
 */
class RuntemplateButton extends \Ease\TWB4\LinkButton {

    //#[\Override]
    public function __construct(\MultiFlexi\RunTemplate $runTemplate, array $properties = []) {
        parent::__construct('runtemplate.php?id=' . $runTemplate->getMyKey(), '⚗️&nbsp;' . $runTemplate->getRecordName(), 'dark btn-lg btn-block', $properties);
    }
}
