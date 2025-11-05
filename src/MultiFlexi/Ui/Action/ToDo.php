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

namespace MultiFlexi\Ui\Action;

use MultiFlexi\Ui\CredentialSelect;

/**
 * ToDo Action UI Class.
 *
 * @author vitex
 */
class ToDo extends \MultiFlexi\Action\ToDo
{
    public static function logo(): string
    {
        return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgMTAwNy45MjIgODIxLjgyNyI+PGRlZnM+PHN0eWxlPi5he2ZpbGw6I2ZmZjt9LmJ7ZmlsbDp1cmwoI2EpO30uY3ttYXNrOnVybCgjYik7fS5ke2ZpbGw6dXJsKCNjKTt9LmV7bWFzazp1cmwoI2QpO30uZntmaWxsOnVybCgjZSk7fS5ne2ZpbGw6dXJsKCNmKTt9Lmh7bWFzazp1cmwoI2cpO30uaXtmaWxsOnVybCgjaCk7fS5qe2ZpbGw6IzE5NWFiZDt9Lmt7ZmlsbDp1cmwoI2kpO308L3N0eWxlPjxsaW5lYXJHcmFkaWVudCBpZD0iYSIgeDE9IjcwMC43NjYiIHkxPSI1OTcuMDI0IiB4Mj0iNzQ5Ljc2NSIgeTI9IjU5Ny4wMjQiIGdyYWRpZW50VHJhbnNmb3JtPSJ0cmFuc2xhdGUoODYuNjAzIC0xNDIuMjk2KSBzY2FsZSgwLjg2NyAxLjMwNykiIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIj48c3RvcCBvZmZzZXQ9IjAiIHN0b3Atb3BhY2l0eT0iMC4xMyIvPjxzdG9wIG9mZnNldD0iMC45OTQiIHN0b3Atb3BhY2l0eT0iMCIvPjwvbGluZWFyR3JhZGllbnQ+PG1hc2sgaWQ9ImIiIHg9IjMxNy4xMzciIHk9IjY1MS44MjciIHdpZHRoPSIxNzAiIGhlaWdodD0iMjA1LjIwOCIgbWFza1VuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTguMDY0IC0xMTYuNTIxKSI+PHJlY3QgY2xhc3M9ImEiIHg9IjM2Ny43MDEiIHk9Ijg3MC45NTMiIHdpZHRoPSI4NSIgaGVpZ2h0PSI4NSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNzY2LjA1NCAtMjIuNTEyKSByb3RhdGUoNDUpIi8+PC9nPjwvbWFzaz48cmFkaWFsR3JhZGllbnQgaWQ9ImMiIGN4PSI0MTAuMjAxIiBjeT0iODUzLjM0OSIgcj0iODUiIGdyYWRpZW50VHJhbnNmb3JtPSJ0cmFuc2xhdGUoNzE1LjQ5IC0xNTYuNjM3KSByb3RhdGUoNDUpIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHN0b3Agb2Zmc2V0PSIwLjUiIHN0b3Atb3BhY2l0eT0iMC4xMyIvPjxzdG9wIG9mZnNldD0iMC45OTQiIHN0b3Atb3BhY2l0eT0iMCIvPjwvcmFkaWFsR3JhZGllbnQ+PG1hc2sgaWQ9ImQiIHg9IjgzNy45MjIiIHk9Ijk1LjgzNSIgd2lkdGg9IjIwNS4yMDgiIGhlaWdodD0iMjA1LjIwOCIgbWFza1VuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTguMDY0IC0xMTYuNTIxKSI+PHJlY3QgY2xhc3M9ImEiIHg9Ijg3Ni4wMzgiIHk9IjI2MC4wMTIiIHdpZHRoPSIxNzAiIGhlaWdodD0iODUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE0MjYuNjg1IDExOTUuOTc3KSByb3RhdGUoLTEzNSkiLz48L2c+PC9tYXNrPjxyYWRpYWxHcmFkaWVudCBpZD0iZSIgY3g9IjEwNTEuMTI2IiBjeT0iMTI2NS44NTIiIHI9Ijg1IiBncmFkaWVudFRyYW5zZm9ybT0idHJhbnNsYXRlKDc3MS4wODcgMTg1NC4zOTQpIHJvdGF0ZSgtMTM1KSIgeGxpbms6aHJlZj0iI2MiLz48bGluZWFyR3JhZGllbnQgaWQ9ImYiIHgxPSIxODgwLjgiIHkxPSIzNC4yODYiIHgyPSIxOTI5Ljc5OSIgeTI9IjM0LjI4NiIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgwLjg2NywgMCwgMCwgLTAuNzk2LCAtMTQ0Ni4wMzEsIDc2Ny4xNDcpIiB4bGluazpocmVmPSIjYSIvPjxtYXNrIGlkPSJnIiB4PSItMzUuMjA4IiB5PSIyOTkuNDgyIiB3aWR0aD0iMjA1LjIwOCIgaGVpZ2h0PSIyMDUuMjA4IiBtYXNrVW5pdHM9InVzZXJTcGFjZU9uVXNlIj48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtOC4wNjQgLTExNi41MjEpIj48cmVjdCBjbGFzcz0iYSIgeD0iLTIxLjk4OCIgeT0iNDYzLjY1OSIgd2lkdGg9IjE3MCIgaGVpZ2h0PSI4NSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTMzOS40NTMgMTkyLjgwNykgcm90YXRlKC00NSkiLz48L2c+PC9tYXNrPjxyYWRpYWxHcmFkaWVudCBpZD0iaCIgY3g9IjI3LjYwOCIgY3k9IjIwMDEuMzciIHI9Ijg1IiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDAuNzA3LCAtMC43MDcsIC0wLjcwNywgLTAuNzA3LCAxNDgwLjY2LCAxODU0LjM5NCkiIHhsaW5rOmhyZWY9IiNjIi8+PGxpbmVhckdyYWRpZW50IGlkPSJpIiB4MT0iMzA4LjM3OCIgeTE9IjgxMS42MjkiIHgyPSI5MTkuMzE4IiB5Mj0iMjAwLjY4OSIgZ3JhZGllbnRUcmFuc2Zvcm09InRyYW5zbGF0ZSgtMTc4LjExNyA1ODIuMzA3KSByb3RhdGUoLTQ1KSIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiPjxzdG9wIG9mZnNldD0iMCIgc3RvcC1jb2xvcj0iIzI5ODdlNiIvPjxzdG9wIG9mZnNldD0iMC45OTQiIHN0b3AtY29sb3I9IiM1OGMxZjUiLz48L2xpbmVhckdyYWRpZW50PjwvZGVmcz48dGl0bGU+VG9kbzwvdGl0bGU+PHJlY3QgY2xhc3M9ImIiIHg9IjY5NC40MjIiIHk9IjI2OS43ODUiIHdpZHRoPSI0Mi41IiBoZWlnaHQ9IjczNi41IiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg2NTIuNzEgLTQzNS43MDEpIHJvdGF0ZSg0NSkiLz48ZyBjbGFzcz0iYyI+PGNpcmNsZSBjbGFzcz0iZCIgY3g9IjQwMi4xMzciIGN5PSI3MzYuODI3IiByPSI4NSIvPjwvZz48ZyBjbGFzcz0iZSI+PGNpcmNsZSBjbGFzcz0iZiIgY3g9IjkyMi45MjIiIGN5PSIyMTYuMDQzIiByPSI4NSIvPjwvZz48cmVjdCBjbGFzcz0iZyIgeD0iMTg1LjMwNSIgeT0iNTE1LjYwOCIgd2lkdGg9IjQyLjUiIGhlaWdodD0iNDQ4LjUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDg2Ny43MDUgMTAwMC40MzkpIHJvdGF0ZSgxMzUpIi8+PGcgY2xhc3M9ImgiPjxjaXJjbGUgY2xhc3M9ImkiIGN4PSI4NSIgY3k9IjQxOS42OSIgcj0iODUiLz48L2c+PHJlY3QgY2xhc3M9ImoiIHg9IjE2NC4zNzgiIHk9IjMxOS45ODIiIHdpZHRoPSIyODgiIGhlaWdodD0iNTc2IiByeD0iNDIuNSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTM0Ny42NTEgMjc5LjYwOSkgcm90YXRlKC00NSkiLz48cmVjdCBjbGFzcz0iayIgeD0iNDY5Ljg0OCIgeT0iNzQuMTU5IiB3aWR0aD0iMjg4IiBoZWlnaHQ9Ijg2NCIgcng9IjQyLjUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDUyOS42MzYgLTQwMi4zMjcpIHJvdGF0ZSg0NSkiLz48L3N2Zz4=';
    }

    /**
     * Generate configuration form inputs for ToDo action.
     *
     * @param string $prefix Form field prefix
     *
     * @return \Ease\Embedable Form field(s)
     */
    public function inputs(string $prefix): \Ease\Embedable
    {
        $companyId = 0;

        if (isset($_SESSION) && \array_key_exists('company', $_SESSION)) {
            $companyId = $_SESSION['company'];
        }

        $container = new \Ease\Html\DivTag();

        $cnf = new \MultiFlexi\ActionConfig();
        $actionConfig = $cnf->getModuleConfig('ToDo', 'credential', $prefix, $this->runtemplate)->fetchAll();
        $userId = $cnf->getModuleConfig('ToDo', 'user_id', $prefix, $this->runtemplate)->fetchAll();

        // Office365 Credential Selection
        $container->addItem(new \Ease\TWB4\FormGroup(
            _('Office365 Credential'),
            new CredentialSelect($prefix.'[ToDo][credential]', $companyId, 'Office365'),
            '',
            _('Select Office365 credential for ToDo integration'),
        ));

        // Check if Office365 credentials exist for this company
        $credentialEngine = new \MultiFlexi\Credential(\array_key_exists(0, $actionConfig) && \array_key_exists('value', $actionConfig[0]) ? (int) $actionConfig[0]['value'] : null);
        $office365Credentials = $credentialEngine->getCompanyCredentials($companyId, ['Office365']);

        if (!empty($office365Credentials) && $credentialEngine->getMyKey()) {
            // ToDo List Selection

            $credentialData = $credentialEngine->getData();

            // Validate tenant format
            $tenant = $credentialData['OFFICE365_TENANT'] ? self::getFullTenantDomain($credentialData['OFFICE365_TENANT']) : '';

            if (!self::isValidTenant($tenant)) {
                $fullDomain = self::getFullTenantDomain($tenant);

                if ($fullDomain !== $tenant) {
                    // SharePoint tenant name detected - suggest the full domain
                    $container->addItem(new \Ease\TWB4\Alert(
                        'warning',
                        sprintf(_('SharePoint tenant "%s" detected. For Office365 authentication, please use the full domain: "%s"'), $tenant, $fullDomain),
                    ));
                } else {
                    // Invalid format
                    $container->addItem(new \Ease\TWB4\Alert(
                        'danger',
                        sprintf(_('Invalid Office365 Tenant identifier: "%s". Please use a valid tenant ID (GUID format like "8bc80782-70b2-4c64-a00c-2ea30b7d67d5") or domain name (like "contoso.onmicrosoft.com").'), $tenant),
                    ));
                }

                $container->addItem(new \Ease\TWB4\LinkButton(
                    'credential.php?company_id='.$companyId.'&class=Office365&id='.$credentialEngine->getMyKey(),
                    _('Fix Office365 Credential'),
                    'warning',
                ));

                return $container;
            }

            $credentialData['OFFICE365_TENANT'] = $tenant;

            // Determine authentication method and validate accordingly
            $clientSecret = $credentialData['OFFICE365_CLSECRET'] ?? '';
            $username = $credentialData['OFFICE365_USERNAME'] ?? '';
            $password = $credentialData['OFFICE365_PASSWORD'] ?? '';
            $clientId = $credentialData['OFFICE365_CLIENTID'] ?? '';

            $isClientCredentialsAuth = !empty($clientId) && !empty($clientSecret);
            $isUsernamePasswordAuth = !empty($username) && !empty($password);

            if (!$isClientCredentialsAuth && !$isUsernamePasswordAuth) {
                $container->addItem(new \Ease\TWB4\Alert(
                    'danger',
                    _('Invalid authentication configuration. Please provide either Username/Password OR Client ID/Client Secret.'),
                ));
                $container->addItem(new \Ease\TWB4\LinkButton(
                    'credential.php?company_id='.$companyId.'&class=Office365&id='.$credentialEngine->getMyKey(),
                    _('Fix Office365 Credential'),
                    'danger',
                ));

                return $container;
            }

            if ($isClientCredentialsAuth) {
                // Validate client secret format
                if (self::isClientSecretId($clientSecret)) {
                    $container->addItem(new \Ease\TWB4\Alert(
                        'danger',
                        _('Invalid client secret detected. You are using a Client Secret ID (GUID format) instead of the Client Secret Value. Please go to Azure Portal → App registrations → Your app → Certificates & secrets, and copy the actual secret VALUE (not the ID).'),
                    ));
                    $container->addItem(new \Ease\TWB4\LinkButton(
                        'credential.php?company_id='.$companyId.'&class=Office365&id='.$credentialEngine->getMyKey(),
                        _('Fix Client Secret'),
                        'danger',
                    ));

                    return $container;
                }

                // For client credentials, we need a specific user ID
                if (empty($userId)) {
                    $container->addItem(new \Ease\TWB4\Alert(
                        'warning',
                        _('For Client ID/Secret authentication, you need to provide OFFICE365_USER_ID. This should be the Azure AD User ID (GUID format) of the user whose ToDo lists you want to access.'),
                    ));

                    // Add field for entering User ID
                    $container->addItem(new \Ease\TWB4\FormGroup(
                        _('Office365 User ID'),
                        new \Ease\Html\InputTextTag($prefix.'[ToDo][user_id]', '', [
                            'class' => 'form-control',
                            'placeholder' => _('e.g. 5cded639-0b8d-4abc-8976-d202aa1770fa'),
                            'pattern' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
                            'title' => _('Enter Azure AD User ID in GUID format'),
                        ]),
                        '',
                        _('Azure AD User ID (GUID format) for the user whose ToDo lists you want to access. Required for Client ID/Secret authentication.'),
                    ));

                    $container->addItem(new \Ease\TWB4\LinkButton(
                        'credential.php?company_id='.$companyId.'&class=Office365&id='.$credentialEngine->getMyKey(),
                        _('Edit Credential'),
                        'warning',
                    ));

                    return $container;
                }

                $credentialData['OFFICE365_USER_ID'] = $userId[0]['value'];
            }

            // Show authentication method info
            if ($isClientCredentialsAuth) {
                $container->addItem(new \Ease\TWB4\Alert(
                    'info',
                    _('Using Client ID/Secret authentication (Application permissions). Accessing ToDo lists for user: ').$userId[0]['value'],
                ));
            } elseif ($isUsernamePasswordAuth) {
                $container->addItem(new \Ease\TWB4\Alert(
                    'info',
                    _('Using Username/Password authentication (Delegated permissions). Accessing ToDo lists for: ').$username,
                ));
            }

            $lists = $this->getToDoLists($credentialData);

            // Prepare options for ToDo list selection
            $listOptions = ['' => _('Please select a ToDo list')];

            if (!empty($lists)) {
                foreach ($lists as $list) {
                    $listOptions[$list['id']] = $list['displayName'];
                }
            } else {
                $listOptions = ['' => _('No ToDo lists found or authentication failed')];
            }

            $container->addItem(new \Ease\TWB4\FormGroup(
                _('ToDo List'),
                new \Ease\Html\SelectTag($prefix.'[ToDo][list]', $listOptions, '', ['class' => 'form-control', 'id' => 'todo-list-select']),
                '',
                _('Select which ToDo list to use for task management'),
            ));

            // Task Priority Selection
            $container->addItem(new \Ease\TWB4\FormGroup(
                _('Default Task Priority'),
                new \Ease\Html\SelectTag($prefix.'[ToDo][priority]', [
                    'low' => _('Low'),
                    'normal' => _('Normal'),
                    'high' => _('High'),
                ], 'normal', ['class' => 'form-control']),
                '',
                _('Default priority for created tasks'),
            ));

            // Task Subject Template
            $container->addItem(new \Ease\TWB4\FormGroup(
                _('Task Subject Template'),
                new \Ease\Html\InputTextTag(
                    $prefix.'[ToDo][subject_template]',
                    _('Task: {job_name} - {status}'),
                    ['class' => 'form-control', 'placeholder' => _('Use {job_name}, {status}, {company} placeholders')],
                ),
                '',
                _('Template for task subject. Available placeholders: {job_name}, {status}, {company}'),
            ));

            // Task Body Template
            $container->addItem(new \Ease\TWB4\FormGroup(
                _('Task Body Template'),
                new \Ease\Html\TextareaTag(
                    $prefix.'[ToDo][body_template]',
                    _("Job: {job_name}\nCompany: {company}\nStatus: {status}\nTime: {timestamp}"),
                    ['class' => 'form-control', 'rows' => 4,
                        'placeholder' => _('Use {job_name}, {status}, {company}, {timestamp} placeholders')],
                ),
                '',
                _('Template for task body. Available placeholders: {job_name}, {status}, {company}, {timestamp}'),
            ));
        } else {
            // No credentials available - show create button
            $container->addItem(new \Ease\TWB4\Alert(
                'warning',
                _('No Office365 credentials found. Please create one first.'),
            ));
            $container->addItem(new \Ease\TWB4\LinkButton(
                'credential.php?company_id='.$companyId.'&class=Office365',
                _('Create Office365 Credential'),
                'primary',
            ));
        }

        return $container;
    }

    /**
     * Gets a list of To Do lists from Microsoft Graph API.
     *
     * @param array $credentialData Office365 credential data
     *
     * @return array List of To Do lists or empty array on error
     */
    public function getToDoLists(array $credentialData): array
    {
        // Determine authentication method
        $clientId = $credentialData['OFFICE365_CLIENTID'] ?? '';
        $clientSecret = $credentialData['OFFICE365_CLSECRET'] ?? '';
        $username = $credentialData['OFFICE365_USERNAME'] ?? '';
        $password = $credentialData['OFFICE365_PASSWORD'] ?? '';
        $userId = $credentialData['OFFICE365_USER_ID'] ?? '';

        $isClientCredentialsAuth = !empty($clientId) && !empty($clientSecret);
        $isUsernamePasswordAuth = !empty($username) && !empty($password);

        if ($isClientCredentialsAuth) {
            // Use client credentials flow (application authentication)
            $accessToken = $this->getAccessToken($credentialData);

            if (!$accessToken) {
                $this->addStatusMessage('Failed to get Office365 access token using client credentials', 'error');

                return [];
            }

            // For client credentials flow, we need a specific user ID
            if (empty($userId)) {
                $this->addStatusMessage('Office365 User ID is required for client credentials authentication', 'error');

                return [];
            }

            // First, verify the user exists and we have permission to access their data
            if (!$this->verifyUserAccess($userId, $accessToken)) {
                $this->debugTokenPermissions($accessToken);
                $this->addStatusMessage('Cannot access user data. Please check: 1) User ID is correct, 2) Application has proper Graph API permissions (Tasks.ReadWrite.All, User.Read.All), 3) Admin consent is granted', 'error');
                $this->addStatusMessage('Required Azure App Registration permissions: API Permissions → Microsoft Graph → Application permissions → Tasks.ReadWrite.All, User.Read.All', 'info');

                return [];
            }

            // Use specific user endpoint for application authentication
            $apiUrl = "https://graph.microsoft.com/v1.0/users/{$userId}/todo/lists";
        } elseif ($isUsernamePasswordAuth) {
            // Use username/password flow (delegated authentication)
            $accessToken = $this->getAccessTokenWithUsernamePassword($credentialData);

            if (!$accessToken) {
                $this->addStatusMessage('Failed to get Office365 access token using username/password', 'error');

                return [];
            }

            // Use /me endpoint for delegated authentication
            $apiUrl = 'https://graph.microsoft.com/v1.0/me/todo/lists';
        } else {
            $this->addStatusMessage('Invalid authentication configuration. Provide either Client ID/Secret OR Username/Password', 'error');

            return [];
        }

        // Initialize cURL for getting task lists
        $ch = curl_init();

        // Set cURL parameters for getting lists
        curl_setopt_array($ch, [
            \CURLOPT_URL => $apiUrl,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$accessToken,
                'Content-Type: application/json',
            ],
        ]);

        // Execute HTTP request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Check cURL errors
        if ($curlError) {
            $this->addStatusMessage('cURL error while getting lists: '.$curlError, 'error');

            return [];
        }

        // Check HTTP status code
        if ($httpCode !== 200) {
            // Try to decode error response for better error messages
            $errorData = json_decode($response, true);

            if ($errorData && isset($errorData['error'])) {
                $errorCode = $errorData['error']['code'] ?? $errorData['error'];
                $errorMessage = $errorData['error']['message'] ?? '';
                $requestId = $errorData['error']['innerError']['request-id'] ?? '';

                // Handle specific error cases
                if ($errorCode === 'UnknownError' && empty($errorMessage)) {
                    $this->addStatusMessage('Microsoft Graph API returned UnknownError. This often indicates insufficient permissions or invalid user ID. Request ID: '.$requestId, 'error');
                    $this->addStatusMessage('API URL used: '.$apiUrl, 'debug');
                } elseif ($errorData['error'] === 'invalid_client' || (isset($errorData['error_codes']) && \in_array(7000215, $errorData['error_codes'], true))) {
                    $this->addStatusMessage('Authentication failed: Invalid client secret. Please check your Office365 client secret in credentials.', 'error');
                } else {
                    $errorMsg = $errorData['error_description'] ?? $errorMessage ?? $errorCode;
                    $this->addStatusMessage('Microsoft Graph API error: '.$errorMsg.' (Request ID: '.$requestId.')', 'error');
                }
            } else {
                $this->addStatusMessage('HTTP error while getting lists: '.$httpCode.'. Response: '.substr($response, 0, 500), 'error');
            }

            return [];
        }

        // Decode JSON response
        $data = json_decode($response, true);

        if (!$data || !isset($data['value'])) {
            $this->addStatusMessage('Invalid response while getting lists', 'error');

            return [];
        }

        return $data['value'];
    }

    /**
     * Validates if the tenant identifier is in a correct format.
     *
     * @param string $tenant Tenant identifier to validate
     *
     * @return bool True if tenant format is valid
     */
    private static function isValidTenant(string $tenant): bool
    {
        if (empty($tenant)) {
            return false;
        }

        // Check if it's a valid GUID format
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $tenant)) {
            return true;
        }

        // Check if it's a valid domain name format (contains at least one dot)
        if (filter_var($tenant, \FILTER_VALIDATE_DOMAIN, \FILTER_FLAG_HOSTNAME) && str_contains($tenant, '.')) {
            return true;
        }

        // Special case for "common" tenant
        if ($tenant === 'common') {
            return true;
        }

        // Check if it's a valid SharePoint/OneDrive tenant name (alphanumeric + hyphens, no dots)
        // These should be converted to full domain format for authentication
        if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$/', $tenant) && !strpos($tenant, '.')) {
            // This is likely a SharePoint tenant name that needs .onmicrosoft.com suffix
            return false; // We'll handle this with a helpful error message
        }

        return false;
    }

    /**
     * Converts a SharePoint tenant name to full Office365 domain format.
     *
     * @param string $tenant SharePoint tenant name
     *
     * @return string Full Office365 domain
     */
    private static function getFullTenantDomain(string $tenant): string
    {
        if (!str_contains($tenant, '.') && preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$/', $tenant)) {
            return $tenant.'.onmicrosoft.com';
        }

        return $tenant;
    }

    /**
     * Checks if the provided client secret is actually a client secret ID (GUID format).
     *
     * @param string $secret The client secret to check
     *
     * @return bool True if it appears to be a client secret ID instead of a value
     */
    private static function isClientSecretId(string $secret): bool
    {
        // Client secret IDs are typically GUIDs (same format as tenant IDs)
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $secret) === 1;
    }

    /**
     * Get Office365 access token using username/password (delegated authentication).
     *
     * @param array $credentialData Office365 credential data
     *
     * @return null|string Access token or null on failure
     */
    private function getAccessTokenWithUsernamePassword(array $credentialData): ?string
    {
        $tenant = $credentialData['OFFICE365_TENANT'] ?? '';
        $username = $credentialData['OFFICE365_USERNAME'] ?? '';
        $password = $credentialData['OFFICE365_PASSWORD'] ?? '';
        $clientId = $credentialData['OFFICE365_CLIENTID'] ?? '';

        if (empty($tenant) || empty($username) || empty($password)) {
            $this->addStatusMessage('Missing required Office365 credentials for username/password authentication', 'error');

            return null;
        }

        // Use default client ID if not provided (Microsoft Graph PowerShell client)
        if (empty($clientId)) {
            $clientId = '14d82eec-204b-4c2f-b7e8-296a70dab67e'; // Microsoft Graph PowerShell
        }

        // OAuth2 endpoint for getting access token with username/password
        $tokenUrl = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";

        $postData = [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'username' => $username,
            'password' => $password,
            'scope' => 'https://graph.microsoft.com/Tasks.ReadWrite https://graph.microsoft.com/User.Read',
        ];

        // Initialize cURL for token request
        $ch = curl_init();

        curl_setopt_array($ch, [
            \CURLOPT_URL => $tokenUrl,
            \CURLOPT_POST => true,
            \CURLOPT_POSTFIELDS => http_build_query($postData),
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->addStatusMessage('cURL error while getting token with username/password: '.$curlError, 'error');

            return null;
        }

        if ($httpCode !== 200) {
            // Try to decode error response for better error messages
            $errorData = json_decode($response, true);

            if ($errorData && isset($errorData['error'])) {
                $errorMsg = $errorData['error_description'] ?? $errorData['error'];
                $this->addStatusMessage('Authentication failed with username/password: '.$errorMsg, 'error');
            } else {
                $this->addStatusMessage('HTTP error while getting token with username/password: '.$httpCode, 'error');
            }

            return null;
        }

        $tokenData = json_decode($response, true);

        if (!$tokenData || !isset($tokenData['access_token'])) {
            $this->addStatusMessage('Invalid token response from username/password authentication', 'error');

            return null;
        }

        return $tokenData['access_token'];
    }

    /**
     * Verify that we can access the specified user's data.
     *
     * @param string $userId      The user ID to check
     * @param string $accessToken The access token to use
     *
     * @return bool True if user is accessible, false otherwise
     */
    private function verifyUserAccess(string $userId, string $accessToken): bool
    {
        // Try to get basic user information first
        $userUrl = "https://graph.microsoft.com/v1.0/users/{$userId}";

        $ch = curl_init();
        curl_setopt_array($ch, [
            \CURLOPT_URL => $userUrl,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$accessToken,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $userData = json_decode($response, true);

            if ($userData && isset($userData['id'])) {
                $this->addStatusMessage('Successfully verified access to user: '.($userData['displayName'] ?? $userData['userPrincipalName'] ?? $userId), 'success');

                return true;
            }
        } else {
            $errorData = json_decode($response, true);
            $errorMsg = '';

            if ($errorData && isset($errorData['error'])) {
                $errorCode = $errorData['error']['code'] ?? '';
                $errorMessage = $errorData['error']['message'] ?? '';
                $errorMsg = " Error: {$errorCode} - {$errorMessage}";
            }

            $this->addStatusMessage("Cannot access user information (HTTP {$httpCode}).{$errorMsg}", 'error');
        }

        return false;
    }

    /**
     * Check what permissions the current access token has.
     *
     * @param string $accessToken The access token to check
     */
    private function debugTokenPermissions(string $accessToken): void
    {
        // Try to get token information to see what permissions we have
        $tokenInfoUrl = 'https://graph.microsoft.com/v1.0/me';

        $ch = curl_init();
        curl_setopt_array($ch, [
            \CURLOPT_URL => $tokenInfoUrl,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$accessToken,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $this->addStatusMessage('Token has access to /me endpoint (delegated permissions)', 'debug');
        } else {
            $this->addStatusMessage('Token does not have access to /me endpoint - likely using application permissions', 'debug');
        }

        // Try to get users list to check application permissions
        $usersUrl = 'https://graph.microsoft.com/v1.0/users?$top=1';

        $ch = curl_init();
        curl_setopt_array($ch, [
            \CURLOPT_URL => $usersUrl,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$accessToken,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $this->addStatusMessage('Token has access to /users endpoint (application permissions)', 'debug');
        } else {
            $errorData = json_decode($response, true);
            $errorMsg = '';

            if ($errorData && isset($errorData['error'])) {
                $errorMsg = $errorData['error']['code'] ?? 'Unknown error';
            }

            $this->addStatusMessage('Token does not have access to /users endpoint: '.$errorMsg, 'debug');
        }
    }
}
