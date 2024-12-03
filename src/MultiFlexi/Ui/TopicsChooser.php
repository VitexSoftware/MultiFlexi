<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of TopicsChooser
 *
 * @author vitex
 */
class TopicsChooser extends PillBox
{
    #[\Override]
    public function __construct($name, $valuesAvailble, $valuesShown, $properties = [])
    {
        parent::__construct($name, $valuesAvailble, $valuesShown, $properties);
    }
}
