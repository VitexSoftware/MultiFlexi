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
 * Description of RedmineIssue.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class RedmineIssue extends \MultiFlexi\CommonAction
{
    /**
     * Module Caption.
     *
     * @return string
     */
    public static function name()
    {
        return _('Redmine Issue');
    }

    /**
     * Module Description.
     *
     * @return string
     */
    public static function description()
    {
        return _('Make Redine issue using Job output');
    }

    public static function logo()
    {
        return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIxLjEuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgMTYgMTYiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDE2IDE2OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+Cgkuc3Qwe2ZpbGw6I0ZGMDAwMDt9Cjwvc3R5bGU+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xLjksMTAuMWwyLjUsMC42YzAsMC0wLjIsMS0wLjIsMS40YzAsMC40LDAsMC44LDAsMC44bC0yLjgsMGMwLDAsMC4xLTAuOCwwLjItMS41QzEuNiwxMC44LDEuOSwxMC4xLDEuOSwxMC4xICB6Ii8+Cjxwb2x5Z29uIGNsYXNzPSJzdDAiIHBvaW50cz0iMi4yLDkuMSA0LjQsMTAgNC45LDguMSAyLjksNy4xICIvPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMy42LDYuMmwxLjcsMS4zYzAsMCwwLjUtMC43LDAuNy0wLjhjMC4yLTAuMiwwLjYtMC41LDAuNi0wLjVMNS4yLDQuNmMwLDAtMC43LDAuNS0wLjksMC43UzMuNiw2LjIsMy42LDYuMnoiLz4KPHBhdGggY2xhc3M9InN0MCIgZD0iTTIuNywzLjYiLz4KPHBhdGggY2xhc3M9InN0MCIgZD0iTTYuNCw0LjFjMCwwLDEuMS0wLjQsMS44LTAuM2MwLjgsMCwxLjYsMC40LDEuNiwwLjRMOSw1LjlsLTEuOCwwTDYuNCw0LjF6Ii8+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xMC45LDQuN0w5LjYsNi4yYzAsMCwwLjUsMC40LDAuNywwLjZjMC4yLDAuMiwwLjUsMC43LDAuNSwwLjdsMS43LTEuM2MwLDAtMC42LTAuOC0wLjktMSAgQzExLjQsNC45LDEwLjksNC43LDEwLjksNC43eiIvPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMTEuMiw4LjFsMC42LDJMMTQsOWMwLDAtMC4yLTAuNy0wLjQtMS4xYy0wLjItMC40LTAuNC0wLjgtMC40LTAuOEwxMS4yLDguMXoiLz4KPHBhdGggY2xhc3M9InN0MCIgZD0iTTExLjksMTAuN2wwLjEsMi4yaDIuOGMwLDAtMC4xLTAuOS0wLjItMS41cy0wLjMtMS4zLTAuMy0xLjNMMTEuOSwxMC43eiIvPgo8L3N2Zz4=';
    }

    /**
     * Is this Action Situable for Application.
     *
     * @param Application $app
     */
    public static function usableForApp($app): bool
    {
        return \is_object($app);
    }

    /**
     * Perform Action - create Redmine issue using Job output.
     */
    public function perform(\MultiFlexi\Job $job): void
    {
        $token = $this->getDataValue('token');
        $redmineUrl = rtrim($this->getDataValue('url'), '/'); // e.g. https://redmine.example.com
        $projectId = $this->getDataValue('project_id'); // Redmine project identifier

        $title = $this->runtemplate->application->getRecordName().' problem';
        $body = 'JOB ID: '.$job->getMyKey()."\n\n";
        $body .= 'Command: '.$job->getDataValue('command')."\n\n";
        $body .= 'ExitCode: '.$job->getDataValue('exitcode')."\n\n";
        $body .= "\nStdout:\n```\n".stripslashes($job->getDataValue('stdout'))."\n```";
        $body .= "\nSterr:\n```\n".stripslashes($job->getDataValue('stderr'))."\n```\n\n";
        $body .= 'MultiFlexi: '.\Ease\Shared::appName().' '.\Ease\Shared::appVersion()."\n\n";

        $data = [
            'issue' => [
                'project_id' => $projectId,
                'subject' => $title,
                'description' => $body,
                'tracker_id' => 1, // 1 = Bug, adjust as needed
            ],
        ];
        $data_string = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, \CURLOPT_URL, $redmineUrl.'/issues.json');
        curl_setopt($ch, \CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Redmine-API-Key: '.$token,
        ]);
        curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, \CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_VERBOSE, (bool) \Ease\Shared::cfg('API_DEBUG', false));

        $response = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);
        $curlInfo['when'] = microtime();

        $success = ($curlInfo['http_code'] >= 200 && $curlInfo['http_code'] < 300);
        $this->addStatusMessage($response, $success ? 'success' : 'error');
        curl_close($ch);
    }
}
