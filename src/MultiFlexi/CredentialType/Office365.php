<?php 


/** 
 * TODO:

if (!$this->isValidTenant($tenant)) {
                $fullDomain = $this->getFullTenantDomain($tenant);
                if ($fullDomain !== $tenant) {
                    // SharePoint tenant name detected - suggest the full domain
                    $container->addItem(new \Ease\TWB4\Alert('warning', 
                        sprintf(_('SharePoint tenant "%s" detected. For Office365 authentication, please use the full domain: "%s"'), $tenant, $fullDomain)));
                } else {
                    // Invalid format
                    $container->addItem(new \Ease\TWB4\Alert('danger', 
                        sprintf(_('Invalid Office365 Tenant identifier: "%s". Please use a valid tenant ID (GUID format like "8bc80782-70b2-4c64-a00c-2ea30b7d67d5") or domain name (like "contoso.onmicrosoft.com").'), $tenant)));
                }
                $container->addItem(new \Ease\TWB4\LinkButton(
                    'credential.php?company_id='.$companyId.'&class=Office365&id='.$credentialEngine->getMyKey(), 
                    _('Fix Office365 Credential'), 
                    'warning'
                ));
                return $container;
            }
 */
