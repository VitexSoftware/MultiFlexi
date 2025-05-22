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
 * Page Bottom.
 *
 * @author     Vitex <vitex@hippy.cz>
 */
class PageBottom extends \Ease\Html\FooterTag
{
    public string $apiLink;

    public function __construct($apiLink = '', $properties = [])
    {
        parent::__construct('', $properties);
        $this->apiLink = $apiLink;
    }

    /**
     * Displays the bottom of the page.
     */
    public function finalize(): void
    {
        $this->includeCSS('https://use.fontawesome.com/releases/v5.3.1/css/all.css');
        $container = $this->setTagID('footer');
        //        if (\Ease\Shared::user()->getUserID()) {
        //        $this->addItem(new \Ease\Html\WidgetsBrowsingHistory());
        //        }
        $this->addItem('<hr>');
        $footrow = new \Ease\TWB5\Row();

        $author = '<strong><a href="https://multiflexi.eu/">MultiFlexi</a></strong> '.\Ease\Shared::appVersion().'<br>&nbsp;&nbsp; &copy; 2020-2025 <a href="https://vitexsoftware.com/">Vitex Software</a>';

        $footrow->addColumn(6, [$author]);

        $github = new \Ease\Html\ATag('https://github.com/VitexSoftware/MultiFlexi/', new \Ease\Html\ImgTag('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48cGF0aCBkPSJNMTIgMGMtNi42MjYgMC0xMiA1LjM3My0xMiAxMiAwIDUuMzAyIDMuNDM4IDkuOCA4LjIwNyAxMS4zODcuNTk5LjExMS43OTMtLjI2MS43OTMtLjU3N3YtMi4yMzRjLTMuMzM4LjcyNi00LjAzMy0xLjQxNi00LjAzMy0xLjQxNi0uNTQ2LTEuMzg3LTEuMzMzLTEuNzU2LTEuMzMzLTEuNzU2LTEuMDg5LS43NDUuMDgzLS43MjkuMDgzLS43MjkgMS4yMDUuMDg0IDEuODM5IDEuMjM3IDEuODM5IDEuMjM3IDEuMDcgMS44MzQgMi44MDcgMS4zMDQgMy40OTIuOTk3LjEwNy0uNzc1LjQxOC0xLjMwNS43NjItMS42MDQtMi42NjUtLjMwNS01LjQ2Ny0xLjMzNC01LjQ2Ny01LjkzMSAwLTEuMzExLjQ2OS0yLjM4MSAxLjIzNi0zLjIyMS0uMTI0LS4zMDMtLjUzNS0xLjUyNC4xMTctMy4xNzYgMCAwIDEuMDA4LS4zMjIgMy4zMDEgMS4yMy45NTctLjI2NiAxLjk4My0uMzk5IDMuMDAzLS40MDQgMS4wMi4wMDUgMi4wNDcuMTM4IDMuMDA2LjQwNCAyLjI5MS0xLjU1MiAzLjI5Ny0xLjIzIDMuMjk3LTEuMjMuNjUzIDEuNjUzLjI0MiAyLjg3NC4xMTggMy4xNzYuNzcuODQgMS4yMzUgMS45MTEgMS4yMzUgMy4yMjEgMCA0LjYwOS0yLjgwNyA1LjYyNC01LjQ3OSA1LjkyMS40My4zNzIuODIzIDEuMTAyLjgyMyAyLjIyMnYzLjI5M2MwIC4zMTkuMTkyLjY5NC44MDEuNTc2IDQuNzY1LTEuNTg5IDguMTk5LTYuMDg2IDguMTk5LTExLjM4NiAwLTYuNjI3LTUuMzczLTEyLTEyLTEyeiIvPjwvc3ZnPg==', 'Github', ['title' => _('Github Project')]));
        $linkedIn = new \Ease\Html\ATag('https://www.linkedin.com/in/vitexsoftware/', new \Ease\Html\ImgTag('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyBoZWlnaHQ9IjcyIiB2aWV3Qm94PSIwIDAgNzIgNzIiIHdpZHRoPSI3MiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik04LDcyIEw2NCw3MiBDNjguNDE4Mjc4LDcyIDcyLDY4LjQxODI3OCA3Miw2NCBMNzIsOCBDNzIsMy41ODE3MjIgNjguNDE4Mjc4LC04LjExNjI0NTAxZS0xNiA2NCwwIEw4LDAgQzMuNTgxNzIyLDguMTE2MjQ1MDFlLTE2IC01LjQxMDgzMDAxZS0xNiwzLjU4MTcyMiAwLDggTDAsNjQgQzUuNDEwODMwMDFlLTE2LDY4LjQxODI3OCAzLjU4MTcyMiw3MiA4LDcyIFoiIGZpbGw9IiMwMDdFQkIiLz48cGF0aCBkPSJNNjIsNjIgTDUxLjMxNTYyNSw2MiBMNTEuMzE1NjI1LDQzLjgwMjExNDkgQzUxLjMxNTYyNSwzOC44MTI3NTQyIDQ5LjQxOTc5MTcsMzYuMDI0NTMyMyA0NS40NzA3MDMxLDM2LjAyNDUzMjMgQzQxLjE3NDYwOTQsMzYuMDI0NTMyMyAzOC45MzAwNzgxLDM4LjkyNjExMDMgMzguOTMwMDc4MSw0My44MDIxMTQ5IEwzOC45MzAwNzgxLDYyIEwyOC42MzMzMzMzLDYyIEwyOC42MzMzMzMzLDI3LjMzMzMzMzMgTDM4LjkzMDA3ODEsMjcuMzMzMzMzMyBMMzguOTMwMDc4MSwzMi4wMDI5MjgzIEMzOC45MzAwNzgxLDMyLjAwMjkyODMgNDIuMDI2MDQxNywyNi4yNzQyMTUxIDQ5LjM4MjU1MjEsMjYuMjc0MjE1MSBDNTYuNzM1Njc3MSwyNi4yNzQyMTUxIDYyLDMwLjc2NDQ3MDUgNjIsNDAuMDUxMjEyIEw2Miw2MiBaIE0xNi4zNDkzNDksMjIuNzk0MDEzMyBDMTIuODQyMDU3MywyMi43OTQwMTMzIDEwLDE5LjkyOTY1NjcgMTAsMTYuMzk3MDA2NyBDMTAsMTIuODY0MzU2NiAxMi44NDIwNTczLDEwIDE2LjM0OTM0OSwxMCBDMTkuODU2NjQwNiwxMCAyMi42OTcwMDUyLDEyLjg2NDM1NjYgMjIuNjk3MDA1MiwxNi4zOTcwMDY3IEMyMi42OTcwMDUyLDE5LjkyOTY1NjcgMTkuODU2NjQwNiwyMi43OTQwMTMzIDE2LjM0OTM0OSwyMi43OTQwMTMzIFogTTExLjAzMjU1MjEsNjIgTDIxLjc2OTQwMSw2MiBMMjEuNzY5NDAxLDI3LjMzMzMzMzMgTDExLjAzMjU1MjEsMjcuMzMzMzMzMyBMMTEuMDMyNTUyMSw2MiBaIiBmaWxsPSIjRkZGIi8+PC9nPjwvc3ZnPg==', 'LinkedIN', ['height' => '25px', 'style' => 'margin: 20x', 'title' => _('LinkedIN Product page')]));

        $footrow->addColumn(2, [$github, '&nbsp;', $linkedIn]);

        $lnks = $footrow->addColumn(4, new \Ease\Html\ATag('https://multiflexi.readthedocs.io', _('Docs')));

        if ($this->apiLink) {
            $lnks->addItem($this->apiLinks());
        }

        $this->addItem(new \Ease\TWB5\Container($footrow));
        parent::finalize();
    }

    public function apiLinks()
    {
        foreach (['html', 'json', 'xml', 'yml', 'csv'] as $format) {
            $links[] = new \Ease\Html\ATag('api/VitexSoftware/MultiFlexi/1.0.0/'.$this->apiLink.'.'.$format, $format);
        }

        return '&nbsp;API ['.implode(',', $links).']';
    }
}
