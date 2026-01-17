<?php

/**
 * MultiFlexi - Application Info Panel.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

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

use Ease\TWB4\Panel;
use MultiFlexi\Application;

/**
 * Description of ApplicationInfo.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class ApplicationInfo extends Panel
{
    public function __construct(Application $application)
    {
        $body = new \Ease\Html\DivTag(null, ['class' => 'p-4']);
        
        $row = new \Ease\TWB4\Row();
        $row->addColumn(4, new \Ease\Html\DivTag(new AppLogo($application, ['class' => 'img-fluid rounded shadow-sm border p-2']), ['class' => 'text-center mb-4']));
        
        $infoCol = $row->addColumn(8, $this->metadataTable($application));
        
        $body->addItem($row);

        parent::__construct(null, 'default', $body, new AppLastMonthChart($application));
    }

    /**
     * @param Application $application
     * @return \Ease\Html\DivTag
     */
    public function metadataTable($application)
    {
        $metadata = new \Ease\Html\DivTag(null, ['class' => 'application-metadata']);
        
        $name = $application->getDataValue('name');
        $description = $application->getDataValue('description');

        if (method_exists($application, 'getLocalizedName')) {
            $name = $application->getLocalizedName() ?? $name;
        }

        if (method_exists($application, 'getLocalizedDescription')) {
            $description = $application->getLocalizedDescription() ?? $description;
        }

        $metadata->addItem(new \Ease\Html\H3Tag($name, ['class' => 'border-bottom pb-2 mb-3']));
        $metadata->addItem(new \Ease\Html\PTag($description, ['class' => 'lead']));

        $details = new \Ease\TWB4\Row();
        
        $col1 = $details->addColumn(6);
        $col1->addItem($this->infoRow('🏠', _('Homepage'), new \Ease\Html\ATag($application->getDataValue('homepage'), $application->getDataValue('homepage'))));
        $col1->addItem($this->infoRow('🆔', _('UUID'), $application->getDataValue('uuid')));
        $col1->addItem($this->infoRow('📦', _('Image'), $application->getDataValue('ociimage')));

        $col2 = $details->addColumn(6);
        $col2->addItem($this->infoRow('📁', _('Binary'), $application->getDataValue('executable')));
        $col2->addItem($this->infoRow('🏷️', _('Version'), $application->getDataValue('version')));
        $col2->addItem($this->infoRow('📜', _('Requirements'), new RequirementsOverview($application->getRequirements())));

        $metadata->addItem($details);

        return $metadata;
    }

    /**
     * Helper for info rows
     */
    private function infoRow($icon, $label, $value)
    {
        if (empty($value)) return null;
        return new \Ease\Html\DivTag([
            new \Ease\Html\SmallTag($icon . ' ' . $label, ['class' => 'text-muted d-block font-weight-bold text-uppercase small']),
            new \Ease\Html\DivTag($value, ['class' => 'mb-3 font-weight-normal'])
        ]);
    }

}
