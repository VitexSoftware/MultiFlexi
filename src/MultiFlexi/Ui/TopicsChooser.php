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
 * Description of TopicsChooser.
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
