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
$result = false;
$action = \Ease\TWB4\WebPage::getRequestValue('action');

if ($action === 'delete') {
    // Delete environment variable by keyword for current company
    $keyword = \Ease\TWB4\WebPage::getRequestValue('keyword');
    $companyId = $_SESSION['company'] ?? null;

    if (!empty($keyword) && null !== $companyId) {
        $companyEnver = new \MultiFlexi\CompanyEnv(new \MultiFlexi\Company((int) $companyId));

        try {
            $deleted = $companyEnver->deleteFromSQL([
                'company_id' => (int) $companyId,
                'keyword' => $keyword,
            ]);
            http_response_code($deleted ? 200 : 500);
        } catch (\Exception $e) {
            http_response_code(500);
        }
    } else {
        http_response_code(400);
    }
} else {
    // Update environment variable (inline edit)
    // bootstrap-editable sends: pk=<keyword>, name=<field>, value=<new value>
    $pk = \Ease\TWB4\WebPage::getRequestValue('pk');       // keyword string (not numeric ID)
    $name = \Ease\TWB4\WebPage::getRequestValue('name');   // 'keyword' or 'value'
    $value = \Ease\TWB4\WebPage::getRequestValue('value'); // new value
    $companyId = $_SESSION['company'] ?? null;

    if (null !== $pk && null !== $companyId) {
        $companyEnver = new \MultiFlexi\CompanyEnv(new \MultiFlexi\Company((int) $companyId));

        if ($name === 'keyword' && empty($value)) {
            // Empty keyword means delete
            try {
                $deleted = $companyEnver->deleteFromSQL([
                    'company_id' => (int) $companyId,
                    'keyword' => $pk,
                ]);
                http_response_code($deleted ? 201 : 500);
            } catch (\Exception $e) {
                http_response_code(500);
            }
        } else {
            // Update the field (keyword or value) for this company+keyword row
            $conditions = [
                'company_id' => (int) $companyId,
                'keyword' => $pk,
            ];

            if ($name === 'keyword') {
                $updateData = ['keyword' => $value];
            } else {
                $updateData = ['value' => $value];
            }

            try {
                $updated = $companyEnver->updateToSQL($updateData, $conditions);
                http_response_code($updated ? 201 : 400);
            } catch (\Exception $e) {
                http_response_code(500);
            }
        }
    } else {
        http_response_code(404);
    }
}
