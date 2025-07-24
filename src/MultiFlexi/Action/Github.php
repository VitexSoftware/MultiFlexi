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

use MultiFlexi\Application;

/**
 * Description of RedmineIssue.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class Github extends \MultiFlexi\CommonAction
{
    /**
     * Module Caption.
     *
     * @return string
     */
    public static function name()
    {
        return _('Github Issue');
    }

    /**
     * Module Description.
     *
     * @return string
     */
    public static function description()
    {
        return _('Make Github issue using Job output');
    }

    public static function logo()
    {
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOTgiIGhlaWdodD0iOTYiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik00OC44NTQgMEMyMS44MzkgMCAwIDIyIDAgNDkuMjE3YzAgMjEuNzU2IDEzLjk5MyA0MC4xNzIgMzMuNDA1IDQ2LjY5IDIuNDI3LjQ5IDMuMzE2LTEuMDU5IDMuMzE2LTIuMzYyIDAtMS4xNDEtLjA4LTUuMDUyLS4wOC05LjEyNy0xMy41OSAyLjkzNC0xNi40Mi01Ljg2Ny0xNi40Mi01Ljg2Ny0yLjE4NC01LjcwNC01LjQyLTcuMTctNS40Mi03LjE3LTQuNDQ4LTMuMDE1LjMyNC0zLjAxNS4zMjQtMy4wMTUgNC45MzQuMzI2IDcuNTIzIDUuMDUyIDcuNTIzIDUuMDUyIDQuMzY3IDcuNDk2IDExLjQwNCA1LjM3OCAxNC4yMzUgNC4wNzQuNDA0LTMuMTc4IDEuNjk5LTUuMzc4IDMuMDc0LTYuNi0xMC44MzktMS4xNDEtMjIuMjQzLTUuMzc4LTIyLjI0My0yNC4yODMgMC01LjM3OCAxLjk0LTkuNzc4IDUuMDE0LTEzLjItLjQ4NS0xLjIyMi0yLjE4NC02LjI3NS40ODYtMTMuMDM4IDAgMCA0LjEyNS0xLjMwNCAxMy40MjYgNS4wNTJhNDYuOTcgNDYuOTcgMCAwIDEgMTIuMjE0LTEuNjNjNC4xMjUgMCA4LjMzLjU3MSAxMi4yMTMgMS42MyA5LjMwMi02LjM1NiAxMy40MjctNS4wNTIgMTMuNDI3LTUuMDUyIDIuNjcgNi43NjMuOTcgMTEuODE2LjQ4NSAxMy4wMzggMy4xNTUgMy40MjIgNS4wMTUgNy44MjIgNS4wMTUgMTMuMiAwIDE4LjkwNS0xMS40MDQgMjMuMDYtMjIuMzI0IDI0LjI4MyAxLjc4IDEuNTQ4IDMuMzE2IDQuNDgxIDMuMzE2IDkuMTI2IDAgNi42LS4wOCAxMS44OTctLjA4IDEzLjUyNiAwIDEuMzA0Ljg5IDIuODUzIDMuMzE2IDIuMzY0IDE5LjQxMi02LjUyIDMzLjQwNS0yNC45MzUgMzMuNDA1LTQ2LjY5MUM5Ny43MDcgMjIgNzUuNzg4IDAgNDguODU0IDB6IiBmaWxsPSIjMjQyOTJmIi8+PC9zdmc+';
    }

    public static function configForm()
    {
        return new \Ease\TWB4\FormGroup(_('GitHub token'), new \Ease\Html\InputTextTag('Github[token]'), 'ghp_iupB8adLxIIBezDWB1BH9HJCAtpcOL2scdmX', new \Ease\Html\ATag('https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens', _('How to obtain Github Token')));
    }

    /**
     * Form Inputs.
     *
     * @return mixed
     */
    public static function inputs(string $action)
    {
        return new \Ease\TWB4\Badge('info', _('No Fields required').' ('.$action.')');
    }

    /**
     * Is this Action Situable for Application.
     */
    public static function usableForApp(Application $app): bool
    {
        return (null === strstr($app->getDataValue('homepage'), 'github.com')) === false;
    }

    /**
     * Perform Action.
     */
    public function perform(\MultiFlexi\Job $job): void
    {
        $token = $this->getDataValue('token');
        $headerValue = ' Bearer '.$token;
        $header = [
            'Authorization:'.$headerValue,
            'Accept: application/vnd.github+json',
            'X-GitHub-Api-Version: 2022-11-28',
        ];
        $title = $this->runtemplate->application->getRecordName().' problem';
        $body = 'JOB ID: '.$job->getMyKey()."\n\n";

        $body .= 'Command: '.$job->getDataValue('command')."\n\n";
        $body .= 'ExitCode: '.$job->getDataValue('exitcode')."\n\n";

        $body .= "\nStdout:\n```\n".stripslashes($job->getDataValue('stdout'))."\n```";
        $body .= "\nSterr:\n```\n".stripslashes($job->getDataValue('stderr'))."\n```\n\n";

        $body .= 'MultiFlexi: '.\Ease\Shared::appName().' '.\Ease\Shared::appVersion()."\n\n";

        $labels = ['Bug'];
        $data = ['title' => $title, 'body' => $body, 'labels' => $labels];

        $data_string = json_encode($data);

        $ch = curl_init();

        $userRepo = parse_url($this->runtemplate->application->getDataValue('homepage'), \PHP_URL_PATH);

        curl_setopt($ch, \CURLOPT_URL, 'https://api.github.com/repos'.$userRepo.'/issues');
        curl_setopt($ch, \CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, \CURLOPT_USERAGENT, \Ease\Shared::appName().' '.\Ease\Shared::appVersion());
        curl_setopt($ch, \CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true); // return content as a string from curl_exec
        curl_setopt($ch, \CURLOPT_VERBOSE, (bool) \Ease\Shared::cfg('API_DEBUG', false)); // For debugging

        $response = curl_exec($ch);

        $curlInfo = curl_getinfo($ch);
        $curlInfo['when'] = microtime();

        $this->addStatusMessage($response, $curlInfo['http_code'] === 200 ? 'success' : 'error');
        curl_close($ch);
    }
}
