const { By } = require('selenium-webdriver');
const WebDriverHelper = require('./WebDriverHelper');
require('dotenv').config();

/**
 * Page Object Model for MultiFlexi Authentication
 */
class AuthPage extends WebDriverHelper {
    constructor() {
        super();
        
        // Selectors for login page elements
        this.selectors = {
            // Login form
            loginForm: By.id('loginForm'),
            usernameInput: By.name('login'),
            passwordInput: By.name('password'),
            loginButton: By.css('button[type="submit"]'),
            
            // Registration form  
            registrationForm: By.id('registrationForm'),
            regUsernameInput: By.name('username'),
            regPasswordInput: By.name('password'),
            regPasswordConfirmInput: By.name('password_confirm'),
            regEmailInput: By.name('email'),
            regFirstNameInput: By.name('firstname'),
            regLastNameInput: By.name('lastname'),
            registerButton: By.css('button[type="submit"]'),
            
            // Navigation
            registerLink: By.linkText('Register'),
            loginLink: By.linkText('Login'),
            
            // Messages
            successMessage: By.css('.alert-success'),
            errorMessage: By.css('.alert-danger'),
            
            // User menu (after login)
            userMenu: By.css('.navbar .dropdown-toggle'),
            logoutLink: By.linkText('Logout')
        };
    }

    /**
     * Navigate to login page
     */
    async goToLogin() {
        await this.navigateTo('/login.php');
        await this.waitForElement(this.selectors.loginForm);
    }

    /**
     * Navigate to registration page
     */
    async goToRegistration() {
        await this.navigateTo('/register.php');
        await this.waitForElement(this.selectors.registrationForm);
    }

    /**
     * Login with credentials
     */
    async login(username, password) {
        await this.goToLogin();
        
        await this.fillInput(this.selectors.usernameInput, username);
        await this.fillInput(this.selectors.passwordInput, password);
        await this.clickElement(this.selectors.loginButton);
        
        // Wait for redirect after successful login
        await this.driver.sleep(2000);
    }

    /**
     * Register new user account
     */
    async register(userData) {
        await this.goToRegistration();
        
        await this.fillInput(this.selectors.regUsernameInput, userData.username);
        await this.fillInput(this.selectors.regPasswordInput, userData.password);
        await this.fillInput(this.selectors.regPasswordConfirmInput, userData.password);
        await this.fillInput(this.selectors.regEmailInput, userData.email);
        
        if (userData.firstName) {
            await this.fillInput(this.selectors.regFirstNameInput, userData.firstName);
        }
        if (userData.lastName) {
            await this.fillInput(this.selectors.regLastNameInput, userData.lastName);
        }
        
        await this.clickElement(this.selectors.registerButton);
        
        // Wait for registration to complete
        await this.driver.sleep(2000);
    }

    /**
     * Register admin user with default credentials
     */
    async registerAdmin() {
        const adminData = {
            username: process.env.ADMIN_USERNAME || 'admin',
            password: process.env.ADMIN_PASSWORD || 'admin123',
            email: process.env.ADMIN_EMAIL || 'admin@example.com',
            firstName: 'Admin',
            lastName: 'User'
        };
        
        await this.register(adminData);
        return adminData;
    }

    /**
     * Login as admin with default credentials
     */
    async loginAsAdmin() {
        const username = process.env.ADMIN_USERNAME || 'admin';
        const password = process.env.ADMIN_PASSWORD || 'admin123';
        
        await this.login(username, password);
    }

    /**
     * Check if user is logged in
     */
    async isLoggedIn() {
        try {
            await this.findElement(this.selectors.userMenu, 5000);
            return true;
        } catch (error) {
            return false;
        }
    }

    /**
     * Logout current user
     */
    async logout() {
        if (await this.isLoggedIn()) {
            await this.clickElement(this.selectors.userMenu);
            await this.clickElement(this.selectors.logoutLink);
            await this.driver.sleep(2000);
        }
    }

    /**
     * Wait for successful login (redirect to dashboard)
     */
    async waitForLoginSuccess() {
        // Wait for user menu to appear or redirect to dashboard
        try {
            await this.findElement(this.selectors.userMenu, 10000);
            return true;
        } catch (error) {
            // Check for error messages
            if (await this.elementExists(this.selectors.errorMessage)) {
                const errorText = await this.getElementText(this.selectors.errorMessage);
                throw new Error(`Login failed: ${errorText}`);
            }
            throw error;
        }
    }

    /**
     * Wait for successful registration
     */
    async waitForRegistrationSuccess() {
        try {
            await this.findElement(this.selectors.successMessage, 10000);
            return true;
        } catch (error) {
            // Check for error messages
            if (await this.elementExists(this.selectors.errorMessage)) {
                const errorText = await this.getElementText(this.selectors.errorMessage);
                throw new Error(`Registration failed: ${errorText}`);
            }
            throw error;
        }
    }
    /**
     * Login as test user with configured credentials
     */
    async loginAsTestUser() {
        const username = process.env.TEST_USER_USERNAME || 'testuser';
        const password = process.env.TEST_USER_PASSWORD || 'testpass123';
        await this.login(username, password);
    }

    /**
     * Login as customer with configured credentials
     */
    async loginAsCustomer() {
        const username = process.env.CUSTOMER_USERNAME || 'testcustomer';
        const password = process.env.CUSTOMER_PASSWORD || 'testpass123';
        await this.login(username, password);
    }

    /**
     * Get admin credentials from environment
     */
    getAdminCredentials() {
        return {
            username: process.env.ADMIN_USERNAME || 'admin',
            password: process.env.ADMIN_PASSWORD || 'admin123',
            email: process.env.ADMIN_EMAIL || 'admin@multiflexi.test'
        };
    }

    /**
     * Get test user credentials from environment
     */
    getTestUserCredentials() {
        return {
            username: process.env.TEST_USER_USERNAME || 'testuser',
            password: process.env.TEST_USER_PASSWORD || 'testpass123',
            email: process.env.TEST_USER_EMAIL || 'testuser@test.com'
        };
    }

    /**
     * Get customer credentials from environment
     */
    getCustomerCredentials() {
        return {
            username: process.env.CUSTOMER_USERNAME || 'testcustomer',
            password: process.env.CUSTOMER_PASSWORD || 'testpass123',
            email: process.env.CUSTOMER_EMAIL || 'customer@test.com'
        };
    }
}

module.exports = AuthPage;

