/**
 * Password Strength Indicator
 * Provides real-time password strength feedback to users
 */

class PasswordStrengthIndicator {
    constructor(passwordInput, options = {}) {
        this.passwordInput = passwordInput;
        this.options = {
            minLength: options.minLength || 8,
            requireUppercase: options.requireUppercase !== false,
            requireLowercase: options.requireLowercase !== false,
            requireNumbers: options.requireNumbers !== false,
            requireSpecialChars: options.requireSpecialChars !== false,
            showRequirements: options.showRequirements !== false,
            showStrengthBar: options.showStrengthBar !== false,
            ...options
        };
        
        this.commonPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123', 'password123',
            'admin', 'root', 'user', 'guest', 'test', '12345678', 'password1',
            'welcome', 'login', 'letmein', 'monkey', '1234567890', 'dragon',
            'qwerty123', 'hello', 'sunshine', 'princess', 'football', 'master',
            'superman', 'computer', 'shadow', 'baseball'
        ];
        
        this.init();
    }
    
    init() {
        this.createIndicator();
        this.passwordInput.addEventListener('input', () => this.updateIndicator());
        this.passwordInput.addEventListener('blur', () => this.updateIndicator());
        this.updateIndicator(); // Initial check
    }
    
    createIndicator() {
        // Create container for password strength indicator
        this.container = document.createElement('div');
        this.container.className = 'password-strength-container';
        this.container.style.cssText = 'margin-top: 8px;';
        
        // Insert after password input
        this.passwordInput.parentNode.insertBefore(this.container, this.passwordInput.nextSibling);
        
        if (this.options.showStrengthBar) {
            this.createStrengthBar();
        }
        
        if (this.options.showRequirements) {
            this.createRequirementsList();
        }
    }
    
    createStrengthBar() {
        this.strengthBar = document.createElement('div');
        this.strengthBar.className = 'password-strength-bar';
        this.strengthBar.style.cssText = `
            height: 4px;
            background-color: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 8px;
        `;
        
        this.strengthProgress = document.createElement('div');
        this.strengthProgress.className = 'password-strength-progress';
        this.strengthProgress.style.cssText = `
            height: 100%;
            width: 0%;
            background-color: #dc3545;
            border-radius: 2px;
            transition: all 0.3s ease;
        `;
        
        this.strengthBar.appendChild(this.strengthProgress);
        this.container.appendChild(this.strengthBar);
        
        this.strengthText = document.createElement('div');
        this.strengthText.className = 'password-strength-text';
        this.strengthText.style.cssText = `
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 8px;
        `;
        this.container.appendChild(this.strengthText);
    }
    
    createRequirementsList() {
        this.requirementsList = document.createElement('ul');
        this.requirementsList.className = 'password-requirements';
        this.requirementsList.style.cssText = `
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 12px;
        `;
        
        const requirements = this.getRequirements();
        requirements.forEach((requirement, index) => {
            const li = document.createElement('li');
            li.className = 'password-requirement';
            li.style.cssText = `
                padding: 2px 0;
                color: #dc3545;
                position: relative;
                padding-left: 20px;
            `;
            
            const icon = document.createElement('span');
            icon.className = 'requirement-icon';
            icon.style.cssText = `
                position: absolute;
                left: 0;
                top: 2px;
            `;
            icon.innerHTML = '✗';
            
            const text = document.createElement('span');
            text.textContent = requirement;
            
            li.appendChild(icon);
            li.appendChild(text);
            li.setAttribute('data-requirement', index);
            
            this.requirementsList.appendChild(li);
        });
        
        this.container.appendChild(this.requirementsList);
    }
    
    getRequirements() {
        const requirements = [];
        
        requirements.push(`At least ${this.options.minLength} characters long`);
        
        if (this.options.requireUppercase) {
            requirements.push('At least one uppercase letter (A-Z)');
        }
        
        if (this.options.requireLowercase) {
            requirements.push('At least one lowercase letter (a-z)');
        }
        
        if (this.options.requireNumbers) {
            requirements.push('At least one number (0-9)');
        }
        
        if (this.options.requireSpecialChars) {
            requirements.push('At least one special character (!@#$%^&*(),.?":{}|<>)');
        }
        
        requirements.push('Cannot be a common password');
        requirements.push('Cannot contain sequential characters');
        requirements.push('Cannot contain more than 3 repeated characters');
        
        return requirements;
    }
    
    updateIndicator() {
        const password = this.passwordInput.value;
        const validation = this.validatePassword(password);
        
        if (this.options.showStrengthBar) {
            this.updateStrengthBar(validation.strength);
        }
        
        if (this.options.showRequirements) {
            this.updateRequirements(password);
        }
    }
    
    updateStrengthBar(strength) {
        this.strengthProgress.style.width = strength + '%';
        
        let color, text;
        if (strength < 30) {
            color = '#dc3545'; // red
            text = 'Very Weak';
        } else if (strength < 50) {
            color = '#fd7e14'; // orange
            text = 'Weak';
        } else if (strength < 70) {
            color = '#ffc107'; // yellow
            text = 'Medium';
        } else if (strength < 90) {
            color = '#20c997'; // teal
            text = 'Strong';
        } else {
            color = '#28a745'; // green
            text = 'Very Strong';
        }
        
        this.strengthProgress.style.backgroundColor = color;
        this.strengthText.textContent = `Password Strength: ${text} (${strength}%)`;
        this.strengthText.style.color = color;
    }
    
    updateRequirements(password) {
        const requirements = this.requirementsList.querySelectorAll('.password-requirement');
        const checks = [
            password.length >= this.options.minLength,
            !this.options.requireUppercase || /[A-Z]/.test(password),
            !this.options.requireLowercase || /[a-z]/.test(password),
            !this.options.requireNumbers || /\d/.test(password),
            !this.options.requireSpecialChars || /[!@#$%^&*(),.?":{}|<>]/.test(password),
            !this.commonPasswords.includes(password.toLowerCase()),
            !this.hasSequentialChars(password),
            !/(.)\1{3,}/.test(password)
        ];
        
        requirements.forEach((req, index) => {
            const icon = req.querySelector('.requirement-icon');
            const isValid = checks[index];
            
            if (isValid) {
                req.style.color = '#28a745';
                icon.innerHTML = '✓';
                icon.style.color = '#28a745';
            } else {
                req.style.color = '#dc3545';
                icon.innerHTML = '✗';
                icon.style.color = '#dc3545';
            }
        });
    }
    
    validatePassword(password) {
        const errors = [];
        let strength = 0;
        
        // Length check
        if (password.length < this.options.minLength) {
            errors.push(`Password must be at least ${this.options.minLength} characters long`);
        }
        
        // Character requirements
        if (this.options.requireUppercase && !/[A-Z]/.test(password)) {
            errors.push('Password must contain at least one uppercase letter');
        }
        
        if (this.options.requireLowercase && !/[a-z]/.test(password)) {
            errors.push('Password must contain at least one lowercase letter');
        }
        
        if (this.options.requireNumbers && !/\d/.test(password)) {
            errors.push('Password must contain at least one number');
        }
        
        if (this.options.requireSpecialChars && !/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
            errors.push('Password must contain at least one special character');
        }
        
        // Common password check
        if (this.commonPasswords.includes(password.toLowerCase())) {
            errors.push('Password is too common and easily guessable');
        }
        
        // Sequential characters
        if (this.hasSequentialChars(password)) {
            errors.push('Password cannot contain sequential characters');
        }
        
        // Repeated characters
        if (/(.)\1{3,}/.test(password)) {
            errors.push('Password cannot contain more than 3 repeated characters in a row');
        }
        
        // Calculate strength
        strength = this.calculateStrength(password);
        
        return {
            valid: errors.length === 0,
            errors: errors,
            strength: strength
        };
    }
    
    calculateStrength(password) {
        let score = 0;
        const length = password.length;
        
        // Length bonus
        score += Math.min(length * 4, 25);
        
        // Character variety bonus
        if (/[a-z]/.test(password)) score += 5;
        if (/[A-Z]/.test(password)) score += 5;
        if (/\d/.test(password)) score += 5;
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score += 10;
        
        // Length milestones
        if (length >= 8) score += 10;
        if (length >= 12) score += 10;
        if (length >= 16) score += 15;
        
        // Complexity bonus
        let charSets = 0;
        if (/[a-z]/.test(password)) charSets++;
        if (/[A-Z]/.test(password)) charSets++;
        if (/\d/.test(password)) charSets++;
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) charSets++;
        
        score += charSets * 5;
        
        // Penalties
        if (this.hasSequentialChars(password)) score -= 15;
        if (/(.)\1{2,}/.test(password)) score -= 10;
        if (this.commonPasswords.includes(password.toLowerCase())) score -= 25;
        
        return Math.max(0, Math.min(100, score));
    }
    
    hasSequentialChars(password) {
        const sequences = [
            '0123456789', 'abcdefghijklmnopqrstuvwxyz', 'qwertyuiop',
            'asdfghjkl', 'zxcvbnm', '9876543210', 'zyxwvutsrqponmlkjihgfedcba'
        ];
        
        for (const sequence of sequences) {
            for (let i = 0; i <= sequence.length - 3; i++) {
                const substr = sequence.substr(i, 3);
                if (password.toLowerCase().includes(substr)) {
                    return true;
                }
            }
        }
        
        return false;
    }
}

// Auto-initialize password strength indicators
document.addEventListener('DOMContentLoaded', function() {
    // Find all password inputs and add strength indicators
    const passwordInputs = document.querySelectorAll('input[type="password"][name="password"]');
    passwordInputs.forEach(input => {
        if (!input.hasAttribute('data-no-strength-indicator')) {
            new PasswordStrengthIndicator(input, {
                showRequirements: true,
                showStrengthBar: true
            });
        }
    });
});