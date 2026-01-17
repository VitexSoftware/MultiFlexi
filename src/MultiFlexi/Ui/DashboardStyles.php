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
 * Dashboard CSS styles.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2023-2026 Vitex Software
 */
class DashboardStyles
{
    /**
     * Get dashboard CSS styles.
     *
     * @return string CSS styles
     */
    public static function getStyles(): string
    {
        return <<<'EOD'

.chart-container {
    margin: 20px 0;
    text-align: center;
}
.chart-container svg {
    max-width: 100%;
    height: auto;
}
.card {
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}
.card-body {
    padding: 1.5rem;
}
.card-title {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}
.display-4 {
    font-size: 2.5rem;
    font-weight: bold;
    margin: 0.5rem 0;
}

EOD;
    }
}
