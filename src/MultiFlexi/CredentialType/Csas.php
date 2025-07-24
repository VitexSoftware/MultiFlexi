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

namespace MultiFlexi\CredentialType;

/**
 * Description of Csas.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class Csas extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    private bool $tokenHelper = false;

    public function __construct()
    {
        parent::__construct();

        $envFile = new \MultiFlexi\ConfigField('CSAS_TOKEN_ID', 'string', _('Token unique identifier'), _('Token UUID'));
        $envFile->setHint('71004963-e3d4-471f-96fc-1aef79d17ec1')->setRequired(true);

        $this->configFieldsInternal->addField($envFile);

        $csasApiKey = new \MultiFlexi\ConfigField('CSAS_API_KEY', 'string', _('CSAS API Key'), _('API Key for CSAS services'));
        $csasApiKey->setHint('c5f91ec2-0237-4af2-9f90-c8366e209ff8')->setValue('c5f91ec2-0237-4af2-9f90-c8366e209ff8')->setManual(false)->setRequired(true);
        $this->configFieldsProvided->addField($csasApiKey);

        $csasSandboxMode = new \MultiFlexi\ConfigField('CSAS_SANDBOX_MODE', 'bool', _('CSAS Sandbox Mode'), _('Enable or disable sandbox mode for CSAS services'));
        $csasSandboxMode->setHint('true')->setValue('true')->setManual(false);
        $this->configFieldsProvided->addField($csasSandboxMode);

        $csasAccessToken = new \MultiFlexi\ConfigField('CSAS_ACCESS_TOKEN', 'string', _('CSAS Access Token'), _('Access token for CSAS services'));
        $csasAccessToken->setHint('ewogIC.....uNjQzWiIKfQ==')->setManual(false)->setRequired(true)->setExpiring(true);
        $this->configFieldsProvided->addField($csasAccessToken);

        // Check if csas-access-token command exists
        $whichCmd = (str_starts_with(strtolower(\PHP_OS), strtolower('WIN'))) ? 'where' : 'which';
        $cmdPath = trim(shell_exec("{$whichCmd} csas-access-token"));

        $this->tokenHelper = (empty($cmdPath) === false);
    }

    public function tokensAvailable(): array
    {
        $tokens = [];
        $subCommand = 'csas-access-token -l -j';
        $this->addStatusMessage(sprintf(_('Obtaining tokens availble: %s'), $subCommand), 'debug');

        $process = popen($subCommand, 'r');
        $tokensJson = '';

        if ($process) {
            while (!feof($process)) {
                $tokensJson .= fread($process, 4096);
            }

            pclose($process);
        }

        if (\Ease\Functions::isJson($tokensJson)) {
            $tokensAvailble = json_decode($tokensJson, true);

            if (empty($tokensAvailble)) {
                $this->addStatusMessage(_('No tokens available'), 'warning');
            } else {
                $tokens = $tokensAvailble;
            }
        }

        return $tokens;
    }

    #[\Override]
    public static function name(): string
    {
        return _('Česká Spořitelna a.s.');
    }

    public static function description(): string
    {
        return _('ČS a.s. / Erste');
    }

    public function prepareConfigForm(): void
    {
        if ($this->tokenHelper === false) {
            $this->addStatusMessage(_('csas-access-token command not found in PATH.'), 'warning');

            // Install  https://github.com/Spoje-NET/csas-authorize
        }

        $tokenTable = new \Ease\TWB4\Table();

        $tokens = $this->tokensAvailable();

        if ($tokens) {
            $tokenTable->addRowHeaderColumns([_('Name'), _('UUID'), _('Expire in days')]);

            foreach ($tokens as $tokenInfo) {
                $tokenExpire = (new \DateTime())->setTimestamp($tokenInfo['expires_at'] ?? 0);
                $expiresInDays = $tokenExpire->diff(new \DateTime())->days;
                $tokenTable->addRowColumns([
                    $tokenInfo['name'],
                    new \Ease\Html\SpanTag(
                        $tokenInfo['uuid'],
                        [
                            'onclick' => "document.getElementsByName('Csas[CSAS_TOKEN_ID]')[0].value='{$tokenInfo['uuid']}';",
                            'style' => 'cursor:pointer;',
                            'title' => _('Click to use this UUID'),
                        ],
                    ),
                    empty($expiresInDays) ? '' : $expiresInDays.' '._('days'),
                ]);
            }
        }

        $this->configFieldsInternal->getFieldByCode('CSAS_TOKEN_ID')->setDescription(_('Tokens available').(string) $tokenTable);
    }

    #[\Override]
    public function fieldsInternal(): \MultiFlexi\ConfigFields
    {
        return $this->configFieldsInternal;
    }

    #[\Override]
    public function fieldsProvided(): \MultiFlexi\ConfigFields
    {
        return $this->configFieldsProvided;
    }

    #[\Override]
    public static function logo(): string
    {
        return 'csas-authorize.svg';
    }

    /**
     * Query CSAS credential values.
     *
     * @param bool $checkOnly If true, only check if token can be obtained (do not populate values)
     */
    #[\Override]
    public function query(bool $checkOnly = false): \MultiFlexi\ConfigFields
    {
        $tokenUuid = $this->configFieldsInternal->getFieldByCode('CSAS_TOKEN_ID')->getValue();

        if ($tokenUuid) {
            $tmpfile = sys_get_temp_dir().'/'.time().'.env';
            $subCommand = 'csas-access-token -t'.$tokenUuid.' -o'.$tmpfile;
            $this->addStatusMessage(sprintf(_('Obtaining fresh token using: %s'), $subCommand), 'debug');
            system('XDEBUG_MODE=off '.$subCommand);
            $envData = EnvFile::readEnvFile($tmpfile);

            if ($checkOnly) {
                // Only check if token was obtained, do not populate values
                if (isset($envData['CSAS_ACCESS_TOKEN']) && !empty($envData['CSAS_ACCESS_TOKEN'])) {
                    $this->addStatusMessage(_('Token successfully obtained.'), 'success');
                } else {
                    $this->addStatusMessage(_('Token could not be obtained.'), 'error');
                }

                unlink($tmpfile);

                return $this->configFieldsProvided;
            }

            foreach (array_keys($this->configFieldsProvided->getFields()) as $configField) {
                if (\array_key_exists($configField, $envData)) {
                    $this->configFieldsProvided->getFieldByCode($configField)->setValue($envData[$configField]);
                    $this->configFieldsProvided->getFieldByCode($configField)->setSource($tokenUuid);
                    $this->configFieldsProvided->getFieldByCode($configField)->setNote('Spoje-NET/csas-authorize');
                }
            }

            unlink($tmpfile);
        } else {
            $this->addStatusMessage(_('Configure the CSAS_TOKEN_ID in Credential setting first'), 'warning');
        }

        return parent::query();
    }
}
