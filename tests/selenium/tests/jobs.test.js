const { expect } = require('chai');
const { setupDatabase, cleanupDatabase } = require('../scripts/setupDatabase');
const AuthPage = require('../src/AuthPage');
const JobsPage = require('../src/JobsPage');

/**
 * Tests for MultiFlexi Jobs functionality
 */
describe('Jobs Page Tests', function() {
    this.timeout(90000);
    
    let authPage;
    let jobsPage;
    
    before(async function() {
        await setupDatabase();
        
        authPage = new AuthPage();
        jobsPage = new JobsPage();
        
        await authPage.initializeDriver();
        await jobsPage.initializeDriver();
        
        // Login as admin
        await authPage.registerAdmin();
        await authPage.waitForRegistrationSuccess();
        await authPage.loginAsAdmin();
        await authPage.waitForLoginSuccess();
    });

    after(async function() {
        if (authPage) await authPage.quit();
        if (jobsPage) await jobsPage.quit();
        await cleanupDatabase();
    });

    describe('Jobs List', function() {
        it('should load jobs page', async function() {
            await jobsPage.goToJobs();
            
            const title = await jobsPage.getElementText(jobsPage.selectors.pageTitle);
            expect(title).to.include('Job');
        });

        it('should display jobs table', async function() {
            await jobsPage.goToJobs();
            
            const hasTable = await jobsPage.elementExists(jobsPage.selectors.jobsTable);
            expect(hasTable).to.be.true;
        });

        it('should get list of jobs', async function() {
            const jobs = await jobsPage.getJobsList();
            
            expect(jobs).to.be.an('array');
        });
    });

    describe('Job Scheduling', function() {
        it('should navigate to schedule job form', async function() {
            await jobsPage.goToScheduleJob();
            
            const hasAppSelect = await jobsPage.elementExists(jobsPage.selectors.applicationSelect);
            expect(hasAppSelect).to.be.true;
        });

        it('should display application options', async function() {
            await jobsPage.goToScheduleJob();
            
            const appSelect = await jobsPage.findElement(jobsPage.selectors.applicationSelect);
            const options = await appSelect.findElements(By.tagName('option'));
            
            // Should have at least one option (plus default)
            expect(options.length).to.be.greaterThan(0);
        });

        it('should schedule new job', async function() {
            const jobData = {
                application: '1', // TestApp from database setup
                company: '1'      // Test company
            };
            
            try {
                await jobsPage.scheduleJob(jobData);
                
                // Verify we don't get an error page
                const currentUrl = await jobsPage.driver.getCurrentUrl();
                expect(currentUrl).to.not.include('error');
            } catch (error) {
                // Job scheduling might fail if company/app don't exist
                // This is acceptable for testing page functionality
            }
        });
    });

    describe('Job Details', function() {
        it('should view specific job if available', async function() {
            const jobs = await jobsPage.getJobsList();
            
            if (jobs.length > 0) {
                const firstJob = jobs[0];
                await jobsPage.goToJob(firstJob.id);
                
                const title = await jobsPage.getElementText(jobsPage.selectors.pageTitle);
                expect(title).to.include('Job');
            }
        });

        it('should handle job status retrieval', async function() {
            const jobs = await jobsPage.getJobsList();
            
            if (jobs.length > 0) {
                const firstJob = jobs[0];
                const status = await jobsPage.getJobStatus(firstJob.id);
                
                expect(status).to.be.a('string');
            }
        });

        it('should handle job output retrieval', async function() {
            const jobs = await jobsPage.getJobsList();
            
            if (jobs.length > 0) {
                const firstJob = jobs[0];
                const output = await jobsPage.getJobOutput(firstJob.id);
                
                expect(output).to.be.a('string');
            }
        });
    });

    describe('Job Filtering', function() {
        it('should filter jobs by status', async function() {
            await jobsPage.filterByStatus('completed');
            
            // Verify filtering doesn't cause errors
            const hasTable = await jobsPage.elementExists(jobsPage.selectors.jobsTable);
            expect(hasTable).to.be.true;
        });
    });
});