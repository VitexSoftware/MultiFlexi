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
 * Description of RuntemplateButton.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class RuntemplateButton extends \Ease\TWB5\LinkButton
{
    // #[\Override]
    public function __construct(\MultiFlexi\RunTemplate $runTemplate, array $properties = [])
    {
        parent::__construct('runtemplate.php?id='.$runTemplate->getMyKey(), '⚗️&nbsp;'.$runTemplate->getRecordName(), 'dark btn-lg btn-block', $properties);
    }
}
