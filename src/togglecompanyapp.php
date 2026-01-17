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

require_once './init.php';

WebPage::singleton()->onlyForLogged();

$appId = \Ease\TWB4\WebPage::getRequestValue('app_id', 'int');
$companyId = \Ease\TWB4\WebPage::getRequestValue('company_id', 'int');
$state = \Ease\TWB4\WebPage::getRequestValue('state') === 'true';

$result = 400;

if ($appId && $companyId) {
    $company = new \MultiFlexi\Company($companyId);

    if ($company->getMyKey()) {
        $companyApp = new \MultiFlexi\CompanyApp($company);
        $assignedRaw = $companyApp->getAssigned()->fetchAll('app_id');
        $assigned = empty($assignedRaw) ? [] : array_keys($assignedRaw);

        $isCurrentlyAssigned = \in_array($appId, $assigned, true);

        if ($state && !$isCurrentlyAssigned) {
            // Assign
            $companyApp->assignApps(array_merge($assigned, [$appId]));
            $result = 200;
        } elseif (!$state && $isCurrentlyAssigned) {
            // Unassign
            $newAssigned = array_filter($assigned, static function ($id) use ($appId) {
                return $id !== $appId;
            });
            $companyApp->assignApps($newAssigned);
            $result = 200;
        } else {
            // No change needed
            $result = 200;
        }
    } else {
        $result = 404;
    }
}

http_response_code($result);
header('Content-Type: application/json');
echo json_encode(['result' => $result === 200 ? 'success' : 'error']);
