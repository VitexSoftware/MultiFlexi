<?php

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of AppJson
 *
 * @author vitex
 */
class AppJson extends \Ease\Html\DivTag
{
    public function __construct(\MultiFlexi\Application $app, $properties = [])
    {
        parent::__construct(new \Ease\Html\PreTag(\Rcubitto\JsonPretty\JsonPretty::print(json_decode($app->getAppJson()))), $properties);
        $this->addTagClass('ui-monospace');
        $this->addItem(new \Ease\TWB4\LinkButton('appjson.php?id=' . $app->getMyKey(), _('Download') . ' ' . $app->jsonFileName(), 'info '));
    }
}
