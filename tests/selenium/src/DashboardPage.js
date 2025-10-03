const { By } = require('selenium-webdriver');
const WebDriverHelper = require('./WebDriverHelper');

/**
 * Page Object Model for MultiFlexi Dashboard
 */
class DashboardPage extends WebDriverHelper {
    constructor() {
        super();
        
        this.selectors = {
            // Page elements
            pageTitle: By.css('h1, .page-header'),
            dashboardCards: By.css('.card'),
            
            // Navigation menu
            mainNav: By.css('.navbar'),
            dashboardLink: By.linkText('Dashboard'),
            
            // Dashboard widgets
            statsCards: By.css('.stats-card'),
            recentJobs: By.css('.recent-jobs'),
            systemStatus: By.css('.system-status'),
            
            // Quick actions
            quickActions: By.css('.quick-actions'),
            newJobButton: By.css('a[href*="newjob"]'),
            
            // Charts and graphs
            chartsContainer: By.css('.charts-container'),
            jobChart: By.css('#job-chart'),
            
            // Activity feed
            activityFeed: By.css('.activity-feed'),
            activityItems: By.css('.activity-item')
        };
    }

    /**
     * Navigate to dashboard
     */
    async goToDashboard() {
        await this.navigateTo('/dashboard.php');
        await this.waitForElement(this.selectors.pageTitle);
    }

    /**
     * Check if dashboard loaded correctly
     */
    async isDashboardLoaded() {
        try {
            await this.findElement(this.selectors.pageTitle, 5000);
            return true;
        } catch (error) {
            return false;
        }
    }

    /**
     * Get dashboard statistics
     */
    async getStats() {
        const stats = {};
        
        try {
            const cards = await this.driver.findElements(this.selectors.statsCards);
            for (let i = 0; i < cards.length; i++) {
                const title = await cards[i].findElement(By.css('.card-title')).getText();
                const value = await cards[i].findElement(By.css('.card-value')).getText();
                stats[title] = value;
            }
        } catch (error) {
            // Stats cards might not be present
        }
        
        return stats;
    }

    /**
     * Check if quick actions are available
     */
    async hasQuickActions() {
        return await this.elementExists(this.selectors.quickActions);
    }

    /**
     * Click new job button
     */
    async clickNewJob() {
        await this.clickElement(this.selectors.newJobButton);
    }
}

module.exports = DashboardPage;