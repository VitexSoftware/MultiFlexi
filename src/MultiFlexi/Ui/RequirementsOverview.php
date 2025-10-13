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
 *
 * @no-named-arguments
 */
class RequirementsOverview extends \Ease\Html\DivTag
{
    public function __construct(array $requirementsRaw, $properties = [])
    {
        parent::__construct(_('Requirements'), $properties);

        foreach ($requirementsRaw as $req) {
            $reqCard = null;

            // First try to use the CredentialType class approach
            $formClass = '\\MultiFlexi\\CredentialType\\'.$req;

            try {
                if (class_exists($formClass, true)) {
                    $instance = new $formClass();
                    $reqCard = new \Ease\TWB4\Card(null, ['style' => 'width: 18rem;']);
                    $reqCard->addItem(new \Ease\Html\ImgTag('images/'.$formClass::logo(), $req, ['title' => $req, 'height' => 40, 'class' => 'card-img-top']));
                    $reqCard->addItem(new \Ease\Html\DivTag(new \Ease\Html\H5Tag($formClass::name()), ['class' => 'card-title']));
                    $reqCard->addItem(new \Ease\Html\DivTag(new \Ease\Html\PTag($formClass::description()), ['class' => 'card-text']));
                }
            } catch (\Throwable $e) {
                // Class-based approach failed, try database approach
                $reqCard = null;
            }

            // If class approach failed, try database approach
            if ($reqCard === null) {
                $credentialType = new \MultiFlexi\CredentialType();
                $credentialType->loadFromSQL(['name' => $req]);

                if ($credentialType->getMyKey()) {
                    // CredentialType found in database - use its data
                    $logo = $credentialType->getDataValue('logo') ?: 'images/default-credential.svg';
                    $name = $credentialType->getDataValue('name') ?: $req;
                    $description = $credentialType->getDataValue('description') ?: '';

                    $reqCard = new \Ease\TWB4\Card();
                    $reqCard->addItem(new \Ease\Html\ImgTag($logo, $name, ['title' => $description ?: $name, 'height' => 40, 'class' => 'card-img-top']));
                    $reqCard->addItem(new \Ease\Html\DivTag(new \Ease\Html\H5Tag($name), ['class' => 'card-body']));
                } else {
                    // Final fallback for unknown requirements
                    $reqCard = new \Ease\TWB4\Card();
                    $reqCard->addItem(new \Ease\Html\DivTag(new \Ease\Html\H5Tag($req), ['class' => 'card-body']));
                    $reqCard->addItem(new \Ease\Html\SmallTag(_('Credential Type'), ['class' => 'text-muted']));
                }
            }

            $this->addItem($reqCard);
        }

        $this->addTagClass('card-group');
    }
}
