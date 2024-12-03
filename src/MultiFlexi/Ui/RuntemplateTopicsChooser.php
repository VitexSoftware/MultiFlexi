<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of RuntemplateTopicsChooser
 *
 * @author vitex
 */
class RuntemplateTopicsChooser extends TopicsChooser
{
    #[\Override]
    public function __construct($name, \MultiFlexi\RunTemplate $runtemplate, $properties = [])
    {
        parent::__construct($name, [], [], $properties);
    }
}
