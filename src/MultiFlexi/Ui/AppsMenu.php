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

use Ease\TWB4\Widgets\MainPageMenu;
use MultiFlexi\Application;

/**
 * Description of AppsMenu.
 *
 * @author vitex
 */
class AppsMenu extends MainPageMenu
{
    public Application $apper;

    /**
     * Application Status Menu.
     */
    public function __construct()
    {
        parent::__construct();
        $this->apper = new Application();

        foreach ($this->apper->getAll() as $appData) {
            $this->addApp($appData['id'], $appData['name'], $appData['description'], $appData['executable'], $appData['image'], $appData['enabled']);
        }
    }

    /**
     * Add Application card.
     *
     * @param int    $id         application ID
     * @param string $nazev
     * @param string $popis
     * @param string $executable
     * @param string $image
     * @param mixed  $status
     */
    public function addApp($id, $nazev, $popis, $executable, $image, $status): void
    {
        if (Application::doesBinaryExist($executable) && ($status === 1)) {
            $statusFound = true;
            $properties['class'] = 'text-white bg-success mb-3';
        } else {
            $statusFound = false;
            $properties['class'] = 'text-white bg-warning mb-3';
        }

        if ($statusFound !== (bool) $status) {
            $this->apper->updateToSQL(['id' => $id, 'enabled' => ($statusFound ? 1 : 0)]);
            $this->apper->addStatusMessage(sprintf(_('Updating availbility status of application %s to %s'), $nazev, $statusFound ? _('enabled') : _('disabled')), 'warning');
        }

        $this->addMenuItem($nazev, 'app.php?id='.$id, $image, $popis, _('Configure'), $properties);
    }
}
