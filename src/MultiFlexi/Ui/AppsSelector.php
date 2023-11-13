<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of AppSelector
 *
 * @author vitex
 */
class AppsSelector extends \Ease\Html\InputTextTag
{
    use \Ease\ui\Selectizer;

    public function __construct($identifier = null, $enabled = [])
    {
        parent::__construct($identifier, $enabled);

        $apper = new \MultiFlexi\Application();

        $properties = [
            'valueField' => 'id',
            'labelField' => 'name',
            'searchField' => ['name', 'description', 'homepage']
        ];

        $values = $apper->listingQuery()->select(['id','name','description','homepage','image'], true);

        $properties['render']['item'] = 'function (item, escape) { return "<div><img height=40 align=left src=\"" + escape(item.image) + "\">" + escape(item.name) + "&nbsp;</div>" }';
        $properties['render']['option'] = 'function (item, escape) { return "<div><img height=40 align=right src=\"" + escape(item.image) + "\">" + escape(item.name) + "<br><small>" + escape(item.description) + "</small></div>" }';
        $properties['plugins'] = ['remove_button'];

        $this->selectize($properties, $values->fetchAll());
    }
}
