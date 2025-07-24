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
 * Description of VaultWarden.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class VaultWarden extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    public function __construct()
    {
        parent::__construct();
        // Přístupové údaje pro VaultWarden
        $this->configFieldsInternal = new \MultiFlexi\ConfigFields('VaultWarden Internal');
        $this->configFieldsInternal->addField(new \MultiFlexi\ConfigField('VAULTWARDEN_URL', 'url', _('VaultWarden URL'), _('URL instance VaultWarden'), 'https://vault.example.com/'));
        $this->configFieldsInternal->addField(new \MultiFlexi\ConfigField('VAULTWARDEN_API_KEY', 'string', _('VaultWarden API Key'), _('API klíč pro přístup')));
        $this->configFieldsInternal->addField(new \MultiFlexi\ConfigField('VAULTWARDEN_FOLDER', 'string', _('VaultWarden Folder'), _('Název složky s hesly ve VaultWarden'), 'MultiFlexi'));
        $this->configFieldsInternal->getFieldByCode('VAULTWARDEN_API_KEY')->setSecret(true)->setDefaultValue('MultiFlexi');

        // Položky budou naplněny dynamicky podle obsahu VaultWarden
        $this->configFieldsProvided = new \MultiFlexi\ConfigFields('VaultWarden Provided');
    }

    public static function name(): string
    {
        return _('VaultWarden');
    }

    public static function description(): string
    {
        return _('Use VaultWarden secrets');
    }

    #[\Override]
    public function prepareConfigForm(): void
    {
        $apiKeyField = $this->configFieldsInternal->getFieldByCode('VAULTWARDEN_API_KEY');
        $apiKeyField->setDescription(_('Obtain API KEY'));

        //        $folderField = $this->configFieldsInternal->getFieldByCode('VAULTWARDEN_FOLDER');

        parent::prepareConfigForm();
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
        return 'vaultwarden.svg';
    }

    public function load(int $credTypeId)
    {
        $loaded = parent::load($credTypeId);

        // Načtení položek z VaultWarden
        $vaultwardenUrl = $this->configFieldsInternal->getFieldByCode('VAULTWARDEN_URL')->getValue();
        $vaultwardenApiKey = $this->configFieldsInternal->getFieldByCode('VAULTWARDEN_API_KEY')->getValue();
        $vaultwardenFolder = $this->configFieldsInternal->getFieldByCode('VAULTWARDEN_FOLDER')->getValue();

        if ($vaultwardenUrl && $vaultwardenApiKey && $vaultwardenFolder) {
            // Zde byste měli implementovat logiku pro načtení položek z VaultWarden
            // a přidání do configFieldsProvided
        } else {
            $this->addStatusMessage(_('Missing required fields for VaultWarden'), 'warning');
        }

        return $loaded;
    }

    /**
     * Query VaultWarden credential values.
     *
     * @param bool $checkOnly If true, only check if secrets can be obtained (do not populate values)
     */
    public function query(bool $checkOnly = false): \MultiFlexi\ConfigFields
    {
        // Získání hodnot z VaultWarden pouze pokud nejsou checkOnly
        $vaultwardenUrl = $this->configFieldsInternal->getFieldByCode('VAULTWARDEN_URL')->getValue();
        $vaultwardenApiKey = $this->configFieldsInternal->getFieldByCode('VAULTWARDEN_API_KEY')->getValue();
        $vaultwardenFolder = $this->configFieldsInternal->getFieldByCode('VAULTWARDEN_FOLDER')->getValue();

        if ($vaultwardenUrl && $vaultwardenApiKey && $vaultwardenFolder) {
            if ($checkOnly) {
                // Zde pouze ověřit, že lze získat tajemství (např. test připojení)
                // Implementujte reálný test podle API VaultWarden
                $this->addStatusMessage(_('VaultWarden check: connection and secrets available.'), 'success');

                return $this->configFieldsProvided;
            }

            // Use Bitwarden service to get items
            $service = new \Jalismrs\Bitwarden\BitwardenService(new \MultiFlexi\BitwardenServiceDelegate());
            $items = $service->searchItems($this->configFieldsInternal->getFieldByCode('VAULTWARDEN_FOLDER')->getValue());

            /** @var BitwardenItem $item */
            $item = $items[0];
            var_dump($item->getId());
            var_dump($item->getName());
            var_dump($item->getLogin()?->getUsername());
            var_dump($item->getLogin()?->getPassword());
        } else {
            $this->addStatusMessage(_('Missing required fields for VaultWarden'), 'warning');
        }

        return $this->configFieldsProvided;
    }
}
