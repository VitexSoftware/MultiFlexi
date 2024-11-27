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

namespace MultiFlexi\Ui\Form;

/**
 * Description of AbraFlexi.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class AbraFlexi extends \Ease\TWB4\Panel implements configForm
{
    public static string $logo = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTMwIiBoZWlnaHQ9IjI5LjgxMiIgdmlld0JveD0iMCAwIDEzMCAyOS44MTIiPjxkZWZzPjxwYXRoIGlkPSJhIiBkPSJNMTcuMiAwbDE3LjAyNSAyOS44MTJoMTcuMkwzNC4zOTkgMHoiLz48L2RlZnM+PGNsaXBQYXRoIGlkPSJiIj48dXNlIHhsaW5rOmhyZWY9IiNhIiBvdmVyZmxvdz0idmlzaWJsZSIvPjwvY2xpcFBhdGg+PGxpbmVhckdyYWRpZW50IGlkPSJjIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii02MC4wNjgiIHkxPSI0NC4xMzgiIHgyPSItNTkuNTQ5IiB5Mj0iNDQuMTM4IiBncmFkaWVudFRyYW5zZm9ybT0icm90YXRlKC05MCAtMjk2Mi4zOTcgLTQ2MC4wNDUpIHNjYWxlKDU3LjQ3MikiPjxzdG9wIG9mZnNldD0iMCIgc3RvcC1jb2xvcj0iI2Y2YjExYSIvPjxzdG9wIG9mZnNldD0iLjEwNSIgc3RvcC1jb2xvcj0iI2Y2YWYxZiIvPjxzdG9wIG9mZnNldD0iLjIxMyIgc3RvcC1jb2xvcj0iI2Y1YTkyZCIvPjxzdG9wIG9mZnNldD0iLjMyMyIgc3RvcC1jb2xvcj0iI2YzOWYzZiIvPjxzdG9wIG9mZnNldD0iLjQzNSIgc3RvcC1jb2xvcj0iI2YwOTE1NCIvPjxzdG9wIG9mZnNldD0iLjU0NyIgc3RvcC1jb2xvcj0iI2VlODA2NiIvPjxzdG9wIG9mZnNldD0iLjY2IiBzdG9wLWNvbG9yPSIjZWE2ODc1Ii8+PHN0b3Agb2Zmc2V0PSIuNzcyIiBzdG9wLWNvbG9yPSIjZTc0MjgxIi8+PHN0b3Agb2Zmc2V0PSIuODUiIHN0b3AtY29sb3I9IiNlNTA0ODciLz48c3RvcCBvZmZzZXQ9IjEiIHN0b3AtY29sb3I9IiNlNTA0ODciLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGNsaXAtcGF0aD0idXJsKCNiKSIgZmlsbD0idXJsKCNjKSIgZD0iTTE3LjIgMGgzNC4yMjZ2MjkuODEySDE3LjJ6Ii8+PHBhdGggZmlsbD0iIzc3Nzg3QiIgZD0iTTE3LjIgMEwwIDI5LjgxMWgxNy4yTDM0LjQwOSAwem00NS4wODQgNC41MDdoNC4wNzZsNy40MTggMjAuNzk5aC00LjQzNGwtMS41MzUtNC40OWgtNy4xMjFsLTEuNTA4IDQuNDloLTQuMzEybDcuNDE2LTIwLjc5OXptNC4yMjUgMTIuNTI3bC0yLjI0Ni02LjczNy0yLjI3NCA2LjczN2g0LjUyeiIvPjxkZWZzPjxwYXRoIGlkPSJkIiBkPSJNMCAwaDEzMHYyOS44MTJIMHoiLz48L2RlZnM+PGNsaXBQYXRoIGlkPSJlIj48dXNlIHhsaW5rOmhyZWY9IiNkIiBvdmVyZmxvdz0idmlzaWJsZSIvPjwvY2xpcFBhdGg+PHBhdGggY2xpcC1wYXRoPSJ1cmwoI2UpIiBmaWxsPSIjNzc3ODdCIiBkPSJNNzUuNjY2IDQuNjIzaDcuNjgzYzIuMzAzIDAgNC4yMjUuNjIzIDUuNDA0IDEuODA1LjkxNy45NDYgMS4zOSAyLjA2OCAxLjM5IDMuNTE2di4wODhjMCAyLjQyNC0xLjMzMSAzLjcyMS0yLjgwNiA0LjUyMSAyLjIxNC44NTYgMy42OSAyLjIxNSAzLjY5IDQuOTMydi4xNDhjMCAzLjcyMi0yLjk4MyA1LjY3My03LjQ3MiA1LjY3M2gtNy44OVY0LjYyM3ptMTAuMjUgNi4wODdjMC0xLjUwNS0xLjA2My0yLjMwNS0yLjg5NS0yLjMwNWgtMy4xODh2NC42N2gyLjk4MWMxLjg5MiAwIDMuMTAyLS43NjggMy4xMDItMi4zMzN2LS4wMzJ6bS0yLjMzNSA1Ljk2OGgtMy43NDh2NC44NDVoMy44MzhjMS45MjMgMCAzLjEzMi0uODI1IDMuMTMyLTIuNDJ2LS4wMjljLS4wMDEtMS40OC0xLjA5NC0yLjM5Ni0zLjIyMi0yLjM5Nk05NC4wOTYgNC42MjNoNy43NzNjMi4zOSAwIDQuMzEzLjY4MSA1LjYxMiAxLjk4MSAxLjEyNCAxLjEyMyAxLjc0MiAyLjc0NyAxLjc0MiA0Ljc4N3YuMTE4YzAgMy4xMjktMS41NjggNS4xMTItMy44NDIgNi4xMTZsNC40MDUgNy42ODJoLTQuOTA3bC0zLjgxLTYuNzk2aC0yLjY4OHY2Ljc5NmgtNC4yODZWNC42MjN6bTcuNDQ3IDEwLjEwNmMyLjE4NSAwIDMuMzY2LTEuMjQxIDMuMzY2LTMuMTAydi0uMDI5YzAtMi4wNjgtMS4yNzEtMy4xMDItMy40NTYtMy4xMDJoLTMuMDcxdjYuMjMyaDMuMTYxem0xNi45NjUtMTAuMjIyaDQuMDc1TDEzMCAyNS4zMDZoLTQuNDMybC0xLjUzNC00LjQ5aC03LjEyMmwtMS41MDcgNC40OWgtNC4zMTRsNy40MTctMjAuNzk5em00LjIyMyAxMi41MjdsLTIuMjQzLTYuNzM3LTIuMjc2IDYuNzM3aDQuNTE5eiIvPjwvc3ZnPg==';

    
    public function __construct()
    {
        $header = new \Ease\TWB4\Row();
        $header->addColumn(6, new \Ease\Html\ATag('https://www.abra.eu/flexi/', new \Ease\Html\ImgTag(self::$logo, _('AbraFlexi'), ['height' => 50])));
        $header->addColumn(6, new \Ease\Html\H3Tag(_('AbraFlexi')));

        $body = new \Ease\Html\DivTag();

        $body->addItem(new \Ease\TWB4\FormGroup(_('Login'), new \Ease\Html\InputTextTag('ABRAFLEXI_LOGIN'), 'winstrom', _('AbraFlexi user login')));
        $body->addItem(new \Ease\TWB4\FormGroup(_('Password'), new \Ease\Html\InputTextTag('ABRAFLEXI_PASSWORD'), 'winstrom', _('AbraFlexi user password')));
        $body->addItem(new \Ease\TWB4\FormGroup(_('Server URL'), new \Ease\Html\InputTextTag('ABRAFLEXI_URL'), 'winstrom', _('AbraFlexi server URI')));
        $body->addItem(new \Ease\TWB4\FormGroup(_('Company'), new \Ease\Html\InputTextTag('ABRAFLEXI_COMPANY'), 'demo', _('Company to be handled')));

        parent::__construct($header, 'inverse', $body, '');
    }

    public static function name(): string {
        return _('AbraFlexi');
    }
    
    #[\Override]
    public static function fields(): array
    {
        return [
            'ABRAFLEXI_COMPANY' => [
                'type' => 'string',
                'description' => '',
                'defval' => 'demo_de',
                'required' => false,
            ],
            'ABRAFLEXI_LOGIN' => [
                'type' => 'string',
                'description' => _('AbraFlexi Login'),
                'defval' => 'winstrom',
                'required' => false,
            ],
            'ABRAFLEXI_PASSWORD' => [
                'type' => 'string',
                'description' => _('AbraFlexi password'),
                'defval' => 'winstrom',
                'required' => false,
            ],
            'ABRAFLEXI_URL' => [
                'type' => 'string',
                'description' => _('AbraFlexi Server URI'),
                'defval' => 'https://demo.flexibee.eu:5434',
                'required' => false,
            ],
        ];
    }
}
