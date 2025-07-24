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
 *
 * @no-named-arguments
 */
class RunTemplateDotEnv extends \Ease\Html\DivTag
{
    /**
     * APP JSON Viewer.
     *
     * @param array<string, string> $properties
     */
    public function __construct(\MultiFlexi\RunTemplate $runtemplate, array $properties = [])
    {
        parent::__construct(new \Ease\Html\PreTag($runtemplate->envFile()), $properties);
        $this->addTagClass('ui-monospace custom-control');
        $this->addItem(new \Ease\TWB4\LinkButton('runtemplateenv.php?id='.$runtemplate->getMyKey(), _('Download').' multiflexi_runtemplate_'.$runtemplate->getMyKey().'.env', 'info '));
    }
}
