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
 * Select widget for choosing an EventSource.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class EventSourceSelect extends \Ease\Html\SelectTag
{
    /**
     * @param string     $name         HTML form field name
     * @param int|null   $defaultValue Currently selected event source ID
     * @param array      $properties   Additional HTML properties
     */
    public function __construct(string $name, ?int $defaultValue = null, array $properties = [])
    {
        $sourcer = new \MultiFlexi\EventSource();

        $sources['0'] = _('Please Select Event Source');

        foreach ($sourcer->listingQuery() as $source) {
            $sources[(string) $source['id']] = empty($source['name']) ? (string) ($source['id']) : $source['name'];
        }

        parent::__construct($name, $sources, (string) $defaultValue, $properties);
    }
}
