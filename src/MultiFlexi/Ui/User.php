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
 * Description of User.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class User extends \MultiFlexi\User implements \MultiFlexi\Ui\columns
{
    /**
     * @see https://datatables.net/examples/advanced_init/column_render.html
     *
     * @return string Column rendering
     */
    public function columnDefs()
    {
        return <<<'EOD'

"columnDefs": [
           // { "visible": false,  "targets": [ 0 ] }
        ]
,

EOD;
    }
}
