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
 * Description of RequirementsOverview.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class RequirementsOverview extends \Ease\Html\DivTag
{
    public function __construct(array $requirementsRaw, $properties = [])
    {
        parent::__construct(_('Requirements'), $properties);

        foreach ($requirementsRaw as $req) {
            $formClass = '\\MultiFlexi\\Ui\\Form\\'.$req;

            if (class_exists($formClass)) {
                $reqCard = new \Ease\TWB5\Card([
                    new \Ease\Html\ImgTag($formClass::$logo, $req, ['title' => $req, 'height' => 40, 'class' => 'card-img-top']),
                    new \Ease\Html\DivTag(new \Ease\Html\H5Tag($formClass::name(), ['class' => 'card-body'])),
                ]);
            } else {
                $reqCard = new \Ease\TWB5\Card($req);
            }

            $this->addItem($reqCard);
        }

        $this->addTagClass('card-group');
    }
}
