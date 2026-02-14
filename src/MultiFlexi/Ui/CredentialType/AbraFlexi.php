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

namespace MultiFlexi\Ui\CredentialType;

/**
 * Description of AbraFlexi.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class AbraFlexi extends \MultiFlexi\Ui\CredentialFormHelperPrototype
{
    public function finalize(): void
    {
        $abraFlexiUrlField = $this->credential->getFields()->getFieldByCode('ABRAFLEXI_URL');
        $abraFlexiLoginField = $this->credential->getFields()->getFieldByCode('ABRAFLEXI_LOGIN');
        $abraFlexiPasswordField = $this->credential->getFields()->getFieldByCode('ABRAFLEXI_PASSWORD');
        $abraFlexiCompanyField = $this->credential->getFields()->getFieldByCode('ABRAFLEXI_COMPANY');

        $url = $abraFlexiUrlField ? $abraFlexiUrlField->getValue() : '';
        $login = $abraFlexiLoginField ? $abraFlexiLoginField->getValue() : '';
        $password = $abraFlexiPasswordField ? $abraFlexiPasswordField->getValue() : '';
        $company = $abraFlexiCompanyField ? $abraFlexiCompanyField->getValue() : '';

        if (empty($url)) {
            $this->addItem(new \Ease\TWB4\Alert('danger', _('AbraFlexi URL is not set')));

            return;
        }

        // Step 1: Check if server is reachable
        $serverCheck = self::checkServerReachable($url);

        if (!$serverCheck['success']) {
            $this->addItem(new \Ease\TWB4\Alert('danger', sprintf(_('AbraFlexi server is not reachable: %s'), $serverCheck['message'])));

            return;
        }

        $this->addItem(new \Ease\TWB4\Alert('success', _('AbraFlexi server is reachable')));

        // Display SSL warnings if any
        if (!empty($serverCheck['warnings'])) {
            foreach ($serverCheck['warnings'] as $warning) {
                $this->addItem(new \Ease\TWB4\Alert('warning', sprintf(_('SSL Warning: %s'), $warning)));
            }
        }

        // Step 2: Check authentication and list companies
        $companiesCheck = self::getCompaniesList($url, $login, $password);

        if (!$companiesCheck['success']) {
            $this->addItem(new \Ease\TWB4\Alert('danger', sprintf(_('Authentication failed or cannot list companies: %s'), $companiesCheck['message'])));

            return;
        }

        $companies = $companiesCheck['companies'];
        $this->addItem(new \Ease\TWB4\Alert('success', sprintf(_('Successfully authenticated. Found %d companies.'), \count($companies))));

        // Display SSL warnings from authentication check if any
        if (!empty($companiesCheck['warnings'])) {
            foreach ($companiesCheck['warnings'] as $warning) {
                $this->addItem(new \Ease\TWB4\Alert('warning', sprintf(_('SSL Warning: %s'), $warning)));
            }
        }

        // Display companies list
        $companiesPanel = new \Ease\TWB4\Panel(_('Available Companies'), 'info');
        $companiesList = new \Ease\Html\UlTag();

        foreach ($companies as $companyData) {
            $companyName = $companyData['nazev'];
            $companyCode = $companyData['dbNazev'];
            $companiesList->addItem(new \Ease\Html\LiTag($companyName.' ('.$companyCode.')'));
        }

        $companiesPanel->addItem($companiesList);
        $this->addItem($companiesPanel);

        // Step 3: Check if specified company exists
        if (empty($company)) {
            $this->addItem(new \Ease\TWB4\Alert('warning', _('Company code is not set')));
        } else {
            $companyExists = false;

            foreach ($companies as $companyData) {
                $companyCode = $companyData['dbNazev'];

                if ($companyCode === $company) {
                    $companyExists = true;

                    break;
                }
            }

            if ($companyExists) {
                $this->addItem(new \Ease\TWB4\Alert('success', sprintf(_('Company "%s" is available'), $company)));
            } else {
                $this->addItem(new \Ease\TWB4\Alert('danger', sprintf(_('Company "%s" is not found in available companies'), $company)));
            }
        }

        parent::finalize();
    }

    /**
     * @return array{success: bool, message: string, warnings: array<string>}
     */
    private static function checkServerReachable(string $url): array
    {
        $ch = curl_init();
        curl_setopt($ch, \CURLOPT_URL, rtrim($url, '/').'/start');
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, true); // Enable SSL verification
        curl_setopt($ch, \CURLOPT_SSL_VERIFYHOST, 2);   // Verify host name
        curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true); // Follow redirects
        curl_setopt($ch, \CURLOPT_MAXREDIRS, 5); // Limit redirects

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $effectiveUrl = curl_getinfo($ch, \CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        $warnings = [];

        if ($error) {
            // Check for SSL-related errors
            if (str_contains($error, 'SSL certificate problem')) {
                if (str_contains($error, 'certificate has expired')) {
                    $warnings[] = _('SSL certificate has expired');
                } elseif (str_contains($error, 'certificate verify failed')) {
                    $warnings[] = _('SSL certificate verification failed');
                } elseif (str_contains($error, 'self-signed certificate')) {
                    $warnings[] = _('Server uses self-signed SSL certificate');
                } else {
                    $warnings[] = _('SSL certificate issue: ').$error;
                }
                // Continue despite SSL warnings
            } elseif (str_contains($error, 'SSL: no alternative certificate subject name matches target host name')
                     || str_contains($error, 'certificate subject name')) {
                $warnings[] = _('SSL certificate is not valid for this domain');
                // Continue despite domain mismatch warning
            } else {
                // Other connection errors are failures
                return ['success' => false, 'message' => $error, 'warnings' => $warnings];
            }
        }

        // Accept 200-399 as success (including redirects which indicate server is alive)
        if ($httpCode >= 200 && $httpCode < 400) {
            return ['success' => true, 'message' => '', 'warnings' => $warnings];
        }

        return ['success' => false, 'message' => 'HTTP '.$httpCode, 'warnings' => $warnings];
    }

    /**
     * @return array{success: bool, message: string, companies: array<array{id?: string, name?: string, dbNazev?: string, dbKod?: string}>, warnings: array<string>}
     */
    private static function getCompaniesList(string $url, string $login, string $password): array
    {
        $ch = curl_init();
        curl_setopt($ch, \CURLOPT_URL, rtrim($url, '/').'/c.json');
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, true); // Enable SSL verification
        curl_setopt($ch, \CURLOPT_SSL_VERIFYHOST, 2);   // Verify host name
        curl_setopt($ch, \CURLOPT_HTTPAUTH, \CURLAUTH_BASIC);
        curl_setopt($ch, \CURLOPT_USERPWD, $login.':'.$password);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $warnings = [];

        if ($error) {
            // Check for SSL-related errors
            if (str_contains($error, 'SSL certificate problem')) {
                if (str_contains($error, 'certificate has expired')) {
                    $warnings[] = _('SSL certificate has expired');
                } elseif (str_contains($error, 'certificate verify failed')) {
                    $warnings[] = _('SSL certificate verification failed');
                } elseif (str_contains($error, 'self-signed certificate')) {
                    $warnings[] = _('Server uses self-signed SSL certificate');
                } else {
                    $warnings[] = _('SSL certificate issue: ').$error;
                }
                // Continue despite SSL warnings for authentication check
            } elseif (str_contains($error, 'SSL: no alternative certificate subject name matches target host name')
                     || str_contains($error, 'certificate subject name')) {
                $warnings[] = _('SSL certificate is not valid for this domain');
                // Continue despite domain mismatch warning
            } else {
                // Other connection errors are failures
                return ['success' => false, 'message' => $error, 'companies' => [], 'warnings' => $warnings];
            }
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'message' => 'HTTP '.$httpCode, 'companies' => [], 'warnings' => $warnings];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== \JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Invalid JSON response', 'companies' => [], 'warnings' => $warnings];
        }

        if (!isset($data['companies']) || !\is_array($data['companies'])) {
            return ['success' => false, 'message' => 'No companies data in response', 'companies' => [], 'warnings' => $warnings];
        }

        return ['success' => true, 'message' => '', 'companies' => \Ease\Functions::reindexArrayBy($data['companies']['company'], 'dbNazev'), 'warnings' => $warnings];
    }
}
