const { By } = require('selenium-webdriver');
const WebDriverHelper = require('./WebDriverHelper');

/**
 * Page Object Model for MultiFlexi RunTemplate management
 */
class RunTemplatePage extends WebDriverHelper {
    constructor() {
        super();
        
        this.selectors = {
            // Navigation
            runTemplatesMenu: By.linkText('Run Templates'),
            createNewButton: By.css('a[href*="runtemplate"]'),
            addButton: By.css('.btn-success'),
            
            // RunTemplate form
            form: By.id('runtemplate-form'),
            nameInput: By.name('name'),
            descriptionInput: By.name('description'),
            applicationSelect: By.name('app'),
            companySelect: By.name('company'),
            
            // Scheduling options
            scheduleTypeSelect: By.name('schedule_type'),
            intervalInput: By.name('interval_value'),
            intervalUnitSelect: By.name('interval_unit'),
            
            // Advanced options
            enabledCheckbox: By.name('enabled'),
            retryCountInput: By.name('retry_count'),
            timeoutInput: By.name('timeout'),
            
            // Environment variables section
            envVarsSection: By.id('env-vars-section'),
            addEnvVarButton: By.id('add-env-var'),
            envVarNameInput: By.css('input[name^="env_name"]'),
            envVarValueInput: By.css('input[name^="env_value"]'),
            
            // Form actions
            saveButton: By.css('button[type="submit"]'),
            cancelButton: By.css('a.btn-secondary'),
            
            // List view
            runTemplatesList: By.id('runtemplates-list'),
            runTemplateRow: By.css('table tbody tr'),
            executeButton: By.css('.btn-primary[title*="Execute"]'),
            editButton: By.css('.btn-warning[title*="Edit"]'),
            deleteButton: By.css('.btn-danger[title*="Delete"]'),
            
            // Execution
            executeModal: By.id('execute-modal'),
            confirmExecuteButton: By.id('confirm-execute'),
            cancelExecuteButton: By.id('cancel-execute'),
            
            // Messages
            successMessage: By.css('.alert-success'),
            errorMessage: By.css('.alert-danger'),
            
            // Job status
            jobsList: By.id('jobs-list'),
            jobStatus: By.css('.job-status'),
            jobOutput: By.css('.job-output')
        };
    }

    /**
     * Navigate to RunTemplates list
     */
    async goToRunTemplates() {
        await this.navigateTo('/runtemplates.php');
        await this.waitForElement(this.selectors.runTemplatesList);
    }

    /**
     * Navigate to create new RunTemplate
     */
    async goToCreateRunTemplate() {
        await this.goToRunTemplates();
        await this.clickElement(this.selectors.createNewButton);
        await this.waitForElement(this.selectors.form);
    }

    /**
     * Create a new RunTemplate
     */
    async createRunTemplate(templateData) {
        await this.goToCreateRunTemplate();
        
        // Fill basic information
        await this.fillInput(this.selectors.nameInput, templateData.name);
        
        if (templateData.description) {
            await this.fillInput(this.selectors.descriptionInput, templateData.description);
        }
        
        // Select application
        if (templateData.application) {
            await this.selectOption(this.selectors.applicationSelect, templateData.application);
        }
        
        // Select company
        if (templateData.company) {
            await this.selectOption(this.selectors.companySelect, templateData.company);
        }
        
        // Configure scheduling
        if (templateData.scheduleType) {
            await this.selectOption(this.selectors.scheduleTypeSelect, templateData.scheduleType);
        }
        
        if (templateData.interval) {
            await this.fillInput(this.selectors.intervalInput, templateData.interval.toString());
        }
        
        if (templateData.intervalUnit) {
            await this.selectOption(this.selectors.intervalUnitSelect, templateData.intervalUnit);
        }
        
        // Set advanced options
        if (templateData.enabled !== undefined) {
            await this.setCheckbox(this.selectors.enabledCheckbox, templateData.enabled);
        }
        
        if (templateData.retryCount !== undefined) {
            await this.fillInput(this.selectors.retryCountInput, templateData.retryCount.toString());
        }
        
        if (templateData.timeout !== undefined) {
            await this.fillInput(this.selectors.timeoutInput, templateData.timeout.toString());
        }
        
        // Add environment variables
        if (templateData.envVars) {
            for (const [name, value] of Object.entries(templateData.envVars)) {
                await this.addEnvironmentVariable(name, value);
            }
        }
        
        // Save the RunTemplate
        await this.clickElement(this.selectors.saveButton);
        
        // Wait for success message or redirect
        await this.driver.sleep(2000);
    }

    /**
     * Create a default test RunTemplate
     */
    async createDefaultRunTemplate() {
        const templateData = {
            name: process.env.TEST_RUNTEMPLATE_NAME || 'Test RunTemplate',
            description: 'Automated test RunTemplate created by Selenium',
            application: 'TestApp', // Should match the test app created in database setup
            scheduleType: 'interval',
            interval: 60,
            intervalUnit: 'minutes',
            enabled: true,
            retryCount: 3,
            timeout: 300,
            envVars: {
                'TEST_VAR': 'test_value',
                'DEBUG': 'true'
            }
        };
        
        await this.createRunTemplate(templateData);
        return templateData;
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

    /**
     * Add environment variable
     */
    async addEnvironmentVariable(name, value) {
        await this.clickElement(this.selectors.addEnvVarButton);
        
        // Find the last added env var inputs
        const nameInputs = await this.driver.findElements(this.selectors.envVarNameInput);
        const valueInputs = await this.driver.findElements(this.selectors.envVarValueInput);
        
        const lastNameInput = nameInputs[nameInputs.length - 1];
        const lastValueInput = valueInputs[valueInputs.length - 1];
        
        await lastNameInput.sendKeys(name);
        await lastValueInput.sendKeys(value);
    }

    /**
     * Execute a RunTemplate
     */
    async executeRunTemplate(templateName) {
        await this.goToRunTemplates();
        
        // Find the RunTemplate row
        const rows = await this.driver.findElements(this.selectors.runTemplateRow);
        
        for (const row of rows) {
            const rowText = await row.getText();
            if (rowText.includes(templateName)) {
                const executeBtn = await row.findElement(this.selectors.executeButton);
                await executeBtn.click();
                break;
            }
        }
        
        // Confirm execution if modal appears
        try {
            await this.findElement(this.selectors.executeModal, 3000);
            await this.clickElement(this.selectors.confirmExecuteButton);
        } catch (error) {
            // Modal might not appear, continue
        }
        
        // Wait for execution to start
        await this.driver.sleep(2000);
    }

    /**
     * Wait for RunTemplate execution to complete
     */
    async waitForExecutionComplete(templateName, timeout = 60000) {
        const startTime = Date.now();
        
        while (Date.now() - startTime < timeout) {
            await this.navigateTo('/jobs.php');
            
            try {
                // Look for job status
                const statusElements = await this.driver.findElements(this.selectors.jobStatus);
                
                for (const status of statusElements) {
                    const statusText = await status.getText();
                    if (statusText.includes('completed') || statusText.includes('success')) {
                        return true;
                    }
                    if (statusText.includes('failed') || statusText.includes('error')) {
                        throw new Error(`RunTemplate execution failed: ${statusText}`);
                    }
                }
            } catch (error) {
                // Continue waiting
            }
            
            await this.driver.sleep(5000); // Wait 5 seconds before checking again
        }
        
        throw new Error(`RunTemplate execution did not complete within ${timeout}ms`);
    }

    /**
     * Get RunTemplate list
     */
    async getRunTemplatesList() {
        await this.goToRunTemplates();
        
        const rows = await this.driver.findElements(this.selectors.runTemplateRow);
        const templates = [];
        
        for (const row of rows) {
            const cells = await row.findElements(By.tagName('td'));
            if (cells.length > 0) {
                const name = await cells[0].getText();
                const status = await cells[1].getText();
                templates.push({ name, status });
            }
        }
        
        return templates;
    }

    /**
     * Delete RunTemplate
     */
    async deleteRunTemplate(templateName) {
        await this.goToRunTemplates();
        
        const rows = await this.driver.findElements(this.selectors.runTemplateRow);
        
        for (const row of rows) {
            const rowText = await row.getText();
            if (rowText.includes(templateName)) {
                const deleteBtn = await row.findElement(this.selectors.deleteButton);
                await deleteBtn.click();
                
                // Confirm deletion
                await this.driver.switchTo().alert().accept();
                break;
            }
        }
        
        await this.driver.sleep(2000);
    }
}

module.exports = RunTemplatePage;