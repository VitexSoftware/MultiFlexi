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

                    $this->addItem([
                        new \Ease\Html\DivTag([
                            new \Ease\Html\StrongTag(_('Issued: ')),
                            $issueDate->format('Y-m-d H:i:s'),
                            ' (',
                            new \Ease\Html\Widgets\LiveAge($issueDate),
                            ')',
                        ], ['class' => 'mb-2']),
                        new \Ease\TWB4\ProgressBar($percent, _('Certificate validity remaining: ').$percent.'%', $progressColor, ['class' => 'mb-2']),
                        new \Ease\Html\DivTag([
                            new \Ease\Html\StrongTag(_('Expires: ')),
                            $expiryDate->format('Y-m-d H:i:s'),
                            ' (',
                            new \Ease\Html\Widgets\LiveAge($expiryDate),
                            ')',
                        ], ['class' => 'mb-2']),
                    ]);
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
                    new \Ease\Html\CodeTag($certFingerprint),
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
        $handle = fopen($filename, 'rb');

        if ($handle && flock($handle, \LOCK_SH)) {
            $json = stream_get_contents($handle);

            if ($json === false) {
                error_log("Failed to read rate limit store from {$filename}");
                // fall through so the lock can be released and the handle closed
                $data = [];
            } else {
                $decoded = json_decode($json, true);

                if ($decoded === null && json_last_error() !== \JSON_ERROR_NONE) {
                    $this->addStatusMessage('Failed to decode rate limit JSON: '.json_last_error_msg(), 'warning');
                    $data = [];
                } else {
                    $data = $decoded ?? [];
                }
            }

            flock($handle, \LOCK_UN);
        }

        if ($handle) {
            fclose($handle);
        }

        return \is_array($data) ? $data : [];
    }
}
