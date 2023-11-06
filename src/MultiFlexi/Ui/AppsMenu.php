<?php

/**
 * Multi Flexi - Show avilble applications as cards.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\Widgets\MainPageMenu;
use MultiFlexi\Application;

/**
 * Description of AppsMenu
 *
 * @author vitex
 */
class AppsMenu extends MainPageMenu
{
    /**
     *
     * @var Application
     */
    public $apper = null;

    /**
     * Application Status Menu
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
     * Add Appliaction card
     *
     * @param int $id appliaction ID
     * @param string $nazev
     * @param string $popis
     * @param string $executable
     * @param string $image
     */
    public function addApp($id, $nazev, $popis, $executable, $image, $status)
    {
        if (Application::doesBinaryExist($executable) && ($status == 1)) {
            $statusFound = true;
            $properties['class'] = 'text-white bg-success mb-3';
        } else {
            $statusFound = false;
            $properties['class'] = 'text-white bg-warning mb-3';
        }
        if ($statusFound != boolval($status)) {
            $this->apper->updateToSQL(['id' => $id, 'enabled' => ($statusFound ? 1 : 0)]);
            $this->apper->addStatusMessage(sprintf(_('Updating availbility status of application %s to %s'), $nazev, $statusFound ? _('enabled') : _('disabled')), 'warning');
        }
        $this->addMenuItem($nazev, 'app.php?id=' . $id, $image, $popis, _('Configure'), $properties);
    }
}
