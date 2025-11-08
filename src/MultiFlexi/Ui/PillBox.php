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
 * Description of GroupChooser.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class PillBox extends \Ease\Html\InputTextTag
{
    public function __construct(
        $name,
        $valuesAvailble,
        $valuesShown,
        $properties = [],
    ) {
        parent::__construct(
            $name,
            \is_array($valuesShown) ? implode(',', $valuesShown) : $valuesShown,
            $properties,
        );
        $this->setTagID($name.'pillBox');

        $this->addJavaScript(<<<'EOD'

$(document).ready(function() {
    setTimeout(function() {
        if (typeof $.fn.selectize !== 'undefined') {
            $('#
EOD.$this->getTagID().<<<'EOD'
').selectize({
                plugins: ['remove_button'],
                valueField: 'id',
                maxOptions: 10000,
                labelField: 'name',
                searchField: 'name',
                persist: true,
                create: false,
                delimiter: ',',
                options:
EOD.json_encode($valuesAvailble).<<<'EOD'
,
                items:
EOD.json_encode(\is_array($valuesShown) ? $valuesShown : explode(',', (string) $valuesShown)).<<<'EOD'

            });
        } else {
            console.error('Selectize not available for
EOD.$this->getTagID().<<<'EOD'
');
        }
    }, 100);
});

EOD);
    }
}
