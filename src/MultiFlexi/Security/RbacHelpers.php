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
 * Helper functions for Role-Based Access Control integration.
 */
class RbacHelpers
{
    /**
     * Check if RBAC is available and enabled.
     *
     * @return bool True if RBAC is available
     */
    public static function isAvailable(): bool
    {
        return isset($GLOBALS['rbac'])
            && \Ease\Shared::cfg('RBAC_ENABLED', true);
    }

    /**
     * Check if current user has a permission.
     *
     * @param string $permission Permission name
     *
     * @return bool Whether current user has the permission
     */
    public static function currentUserHasPermission(string $permission): bool
    {
        if (!self::isAvailable()) {
            return true; // Allow all if RBAC is disabled
        }

        return $GLOBALS['rbac']->currentUserHasPermission($permission);
    }

    /**
     * Check if a user has a permission.
     *
     * @param int    $userId     User ID
     * @param string $permission Permission name
     *
     * @return bool Whether user has the permission
     */
    public static function userHasPermission(int $userId, string $permission): bool
    {
        if (!self::isAvailable()) {
            return true; // Allow all if RBAC is disabled
        }

        return $GLOBALS['rbac']->userHasPermission($userId, $permission);
    }

    /**
     * Check if current user has a role.
     *
     * @param string $roleName Role name
     *
     * @return bool Whether current user has the role
     */
    public static function currentUserHasRole(string $roleName): bool
    {
        if (!self::isAvailable()) {
            return false;
        }

        $userId = self::getCurrentUserId();

        return $userId ? $GLOBALS['rbac']->userHasRole($userId, $roleName) : false;
    }

    /**
     * Check if a user has a role.
     *
     * @param int    $userId   User ID
     * @param string $roleName Role name
     *
     * @return bool Whether user has the role
     */
    public static function userHasRole(int $userId, string $roleName): bool
    {
        if (!self::isAvailable()) {
            return false;
        }

        return $GLOBALS['rbac']->userHasRole($userId, $roleName);
    }

    /**
     * Enforce permission check - throws exception if user lacks permission.
     *
     * @param string      $permission   Permission name
     * @param null|int    $userId       User ID (uses current user if null)
     * @param null|string $errorMessage Custom error message
     *
     * @throws \Exception If user lacks permission
     */
    public static function enforcePermission(string $permission, ?int $userId = null, ?string $errorMessage = null): void
    {
        if (!self::isAvailable()) {
            return; // Allow all if RBAC is disabled
        }

        if ($userId === null) {
            $userId = self::getCurrentUserId();
        }

        if (!$userId) {
            throw new \Exception('User not authenticated', 401);
        }

        $GLOBALS['rbac']->enforcePermission($userId, $permission, $errorMessage);
    }

    /**
     * Enforce role check - throws exception if user lacks role.
     *
     * @param string      $roleName     Role name
     * @param null|int    $userId       User ID (uses current user if null)
     * @param null|string $errorMessage Custom error message
     *
     * @throws \Exception If user lacks role
     */
    public static function enforceRole(string $roleName, ?int $userId = null, ?string $errorMessage = null): void
    {
        if (!self::isAvailable()) {
            return; // Allow all if RBAC is disabled
        }

        if ($userId === null) {
            $userId = self::getCurrentUserId();
        }

        if (!$userId) {
            throw new \Exception('User not authenticated', 401);
        }

        if (!$GLOBALS['rbac']->userHasRole($userId, $roleName)) {
            $message = $errorMessage ?: "Access denied: Missing role '{$roleName}'";

            // Log access denied
            if (isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'access_denied',
                    "Access denied for user {$userId}, missing role: {$roleName}",
                    'medium',
                    $userId,
                    ['role' => $roleName],
                );
            }

            throw new \Exception($message, 403);
        }
    }

    /**
     * Check if current user is admin.
     *
     * @return bool Whether current user has admin role
     */
    public static function isCurrentUserAdmin(): bool
    {
        return self::currentUserHasRole('admin') || self::currentUserHasRole('super_admin');
    }

    /**
     * Enforce admin access.
     *
     * @throws \Exception If user is not admin
     */
    public static function enforceAdmin(): void
    {
        if (!self::isCurrentUserAdmin()) {
            throw new \Exception('Administrative access required', 403);
        }
    }

    /**
     * Get all roles for current user.
     *
     * @return array Array of role data
     */
    public static function getCurrentUserRoles(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        $userId = self::getCurrentUserId();

        return $userId ? $GLOBALS['rbac']->getUserRoles($userId) : [];
    }

    /**
     * Get all permissions for current user.
     *
     * @return array Array of permission data
     */
    public static function getCurrentUserPermissions(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        $userId = self::getCurrentUserId();

        return $userId ? $GLOBALS['rbac']->getUserPermissions($userId) : [];
    }

    /**
     * Assign role to a user.
     *
     * @param int      $userId     User ID
     * @param string   $roleName   Role name
     * @param null|int $assignedBy User ID who is assigning the role
     *
     * @return bool Success status
     */
    public static function assignRoleToUser(int $userId, string $roleName, ?int $assignedBy = null): bool
    {
        if (!self::isAvailable()) {
            return false;
        }

        // Get role ID
        $roles = $GLOBALS['rbac']->getAllRoles();
        $roleId = null;

        foreach ($roles as $role) {
            if ($role['name'] === $roleName) {
                $roleId = $role['id'];

                break;
            }
        }

        if (!$roleId) {
            return false;
        }

        if ($assignedBy === null) {
            $assignedBy = self::getCurrentUserId();
        }

        return $GLOBALS['rbac']->assignRoleToUser($userId, $roleId, $assignedBy);
    }

    /**
     * Remove role from a user.
     *
     * @param int    $userId   User ID
     * @param string $roleName Role name
     *
     * @return bool Success status
     */
    public static function removeRoleFromUser(int $userId, string $roleName): bool
    {
        if (!self::isAvailable()) {
            return false;
        }

        // Get role ID
        $roles = $GLOBALS['rbac']->getAllRoles();
        $roleId = null;

        foreach ($roles as $role) {
            if ($role['name'] === $roleName) {
                $roleId = $role['id'];

                break;
            }
        }

        if (!$roleId) {
            return false;
        }

        return $GLOBALS['rbac']->removeRoleFromUser($userId, $roleId);
    }

    /**
     * Get all available roles.
     *
     * @return array Array of role data
     */
    public static function getAllRoles(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        return $GLOBALS['rbac']->getAllRoles();
    }

    /**
     * Get all available permissions.
     *
     * @return array Array of permission data
     */
    public static function getAllPermissions(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        return $GLOBALS['rbac']->getAllPermissions();
    }

    /**
     * Get RBAC statistics.
     *
     * @return array Statistics data
     */
    public static function getStatistics(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        return $GLOBALS['rbac']->getStatistics();
    }

    /**
     * Create middleware for permission checking.
     *
     * @param string $permission Required permission
     *
     * @return callable Middleware function
     */
    public static function createPermissionMiddleware(string $permission): callable
    {
        return static function () use ($permission): void {
            self::enforcePermission($permission);
        };
    }

    /**
     * Create middleware for role checking.
     *
     * @param string $roleName Required role
     *
     * @return callable Middleware function
     */
    public static function createRoleMiddleware(string $roleName): callable
    {
        return static function () use ($roleName): void {
            self::enforceRole($roleName);
        };
    }

    /**
     * Generate role selection HTML for forms.
     *
     * @param string $fieldName          Field name for the select element
     * @param array  $selectedRoles      Currently selected role IDs
     * @param bool   $includeSystemRoles Whether to include system roles
     *
     * @return string HTML for role selection
     */
    public static function generateRoleSelectHtml(string $fieldName, array $selectedRoles = [], bool $includeSystemRoles = true): string
    {
        if (!self::isAvailable()) {
            return '<p class="text-muted">RBAC is not enabled</p>';
        }

        $roles = $GLOBALS['rbac']->getAllRoles();
        $html = '<div class="role-selection">';

        foreach ($roles as $role) {
            if (!$includeSystemRoles && $role['is_system']) {
                continue;
            }

            $checked = \in_array($role['id'], $selectedRoles, true) ? 'checked' : '';
            $systemBadge = $role['is_system'] ? '<small class="badge badge-secondary">System</small>' : '';

            $html .= '<div class="form-check">';
            $html .= '<input class="form-check-input" type="checkbox" name="'.htmlspecialchars($fieldName).'[]" ';
            $html .= 'value="'.$role['id'].'" id="role_'.$role['id'].'" '.$checked.'>';
            $html .= '<label class="form-check-label" for="role_'.$role['id'].'">';
            $html .= htmlspecialchars($role['display_name']).' '.$systemBadge;
            $html .= '<br><small class="text-muted">'.htmlspecialchars($role['description']).'</small>';
            $html .= '</label>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Generate permissions table HTML for display.
     *
     * @param array $userPermissions User's current permissions (optional)
     *
     * @return string HTML for permissions table
     */
    public static function generatePermissionsTableHtml(array $userPermissions = []): string
    {
        if (!self::isAvailable()) {
            return '<p class="text-muted">RBAC is not enabled</p>';
        }

        $permissions = $GLOBALS['rbac']->getAllPermissions();
        $userPermissionNames = array_column($userPermissions, 'name');

        // Group permissions by resource
        $groupedPermissions = [];

        foreach ($permissions as $permission) {
            $resource = $permission['resource'] ?: 'general';
            $groupedPermissions[$resource][] = $permission;
        }

        $html = '<div class="permissions-table">';

        foreach ($groupedPermissions as $resource => $resourcePermissions) {
            $html .= '<h6 class="mt-3">'.ucfirst(htmlspecialchars($resource)).'</h6>';
            $html .= '<div class="table-responsive">';
            $html .= '<table class="table table-sm">';
            $html .= '<thead><tr><th>Permission</th><th>Description</th><th>Status</th></tr></thead>';
            $html .= '<tbody>';

            foreach ($resourcePermissions as $permission) {
                $hasPermission = \in_array($permission['name'], $userPermissionNames, true);
                $statusClass = $hasPermission ? 'text-success' : 'text-muted';
                $statusIcon = $hasPermission ? '✓' : '—';

                $html .= '<tr>';
                $html .= '<td><code>'.htmlspecialchars($permission['name']).'</code></td>';
                $html .= '<td>'.htmlspecialchars($permission['description']).'</td>';
                $html .= '<td class="'.$statusClass.'">'.$statusIcon.'</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table></div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Check multiple permissions at once.
     *
     * @param array    $permissions Array of permission names
     * @param null|int $userId      User ID (uses current user if null)
     * @param bool     $requireAll  Whether all permissions are required (true) or any (false)
     *
     * @return bool Whether user meets the permission requirements
     */
    public static function checkMultiplePermissions(array $permissions, ?int $userId = null, bool $requireAll = false): bool
    {
        if (!self::isAvailable()) {
            return true; // Allow all if RBAC is disabled
        }

        if ($userId === null) {
            $userId = self::getCurrentUserId();
        }

        if (!$userId) {
            return false;
        }

        $hasPermission = false;

        foreach ($permissions as $permission) {
            $userHasThisPermission = $GLOBALS['rbac']->userHasPermission($userId, $permission);

            if ($requireAll && !$userHasThisPermission) {
                return false; // Need all, but missing this one
            }

            if (!$requireAll && $userHasThisPermission) {
                return true; // Need any, and have this one
            }

            $hasPermission = $hasPermission || $userHasThisPermission;
        }

        return $requireAll ? true : $hasPermission;
    }

    /**
     * Get current user ID from session or framework.
     *
     * @return null|int Current user ID or null
     */
    private static function getCurrentUserId(): ?int
    {
        // Try various methods to get current user ID
        if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }

        if (isset($_SESSION['USER_ID']) && is_numeric($_SESSION['USER_ID'])) {
            return (int) $_SESSION['USER_ID'];
        }

        // Check if using Ease framework user system
        if (class_exists('\\Ease\\User') && method_exists('\\Ease\\User', 'singleton')) {
            $user = \Ease\User::singleton();

            if (method_exists($user, 'getUserID') && $user->getUserID()) {
                return (int) $user->getUserID();
            }
        }

        return null;
    }
}
