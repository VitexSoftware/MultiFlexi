<?php

declare(strict_types=1);

/**
 * Multi Flexi - Github Issue Action
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Action;

/**
 * Description of RedmineIssue
 *
 * @author vitex
 */
class Github extends \MultiFlexi\CommonAction
{
    /**
     * Module Caption
     *
     * @return string
     */
    public static function name()
    {
        return _('Github Issue');
    }

    public function curl()
    {
//curl --request POST \
//  --url https://api.github.com/repos/{owner}/{repo}/issues \
//  --header 'authorization: Bearer YOUR_TOKEN' \
//  --header 'content-type: application/json' \
//  --data '{
//    "title": "Issue Title",
//    "body": "Issue Body",
//    "assignees": [
//      "octocat"
//    ],
//    "labels": [
//      "bug"
//    ]
//  }'
    }

    public function curl2()
    {
// $Token = personal access token for the account that will post the issue
        $headerValue = " Bearer " . $token;

// Format the curl heaer (-H) with our auth code
        $header = array("Authorization:" . $headerValue);

// Content for the issue
        $title = "I have a problem";
        $body = "I broke it, how can i fix it?";

        /* $label is an array of stirngs containing the labels we want to assign
         * If there isnt a label with the given name, github will generate it however
         *  we don't get to control the color of the label
         */
        $label = array("Bug", "Feature");

// Format our data into an array with the following keys
        $data = array("title" => $title, "body" => $body, "labels" => array($label));

// prepare as json data
        $data_string = json_encode($data);

// $repoOwner is the owern of the repository, usually the username
// $repo is the name of the repository
        $url = sprintf("https://api.github.com/repos/%s/%s/issues", $repoOwner, $repo);

// input the name of our application for github tracking
        $appName = 'My First API intergration';

//Send curl message
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERAGENT, $appName);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

// By setting the execution as a variable, we can get the returned
// json data and extract it to outputs for viewing
        $response = curl_exec($ch);

// close our connection
        curl_close($ch);
    }

    /**
     * Module Description
     *
     * @return string
     */
    public static function description()
    {
        return _('Make Github issue using Job output');
    }

    public static function logo()
    {
        return 'data:image / svg + xml; base64, PHN2ZyB3aWR0aD0iOTgiIGhlaWdodD0iOTYiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik00OC44NTQgMEMyMS44MzkgMCAwIDIyIDAgNDkuMjE3YzAgMjEuNzU2IDEzLjk5MyA0MC4xNzIgMzMuNDA1IDQ2LjY5IDIuNDI3LjQ5IDMuMzE2LTEuMDU5IDMuMzE2LTIuMzYyIDAtMS4xNDEtLjA4LTUuMDUyLS4wOC05LjEyNy0xMy41OSAyLjkzNC0xNi40Mi01Ljg2Ny0xNi40Mi01Ljg2Ny0yLjE4NC01LjcwNC01LjQyLTcuMTctNS40Mi03LjE3LTQuNDQ4LTMuMDE1LjMyNC0zLjAxNS4zMjQtMy4wMTUgNC45MzQuMzI2IDcuNTIzIDUuMDUyIDcuNTIzIDUuMDUyIDQuMzY3IDcuNDk2IDExLjQwNCA1LjM3OCAxNC4yMzUgNC4wNzQuNDA0LTMuMTc4IDEuNjk5LTUuMzc4IDMuMDc0LTYuNi0xMC44MzktMS4xNDEtMjIuMjQzLTUuMzc4LTIyLjI0My0yNC4yODMgMC01LjM3OCAxLjk0LTkuNzc4IDUuMDE0LTEzLjItLjQ4NS0xLjIyMi0yLjE4NC02LjI3NS40ODYtMTMuMDM4IDAgMCA0LjEyNS0xLjMwNCAxMy40MjYgNS4wNTJhNDYuOTcgNDYuOTcgMCAwIDEgMTIuMjE0LTEuNjNjNC4xMjUgMCA4LjMzLjU3MSAxMi4yMTMgMS42MyA5LjMwMi02LjM1NiAxMy40MjctNS4wNTIgMTMuNDI3LTUuMDUyIDIuNjcgNi43NjMuOTcgMTEuODE2LjQ4NSAxMy4wMzggMy4xNTUgMy40MjIgNS4wMTUgNy44MjIgNS4wMTUgMTMuMiAwIDE4LjkwNS0xMS40MDQgMjMuMDYtMjIuMzI0IDI0LjI4MyAxLjc4IDEuNTQ4IDMuMzE2IDQuNDgxIDMuMzE2IDkuMTI2IDAgNi42LS4wOCAxMS44OTctLjA4IDEzLjUyNiAwIDEuMzA0Ljg5IDIuODUzIDMuMzE2IDIuMzY0IDE5LjQxMi02LjUyIDMzLjQwNS0yNC45MzUgMzMuNDA1LTQ2LjY5MUM5Ny43MDcgMjIgNzUuNzg4IDAgNDguODU0IDB6IiBmaWxsPSIjMjQyOTJmIi8+PC9zdmc+';
    }

    public static function configForm()
    {
        return new \Ease\TWB4\FormGroup(_('GitHub token'), new \Ease\Html\InputTextTag('Github[token]'), 'ghp_iupB8adLxIIBezDWB1BH9HJCAtpcOL2scdmX', new \Ease\Html\ATag('https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens', _('How to obtain Github Token'))) ;
    }

    /**
     * Form Inputs
     *
     * @return mixed
     */
    public static function inputs(string $action)
    {
        return new \Ease\TWB4\Badge('info', _('No Fields required') . ' (' . $action . ')');
    }


    /**
     * Is this Action Situable for Application
     *
     * @param Application $app
     */
    public static function usableForApp($app): bool
    {
        return is_null(strstr($app->getDataValue('homepage'), 'github.com')) === false;
    }
}
