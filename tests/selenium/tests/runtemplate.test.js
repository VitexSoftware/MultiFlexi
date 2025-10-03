const { expect } = require('chai');
const { setupDatabase, cleanupDatabase } = require('../scripts/setupDatabase');
const AuthPage = require('../src/AuthPage');
const RunTemplatePage = require('../src/RunTemplatePage');

/**
 * Tests focused on RunTemplate functionality
 */
describe('RunTemplate Tests', function() {
    this.timeout(90000);
    
    let authPage;
    let runTemplatePage;
    
    before(async function() {
        // Setup clean database for RunTemplate tests
        await setupDatabase();
        
        authPage = new AuthPage();
        runTemplatePage = new RunTemplatePage();
        
        await authPage.initializeDriver();
        await runTemplatePage.initializeDriver();
        
        // Setup admin user and login
        await authPage.registerAdmin();
        await authPage.waitForRegistrationSuccess();
        await authPage.loginAsAdmin();
        await authPage.waitForLoginSuccess();
    });

    after(async function() {
        if (authPage) await authPage.quit();
        if (runTemplatePage) await runTemplatePage.quit();
        await cleanupDatabase();
    });

    describe('RunTemplate Creation', function() {
        it('should create RunTemplate with basic settings', async function() {
            const templateData = {
                name: 'Basic Test Template',
                description: 'Basic test template description',
                application: 'TestApp',
                scheduleType: 'manual',
                enabled: true
            };
            
            await runTemplatePage.createRunTemplate(templateData);
            
            const templates = await runTemplatePage.getRunTemplatesList();
            const created = templates.find(t => t.name === templateData.name);
            expect(created).to.exist;
        });

        it('should create RunTemplate with scheduling', async function() {
            const templateData = {
                name: 'Scheduled Test Template',
                description: 'Template with interval scheduling',
                application: 'TestApp',
                scheduleType: 'interval',
                interval: 30,
                intervalUnit: 'minutes',
                enabled: true,
                retryCount: 2,
                timeout: 600
            };
            
            await runTemplatePage.createRunTemplate(templateData);
            
            const templates = await runTemplatePage.getRunTemplatesList();
            const created = templates.find(t => t.name === templateData.name);
            expect(created).to.exist;
        });

        it('should create RunTemplate with environment variables', async function() {
            const templateData = {
                name: 'Template with EnvVars',
                description: 'Template with custom environment variables',
                application: 'TestApp',
                scheduleType: 'manual',
                enabled: true,
                envVars: {
                    'CUSTOM_VAR': 'custom_value',
                    'DEBUG_MODE': 'true',
                    'MAX_RETRIES': '5'
                }
            };
            
            await runTemplatePage.createRunTemplate(templateData);
            
            const templates = await runTemplatePage.getRunTemplatesList();
            const created = templates.find(t => t.name === templateData.name);
            expect(created).to.exist;
        });
    });

    describe('RunTemplate Execution', function() {
        let testTemplateName;
        
        beforeEach(async function() {
            // Create a fresh test template for each execution test
            testTemplateName = `Exec Test ${Date.now()}`;
            const templateData = {
                name: testTemplateName,
                description: 'Template for execution testing',
                application: 'TestApp',
                scheduleType: 'manual',
                enabled: true,
                timeout: 30
            };
            
            await runTemplatePage.createRunTemplate(templateData);
        });

        it('should execute RunTemplate manually', async function() {
            await runTemplatePage.executeRunTemplate(testTemplateName);
            
            // Verify execution was initiated
            // (The actual execution verification depends on the test app behavior)
            // For now, we just verify no errors occurred during execution initiation
            
            const hasError = await runTemplatePage.elementExists(runTemplatePage.selectors.errorMessage);
            expect(hasError).to.be.false;
        });

        it('should handle execution of disabled RunTemplate', async function() {
            // This test would verify that disabled templates cannot be executed
            // Implementation depends on UI behavior for disabled templates
        });
    });

    describe('RunTemplate Management', function() {
        it('should list all RunTemplates', async function() {
            await runTemplatePage.goToRunTemplates();
            
            const templates = await runTemplatePage.getRunTemplatesList();
            expect(templates).to.be.an('array');
            expect(templates.length).to.be.greaterThan(0);
        });

        it('should delete RunTemplate', async function() {
            const templateName = 'Template to Delete';
            const templateData = {
                name: templateName,
                description: 'This template will be deleted',
                application: 'TestApp',
                scheduleType: 'manual',
                enabled: false
            };
            
            await runTemplatePage.createRunTemplate(templateData);
            
            // Verify it was created
            let templates = await runTemplatePage.getRunTemplatesList();
            let created = templates.find(t => t.name === templateName);
            expect(created).to.exist;
            
            // Delete it
            await runTemplatePage.deleteRunTemplate(templateName);
            
            // Verify it was deleted
            templates = await runTemplatePage.getRunTemplatesList();
            created = templates.find(t => t.name === templateName);
            expect(created).to.be.undefined;
        });
    });
});