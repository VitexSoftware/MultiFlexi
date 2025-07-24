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
 * Description of RuntemplateTopicsChooser.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class RuntemplateTopicsChooser extends TopicsChooser
{
    #[\Override]
    public function __construct($name, \MultiFlexi\RunTemplate $runtemplate, $properties = [])
    {
        parent::__construct($name, [], [], $properties);
    }
}
