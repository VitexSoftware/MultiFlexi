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

namespace MultiFlexi\Action;

/**
 * Description of Reschedule.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class WebHook extends \MultiFlexi\CommonAction
{
    /**
     * Module Caption.
     *
     * @return string
     */
    public static function name()
    {
        return _('WebHook');
    }

    /**
     * Module Description.
     *
     * @return string
     */
    public static function description()
    {
        return _('Post Job output to URI');
    }

    public static function logo()
    {
        return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjxzdmcKICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIgogICB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c29kaXBvZGk9Imh0dHA6Ly9zb2RpcG9kaS5zb3VyY2Vmb3JnZS5uZXQvRFREL3NvZGlwb2RpLTAuZHRkIgogICB4bWxuczppbmtzY2FwZT0iaHR0cDovL3d3dy5pbmtzY2FwZS5vcmcvbmFtZXNwYWNlcy9pbmtzY2FwZSIKICAgdmlld0JveD0iMCAwIDE3MCAxNzAiCiAgIHZlcnNpb249IjEuMSIKICAgaWQ9InN2ZzEwIgogICBzb2RpcG9kaTpkb2NuYW1lPSJrYW5jYS5zdmciCiAgIGlua3NjYXBlOnZlcnNpb249IjAuOTIuMiAoNWMzZTgwZCwgMjAxNy0wOC0wNikiPgogIDxtZXRhZGF0YQogICAgIGlkPSJtZXRhZGF0YTE2Ij4KICAgIDxyZGY6UkRGPgogICAgICA8Y2M6V29yawogICAgICAgICByZGY6YWJvdXQ9IiI+CiAgICAgICAgPGRjOmZvcm1hdD5pbWFnZS9zdmcreG1sPC9kYzpmb3JtYXQ+CiAgICAgICAgPGRjOnR5cGUKICAgICAgICAgICByZGY6cmVzb3VyY2U9Imh0dHA6Ly9wdXJsLm9yZy9kYy9kY21pdHlwZS9TdGlsbEltYWdlIiAvPgogICAgICAgIDxkYzp0aXRsZT48L2RjOnRpdGxlPgogICAgICA8L2NjOldvcms+CiAgICA8L3JkZjpSREY+CiAgPC9tZXRhZGF0YT4KICA8ZGVmcwogICAgIGlkPSJkZWZzMTQiIC8+CiAgPHNvZGlwb2RpOm5hbWVkdmlldwogICAgIHBhZ2Vjb2xvcj0iI2ZmZmZmZiIKICAgICBib3JkZXJjb2xvcj0iIzY2NjY2NiIKICAgICBib3JkZXJvcGFjaXR5PSIxIgogICAgIG9iamVjdHRvbGVyYW5jZT0iMTAiCiAgICAgZ3JpZHRvbGVyYW5jZT0iMTAiCiAgICAgZ3VpZGV0b2xlcmFuY2U9IjEwIgogICAgIGlua3NjYXBlOnBhZ2VvcGFjaXR5PSIwIgogICAgIGlua3NjYXBlOnBhZ2VzaGFkb3c9IjIiCiAgICAgaW5rc2NhcGU6d2luZG93LXdpZHRoPSIxNDc0IgogICAgIGlua3NjYXBlOndpbmRvdy1oZWlnaHQ9Ijk0NyIKICAgICBpZD0ibmFtZWR2aWV3MTIiCiAgICAgc2hvd2dyaWQ9ImZhbHNlIgogICAgIGlua3NjYXBlOnpvb209IjUuNTUyOTQxMiIKICAgICBpbmtzY2FwZTpjeD0iOTEuNjYxMjI0IgogICAgIGlua3NjYXBlOmN5PSI4MC44NDIzNDUiCiAgICAgaW5rc2NhcGU6d2luZG93LXg9IjUwNiIKICAgICBpbmtzY2FwZTp3aW5kb3cteT0iMjI4IgogICAgIGlua3NjYXBlOndpbmRvdy1tYXhpbWl6ZWQ9IjAiCiAgICAgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0iZzgiIC8+CiAgPGcKICAgICB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIC04ODIuMzYpIgogICAgIGlkPSJnOCIKICAgICBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgPHBhdGgKICAgICAgIHNvZGlwb2RpOnR5cGU9InN0YXIiCiAgICAgICBzdHlsZT0iZmlsbDojZjlkMTAwO2ZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTojMDAwMDAwO3N0cm9rZS13aWR0aDo3LjU1OTA1NTMzO3N0cm9rZS1saW5lY2FwOnJvdW5kO3N0cm9rZS1saW5lam9pbjpyb3VuZDtzdHJva2UtbWl0ZXJsaW1pdDo0O3N0cm9rZS1kYXNoYXJyYXk6bm9uZTtzdHJva2UtZGFzaG9mZnNldDo2O3N0cm9rZS1vcGFjaXR5OjEiCiAgICAgICBpZD0icGF0aDgyMyIKICAgICAgIHNvZGlwb2RpOnNpZGVzPSIzIgogICAgICAgc29kaXBvZGk6Y3g9Ijg1LjAwMDAwMSIKICAgICAgIHNvZGlwb2RpOmN5PSI5ODcuMzU2MTQiCiAgICAgICBzb2RpcG9kaTpyMT0iNzkuOTg1Mjk4IgogICAgICAgc29kaXBvZGk6cjI9IjM5Ljk5MjY0OSIKICAgICAgIHNvZGlwb2RpOmFyZzE9Ii0xLjU3MDc5NjMiCiAgICAgICBzb2RpcG9kaTphcmcyPSItMC41MjM1OTg3OCIKICAgICAgIGlua3NjYXBlOmZsYXRzaWRlZD0iZmFsc2UiCiAgICAgICBpbmtzY2FwZTpyb3VuZGVkPSIwIgogICAgICAgaW5rc2NhcGU6cmFuZG9taXplZD0iMCIKICAgICAgIGQ9Im0gODUuMDAwMDAyLDkwNy4zNzA4NCAzNC42MzQ2NDgsNTkuOTg4OTggMzQuNjM0NjUsNTkuOTg4OTggLTY5LjI2OTMsMCAtNjkuMjY5MzAxLDAgMzQuNjM0NjUxLC01OS45ODg5OCB6IgogICAgICAgaW5rc2NhcGU6dHJhbnNmb3JtLWNlbnRlci15PSItMTkuOTk2MzI1IiAvPgogICAgPHBhdGgKICAgICAgIHN0eWxlPSJmaWxsOiMwMDAwMDA7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmU7c3Ryb2tlLXdpZHRoOjYuNjIzNjcwMTtzdHJva2UtbGluZWNhcDpyb3VuZDtzdHJva2UtbGluZWpvaW46cm91bmQ7c3Ryb2tlLW1pdGVybGltaXQ6NDtzdHJva2UtZGFzaGFycmF5Om5vbmU7c3Ryb2tlLWRhc2hvZmZzZXQ6NjtzdHJva2Utb3BhY2l0eToxIgogICAgICAgZD0ibSA3MS40Njk0NzgsOTQ1LjAxNjI1IHYgMTIuNjI1IGggOC44MDI3MzUgYyAwLjA4MTUsNC40MDczMiAwLjQ1Mzk4NiwxMS4xNDEzMiAyLjAwMTk1MywxNC4xMzQ3NyAyLjQxMTQzOSw0LjY2MzIxIDYuMDUxNDc4LDUuODM3MyAxMC40MzE2NCw4LjI5ODAyIDQuMzgwMTU2LDIuNDYwNzIgNi43OTUxOTYsOC4yNjg0OCA2LjMwMjczNiwxMy45MjA3MyAtMC45MzU4NTMsOS45ODQ1MyAtNi4xNTk4NDIsMTEuNjk2NTMgLTEzLjg2NDg1LDExLjM3NzczIC0xMy44NjY5NzQsLTAuNDk1OCAtMTYuNTk3MTkxLC0xNy43MTQ5NSAtMTIuNzkxNDAxLC0yOS4xMzM1OSAwLDAgLTcuNDIyMDgyLDAuMTQ4MTggLTguNzYzNjcyLDYuMjA3MDMgLTAuOTI3ODM2LDQuMTkwMjYgLTIuNDI0MzUzLDkuOTcwNzYgMC4wMDIsMTguMDcwMzYgMi43NDQxODQsOS4xNjA4IDEwLjc1ODQwMSwxNC4zMjU5IDE5LjgyNjE3MiwxNS4yNDQxIDkuMDY3NzcxLDAuOTE4MyAxOS44ODg1MDksLTIuMjAzOCAyNS40ODA0NjksLTEwLjE1MjMgMi4yNTkzMSwtMy4yMTE1IDMuMzQwOTYsLTcuMTE5NDEgMy43MDcwMywtMTAuOTQxNDYgMC4zNjYwNiwtMy44MjIxMyAwLjA0MDgsLTcuNTUxMTkgLTEuMjUsLTEwLjY5MzM2IC0xLjk2OTQyLC04LjU3NzIzIC0xMy41OTc0MDQsLTExLjMzNDg5IC0xNi43MTk4MjMsLTE2Ljg5ODQ0IC0wLjYzOTg3MiwtMS4yMzczOCAtMC40MzIwMTcsLTcuOTEzMjcgLTAuNDQwMzc5LC05LjQzMzU5IGggMi41MjM0MzcgdiAtMTIuNjI1IHoiCiAgICAgICBpZD0icmVjdDgyNSIKICAgICAgIGlua3NjYXBlOmNvbm5lY3Rvci1jdXJ2YXR1cmU9IjAiCiAgICAgICBzb2RpcG9kaTpub2RldHlwZXM9ImNjY3NzY2Njc2NjY2NjY2NjY2MiIC8+CiAgPC9nPgo8L3N2Zz4K';
    }

    /**
     * Perform Action.
     */
    public function perform(\MultiFlexi\Job $job): void
    {
        $uri = $this->getDataValue('uri');

        if ($uri) {
            $payload = stripslashes($this->runtemplate->getDataValue('stdout'));

            $this->addStatusMessage(_('Perform begin'));
            // $exitCode = $this->job->executor->launch($command);
            $ch = curl_init();
            curl_setopt($ch, \CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);

            // set URL and other appropriate options
            curl_setopt($ch, \CURLOPT_URL, $uri);
            curl_setopt($ch, \CURLOPT_HEADER, 0);

            // grab URL and pass it to the browser
            $this->addStatusMessage((string) curl_exec($ch), 'debug');

            // close cURL resource, and free up system resources
            curl_close($ch);
            $this->addStatusMessage(_('Perform done'));
        }
    }

    /**
     * Is this Action Suitable for Application.
     *
     * @param Application $app
     */
    public static function usableForApp($app): bool
    {
        return true;
    }

    public static function inputs(string $prefix)
    {
        return new \Ease\TWB4\FormGroup(_('Uri'), new \Ease\Html\InputTextTag($prefix.'[WebHook][uri]'), '', _('Report endpoint'));
    }
}
