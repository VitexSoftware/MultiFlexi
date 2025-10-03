const { By } = require('selenium-webdriver');
const WebDriverHelper = require('./WebDriverHelper');

/**
 * Page Object Model for MultiFlexi Applications management
 */
class ApplicationsPage extends WebDriverHelper {
    constructor() {
        super();
        
        this.selectors = {
            // Page elements
            pageTitle: By.css('h1'),
            applicationsTable: By.css('#applications-table, .applications-list'),
            
            // Application list
            appRows: By.css('tbody tr'),
            appName: By.css('td:first-child'),
            appStatus: By.css('.app-status'),
            
            // Actions
            addAppButton: By.css('a[href*="app.php"]'),
            editButton: By.css('.btn-edit'),
            deleteButton: By.css('.btn-delete'),
            
            // Application details
            appLogo: By.css('.app-logo'),
            appDescription: By.css('.app-description'),
            appVersion: By.css('.app-version'),
            
            // Filters and search
            searchInput: By.css('input[type="search"]'),
            statusFilter: By.css('select[name="status"]'),
            
            // Application form
            nameInput: By.name('name'),
            descriptionInput: By.name('description'),
            executableInput: By.name('executable'),
            homepageInput: By.name('homepage'),
            versionInput: By.name('version'),
            enabledCheckbox: By.name('enabled'),
            saveButton: By.css('button[type="submit"]')
        };
    }

    /**
     * Navigate to applications list
     */
    async goToApplications() {
        await this.navigateTo('/apps.php');
        await this.waitForElement(this.selectors.pageTitle);
    }

    /**
     * Navigate to specific application
     */
    async goToApplication(appId) {
        await this.navigateTo(`/app.php?id=${appId}`);
        await this.waitForElement(this.selectors.pageTitle);
    }

    /**
     * Navigate to new application form
     */
    async goToNewApplication() {
        await this.navigateTo('/app.php');
        await this.waitForElement(this.selectors.nameInput);
    }

    /**
     * Get list of applications
     */
    async getApplicationsList() {
        await this.goToApplications();
        
        const apps = [];
        const rows = await this.driver.findElements(this.selectors.appRows);
        
        for (const row of rows) {
            try {
                const cells = await row.findElements(By.tagName('td'));
                if (cells.length > 0) {
                    const name = await cells[0].getText();
                    apps.push({ name });
                }
            } catch (error) {
                // Skip problematic rows
            }
        }
        
        return apps;
    }

    /**
     * Create new application
     */
    async createApplication(appData) {
        await this.goToNewApplication();
        
        await this.fillInput(this.selectors.nameInput, appData.name);
        
        if (appData.description) {
            await this.fillInput(this.selectors.descriptionInput, appData.description);
        }
        
        if (appData.executable) {
            await this.fillInput(this.selectors.executableInput, appData.executable);
        }
        
        if (appData.homepage) {
            await this.fillInput(this.selectors.homepageInput, appData.homepage);
        }
        
        if (appData.version) {
            await this.fillInput(this.selectors.versionInput, appData.version);
        }
        
        if (appData.enabled !== undefined) {
            await this.setCheckbox(this.selectors.enabledCheckbox, appData.enabled);
        }
        
        await this.clickElement(this.selectors.saveButton);
        await this.driver.sleep(2000);
    }

    /**
     * Search for application
     */
    async searchApplication(searchTerm) {
        await this.goToApplications();
        
        if (await this.elementExists(this.selectors.searchInput)) {
            await this.fillInput(this.selectors.searchInput, searchTerm);
            await this.driver.sleep(1000);
        }
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

module.exports = ApplicationsPage;