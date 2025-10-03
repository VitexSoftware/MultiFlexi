const { By } = require('selenium-webdriver');
const WebDriverHelper = require('./WebDriverHelper');

/**
 * Page Object Model for MultiFlexi Companies management
 */
class CompaniesPage extends WebDriverHelper {
    constructor() {
        super();
        
        this.selectors = {
            // Page elements
            pageTitle: By.css('h1'),
            companiesTable: By.css('#companies-table'),
            
            // Company list
            companyRows: By.css('tbody tr'),
            companyName: By.css('td:first-child'),
            
            // Actions
            addCompanyButton: By.css('a[href*="company.php"]'),
            editButton: By.css('.btn-edit'),
            deleteButton: By.css('.btn-delete'),
            
            // Company form
            nameInput: By.name('name'),
            logoInput: By.name('logo'),
            enabledCheckbox: By.name('enabled'),
            saveButton: By.css('button[type="submit"]'),
            
            // Company details
            companyLogo: By.css('.company-logo'),
            companyApps: By.css('.company-apps'),
            
            // Navigation
            companySetupLink: By.linkText('Company Setup'),
            companyAppsLink: By.linkText('Company Applications')
        };
    }

    /**
     * Navigate to companies list
     */
    async goToCompanies() {
        await this.navigateTo('/companies.php');
        await this.waitForElement(this.selectors.pageTitle);
    }

    /**
     * Navigate to specific company
     */
    async goToCompany(companyId) {
        await this.navigateTo(`/company.php?id=${companyId}`);
        await this.waitForElement(this.selectors.pageTitle);
    }

    /**
     * Navigate to new company form
     */
    async goToNewCompany() {
        await this.navigateTo('/company.php');
        await this.waitForElement(this.selectors.nameInput);
    }

    /**
     * Get list of companies
     */
    async getCompaniesList() {
        await this.goToCompanies();
        
        const companies = [];
        const rows = await this.driver.findElements(this.selectors.companyRows);
        
        for (const row of rows) {
            try {
                const cells = await row.findElements(By.tagName('td'));
                if (cells.length > 0) {
                    const name = await cells[0].getText();
                    companies.push({ name });
                }
            } catch (error) {
                // Skip problematic rows
            }
        }
        
        return companies;
    }

    /**
     * Create new company
     */
    async createCompany(companyData) {
        await this.goToNewCompany();
        
        await this.fillInput(this.selectors.nameInput, companyData.name);
        
        if (companyData.logo) {
            await this.fillInput(this.selectors.logoInput, companyData.logo);
        }
        
        if (companyData.enabled !== undefined) {
            await this.setCheckbox(this.selectors.enabledCheckbox, companyData.enabled);
        }
        
        await this.clickElement(this.selectors.saveButton);
        await this.driver.sleep(2000);
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

module.exports = CompaniesPage;