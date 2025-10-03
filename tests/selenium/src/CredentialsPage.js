const { By } = require('selenium-webdriver');
const WebDriverHelper = require('./WebDriverHelper');

/**
 * Page Object Model for MultiFlexi Credentials management
 */
class CredentialsPage extends WebDriverHelper {
    constructor() {
        super();
        
        this.selectors = {
            // Page elements
            pageTitle: By.css('h1'),
            credentialsTable: By.css('#credentials-table'),
            
            // Credentials list
            credentialRows: By.css('tbody tr'),
            credentialName: By.css('td:first-child'),
            credentialType: By.css('.credential-type'),
            
            // Actions
            addCredentialButton: By.css('a[href*="credential.php"]'),
            editButton: By.css('.btn-edit'),
            deleteButton: By.css('.btn-delete'),
            
            // Credential form
            nameInput: By.name('name'),
            typeSelect: By.name('credential_type'),
            loginInput: By.name('login'),
            passwordInput: By.name('password'),
            urlInput: By.name('url'),
            enabledCheckbox: By.name('enabled'),
            saveButton: By.css('button[type="submit"]'),
            
            // Credential types
            credentialTypesLink: By.linkText('Credential Types'),
            addTypeButton: By.css('a[href*="credentialtype.php"]')
        };
    }

    /**
     * Navigate to credentials list
     */
    async goToCredentials() {
        await this.navigateTo('/credentials.php');
        await this.waitForElement(this.selectors.pageTitle);
    }

    /**
     * Navigate to specific credential
     */
    async goToCredential(credentialId) {
        await this.navigateTo(`/credential.php?id=${credentialId}`);
        await this.waitForElement(this.selectors.pageTitle);
    }

    /**
     * Navigate to new credential form
     */
    async goToNewCredential() {
        await this.navigateTo('/credential.php');
        await this.waitForElement(this.selectors.nameInput);
    }

    /**
     * Navigate to credential types
     */
    async goToCredentialTypes() {
        await this.navigateTo('/credentialtypes.php');
        await this.waitForElement(this.selectors.pageTitle);
    }

    /**
     * Get list of credentials
     */
    async getCredentialsList() {
        await this.goToCredentials();
        
        const credentials = [];
        const rows = await this.driver.findElements(this.selectors.credentialRows);
        
        for (const row of rows) {
            try {
                const cells = await row.findElements(By.tagName('td'));
                if (cells.length > 0) {
                    const name = await cells[0].getText();
                    const type = cells.length > 1 ? await cells[1].getText() : '';
                    credentials.push({ name, type });
                }
            } catch (error) {
                // Skip problematic rows
            }
        }
        
        return credentials;
    }

    /**
     * Create new credential
     */
    async createCredential(credentialData) {
        await this.goToNewCredential();
        
        await this.fillInput(this.selectors.nameInput, credentialData.name);
        
        if (credentialData.type) {
            await this.selectOption(this.selectors.typeSelect, credentialData.type);
        }
        
        if (credentialData.login) {
            await this.fillInput(this.selectors.loginInput, credentialData.login);
        }
        
        if (credentialData.password) {
            await this.fillInput(this.selectors.passwordInput, credentialData.password);
        }
        
        if (credentialData.url) {
            await this.fillInput(this.selectors.urlInput, credentialData.url);
        }
        
        if (credentialData.enabled !== undefined) {
            await this.setCheckbox(this.selectors.enabledCheckbox, credentialData.enabled);
        }
        
        await this.clickElement(this.selectors.saveButton);
        await this.driver.sleep(2000);
    }

    /**
     * Select option from dropdown
     */
    async selectOption(selectLocator, optionValue) {
        const selectElement = await this.findVisibleElement(selectLocator);
        const optionLocator = By.css(`option[value="${optionValue}"]`);
        const option = await selectElement.findElement(optionLocator);
        await option.click();
    }

    /**
     * Set checkbox state
     */
    async setCheckbox(checkboxLocator, checked) {
        const checkbox = await this.findElement(checkboxLocator);
        const isChecked = await checkbox.isSelected();
        
        if (isChecked !== checked) {
            await checkbox.click();
        }
    }
}

module.exports = CredentialsPage;