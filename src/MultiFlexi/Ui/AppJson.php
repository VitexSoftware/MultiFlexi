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
 * Description of AppJson.
 *
 * @author vitex
 */
class AppJson extends \Ease\Html\DivTag
{
    /**
     * APP JSON Viewer.
     *
     * @param array<string, string> $properties
     */
    public function __construct(\MultiFlexi\Application $app, array $properties = [])
    {
        parent::__construct(new \Ease\Html\PreTag(\Rcubitto\JsonPretty\JsonPretty::print(json_decode($app->getAppJson()))), $properties);
        $this->addTagClass('ui-monospace');
        $this->addItem(new \Ease\TWB4\LinkButton('appjson.php?id='.$app->getMyKey(), _('Download').' '.$app->jsonFileName(), 'info '));
    }
}
