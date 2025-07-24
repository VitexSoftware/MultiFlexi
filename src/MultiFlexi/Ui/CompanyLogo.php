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
 * Description of CompanyLogo.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class CompanyLogo extends \Ease\Html\ImgTag
{
    /**
     * SVG Question Mark.
     */
    public static string $none = <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<svg version="1.1" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
<metadata>
<rdf:RDF>
<cc:Work rdf:about="">
<dc:format>image/svg+xml</dc:format>
<dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage"/>
<dc:title/>
</cc:Work>
</rdf:RDF>
</metadata>
<g transform="matrix(.05525 0 0 .046036 7.7366 7.9085)">
<path d="m-127.47-157.46v324.27h270.93v-241.3l-82.965-82.965z" fill="#fff" stroke="#636363" stroke-width="6.4"/>
<g transform="matrix(1.0667 0 0 1.0667 -388.86 -537.74)" stroke-linecap="round" stroke-linejoin="round">
<rect x="307.06" y="451.19" width="130" height="150" fill="#fff" stroke="#a6a6a6" stroke-width="6"/>
<path d="m343.53 497.65 57.065 57.065" fill="none" stroke="#e00000" stroke-width="12"/>
<path d="m400.6 497.65-57.065 57.065" fill="none" stroke="#e00000" stroke-width="12"/>
</g>
<path d="m143.47-74.496h-82.967v-82.967" fill="none" stroke="#636363" stroke-linejoin="round" stroke-width="6.4"/>
</g>
</svg>

EOD;

    /**
     * Emebed Company logo into page.
     *
     * @param \MultiFlexi\Company   $company       Object
     * @param array<string, string> $tagProperties Additional tag properties
     */
    public function __construct(\MultiFlexi\Company $company, array $tagProperties = [])
    {
        if ($company->getDataValue('logo')) {
            parent::__construct($company->getDataValue('logo'), $company->getDataValue('name'), $tagProperties);
        } else {
            parent::__construct('data:image/svg+xml;base64,'.base64_encode(self::$none), _('none'), $tagProperties);
        }
    }
}
