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

//    DataTables\Editor\Field,
//    DataTables\Editor\Format,
//    DataTables\Editor\Mjoin,
//    DataTables\Editor\Options,
//    DataTables\Editor\Upload,
//    DataTables\Editor\Validate,
//    DataTables\Editor\ValidateOptions;

/**
 * MultiFlexi - DataTable feeder.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

// Bypass CSRF protection for DataTables AJAX requests
\define('BYPASS_CSRF_PROTECTION', true);

require_once './init.php';
header('Content-Type: application/json');
// Return DataTables-shaped JSON when unauthenticated to avoid HTML redirects
if (!WebPage::singleton()->isLogged()) {
    // Build a placeholder row where each requested column contains "Unauthenticated"
    $columns = $_REQUEST['columns'] ?? [];
    $row = [];
    if (is_array($columns) && !empty($columns)) {
        foreach ($columns as $col) {
            $key = $col['data'] ?? ($col['name'] ?? null);
            if ($key !== null && $key !== '') {
                $row[$key] = 'Unauthenticated';
            } else {
                $row[] = 'Unauthenticated';
            }
        }
    }

    $response = [
        'draw' => isset($_REQUEST['draw']) ? (int) $_REQUEST['draw'] : 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => !empty($row) ? [$row] : [],
        'error' => 'Unauthenticated',
        'session_expired' => true,
        'redirect' => 'login.php',
    ];
    // Use 200 to keep DataTables happy
    http_response_code(200);
    echo json_encode($response);
    exit;
}

$class = \Ease\WebPage::getRequestValue('class');

if ($class === null || $class === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing class parameter']);

    exit;
}

// Whitelist: only allow DataTable engine classes that have getAllForDataTable
$allowedClasses = [
    \MultiFlexi\CompanyJobLister::class,
    \MultiFlexi\CompanyAppRunTemplateLister::class,
    \MultiFlexi\RunTemplateLister::class,
    \MultiFlexi\CredentialTypeLister::class,
    \MultiFlexi\CredentialLister::class,
    \MultiFlexi\ApplicationLister::class,
    \MultiFlexi\ScheduleLister::class,
    \MultiFlexi\Logger::class,
    \MultiFlexi\Customer::class,
];

if (!\in_array($class, $allowedClasses, true)) {
    http_response_code(403);
    echo json_encode(['error' => 'Class not allowed']);

    exit;
}

if (!method_exists($class, 'getAllForDataTable')) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid data source']);

    exit;
}

/**
 * @var \MultiFlexi\Engine Data Source
 */
$engine = new $class();

// Apply filter parameters for CompanyAppRunTemplateLister
if ($engine instanceof \MultiFlexi\CompanyAppRunTemplateLister) {
    $companyId = \Ease\WebPage::getRequestValue('company_id', 'int');
    $appId = \Ease\WebPage::getRequestValue('app_id', 'int');

    if ($companyId) {
        $engine->setCompany($companyId);
    }

    if ($appId) {
        $engine->setApp($appId);
    }
}

// DataTables PHP library
// include( './lib/DataTables.php' );
// if (WebPage::singleton()->getRequestValue('columns')) {

// Remove CSRF token from request data before passing to database engine
$requestData = $_REQUEST;
unset($requestData['csrf_token']);

echo json_encode($engine->getAllForDataTable($requestData));
// } else {
//    $editor = new DataTableSaver($db, $engine);
//    $editor->process($_POST)->json();
// }
