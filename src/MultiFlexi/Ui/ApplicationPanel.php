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

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use MultiFlexi\Application;

/**
 * Description of ApplicationPanel
 *
 * @author vitex
 */
class ApplicationPanel extends Panel {

    /**
     *
     * @param Application $application
     * @param mixed               $content
     * @param mixed               $footer
     */
    public function __construct($application, $content = null, $footer = null) {
        $cid = $application->getMyKey();
        $headRow = new Row();
        $headRow->addColumn(2, [new AppLogo($application, ['style' => 'height: 60px']), '&nbsp;', $application->getRecordName()]);
        $headRow->addColumn(2, new LinkButton('app.php?id=' . $cid, '🛠️&nbsp;' . _('Application'), 'primary btn-lg btn-block'));
        $headRow->addColumn(2, new LinkButton('joblist.php?app_id=' . $cid, '🧑‍💻&nbsp;'. _('Jobs history'), 'secondary btn-lg btn-block'));
//        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('tasks.php?application_id=' . $cid, '🔧&nbsp;' . _('Setup tasks'), 'secondary btn-lg btn-block'));
//        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('adhoc.php?application_id=' . $cid, '🚀&nbsp;' . _('Application launcher'), 'secondary btn-lg btn-block'));
//        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('periodical.php?application_id=' . $cid, '🔁&nbsp;' . _('Periodical Tasks'), 'secondary btn-lg btn-block'));
        parent::__construct($headRow, 'default', $content, $footer);
    }
}
