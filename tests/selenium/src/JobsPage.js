const { By } = require('selenium-webdriver');
const WebDriverHelper = require('./WebDriverHelper');

/**
 * Page Object Model for MultiFlexi Jobs management
 */
class JobsPage extends WebDriverHelper {
    constructor() {
        super();
        
        this.selectors = {
            // Page elements
            pageTitle: By.css('h1'),
            jobsTable: By.css('#jobs-table'),
            
            // Job list
            jobRows: By.css('tbody tr'),
            jobId: By.css('td:first-child'),
            jobStatus: By.css('.job-status'),
            
            // Job details
            jobOutput: By.css('.job-output'),
            jobLogs: By.css('.job-logs'),
            exitCode: By.css('.exit-code'),
            
            // Actions
            viewJobButton: By.css('a[href*="job.php"]'),
            runJobButton: By.css('.btn-run'),
            stopJobButton: By.css('.btn-stop'),
            
            // Filters
            statusFilter: By.css('select[name="status"]'),
            dateFilter: By.css('input[type="date"]'),
            companyFilter: By.css('select[name="company"]'),
            
            // New Job form
            scheduleJobButton: By.css('a[href*="newjob.php"]'),
            applicationSelect: By.name('app_id'),
            companySelect: By.name('company_id'),
            scheduleButton: By.css('button[type="submit"]')
        };
    }

    /**
     * Navigate to jobs list
     */
    async goToJobs() {
        await this.navigateTo('/joblist.php');
        await this.waitForElement(this.selectors.pageTitle);
    }

    /**
     * Navigate to specific job
     */
    async goToJob(jobId) {
        await this.navigateTo(`/job.php?id=${jobId}`);
        await this.waitForElement(this.selectors.pageTitle);
    }

    /**
     * Navigate to schedule new job
     */
    async goToScheduleJob() {
        await this.navigateTo('/newjob.php');
        await this.waitForElement(this.selectors.applicationSelect);
    }

    /**
     * Get list of jobs
     */
    async getJobsList() {
        await this.goToJobs();
        
        const jobs = [];
        const rows = await this.driver.findElements(this.selectors.jobRows);
        
        for (const row of rows) {
            try {
                const cells = await row.findElements(By.tagName('td'));
                if (cells.length > 0) {
                    const id = await cells[0].getText();
                    const status = cells.length > 1 ? await cells[1].getText() : '';
                    jobs.push({ id, status });
                }
            } catch (error) {
                // Skip problematic rows
            }
        }
        
        return jobs;
    }

    /**
     * Schedule new job
     */
    async scheduleJob(jobData) {
        await this.goToScheduleJob();
        
        if (jobData.application) {
            await this.selectOption(this.selectors.applicationSelect, jobData.application);
        }
        
        if (jobData.company) {
            await this.selectOption(this.selectors.companySelect, jobData.company);
        }
        
        await this.clickElement(this.selectors.scheduleButton);
        await this.driver.sleep(2000);
    }

    /**
     * Get job status
     */
    async getJobStatus(jobId) {
        await this.goToJob(jobId);
        
        try {
            const statusElement = await this.findElement(this.selectors.jobStatus);
            return await statusElement.getText();
        } catch (error) {
            return 'unknown';
        }
    }

    /**
     * Get job output
     */
    async getJobOutput(jobId) {
        await this.goToJob(jobId);
        
        try {
            const outputElement = await this.findElement(this.selectors.jobOutput);
            return await outputElement.getText();
        } catch (error) {
            return '';
        }
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
     * Filter jobs by status
     */
    async filterByStatus(status) {
        await this.goToJobs();
        
        if (await this.elementExists(this.selectors.statusFilter)) {
            await this.selectOption(this.selectors.statusFilter, status);
            await this.driver.sleep(1000);
        }
    }
}

module.exports = JobsPage;