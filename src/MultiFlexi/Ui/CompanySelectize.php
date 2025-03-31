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
 * Description of CompanySelect.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CompanySelectize extends CompanySelect
{
    use \Ease\Html\Widgets\Selectizer;

    #[\Override]
    public function finalize(): void
    {
        $this->selectize();
        parent::finalize();
    }
}
