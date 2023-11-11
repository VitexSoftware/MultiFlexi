<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

/**
 *
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of AppLinkButton
 *
 * @author vitex
 */
class AppLinkButton extends \Ease\TWB4\LinkButton
{
    public function __construct(\MultiFlexi\Application $app, $properties = [])
    {
        parent::__construct('app.php?id=' . $app->getMyKey(), [new AppLogo($app, ['style' => 'height: 64px']),'&nbsp;', $app->getRecordName()], 'inverse', $properties);
    }
}
