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

namespace MultiFlexi\Executor;

/**
 * Description of Podman.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class Podman extends Native implements \MultiFlexi\executor
{
    use \Ease\Logger\Logging;
    public const PULLCOMMAND = 'podman pull docker.io/vitexsoftware/debian:bookworm';

    public static function name(): string
    {
        return _('Podman');
    }

    public static function description(): string
    {
        return _('Execute jobs in container using Podman');
    }

    //    public function launch(string $command): void
    //    {
    //        $this->pullImage();
    //        $this->launchContainer();
    //        $this->updateContainer();
    //        $this->deployApp();
    //        $this->runApp();
    //        $this->storeLogs();
    //        $this->stopContainer();
    //    }

    public function pullImage(): void
    {
    }

    public function launchContainer(): void
    {
    }

    public function updateContainer(): void
    {
    }

    public function deployApp(): void
    {
    }

    public function runApp(): void
    {
    }

    public function storeLogs(): void
    {
    }

    public function stopContainer(): void
    {
    }

    /**
     * Can this Executor execute given application ?
     *
     * @param Application $app
     */
    public static function usableForApp($app): bool
    {
        return empty($app->getDataValue('ociimage')) === false; // Container Image must be present
    }

    public static function logo(): string
    {
        return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcm'
        .'cvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMjggMTI4Ij48cGF0aCBmaWxsPSIjM2M2ZWI'
                .'0IiBkPSJNOTMuMTg4IDU5LjgwM2MuMTQ4LjkyNy4zIDEuODU0LjQ4MiAyLjc2'
                .'aDguOTU1di0yLjc2em0xLjQ4NCA2Ljg5NmMuMTg1LjYzNC4zODEgMS4yNjUuN'
                .'Tk4IDEuODkzLjUyLjI1OSAxLjAyNy41NDggMS41MTcuODdoMTEuMzUydi0yLj'
                .'c2M3ptLTE4LjQzMSAxLjI4MmMtLjQyNi4xNTUtLjg0My4zMzYtMS4yNTIuNTMz'
                .'di4wMDRjLjQxLS4xOTguODI1LS4zODIgMS4yNTItLjUzN3ptLTQxLjI0OCAzN'
                .'C41N3YyLjc2SDY4LjE1di0yLjc2em0xOS4zMSA2Ljg5NnYyLjc1Mkg4MS42di'
                .'0yLjc1MnoiLz48cGF0aCBmaWxsPSIjY2NjIiBkPSJNNjEuMTE0IDE2LjI2Yy0'
                .'0LjkxIDAtOS43MjEuNTY3LTEyLjU4NiAxLjcwNy03Ljg5IDIuODg4LTEzLjk1'
                .'IDEwLjc3NS0xNS43NDYgMjAuMzMtMi4zNjUgMTEuMTg2LTIuMjI0IDE5Ljk0M'
                .'S00LjU3MiAyOC4xNzhhMTMuNTg5IDEzLjU4OSAwIDAgMSAzLjUyOS0xLjk0Yz'
                .'EuODM0LS43MjYgNC45MDktMS4wOSA4LjA0OS0xLjA5IDMuMTQgMCA2LjM1LjM'
                .'2NSA4LjQzMSAxLjA5IDQuNTcxIDEuNjcgOC4xNzcgNS45NiA5LjY3IDExLjI4'
                .'NGg5LjcxOWMxLjk5MS0zLjY4MSA1LjAzLTYuNTIzIDguNjMzLTcuODM4IDMuO'
                .'TE5LTEuNTU0IDEzLjE2NS0xLjU1IDE3LjYxNyAwIC40ODMuMTc2Ljk1Mi4zOS'
                .'AxLjQxNC42MTktMy4wNjItOC44NjYtMi42NzMtMTguMTUxLTUuMjQyLTMwLjM'
                .'wMy0xLjc5NS05LjU1NS03Ljg1Ny0xNy40NDItMTUuNzQ2LTIwLjMzLTMuMjU0'
                .'LTEuMTM2LTguMjYxLTEuNzA2LTEzLjE3LTEuNzA3WiIvPjxwYXRoIGZpbGw9I'
                .'iNlN2U4ZTkiIGQ9Ik00NS4yNzUgNDkuNzg3YTMuMzQ0IDMuNTIzIDAgMCAwLT'
                .'MuMzQgMy41MjMgMy4zNDQgMy41MjMgMCAwIDAgMy4zNCAzLjUyMyAzLjM0NCA'
                .'zLjUyMyAwIDAgMCAzLjM0MS0zLjUyMyAzLjM0NCAzLjUyMyAwIDAgMC0zLjM0'
                .'LTMuNTIzem0zMy43OSAwYTMuMzQ0IDMuNTIzIDAgMCAwLTMuMzQgMy41MjMgM'
                .'y4zNDQgMy41MjMgMCAwIDAgMy4zNCAzLjUyMyAzLjM0NCAzLjUyMyAwIDAgMC'
                .'AzLjM1LTMuNTIzIDMuMzQ0IDMuNTIzIDAgMCAwLTMuMzUtMy41MjN6Ii8+PHB'
                .'hdGggZmlsbD0iI2E3YTlhYyIgZD0ibTUwLjkwNSA1MC43MDUtMTIuNDI4IDMu'
                .'MzMyLjM1NiAxLjMzMiAxMi40My0zLjMzMnptMjMuNTIgMC0uMzU4IDEuMzMyI'
                .'DEyLjQyNyAzLjMzMi4zNTgtMS4zMzJ6bS0yMi4yNzYgMi4wMDhMMzkuNjUgNT'
                .'kuOTMybC42OSAxLjE5MyAxMi40OTctNy4yMTh6bTIxLjAzOSAwLS42OSAxLjE'
                .'5NCAxMi40OTggNy4yMTguNjktMS4xOTN6bS0yMC4xMjUgMi4zOTUtOC42MDcg'
                .'OC42MTljLjU4Ni4wNzQgMS4xNDQuMTY3IDEuNjc3LjI3MWw3LjkwNS03LjkxN'
                .'nptMTkuMjEgMC0uOTc0Ljk3NCAxMC44MDkgMTAuODI2Yy42MTItLjA0IDEuMj'
                .'M3LS4wNjcgMS44Ny0uMDh6bS0xOC43OTIgMy4xMzgtMy45NTcgNi44NmMuNDE'
                .'xLjIwMy44MTQuNDI2IDEuMjA1LjY3MmwzLjk0Ny02Ljg0NHptMTguMzc1IDAt'
                .'MS4xOTUuNjg4IDUuMjg3IDkuMTY4Yy4wOTgtLjAzOS4xOTMtLjA4NS4yOTMtL'
                .'jEyMS4zMS0uMTIzLjY2NS0uMjMzIDEuMDM3LS4zMzZ6Ii8+PHBhdGggZmlsbD'
                .'0iI2ZmZiIgZD0iTTczLjI2MSA1MC44NGExMC43MzYgMTAuMjE3IDAgMCAxLTE'
                .'wLjczNiAxMC4yMThBMTAuNzM2IDEwLjIxNyAwIDAgMSA1MS43ODggNTAuODRh'
                .'MTAuNzM2IDEwLjIxNyAwIDAgMSAxMC43MzctMTAuMjE3QTEwLjczNiAxMC4yM'
                .'TcgMCAwIDEgNzMuMjYgNTAuODRaIi8+PHBhdGggZmlsbD0iIzgwODA4MCIgZD'
                .'0iTTY3LjEwNyA0OC45OThjLS4zNi0uOTMzLS4zNi0yLjc5OS0xLjQ0LTIuNzk'
                .'5cy0yLjA4LS43LTMuMTQyLS43Yy0xLjA2MyAwLTIuMDYuNy0zLjE0Mi43LTEu'
                .'MDggMC0xLjA4IDEuODY2LTEuNDQgMi44LS4zNi45MzIgNC41ODIgMy45NjQgN'
                .'C41ODIgMy45NjRzNC45NDItMy4wMzIgNC41ODItMy45NjV6Ii8+PHBhdGggZm'
                .'lsbD0iIzNjNmViNCIgZD0iTTU3LjM5OSA3NC4yOWMuMzMuODg2LjU5IDEuODE'
                .'uNzkzIDIuNzYxaDguNzk5Yy40NDMtLjk3My45NTgtMS44OTcgMS41MzUtMi43'
                .'NjJ6bTQ0LjE3Ny4wMDFhMTkuNjczIDE5LjY3MyAwIDAgMSAxLjUzMiAyLjc2a'
                .'DExLjA3MnYtMi43NnoiLz48cGF0aCBmaWxsPSIjY2NjIiBkPSJNMzkuNzg5ID'
                .'YzLjQ0NWMtMy4xNCAwLTYuMjE2LjM2NC04LjA1IDEuMDktNS4wNDYgMS44NDQ'
                .'tOC45MTcgNi44NzgtMTAuMDY1IDEyLjk3NS0uNiAyLjgyNi0uOTQ1IDUuMzkx'
                .'LTEuMjM4IDcuODA3aDM5LjA4NmMtLjI5My0yLjQxNi0uNjMtNC45OC0xLjIzL'
                .'TcuODA3LTEuMTQ3LTYuMDk3LTUuMDI3LTExLjEzLTEwLjA3NC0xMi45NzQtMi'
                .'4wODItLjcyNS01LjI5LTEuMDktOC40My0xLjA5WiIvPjxwYXRoIGZpbGw9IiN'
                .'lN2U4ZTkiIGQ9Ik0yOS42NjMgODQuODRhMi4xNCAyLjI0OCAwIDAgMC0xLjI1'
                .'NS40NzdoMi41NmEyLjE0IDIuMjQ4IDAgMCAwLTEuMzA1LS40NzZ6bTIxLjYyI'
                .'DBhMi4xNCAyLjI0OCAwIDAgMC0xLjI1Ni40NzdoMi41NjJhMi4xNCAyLjI0OC'
                .'AwIDAgMC0xLjMwNy0uNDc2eiIvPjxwYXRoIGZpbGw9IiNhN2E5YWMiIGQ9Im0'
                .'zMy4yMDIgODUuMTg3LS40ODQuMTNoLjUyem0xNS4xNzIgMC0uMDM1LjEzaC41'
                .'MnoiLz48cGF0aCBmaWxsPSIjZmZmIiBkPSJNNDAuNjk4IDc5YTYuODY4IDYuN'
                .'TIgMCAwIDAtNi44NDYgNi4zMTdoMTMuNjgyQTYuODY4IDYuNTIgMCAwIDAgND'
                .'AuNjk4IDc5WiIvPjxwYXRoIGZpbGw9IiM4MDgwODAiIGQ9Ik00MC42OTggODI'
                .'uMTA2Yy0uNjggMC0xLjMxNy40NS0yLjAwOC40NS0uNjkxIDAtLjY5NiAxLjE4'
                .'OC0uOTI2IDEuNzgzLS4wOC4yMDguMjY4LjU4My43MzUuOTc4aDQuMzk2Yy40N'
                .'jgtLjM5NS44MDgtLjc3LjcyNy0uOTc4LS4yMy0uNTk1LS4yMjYtMS43ODMtLj'
                .'kxNy0xLjc4My0uNjkgMC0xLjMyNy0uNDUtMi4wMDctLjQ1eiIvPjxwYXRoIGZ'
                .'pbGw9IiM4OTJjYTAiIGQ9Im05MS43MzYgMS40NjctNTUuNzM4LjE2NGExLjM4'
                .'IDEuMzggMCAwIDAtMS4wNzMuNTJMLjI5NSA0NS44ODNhMS4zOCAxLjM4IDAgM'
                .'CAwLS4yNiAxLjE2bDEyLjU2IDU0LjM2M2ExLjM4IDEuMzggMCAwIDAgLjc1My4'
                .'5MzVsNTAuMjg1IDI0LjA2MWExLjM4IDEuMzggMCAwIDAgMS4xOTUtLjAxbDUw'
                .'LjE0OC0yNC4zNTVhMS4zOCAxLjM4IDAgMCAwIC43NDQtLjk0M2wxMi4yNDctN'
                .'TQuNDMyYTEuMzggMS4zOCAwIDAgMC0uMjY4LTEuMTZMOTIuODIgMS45NzdhMS'
                .'4zOCAxLjM4IDAgMCAwLTEuMDgzLS41MVptLS42NTcgMi43NiAzNC4wNSA0Mi4'
                .'0OC0xMS45NTQgNTMuMTQyLTQ4Ljk0NSAyMy43NzYtNDkuMDkxLTIzLjQ4Mkwy'
                .'Ljg3NSA0Ny4wNyAzNi42NzMgNC4zODNaIi8+PHBhdGggZmlsbD0iI2NjYyIgZ'
                .'D0iTTEwNi42NDQgOTUuNzM3Yy0uNjA2LTQuMTI0LS45LTguNjEtMi4wMTktMT'
                .'MuODg3LTEuMjI4LTYuNTE4LTUuMzczLTExLjktMTAuNzY4LTEzLjg3LTQuNDU'
                .'yLTEuNTUtMTMuNjk3LTEuNTU0LTE3LjYxNiAwLTUuMzk1IDEuOTctOS41NCA3'
                .'LjM1Mi0xMC43NjggMTMuODctMS4xMTggNS4yNzgtMS40MTMgOS43NjMtMi4wM'
                .'TkgMTMuODg3Ii8+PHBhdGggZmlsbD0iI2U3ZThlOSIgZD0iTTc0LjAyIDg5Lj'
                .'Y4OGEyLjI4NyAyLjQwNCAwIDAgMC0yLjI4NiAyLjQwNkEyLjI4NyAyLjQwNCA'
                .'wIDAgMCA3NC4wMiA5NC41YTIuMjg3IDIuNDA0IDAgMCAwIDIuMjg1LTIuNDA2'
                .'IDIuMjg3IDIuNDA0IDAgMCAwLTIuMjg1LTIuNDA2em0yMy4xMSAwYTIuMjg3I'
                .'DIuNDA0IDAgMCAwLTIuMjg2IDIuNDA2QTIuMjg3IDIuNDA0IDAgMCAwIDk3Lj'
                .'EzIDk0LjVhMi4yODcgMi40MDQgMCAwIDAgMi4yODUtMi40MDYgMi4yODcgMi4'
                .'0MDQgMCAwIDAtMi4yODUtMi40MDZ6Ii8+PHBhdGggZmlsbD0iI2E3YTlhYyIg'
                .'ZD0ibTc3LjgxNCA5MC4xMDQtOC41IDIuMjc3LjM1OCAxLjMzMiA4LjQ5OC0yL'
                .'jI3N3ptMTYuMTk2IDAtLjM1OCAxLjMzMiA4LjUgMi4yNzcuMzU4LTEuMzMyem'
                .'0tMTUuNCAxLjM5Mi04LjU0NCA0LjkyNi42ODggMS4xOTMgOC41NDMtNC45MjR'
                .'6bTE0LjYwNSAwLS42OSAxLjE5NSA4LjU0MyA0LjkyNC42OS0xLjE5M3oiLz48'
                .'cGF0aCBmaWxsPSIjZmZmIiBkPSJNOTMuMTU3IDkwLjQxYTcuMzQzIDYuOTcgM'
                .'CAwIDEtNy4zNDIgNi45NyA3LjM0MyA2Ljk3IDAgMCAxLTcuMzQzLTYuOTcgNy'
                .'4zNDMgNi45NyAwIDAgMSA3LjM0My02Ljk3IDcuMzQzIDYuOTcgMCAwIDEgNy4'
                .'zNDIgNi45N3oiLz48cGF0aCBmaWxsPSIjODA4MDgwIiBkPSJNODguOTQ4IDg5'
                .'LjE1MmMtLjI0Ni0uNjM2LS4yNDYtMS45MS0uOTg1LTEuOTEtLjczOCAwLTEuN'
                .'DItLjQ3Ni0yLjE0OC0uNDc2LS43MjcgMC0xLjQxLjQ3Ny0yLjE0OS40NzctLj'
                .'czOCAwLS43MzggMS4yNzMtLjk4NSAxLjkxLS4yNDYuNjM2IDMuMTM0IDIuNzA'
                .'0IDMuMTM0IDIuNzA0czMuMzgtMi4wNjggMy4xMzMtMi43MDV6Ii8+PHBhdGgg'
                .'ZmlsbD0iIzYwNjA1YiIgZD0iTTYyLjUyNiAzOS41OTJjLTYuNDYxIDAtMTEuN'
                .'zcgNS4wMjItMTEuNzcgMTEuMjUyczUuMzA5IDExLjI1MiAxMS43NyAxMS4yNT'
                .'JjNi40NjIgMCAxMS43NzEtNS4wMjIgMTEuNzcxLTExLjI1MnMtNS4zMTEtMTE'
                .'uMjUyLTExLjc3MS0xMS4yNTJ6bTAgMi4wNjljNS4zOTggMCA5LjcwMyA0LjEy'
                .'OCA5LjcwMyA5LjE4MyAwIDUuMDU2LTQuMzA1IDkuMTgyLTkuNzAzIDkuMTgyL'
                .'TUuMzk3IDAtOS43MDMtNC4xMjYtOS43MDMtOS4xODIgMC01LjA1NSA0LjMwNi05LjE4MyA5LjcwMy05LjE4M3ptLTIxLjgyOCAzNi4zYy00LjI1NCAwLTcuNzggMy4yNi03Ljg5NSA3LjM1NmgyLjA2OWMuMTEyLTIuOTIgMi42MzctNS4yODggNS44MjYtNS4yODggMy4xOSAwIDUuNzEyIDIuMzY4IDUuODI0IDUuMjg4aDIuMDY4Yy0uMTEzLTQuMDk1LTMuNjQtNy4zNTYtNy44OTItNy4zNTZ6bTQ1LjExOSA0LjQ0N2MtNC41ODUgMC04LjM4IDMuNTYtOC4zOCA3Ljk5OCAwIDQuNDQgMy43OTUgOC4wMDYgOC4zOCA4LjAwNiA0LjU4NiAwIDguMzc5LTMuNTY3IDguMzc5LTguMDA2IDAtNC40MzgtMy43OTMtNy45OTgtOC4zOC03Ljk5OHptMCAyLjA2OWMzLjUyNSAwIDYuMzEgMi42NjcgNi4zMSA1LjkzIDAgMy4yNi0yLjc4NSA1LjkzNy02LjMxIDUuOTM3LTMuNTI0IDAtNi4zMS0yLjY3Ny02LjMxLTUuOTM4IDAtMy4yNiAyLjc4Ni01LjkzIDYuMzEtNS45M3oiLz48cGF0aCBmaWxsPSIjM2M2ZWI0IiBkPSJNMTUuOTYyIDg1LjMxN3YyLjc2aDQ4LjQ2MmMuMTItLjg5Ny4yNTMtMS44MTYuNDA2LTIuNzZ6TTQwLjUxNiA5NS42NnYyLjc1Mmg3MS42NjRWOTUuNjZoLTUuNTQ1bC4wMS4wNzZINjMuNDUzbC4wMS0uMDc2eiIvPjxwYXRoIGQ9Ik02MS4xMTYgMTUuMjI3aC0uMDAyYy00Ljk4MiAwLTkuODMuNTQ0LTEyLjk1NSAxLjc4My04LjI4MyAzLjA0My0xNC41MjggMTEuMjI1LTE2LjM4NSAyMS4wNzYtMS42NTIgNy44MTUtMi4wOSAxNC40MjQtMi45NjkgMjAuNDA4LS4zNzUgMi41NTgtLjgzOCA0Ljk5NS0xLjQ5NiA3LjM3Ny0zLjM2MSAyLjY1NS01Ljc3NSA2Ljc2Ni02LjY1OCAxMS40NS0uNjE2IDIuOTEzLS45NTcgNS41NC0xLjI1NCA3Ljk5NmgyLjA3OGMuMjg4LTIuMzcuNjI0LTQuODYgMS4yMDMtNy41OWwuMDA4LS4wMXYtLjAxOGMxLjA5LTUuNzgzIDQuNzctMTAuNDkyIDkuNDA4LTEyLjE4NWwuMDE4LS4wMS4wMDgtLjAwOGMxLjU3My0uNjI0IDQuNjA3LTEuMDE0IDcuNjY4LTEuMDE0IDMuMDU3IDAgNi4yMTEuMzgxIDguMDg0IDEuMDMxIDQuNjM0IDEuNjk2IDguMzExIDYuNDA2IDkuNCAxMi4xODZ2LjAxOGwuMDA4LjAxYy41OCAyLjczLjkxNSA1LjIyMSAxLjIwMyA3LjU5aDIuMDc5Yy0uMjk3LTIuNDU4LS42MzctNS4wOS0xLjI1NC04LjAwNi0xLjIxLTYuNDA2LTUuMjgyLTExLjc1NC0xMC43MzUtMTMuNzQ1aC0uMDA4bC0uMDA4LS4wMWMtMi4yOTItLjc5Ni01LjU1LTEuMTQtOC43Ny0xLjE0Mi0zLjIxNCAwLTYuMzI4LjMzMy04LjQxOSAxLjE2LS41MTguMTktMS4wMi40Mi0xLjUxMi42NjhhNzAuMjQzIDcwLjI0MyAwIDAgMCAxLTUuNDQ1Yy44OTUtNi4xMDIgMS4zMTUtMTIuNjMzIDIuOTM0LTIwLjI4N2wuMDEtLjAxdi0uMDE2YzEuNzM2LTkuMjQgNy42MDYtMTYuODA2IDE1LjA4Ni0xOS41NDRsLjAxLS4wMDhoLjAxNWMyLjYwNS0xLjAzNiA3LjM3NS0xLjYzNiAxMi4yMDUtMS42MzUgNC44MjMgMCA5Ljc2NC41ODMgMTIuODEgMS42NDMgNy40OCAyLjczNyAxMy4zNSAxMC4zMDMgMTUuMDg0IDE5LjU0M2wuMDEuMDJ2LjAwN2MxLjYxOCA3LjY1NCAyLjA0OCAxNC4xODUgMi45NDMgMjAuMjg3LjQwMyAyLjc0OC45MjYgNS40MSAxLjY4IDguMDM1LTIuMzk0LS43MjQtNS42MDQtMS4wNS04Ljc5NS0xLjA1LTMuNDMgMC02Ljc0Ny4zNi04Ljk2NiAxLjIzOC01Ljc5NCAyLjEyLTEwLjEyNSA3LjgtMTEuNDE2IDE0LjYxOS0xLjEzNiA1LjM1OC0xLjQzOCA5Ljg3OS0yLjAzNiAxMy45NDNsMi4wNTEuMzAzYy42MTUtNC4xODMuODk4LTguNjI2IDItMTMuODIzbC4wMS0uMDF2LS4wMDdjMS4xNjgtNi4yMDUgNS4xMjMtMTEuMjc2IDEwLjExLTEzLjA5NmguMDA3bC4wMTgtLjAxYzEuNy0uNjc0IDQuOTQ2LTEuMDkgOC4yMjMtMS4wOSAzLjI3NSAwIDYuNjQ4LjQwNyA4LjY2NCAxLjEwOGguMDFjNC45NzcgMS44MjYgOC45MjIgNi44OSAxMC4wOSAxMy4wODh2LjAxN2MxLjEwMSA1LjE5NiAxLjM5MiA5LjY0IDIuMDA3IDEzLjgyMmwyLjA1My0uMzAyYy0uNTk3LTQuMDY1LS45LTguNTg2LTIuMDM1LTEzLjk0NC0xLjE0Ni02LjA1Ny00LjY5Mi0xMS4yMTMtOS41My0xMy43NzctMS4wMDQtMy4wMTMtMS42Mi02LjA5My0yLjEwMS05LjM2Ny0uODc3LTUuOTgtMS4zMTItMTIuNTg0LTIuOTYxLTIwLjM5LTEuODU0LTkuODY4LTguMTAzLTE4LjA3NC0xNi40MDItMjEuMTEyaC0uMDE4Yy0zLjQ2NC0xLjIxLTguNTItMS43NjQtMTMuNTEtMS43NjV6TTQ1LjU0NiAyNy42OWE2LjkxMiA2LjkxMiAwIDAgMC01LjUwNiAyLjY4NGwtLjYzMy44MTQgMS42NDYgMS4yNjQuNjMxLS44MjNhNC45MiA0LjkyIDAgMCAxIDMuNTkyLTEuODY5YzEuMy0uMDU1IDIuNjUuNDY4IDMuNTc0IDEuMzg1bC43MzYuNzM2IDEuNDYzLTEuNDcyLS43MzYtLjcyN2E2LjkwNSA2LjkwNSAwIDAgMC00Ljc3LTEuOTl6bTMzLjM3MiAwYTYuOTA1IDYuOTA1IDAgMCAwLTQuNzcgMS45OTJsLS43MzYuNzI3IDEuNDU1IDEuNDcyLjczNy0uNzM2YTQuODk1IDQuODk1IDAgMCAxIDMuNTc0LTEuMzg1YzEuMzc1LjA2IDIuNzYuNzc3IDMuNiAxLjg3bC42MzMuODIyIDEuNjM2LTEuMjY0LS42MzMtLjgxNGE2Ljg5NSA2Ljg5NSAwIDAgMC01LjQ5Ni0yLjY4NHptLTMzLjQyNSA5LjExNWE1LjE3IDUuMzA2IDAgMCAwLTUuMTY4IDUuMzA1IDUuMTcgNS4zMDYgMCAwIDAgNS4xNjggNS4zMDYgNS4xNyA1LjMwNiAwIDAgMCA1LjE3NS01LjMwNiA1LjE3IDUuMzA2IDAgMCAwLTUuMTc1LTUuMzA1em0zMy40NjggMGE1LjE3IDUuMzA2IDAgMCAwLTUuMTY3IDUuMzA1IDUuMTcgNS4zMDYgMCAwIDAgNS4xNjcgNS4zMDYgNS4xNyA1LjMwNiAwIDAgMCA1LjE2OC01LjMwNiA1LjE3IDUuMzA2IDAgMCAwLTUuMTY3LTUuMzA1em0tMTYuNDM1IDcuNjZjLS43NTggMC0xLjM3OS4yMzMtMS44OTUuNDA3LS41MTcuMTc1LS45My4yOTQtMS4yNDguMjk0LS41NjcgMC0xLjEyNC4zMTEtMS40MjcuNjc2LS4zMDIuMzY0LS40NDUuNzQtLjU1NSAxLjA5OC0uMjIuNzEzLS4zMjcgMS40MzgtLjQyNCAxLjY5LS4yMjIuNTc0LS4wMSAxLjAzMS4xNjYgMS4zMzEuMTc1LjMuMzg5LjUzNi42My43OC40ODQuNDg2IDEuMDk2Ljk3MyAxLjcxNCAxLjQyNy42MzMuNDY0IDEuMjY4Ljg4NiAxLjc0IDEuMTk0bC0uMDI1LjQxNmMtLjIzMy4xOS0uOC42OTMtMS43NCAxLjAxMS0uNTU2LjE4OC0xLjExNi4yNTUtMS42MDIuMTMxLS40MTMtLjEwNi0uODI0LS40Ny0xLjI0Ni0xLjAyMS4wMjctLjIxOC4wMzctLjMwNy4wODYtLjY2Ni4wODctLjYyOC4yMTMtMS4zODcuMjctMS42MTJsLTIuMDA5LS41MDJjLS4xMi40OC0uMjIyIDEuMTgtLjMxMiAxLjgyOS0uMDkuNjQ4LS4xNTYgMS4yMDMtLjE1NiAxLjIwM2wtLjA0My4zNy4xOTkuMzExYy43MjkgMS4xNDYgMS43MDEgMS44NDEgMi43MDEgMi4wOTYgMSAuMjU2IDEuOTcuMTAxIDIuNzgtLjE3MiAxLjA1LS4zNTcgMS45MDYtLjkzIDIuMzc4LTEuMjkuNDczLjM2IDEuMzI0LjkzNSAyLjM3MSAxLjI5LjgxLjI3NCAxLjc3LjQyOCAyLjc3LjE3MnMxLjk3My0uOTUgMi43MDEtMi4wOTZsLjItLjMxLS4wNDQtLjM3MXMtLjA2Ni0uNTU1LS4xNTYtMS4yMDNjLS4wOS0uNjUtLjE5NC0xLjM0OS0uMzEyLTEuODI5bC0yLjAwOC41MDJjLjA1Ni4yMjUuMTgyLjk4NC4yNyAxLjYxMi4wNDguMzU0LjA2LjQ0LjA4NS42NTgtLjQyNC41NTYtLjgzLjkyMy0xLjI0NiAxLjAzLS40ODYuMTIzLTEuMDQ1LjA1Ni0xLjYwMS0uMTMyLS45NC0uMzE4LTEuNTA4LS44MjEtMS43NC0xLjAxMWwtLjAxOC0uMzg5Yy40NzktLjMxIDEuMTI4LS43NCAxLjc4My0xLjIyLjYxOC0uNDU1IDEuMjMtLjk0MyAxLjcxNS0xLjQzLjI0LS4yNDMuNDU2LS40NzkuNjMtLjc3OC4xNzctLjMuMzg3LS43NTcuMTY1LTEuMzMyLS4wOTctLjI1LS4yMTItLjk3Ni0uNDMyLTEuNjktLjExLS4zNTYtLjI0Mi0uNzM0LS41NDUtMS4wOTktLjMwMy0uMzY0LS44Ni0uNjc0LTEuNDI4LS42NzQtLjMxNyAwLS43My0uMTItMS4yNDYtLjI5NC0uNTE3LS4xNzQtMS4xMzctLjQwNy0xLjg5Ni0uNDA3em0wIDIuMDY5Yy4zMDUgMCAuNzEuMTE4IDEuMjMuMjkzLjQ3OC4xNjIgMS4wNzkuMzQgMS43NjQuMzczLjAyMy4wMzUuMDg1LjE1LjE0OC4zNTUuMTExLjM2LjIyNC45MS4zOCAxLjQ0NS0uMDUuMDY4LS4xMS4xNTMtLjI0MS4yODUtLjM0Mi4zNDQtLjkuNzkxLTEuNDczIDEuMjExLS44OC42NDctMS40My45ODQtMS44MDggMS4yMjEtLjM3NS0uMjM2LS45MzYtLjU3LTEuODE5LTEuMjItLjU3My0uNDItMS4xMi0uODY4LTEuNDYyLTEuMjEyLS4xMzItLjEzMi0uMTktLjIxNy0uMjQtLjI4NS4xNTQtLjUzNS4yNjgtMS4wODQuMzc4LTEuNDQ1YTEuNTUgMS41NSAwIDAgMSAuMTQ5LS4zNTVjLjY4NC0uMDMyIDEuMjc3LS4yMTEgMS43NTYtLjM3My41MTgtLjE3NS45MzQtLjI5MyAxLjIzOC0uMjkzek0yOS41ODQgNzAuMzY5Yy0xLjQuMDYtMi43MS43NDQtMy41NjYgMS44NTRsLS42My44MjIgMS42MzQgMS4yNjQuNjMzLS44MjRhMi44MDMgMi44MDMgMCAwIDEgMi4wMjUtMS4wNDcgMi43NzcgMi43NzcgMCAwIDEgMi4wMDguNzc5bC43MzYuNzI3IDEuNDUzLTEuNDcxLS43MzYtLjcyN2E0Ljc4NiA0Ljc4NiAwIDAgMC0zLjU1Ny0xLjM3N3ptMjEuODM4IDBhNC43NjcgNC43NjcgMCAwIDAtMy41NSAxLjM3N2wtLjczNS43MjcgMS40NTMgMS40Ny43MzctLjcyNmEyLjc3NyAyLjc3NyAwIDAgMSAyLjAwNy0uNzggMi44IDIuOCAwIDAgMSAyLjAyNiAxLjA0OGwuNjIzLjgyNCAxLjY0NC0xLjI2NC0uNjMyLS44MjJhNC43OSA0Ljc5IDAgMCAwLTMuNTczLTEuODU0em00NS42MDQgMy45MTJoLS4wMDJhNS4wNDYgNS4wNDYgMCAwIDAtMy40ODcgMS40NTZsLS43MzYuNzI2IDEuNDU1IDEuNDcuNzM0LS43MjZhMy4wMjkgMy4wMjkgMCAwIDEgMi4yLS44NDdjLjg0LjAzNSAxLjcwMS40NzcgMi4yMTQgMS4xNDJsLjYzMy44MjIgMS42MzctMS4yNjMtLjYzMy0uODIyYTUuMDIyIDUuMDIyIDAgMCAwLTMuNzU4LTEuOTVjLS4wODUtLjAwMy0uMTctLjAwOC0uMjU3LS4wMDh6bS0yMi44MjQgMGMtLjA4NyAwLS4xNzMuMDA0LS4yNi4wMDlhNS4wNCA1LjA0IDAgMCAwLTMuNzY2IDEuOTQ5bC0uNjMuODIyIDEuNjQ0IDEuMjY0LjYzLS44MjNhMy4wMzUgMy4wMzUgMCAwIDEgMi4yMDgtMS4xNDIgMy4wNDggMy4wNDggMCAwIDEgMi4yMDcuODQ3bC43MzYuNzI3IDEuNDU1LTEuNDctLjczNi0uNzI3YTUuMDQ1IDUuMDQ1IDAgMCAwLTMuNDg4LTEuNDU1ek0yOS44IDc2LjU1OWEzLjMwNyAzLjM4NiAwIDAgMC0zLjMwNiAzLjM4NCAzLjMwNyAzLjM4NiAwIDAgMCAzLjMwNiAzLjM5MyAzLjMwNyAzLjM4NiAwIDAgMCAzLjMwNi0zLjM5MyAzLjMwNyAzLjM4NiAwIDAgMC0zLjMwNi0zLjM4NHptMjEuNDEyIDBhMy4zMDcgMy4zODYgMCAwIDAtMy4zMTUgMy4zODQgMy4zMDcgMy4zODYgMCAwIDAgMy4zMTUgMy4zOTMgMy4zMDcgMy4zODYgMCAwIDAgMy4zMDYtMy4zOTMgMy4zMDcgMy4zODYgMCAwIDAtMy4zMDYtMy4zODR6bTIyLjk1MyA0LjI3NmEzLjUzNiAzLjYyIDAgMCAwLTMuNTMgMy42MTcgMy41MzYgMy42MiAwIDAgMCAzLjUzIDMuNjE4IDMuNTM2IDMuNjIgMCAwIDAgMy41NC0zLjYxOCAzLjUzNiAzLjYyIDAgMCAwLTMuNTQtMy42MTd6bTIyLjg5MyAwYTMuNTM2IDMuNjIgMCAwIDAtMy41NCAzLjYxNyAzLjUzNiAzLjYyIDAgMCAwIDMuNTQgMy42MTggMy41MzYgMy42MiAwIDAgMCAzLjUzMi0zLjYxOCAzLjUzNiAzLjYyIDAgMCAwLTMuNTMyLTMuNjE3em0tNTYuMzYxLjI0MmMtLjU2NiAwLTEuMDA0LjE2Ny0xLjMzNC4yNzgtLjMzLjExLS41NTEuMTY0LS42NzQuMTY0LS40NyAwLS45NTcuMjc2LTEuMjAzLjU3Mi0uMjQ2LjI5NS0uMzQ3LjU4LS40MjQuODMtLjE1Ni41MDMtLjIyOS45NjUtLjI2IDEuMDQ3LS4yMDYuNTMzLS4wMS45NC4xMyAxLjE3OC4wMzguMDY2LjA4LjExNS4xMi4xNzJoMy4xNmExMS4xMjUgMTEuMTI1IDAgMCAxLS40NTktLjMyYy0uMzU4LS4yNjQtLjcwNy0uNTQxLS45LS43MzV2LS4wMWMuMDYzLS4yNi4xMzctLjU3Mi4xODItLjcxOS4wMDUtLjAxNi4wMDQtLjAwMi4wMS0uMDE1LjM2Ni0uMDU0LjczMy0uMTI3Ljk3Ni0uMjEuMzMyLS4xMTEuNTYyLS4xNjMuNjc2LS4xNjMuMTEzIDAgLjMzNC4wNTIuNjY2LjE2NC4yNDMuMDgyLjYxMy4xNTUuOTc4LjIwOS4wMDUuMDEzLjAwMyAwIC4wMDguMDE1LjA0Ni4xNDcuMTI4LjQ1Ny4xOS43MmwtLjAwOC4wMDljLS4xOTMuMTk0LS41NC40NzEtLjg5OS43MzRhMTUuNDkgMTUuNDkgMCAwIDEtLjQ1LjMyaDMuMTZjLjA0LS4wNTUuMDgyLS4xMDYuMTE5LS4xNzEuMTQtLjIzOC4zMzctLjY0NS4xMy0xLjE3OC0uMDMtLjA4MS0uMTEzLS41NDQtLjI3LTEuMDQ3YTIuMTk4IDIuMTk4IDAgMCAwLS40MjMtLjgzYy0uMjQ2LS4yOTYtLjcyMy0uNTcyLTEuMTkzLS41NzItLjEyMyAwLS4zNDYtLjA1My0uNjc2LS4xNjQtLjMzLS4xMTEtLjc2Ni0uMjc4LTEuMzMyLS4yNzhabTQ1LjExOSA0LjY1N2MtLjU5IDAtMS4wNS4xNzYtMS40MDMuMjk1cy0uNTk3LjE4LS43NDQuMThjLS40ODIgMC0uOTc2LjI3NS0xLjIzLjU4LS4yNTIuMzAzLS4zNTkuNjAyLS40NC44NjYtLjE2NC41MjgtLjI0NiAxLjAyMy0uMjg1IDEuMTI1LS4yMDkuNTQtLjAwNi45NS4xMzcgMS4xOTQuMTQ0LjI0Ni4zMS40Mi40ODYuNTk4LjM1My4zNTMuNzgxLjY4OCAxLjIxMSAxLjAwMy4zNTUuMjYuNzAxLjQ5Ni45OTYuNjk0LS4xOTUuMTU1LS40NTQuMzY3LS45MjguNTI3LS4zNC4xMTUtLjY1Ni4xNDItLjkwOC4wNzgtLjE4NC0uMDQ3LS4zOTEtLjI2MS0uNTk3LS40OTIuMDE2LS4xMjcuMDItLjEzNS4wNDMtLjI5NS4wNi0uNDI1LjE0NS0uOTUyLjE3My0xLjA2NGwtMi4wMDctLjUwMmMtLjA5Mi4zNjgtLjE1NS44MzQtLjIxNyAxLjI3OS0uMDYyLjQ0Ny0uMTA0LjgzMi0uMTA0LjgzMmwtLjA0My4zNjMuMi4zMTNjLjUzMy44MzggMS4yNzcgMS4zNzkgMi4wNCAxLjU3NC43NjUuMTk1IDEuNDg2LjA3MSAyLjA3OS0uMTI5YTYuMSA2LjEgMCAwIDAgMS41MjMtLjc4OWMuMzU4LjI1Mi44ODcuNTczIDEuNTI0Ljc5LjU5My4yIDEuMzEyLjMyMyAyLjA3Ni4xMjguNzY1LS4xOTUgMS41MDktLjczNiAyLjA0My0xLjU3NGwuMi0uMzEzLS4wNDQtLjM2M3MtLjA1MS0uMzg0LS4xMTMtLjgzYy0uMDYxLS40NDYtLjEyMy0uOTEzLS4yMTUtMS4yODFsLTIuMDA4LjUwMmMuMDI4LjExMi4xMjMuNjQuMTgyIDEuMDY0LjAyLjE2LjAxNy4xNjguMDMzLjI5NS0uMjA1LjIzLS40MDUuNDQ1LS41ODguNDkyLS4yNTIuMDY0LS41Ny4wMzctLjkxLS4wNzgtLjQ2LS4xNTYtLjcxMy0uMzY0LS45MDgtLjUyLjMtLjE5OC42NS0uNDM2IDEuMDEzLS43LjQzLS4zMTYuODU4LS42NSAxLjIxMS0xLjAwNGEyLjg5IDIuODkgMCAwIDAgLjQ4NS0uNTk4Yy4xNDQtLjI0NS4zMzgtLjY1NS4xMy0xLjE5NC0uMDQtLjEwMi0uMTIzLS41OTctLjI4Ny0xLjEyNS0uMDgtLjI2NC0uMTgtLjU2My0uNDMxLS44NjctLjI1NC0uMzA0LS43NDctLjU4LTEuMjI5LS41OC0uMTQ2IDAtLjM5LS4wNi0uNzQ0LS4xOC0uMzUzLS4xMi0uODEyLS4yOTQtMS40MDItLjI5NHptMCAyLjA2OGMuMTM3IDAgLjM4MS4wNjIuNzM2LjE4MmE2LjIyIDYuMjIgMCAwIDAgMS4wODIuMjI0Yy4wMTMuMDI4IDAgLjAxLjAxNS4wNi4wNTQuMTc1LjE0MS41MTEuMjE3LjgxNWwtLjAzNS4wMzVhOS4yNTMgOS4yNTMgMCAwIDEtLjk2OS43OTVjLS41MDUuMzctLjc0NC41MS0xLjA0Ni43MDEtLjMtLjE5LS41NC0uMzI5LTEuMDQ3LS43YTkuMjUxIDkuMjUxIDAgMCAxLS45NzEtLjc5NmMtLjAyMi0uMDIzLS4wMTQtLjAyNC0uMDMzLS4wNDUuMDc0LS4yOTguMTU0LS42MzIuMjA3LS44MDQuMDE2LS4wNTMuMDEyLS4wMzMuMDI1LS4wNmE2LjA0MyA2LjA0MyAwIDAgMCAxLjA3NC0uMjI1Yy4zNTUtLjEyLjYwNy0uMTgyLjc0NC0uMTgyeiIvPjxwYXRoIGZpbGw9IiM4OTJjYTAiIGQ9Ik00NS40OTEgMzUuNzc1YTYuMDg0IDYuMDg0IDAgMCAwLTQuMzgyIDEuODY2IDYuMzYxIDYuMzYxIDAgMCAwLTEuNTM1IDIuNTk1IDYuMzg4IDYuMzg4IDAgMCAwLS4yNyAyLjE5OCA2LjM5NyA2LjM5NyAwIDAgMCAyLjcyMSA0LjkyMyA2LjIxMSA2LjIxMSAwIDAgMCAxLjMzNS43MDEgNi4wNTYgNi4wNTYgMCAwIDAgNC41NS0uMTEgNi4wOTUgNi4wOTUgMCAwIDAgMS41MzEtLjk1NCA2LjI5NSA2LjI5NSAwIDAgMCAxLjM2LTEuNjA3IDYuMzk2IDYuMzk2IDAgMCAwIC44MjYtNC4yMzYgNi40MzEgNi40MzEgMCAwIDAtLjgyNy0yLjMxNyA2LjM5NiA2LjM5NiAwIDAgMC0xLjgzNy0xLjk3MyA2LjIxMSA2LjIxMSAwIDAgMC0xLjMzNi0uNyA2LjA0NyA2LjA0NyAwIDAgMC0yLjEzNC0uMzg2em0zMy40NyAwYTYuMTA3IDYuMTA3IDAgMCAwLTIuMTMyLjM4OSA2LjA3NSA2LjA3NSAwIDAgMC0xLjU3Ny44NzggNi4yNzcgNi4yNzcgMCAwIDAtMS40MyAxLjUzOCA2LjM5NiA2LjM5NiAwIDAgMC0uOTMyIDQuOCA2LjQwMSA2LjQwMSAwIDAgMCAxLjY4OCAzLjIgNi4zMyA2LjMzIDAgMCAwIDEuNDI4IDEuMDk2IDYuMTQxIDYuMTQxIDAgMCAwIDEuNzA1LjY0IDYuMDEgNi4wMSAwIDAgMCAxLjg4NS4wOTcgNi4wNiA2LjA2IDAgMCAwIDMuMzEtMS40MjIgNi4zMiA2LjMyIDAgMCAwIDEuNTA2LTEuODcyIDYuMzgzIDYuMzgzIDAgMCAwIC43MTUtMy42NTIgNi40MzEgNi40MzEgMCAwIDAtLjg2NC0yLjYzIDYuMzkgNi4zOSAwIDAgMC0yLjA4Ni0yLjEzOCA2LjE4IDYuMTggMCAwIDAtMS42NjUtLjcyMiA2LjAyMSA2LjAyMSAwIDAgMC0xLjU1LS4yMDJ6bS0zMy40NyAyLjA2aC4wMDJhNC4zMTUgNC4zMTUgMCAwIDEgLjYzLjA0OCA0LjAwNiA0LjAwNiAwIDAgMSAxLjM0My40NjIgNC4xMDggNC4xMDggMCAwIDEgLjk1Ni43MzIgNC4yMjYgNC4yMjYgMCAwIDEgLjg5IDEuMzU4IDQuMzI1IDQuMzI1IDAgMCAxIC4zMjEgMS40NTMgNC41NDcgNC41NDcgMCAwIDEtLjE4MiAxLjUwNCA0LjMyNSA0LjMyNSAwIDAgMS0uNzYxIDEuNDUgNC4yMjYgNC4yMjYgMCAwIDEtMS4yMjQgMS4wMzUgNC4wNjkgNC4wNjkgMCAwIDEtMi42MDMuNDYgNC4wNTYgNC4wNTYgMCAwIDEtMi41NjItMS40OTcgNC4yNTIgNC4yNTIgMCAwIDEtLjc2LTEuNDUgNC4zNzUgNC4zNzUgMCAwIDEtLjE4Ni0xLjI4IDQuNTQgNC41NCAwIDAgMSAuMTg3LTEuMjggNC4zMyA0LjMzIDAgMCAxIC43Ni0xLjQ0OSA0LjIxOCA0LjIxOCAwIDAgMSAxLjIyLTEuMDM0IDQuMDYyIDQuMDYyIDAgMCAxIDEuNzU3LS41MDZjLjA3LS4wMDQuMTQxLS4wMDUuMjEyLS4wMDV6bTMzLjQ3IDBhNC4wODggNC4wODggMCAwIDEgMS4yMy4xOTEgNC4wMzYgNC4wMzYgMCAwIDEgMS40Ljc3OSA0LjE5IDQuMTkgMCAwIDEgMS4wMDggMS4yNTggNC4zIDQuMyAwIDAgMSAuNDc5IDEuNjA3IDQuNDcyIDQuNDcyIDAgMCAxLS40OCAyLjQ4OSA0LjI4IDQuMjggMCAwIDEtMS4wMDcgMS4yNTggNC4xNDYgNC4xNDYgMCAwIDEtMS4wMi42MzYgNC4wMzMgNC4wMzMgMCAwIDEtMy4yMTggMCA0LjA2OCA0LjA2OCAwIDAgMS0xLjMxNS0uOTExIDQuMjI2IDQuMjI2IDAgMCAxLS44ODgtMS4zNTggNC4zMjYgNC4zMjYgMCAwIDEtLjMyLTEuNDUyIDQuNTQgNC41NCAwIDAgMSAuMDc5LTEuMDkgNC4zNzIgNC4zNzIgMCAwIDEgLjYyMy0xLjUzMiA0LjI1IDQuMjUgMCAwIDEgMS4xMTgtMS4xNSA0LjEwNSA0LjEwNSAwIDAgMSAxLjI3OS0uNTkxIDQuMDM1IDQuMDM1IDAgMCAxIDEuMDMyLS4xMzR6TTI5LjY4OCA3NS41MjdhMS4zOSAxLjM5IDAgMCAwLS4xMTIuMDAxYy0yLjI5NS4xMi00LjExMSAyLjA3NC00LjExMSA0LjQxNCAwIDIuNDE2IDEuOTM3IDQuNDIzIDQuMzM2IDQuNDIzczQuMzQ0LTIuMDA3IDQuMzQ0LTQuNDIzLTEuOTQ1LTQuNDE0LTQuMzQ0LTQuNDE0bC0uMTEzLS4wMDF6bTIxLjQxMyAwYTEuNDE1IDEuNDE1IDAgMCAwLS4xMTMuMDAxYy0yLjI5NS4xMi00LjEyIDIuMDc0LTQuMTIgNC40MTQgMCAyLjQxNiAxLjk0NiA0LjQyMyA0LjM0NSA0LjQyMyAyLjQgMCA0LjMzNi0yLjAwNyA0LjMzNi00LjQyM3MtMS45MzctNC40MTQtNC4zMzYtNC40MTRsLS4xMTItLjAwMXptLTIxLjMgMi4wN2MxLjI1NCAwIDIuMjc1IDEuMDIxIDIuMjc1IDIuMzQ1IDAgMS4zMjQtMS4wMjEgMi4zNTQtMi4yNzUgMi4zNTRzLTIuMjY5LTEuMDMtMi4yNjktMi4zNTQgMS4wMTUtMi4zNDUgMi4yNjktMi4zNDV6bTIxLjQxMiAwYzEuMjU0IDAgMi4yNjggMS4wMjEgMi4yNjggMi4zNDUgMCAxLjMyNC0xLjAxNCAyLjM1NC0yLjI2OCAyLjM1NC0xLjI1NCAwLTIuMjc2LTEuMDMtMi4yNzYtMi4zNTRzMS4wMjItMi4zNDUgMi4yNzYtMi4zNDV6bTIyLjk1MyAyLjE5OGMtMi41MjUgMC00LjU3IDIuMTExLTQuNTcgNC42NTYgMCAyLjU0NiAyLjA0NSA0LjY1NyA0LjU3IDQuNjU3IDIuNTI2IDAgNC41Ny0yLjExIDQuNTctNC42NTcgMC0yLjU0NS0yLjA0NC00LjY1Ni00LjU3LTQuNjU2em0yMi44OTMgMGMtMi41MjUgMC00LjU3IDIuMTExLTQuNTcgNC42NTYgMCAyLjU0NiAyLjA0NSA0LjY1NyA0LjU3IDQuNjU3IDIuNTI2IDAgNC41Ny0yLjExIDQuNTctNC42NTcgMC0yLjU0NS0yLjA0NC00LjY1Ni00LjU3LTQuNjU2em0tMjIuOTU3IDIuMDY4LjA2NS4wMDFjMS4zOCAwIDIuNSAxLjEzNCAyLjUgMi41ODcgMCAxLjQ1NC0xLjEyIDIuNTg4LTIuNSAyLjU4OHMtMi41MDItMS4xMzQtMi41MDItMi41ODhjMC0xLjQwOCAxLjA1LTIuNTIgMi4zNzItMi41ODdhLjc2MS43NjEgMCAwIDEgLjA2NSAwem0yMi44OTIgMCAuMDY2LjAwMWMxLjM4IDAgMi41IDEuMTM0IDIuNSAyLjU4NyAwIDEuNDU0LTEuMTIgMi41ODgtMi41IDIuNTg4cy0yLjUwMi0xLjEzNC0yLjUwMi0yLjU4OGMwLTEuNDA4IDEuMDUtMi41MiAyLjM3Mi0yLjU4N2EuNzU3Ljc1NyAwIDAgMSAuMDY0IDB6Ii8+PHBhdGggZmlsbD0iI2ZmZiIgZD0iTTQzLjc3IDM3Ljk3M2EyLjU0MyAyLjY0NCAwIDAgMC0yLjU0NSAyLjY0IDIuNTQzIDIuNjQ0IDAgMCAwIDIuNTQ1IDIuNjUgMi41NDMgMi42NDQgMCAwIDAgMi41NDUtMi42NSAyLjU0MyAyLjY0NCAwIDAgMC0yLjU0NS0yLjY0em0zMy40NTMgMGEyLjU0MyAyLjY0NCAwIDAgMC0yLjU0NSAyLjY0IDIuNTQzIDIuNjQ0IDAgMCAwIDIuNTQ1IDIuNjUgMi41NDMgMi42NDQgMCAwIDAgMi41NDMtMi42NSAyLjU0MyAyLjY0NCAwIDAgMC0yLjU0My0yLjY0ek0yOC43IDc3LjMwM2ExLjYyNiAxLjY4NyAwIDAgMC0xLjYyNSAxLjY4NyAxLjYyNiAxLjY4NyAwIDAgMCAxLjYyNyAxLjY4OCAxLjYyNiAxLjY4NyAwIDAgMCAxLjYyNy0xLjY4OCAxLjYyNiAxLjY4NyAwIDAgMC0xLjYyNy0xLjY4N3ptMjEuNjIuMjQyYTEuNjI2IDEuNjg3IDAgMCAwLTEuNjI2IDEuNjg4IDEuNjI2IDEuNjg3IDAgMCAwIDEuNjI3IDEuNjg3IDEuNjI2IDEuNjg3IDAgMCAwIDEuNjI5LTEuNjg3IDEuNjI2IDEuNjg3IDAgMCAwLTEuNjMtMS42ODh6bTIyLjY3IDQuMDg2YTEuNzM5IDEuODA0IDAgMCAwLTEuNzQgMS43OTkgMS43MzkgMS44MDQgMCAwIDAgMS43NCAxLjgwOCAxLjczOSAxLjgwNCAwIDAgMCAxLjc0LTEuODA4IDEuNzM5IDEuODA0IDAgMCAwLTEuNzQtMS43OTl6bTIzLjI0Ny4yMzJhMS43MzkgMS44MDQgMCAwIDAtMS43NCAxLjgxIDEuNzM5IDEuODA0IDAgMCAwIDEuNzQgMS44IDEuNzM5IDEuODA0IDAgMCAwIDEuNzQtMS44IDEuNzM5IDEuODA0IDAgMCAwLTEuNzQtMS44MXoiLz48L3N2Zz4K';
    }

    //    public function getErrorOutput(): void
    //    {
    //    }
    //
    //    public function getExitCode(): void
    //    {
    //    }
    //
    //    public function getOutput(): void
    //    {
    //    }
    //
    /**
     * @see https://docker-php.readthedocs.io/en/latest/cookbook/container-run/
     */
    public function cmdparams()
    {
        file_put_contents($this->envFile(), $this->job->envFile());

        return 'run --env-file '.$this->envFile().' '.$this->job->application->getDataValue('ociimage');
    }
    //
    //    public function launchJob(): void
    //    {
    //    }
}
