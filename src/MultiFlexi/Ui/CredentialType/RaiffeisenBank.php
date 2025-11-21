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
 * Description of RaiffeisenBank.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class RaiffeisenBank extends \MultiFlexi\Ui\CredentialFormHelperPrototype
{
    public function finalize(): void
    {
        $certFileField = $this->credential->getFields()->getFieldByCode('CERT_FILE');
        $certPassField = $this->credential->getFields()->getFieldByCode('CERT_PASS');
        $certFile = $certFileField ? $certFileField->getValue() : null;
        $certPass = $certPassField ? $certPassField->getValue() : '';

        if (empty($certFile) === false) {
            if (is_readable($certFile)) {
                $certData = @file_get_contents($certFile);
                $notBefore = null;
                $notAfter = null;
                $errorMessage = null;

                if (str_contains($certData, 'BEGIN CERTIFICATE')) {
                    // PEM format certificate
                    $x509 = openssl_x509_parse($certData);

                    if ($x509 && isset($x509['validFrom_time_t'], $x509['validTo_time_t'])) {
                        $notBefore = $x509['validFrom_time_t'];
                        $notAfter = $x509['validTo_time_t'];
                    } else {
                        $errorMessage = _('Failed to parse PEM certificate');
                    }
                } else {
                    // PKCS12 format certificate
                    $certs = [];

                    if (openssl_pkcs12_read($certData, $certs, $certPass)) {
                        if (isset($certs['cert'])) {
                            $x509 = openssl_x509_parse($certs['cert']);

                            if ($x509 && isset($x509['validFrom_time_t'], $x509['validTo_time_t'])) {
                                $notBefore = $x509['validFrom_time_t'];
                                $notAfter = $x509['validTo_time_t'];
                            } else {
                                $errorMessage = _('Failed to parse certificate from PKCS12');
                            }
                        } else {
                            $errorMessage = _('No certificate found in PKCS12 file');
                        }
                    } else {
                        // Get OpenSSL error for better diagnostics
                        $opensslError = '';

                        while ($msg = openssl_error_string()) {
                            $opensslError .= $msg.' ';
                        }

                        if (str_contains($opensslError, 'mac verify failure')) {
                            $errorMessage = _('Wrong certificate password - MAC verification failed. Please check CERT_PASS credential.');
                        } else {
                            $errorMessage = _('Failed to read PKCS12 certificate').': '.trim($opensslError);
                        }
                    }
                }

                if ($notBefore && $notAfter) {
                    $now = time();
                    $total = $notAfter - $notBefore;
                    $remaining = $notAfter - $now;
                    $percent = max(0, min(100, round(($remaining / $total) * 100)));
                    $issueDate = (new \DateTime())->setTimestamp($notBefore);
                    $expiryDate = (new \DateTime())->setTimestamp($notAfter);

                    // Color code based on remaining validity
                    $progressColor = 'bg-success';

                    if ($percent < 20) {
                        $progressColor = 'bg-danger';
                    } elseif ($percent < 50) {
                        $progressColor = 'bg-warning';
                    }

                    // Create certificate info panel
                    $certPanel = new \Ease\TWB4\Panel(_('Certificate Information'), 'default');

                    // Subject (Certificate holder) information
                    if (isset($x509['subject'])) {
                        $subjectDiv = new \Ease\Html\DivTag(null, ['class' => 'mb-3']);
                        $subjectDiv->addItem(new \Ease\Html\H5Tag('📄 '._('Certificate Holder')));
                        $subjectList = new \Ease\Html\DlTag(null, ['class' => 'row']);

                        if (isset($x509['subject']['CN'])) {
                            $subjectList->addItem(new \Ease\Html\DtTag(_('Common Name (CN)'), ['class' => 'col-sm-4']));
                            $subjectList->addItem(new \Ease\Html\DdTag($x509['subject']['CN'], ['class' => 'col-sm-8']));
                        }
                        if (isset($x509['subject']['O'])) {
                            $subjectList->addItem(new \Ease\Html\DtTag(_('Organization (O)'), ['class' => 'col-sm-4']));
                            $subjectList->addItem(new \Ease\Html\DdTag($x509['subject']['O'], ['class' => 'col-sm-8']));
                        }
                        if (isset($x509['subject']['organizationIdentifier'])) {
                            $subjectList->addItem(new \Ease\Html\DtTag(_('Organization ID'), ['class' => 'col-sm-4']));
                            $subjectList->addItem(new \Ease\Html\DdTag($x509['subject']['organizationIdentifier'], ['class' => 'col-sm-8']));
                        }

                        $subjectDiv->addItem($subjectList);
                        $certPanel->addItem($subjectDiv);
                    }

                    // Issuer information
                    if (isset($x509['issuer'])) {
                        $issuerDiv = new \Ease\Html\DivTag(null, ['class' => 'mb-3']);
                        $issuerDiv->addItem(new \Ease\Html\H5Tag('🏦 '._('Issuer')));
                        $issuerList = new \Ease\Html\DlTag(null, ['class' => 'row']);

                        if (isset($x509['issuer']['CN'])) {
                            $issuerList->addItem(new \Ease\Html\DtTag(_('Common Name (CN)'), ['class' => 'col-sm-4']));
                            $issuerList->addItem(new \Ease\Html\DdTag($x509['issuer']['CN'], ['class' => 'col-sm-8']));
                        }
                        if (isset($x509['issuer']['O'])) {
                            $issuerList->addItem(new \Ease\Html\DtTag(_('Organization (O)'), ['class' => 'col-sm-4']));
                            $issuerList->addItem(new \Ease\Html\DdTag($x509['issuer']['O'], ['class' => 'col-sm-8']));
                        }
                        if (isset($x509['issuer']['L'])) {
                            $issuerList->addItem(new \Ease\Html\DtTag(_('Location (L)'), ['class' => 'col-sm-4']));
                            $issuerList->addItem(new \Ease\Html\DdTag($x509['issuer']['L'], ['class' => 'col-sm-8']));
                        }
                        if (isset($x509['issuer']['C'])) {
                            $issuerList->addItem(new \Ease\Html\DtTag(_('Country (C)'), ['class' => 'col-sm-4']));
                            $issuerList->addItem(new \Ease\Html\DdTag($x509['issuer']['C'], ['class' => 'col-sm-8']));
                        }

                        $issuerDiv->addItem($issuerList);
                        $certPanel->addItem($issuerDiv);
                    }

                    // Validity and technical information
                    $technicalDiv = new \Ease\Html\DivTag(null, ['class' => 'mb-3']);
                    $technicalDiv->addItem(new \Ease\Html\H5Tag('🔐 '._('Technical Details')));
                    $technicalList = new \Ease\Html\DlTag(null, ['class' => 'row']);

                    // Serial number
                    if (isset($x509['serialNumberHex'])) {
                        $serialHex = $x509['serialNumberHex'];
                        $serialDec = isset($x509['serialNumber']) ? $x509['serialNumber'] : hexdec($serialHex);
                        
                        $technicalList->addItem(new \Ease\Html\DtTag(_('Serial Number'), ['class' => 'col-sm-4']));
                        $technicalList->addItem(new \Ease\Html\DdTag([
                            new \Ease\Html\DivTag([
                                new \Ease\Html\SmallTag('HEX: ', ['class' => 'text-muted']),
                                new \Ease\Html\SmallTag($serialHex, ['class' => 'font-monospace']),
                            ]),
                            new \Ease\Html\DivTag([
                                new \Ease\Html\SmallTag('DEC: ', ['class' => 'text-muted']),
                                new \Ease\Html\SmallTag(new \Ease\Html\StrongTag((string)$serialDec), ['class' => 'font-monospace']),
                            ]),
                        ], ['class' => 'col-sm-8']));
                    }

                    // Signature algorithm
                    if (isset($x509['signatureTypeLN'])) {
                        $technicalList->addItem(new \Ease\Html\DtTag(_('Signature Algorithm'), ['class' => 'col-sm-4']));
                        $technicalList->addItem(new \Ease\Html\DdTag($x509['signatureTypeLN'], ['class' => 'col-sm-8']));
                    }

                    // Version
                    if (isset($x509['version'])) {
                        $technicalList->addItem(new \Ease\Html\DtTag(_('Version'), ['class' => 'col-sm-4']));
                        $technicalList->addItem(new \Ease\Html\DdTag('v'.($x509['version'] + 1), ['class' => 'col-sm-8']));
                    }

                    $technicalDiv->addItem($technicalList);
                    $certPanel->addItem($technicalDiv);

                    // Validity period
                    $validityDiv = new \Ease\Html\DivTag(null, ['class' => 'mb-2']);
                    $validityDiv->addItem(new \Ease\Html\H5Tag('📅 '._('Validity Period')));

                    $validityDiv->addItem(new \Ease\Html\DivTag([
                        new \Ease\Html\StrongTag(_('Issued: ')),
                        $issueDate->format('Y-m-d H:i:s'),
                        ' (',
                        new \Ease\Html\Widgets\LiveAge($issueDate),
                        ')',
                    ], ['class' => 'mb-2']));

                    $validityDiv->addItem(new \Ease\TWB4\ProgressBar(
                        $percent,
                        _('Certificate validity remaining: ').$percent.'%',
                        $progressColor,
                        ['class' => 'mb-2']
                    ));

                    $validityDiv->addItem(new \Ease\Html\DivTag([
                        new \Ease\Html\StrongTag(_('Expires: ')),
                        $expiryDate->format('Y-m-d H:i:s'),
                        ' (',
                        new \Ease\Html\Widgets\LiveAge($expiryDate),
                        ')',
                    ], ['class' => 'mb-2']));

                    $certPanel->addItem($validityDiv);

                    $this->addItem($certPanel);
                } elseif ($errorMessage) {
                    $this->addItem(new \Ease\TWB4\Alert('danger', $errorMessage));
                } else {
                    $this->addItem(new \Ease\TWB4\Alert('warning', _('Could not extract certificate validity information')));
                }
            } else {
                $this->addItem(new \Ease\TWB4\Alert('danger', sprintf(_('Cannot read Certificate file %s '), $certFile)));
            }
        } else {
            $this->addItem(new \Ease\TWB4\Alert('danger', _('Certificate not set')));
        }

        // Rate limits are tracked per certificate fingerprint (not per X-IBM-Client-Id)
        // since v1.5.1 of php-vitexsoftware-rbczpremiumapi
        $ratesFile = sys_get_temp_dir().'/rbczpremiumapi_rates.json';
        $rates = $this->getJsonRates($ratesFile);

        // Calculate certificate fingerprint to find rate limit data
        $certFingerprint = null;

        if ($certFile && is_readable($certFile) && $certData) {
            $certs = [];

            if (openssl_pkcs12_read($certData, $certs, $certPass)) {
                if (isset($certs['cert'])) {
                    $certResource = openssl_x509_read($certs['cert']);

                    if ($certResource !== false) {
                        $certFingerprint = strtolower(openssl_x509_fingerprint($certResource, 'sha1'));
                    }
                }
            }
        }

        if ($certFingerprint && \array_key_exists($certFingerprint, $rates)) {
            $clientRates = $rates[$certFingerprint];
            $this->addItem(new \Ease\Html\DivTag([
                new \Ease\Html\StrongTag(_('Rate Limits:')),
                new \Ease\Html\UlTag([
                    new \Ease\Html\LiTag(sprintf(_('Per Second: %d remaining (resets at %s)'), $clientRates['second']['remaining'], (new \DateTime())->setTimestamp($clientRates['second']['timestamp'])->format('Y-m-d H:i:s'))),
                    new \Ease\Html\LiTag(sprintf(_('Per Day: %d remaining (resets at %s)'), $clientRates['day']['remaining'], (new \DateTime())->setTimestamp($clientRates['day']['timestamp'])->format('Y-m-d H:i:s'))),
                ]),
                new \Ease\Html\SmallTag([
                    _('Certificate fingerprint: '),
                    new \Ease\Html\SpanTag($certFingerprint, ['class' => 'font-monospace']),
                ], ['class' => 'text-muted']),
            ]));
        } elseif ($certFingerprint) {
            $this->addStatusMessage(sprintf(_('No rate limit data found for this certificate (fingerprint: %s). Data will be available after first API call.'), substr($certFingerprint, 0, 16).'...'), 'info');
        } else {
            $this->addStatusMessage(_('Cannot calculate certificate fingerprint to check rate limits'), 'warning');
        }

        parent::finalize();
    }

    private function getJsonRates(string $filename): array
    {
        // Return empty array if file doesn't exist yet
        if (!file_exists($filename)) {
            return [];
        }

        $handle = @fopen($filename, 'rb');

        if ($handle === false) {
            // File exists but cannot be opened
            return [];
        }

        $data = [];

        if (flock($handle, \LOCK_SH)) {
            $json = stream_get_contents($handle);

            if ($json === false) {
                error_log("Failed to read rate limit store from {$filename}");
            } else {
                $decoded = json_decode($json, true);

                if ($decoded === null && json_last_error() !== \JSON_ERROR_NONE) {
                    $this->addStatusMessage('Failed to decode rate limit JSON: '.json_last_error_msg(), 'warning');
                } else {
                    $data = $decoded ?? [];
                }
            }

            flock($handle, \LOCK_UN);
        }

        fclose($handle);

        return \is_array($data) ? $data : [];
    }
}
