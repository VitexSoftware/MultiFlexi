const { Builder, By, until } = require('selenium-webdriver');
const chrome = require('selenium-webdriver/chrome');
const EnvironmentManager = require('./EnvironmentManager');

/**
 * WebDriver factory and utility class for MultiFlexi Selenium tests
 */
class WebDriverHelper {
    constructor() {
        this.driver = null;
        this.envManager = EnvironmentManager.getInstance();
        this.config = this.envManager.getConfig();
        
        // Use environment-specific configuration
        this.baseUrl = this.config.baseUrl;
        this.timeout = this.config.browser.timeout;
        this.implicitWait = this.config.browser.implicitWait;
        this.pageLoadTimeout = this.config.browser.pageLoadTimeout;
        this.debugEnabled = this.config.debug.enabled;
    }

    /**
     * Initialize WebDriver with Chrome options
     */
    async initializeDriver() {
        if (this.debugEnabled) {
            this.envManager.logEnvironmentInfo();
        }
        
        const options = new chrome.Options();
        
        if (this.config.browser.headless) {
            options.addArguments('--headless');
        }
        
        // Chrome arguments for better stability
        options.addArguments('--no-sandbox');
        options.addArguments('--disable-dev-shm-usage');
        options.addArguments('--disable-gpu');
        options.addArguments('--window-size=1920,1080');
        options.addArguments('--disable-extensions');
        options.addArguments('--disable-background-timer-throttling');
        options.addArguments('--disable-renderer-backgrounding');
        options.addArguments('--disable-backgrounding-occluded-windows');

        // Handle HTTPS certificates for staging environment
        if (this.envManager.isStagingEnvironment()) {
            options.addArguments('--ignore-certificate-errors');
            options.addArguments('--ignore-ssl-errors');
            options.addArguments('--allow-running-insecure-content');
        }

        this.driver = await new Builder()
            .forBrowser('chrome')
            .setChromeOptions(options)
            .build();

        await this.driver.manage().setTimeouts({
            implicit: this.implicitWait,
            pageLoad: this.pageLoadTimeout,
            script: this.timeout
        });

        if (this.debugEnabled) {
            console.log(`🌐 WebDriver initialized for: ${this.baseUrl}`);
        }

        return this.driver;
    }

    /**
     * Navigate to a specific page
     */
    async navigateTo(path = '') {
        const url = `${this.baseUrl}${path}`;
        if (process.env.DEBUG === 'true') {
            console.log(`Navigating to: ${url}`);
        }
        await this.driver.get(url);
        await this.waitForPageLoad();
    }

    /**
     * Wait for page to be fully loaded
     */
    async waitForPageLoad() {
        await this.driver.wait(
            until.elementLocated(By.tagName('body')),
            this.timeout
        );
    }

    /**
     * Find element with retry logic
     */
    async findElement(locator, timeout = this.timeout) {
        return await this.driver.wait(
            until.elementLocated(locator),
            timeout
        );
    }

    /**
     * Find element and wait until it's visible
     */
    async findVisibleElement(locator, timeout = this.timeout) {
        const element = await this.findElement(locator, timeout);
        await this.driver.wait(until.elementIsVisible(element), timeout);
        return element;
    }

    /**
     * Fill input field
     */
    async fillInput(locator, value) {
        const element = await this.findVisibleElement(locator);
        await element.clear();
        await element.sendKeys(value);
    }

    /**
     * Click element with wait
     */
    async clickElement(locator) {
        const element = await this.findVisibleElement(locator);
        await this.driver.wait(until.elementIsEnabled(element), this.timeout);
        await element.click();
    }

    /**
     * Wait for element to contain text
     */
    async waitForText(locator, text, timeout = this.timeout) {
        await this.driver.wait(
            until.elementTextContains(await this.findElement(locator), text),
            timeout
        );
    }

    /**
     * Get element text
     */
    async getElementText(locator) {
        const element = await this.findElement(locator);
        return await element.getText();
    }

    /**
     * Check if element exists
     */
    async elementExists(locator) {
        try {
            await this.driver.findElement(locator);
            return true;
        } catch (error) {
            return false;
        }
    }

    /**
     * Take screenshot for debugging
     */
    async takeScreenshot(filename = 'screenshot') {
        if (!this.driver) return;
        
        const screenshot = await this.driver.takeScreenshot();
        const fs = require('fs');
        const path = require('path');
        
        const screenshotPath = path.join(__dirname, '..', 'screenshots', `${filename}-${Date.now()}.png`);
        
        // Create screenshots directory if it doesn't exist
        const screenshotDir = path.dirname(screenshotPath);
        if (!fs.existsSync(screenshotDir)) {
            fs.mkdirSync(screenshotDir, { recursive: true });
        }
        
        fs.writeFileSync(screenshotPath, screenshot, 'base64');
        console.log(`Screenshot saved: ${screenshotPath}`);
        return screenshotPath;
    }

    /**
     * Close driver
     */
    async quit() {
        if (this.driver) {
            await this.driver.quit();
            this.driver = null;
        }
    }
}

module.exports = WebDriverHelper;