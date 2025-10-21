<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi\Security;

/**
 * Password validation utility class for enforcing password strength requirements.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class PasswordValidator
{
    private int $minLength;
    private bool $requireUppercase;
    private bool $requireLowercase;
    private bool $requireNumbers;
    private bool $requireSpecialChars;
    private array $commonPasswords;

    public function __construct(
        int $minLength = 8,
        bool $requireUppercase = true,
        bool $requireLowercase = true,
        bool $requireNumbers = true,
        bool $requireSpecialChars = true,
    ) {
        $this->minLength = $minLength;
        $this->requireUppercase = $requireUppercase;
        $this->requireLowercase = $requireLowercase;
        $this->requireNumbers = $requireNumbers;
        $this->requireSpecialChars = $requireSpecialChars;

        // Common passwords to reject
        $this->commonPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123', 'password123',
            'admin', 'root', 'user', 'guest', 'test', '12345678', 'password1',
            'welcome', 'login', 'letmein', 'monkey', '1234567890', 'dragon',
            'qwerty123', 'hello', 'sunshine', 'princess', 'football', 'master',
            'superman', 'computer', 'shadow', 'baseball',
        ];
    }

    /**
     * Validate password strength according to configured rules.
     *
     * @param string $password The password to validate
     *
     * @return array Array of validation results: ['valid' => bool, 'errors' => array]
     */
    public function validate(string $password): array
    {
        $errors = [];

        // Check minimum length
        if (\strlen($password) < $this->minLength) {
            $errors[] = sprintf(_('Password must be at least %d characters long'), $this->minLength);
        }

        // Check for uppercase letters
        if ($this->requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = _('Password must contain at least one uppercase letter');
        }

        // Check for lowercase letters
        if ($this->requireLowercase && !preg_match('/[a-z]/', $password)) {
            $errors[] = _('Password must contain at least one lowercase letter');
        }

        // Check for numbers
        if ($this->requireNumbers && !preg_match('/\d/', $password)) {
            $errors[] = _('Password must contain at least one number');
        }

        // Check for special characters
        if ($this->requireSpecialChars && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = _('Password must contain at least one special character (!@#$%^&*(),.?":{}|<>)');
        }

        // Check against common passwords
        if (\in_array(strtolower($password), $this->commonPasswords, true)) {
            $errors[] = _('Password is too common and easily guessable');
        }

        // Check for repeated characters (more than 3 in a row)
        if (preg_match('/(.)\1{3,}/', $password)) {
            $errors[] = _('Password cannot contain more than 3 repeated characters in a row');
        }

        // Check for sequential characters
        if (self::hasSequentialChars($password)) {
            $errors[] = _('Password cannot contain sequential characters (e.g., 123, abc)');
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $this->calculateStrength($password),
        ];
    }

    /**
     * Get password strength text.
     */
    public function getStrengthText(int $strength): string
    {
        if ($strength < 30) {
            return _('Very Weak');
        }

        if ($strength < 50) {
            return _('Weak');
        }

        if ($strength < 70) {
            return _('Medium');
        }

        if ($strength < 90) {
            return _('Strong');
        }

        return _('Very Strong');
    }

    /**
     * Get password requirements as human-readable text.
     */
    public function getRequirementsText(): array
    {
        $requirements = [];

        $requirements[] = sprintf(_('At least %d characters long'), $this->minLength);

        if ($this->requireUppercase) {
            $requirements[] = _('At least one uppercase letter (A-Z)');
        }

        if ($this->requireLowercase) {
            $requirements[] = _('At least one lowercase letter (a-z)');
        }

        if ($this->requireNumbers) {
            $requirements[] = _('At least one number (0-9)');
        }

        if ($this->requireSpecialChars) {
            $requirements[] = _('At least one special character (!@#$%^&*(),.?":{}|<>)');
        }

        $requirements[] = _('Cannot be a common or easily guessable password');
        $requirements[] = _('Cannot contain sequential characters (e.g., 123, abc)');
        $requirements[] = _('Cannot contain more than 3 repeated characters in a row');

        return $requirements;
    }

    /**
     * Calculate password strength score (0-100).
     */
    private function calculateStrength(string $password): int
    {
        $score = 0;
        $length = \strlen($password);

        // Length bonus
        $score += min($length * 4, 25);

        // Character variety bonus
        if (preg_match('/[a-z]/', $password)) {
            $score += 5;
        }

        if (preg_match('/[A-Z]/', $password)) {
            $score += 5;
        }

        if (preg_match('/\d/', $password)) {
            $score += 5;
        }

        if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $score += 10;
        }

        // Length milestones
        if ($length >= 8) {
            $score += 10;
        }

        if ($length >= 12) {
            $score += 10;
        }

        if ($length >= 16) {
            $score += 15;
        }

        // Complexity bonus
        $charSets = 0;

        if (preg_match('/[a-z]/', $password)) {
            ++$charSets;
        }

        if (preg_match('/[A-Z]/', $password)) {
            ++$charSets;
        }

        if (preg_match('/\d/', $password)) {
            ++$charSets;
        }

        if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            ++$charSets;
        }

        $score += $charSets * 5;

        // Penalties
        if (self::hasSequentialChars($password)) {
            $score -= 15;
        }

        if (preg_match('/(.)\1{2,}/', $password)) {
            $score -= 10;
        }

        if (\in_array(strtolower($password), $this->commonPasswords, true)) {
            $score -= 25;
        }

        return max(0, min(100, $score));
    }

    /**
     * Check for sequential characters.
     */
    private static function hasSequentialChars(string $password): bool
    {
        $sequences = [
            '0123456789', 'abcdefghijklmnopqrstuvwxyz', 'qwertyuiop',
            'asdfghjkl', 'zxcvbnm', '9876543210', 'zyxwvutsrqponmlkjihgfedcba',
        ];

        foreach ($sequences as $sequence) {
            for ($i = 0; $i <= \strlen($sequence) - 3; ++$i) {
                $substr = substr($sequence, $i, 3);

                if (str_contains(strtolower($password), strtolower($substr))) {
                    return true;
                }
            }
        }

        return false;
    }
}
