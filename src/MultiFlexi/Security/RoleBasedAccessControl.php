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
 * Role-Based Access Control (RBAC) implementation with hierarchical roles and permissions.
 */
class RoleBasedAccessControl
{
    /**
     * Database connection.
     */
    private \PDO $pdo;

    /**
     * Tables configuration.
     */
    private array $tables = [
        'roles' => 'rbac_roles',
        'permissions' => 'rbac_permissions',
        'role_permissions' => 'rbac_role_permissions',
        'user_roles' => 'rbac_user_roles',
        'role_hierarchy' => 'rbac_role_hierarchy',
    ];

    /**
     * Cache for roles, permissions, and relationships.
     */
    private array $cache = [];

    /**
     * Default system roles and permissions.
     */
    private array $defaultRoles = [
        'super_admin' => [
            'name' => 'Super Administrator',
            'description' => 'Full system access with all permissions',
            'is_system' => true,
        ],
        'admin' => [
            'name' => 'Administrator',
            'description' => 'Administrative access to most system functions',
            'is_system' => true,
        ],
        'editor' => [
            'name' => 'Editor',
            'description' => 'Can create and edit content',
            'is_system' => true,
        ],
        'user' => [
            'name' => 'User',
            'description' => 'Basic user access',
            'is_system' => true,
        ],
        'viewer' => [
            'name' => 'Viewer',
            'description' => 'Read-only access',
            'is_system' => true,
        ],
    ];
    private array $defaultPermissions = [
        // System permissions
        'system.admin' => 'Full system administration',
        'system.config' => 'Modify system configuration',
        'system.backup' => 'Create and restore backups',
        'system.logs' => 'View system logs',
        // User management
        'users.create' => 'Create new users',
        'users.read' => 'View user information',
        'users.update' => 'Update user information',
        'users.delete' => 'Delete users',
        'users.impersonate' => 'Login as other users',
        // Role and permission management
        'roles.create' => 'Create new roles',
        'roles.read' => 'View roles',
        'roles.update' => 'Update roles',
        'roles.delete' => 'Delete roles',
        'roles.assign' => 'Assign roles to users',
        // Company management
        'companies.create' => 'Create companies',
        'companies.read' => 'View companies',
        'companies.update' => 'Update companies',
        'companies.delete' => 'Delete companies',
        // Application management
        'applications.create' => 'Create applications',
        'applications.read' => 'View applications',
        'applications.update' => 'Update applications',
        'applications.delete' => 'Delete applications',
        // Job management
        'jobs.create' => 'Create jobs',
        'jobs.read' => 'View jobs',
        'jobs.update' => 'Update jobs',
        'jobs.delete' => 'Delete jobs',
        'jobs.execute' => 'Execute jobs manually',
        // Security management
        'security.audit' => 'View security audit logs',
        'security.config' => 'Configure security settings',
        'security.whitelist' => 'Manage IP whitelist',
        // Profile management
        'profile.read' => 'View own profile',
        'profile.update' => 'Update own profile',
    ];

    /**
     * Constructor.
     *
     * @param \PDO       $pdo    Database connection
     * @param null|array $tables Optional custom table names
     */
    public function __construct(\PDO $pdo, ?array $tables = null)
    {
        $this->pdo = $pdo;

        if ($tables !== null) {
            $this->tables = array_merge($this->tables, $tables);
        }

        $this->initializeTables();
        $this->initializeDefaultData();
    }

    /**
     * Create a new role.
     *
     * @param string      $name        Role name (unique identifier)
     * @param string      $displayName Human-readable role name
     * @param null|string $description Role description
     * @param bool        $isSystem    Whether this is a system role
     *
     * @return null|int Role ID or null on failure
     */
    public function createRole(string $name, string $displayName, ?string $description = null, bool $isSystem = false): ?int
    {
        try {
            $sql = <<<EOD

                INSERT INTO `{$this->tables['roles']}`
                (name, display_name, description, is_system, is_active)
                VALUES (?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE
                display_name = VALUES(display_name),
                description = VALUES(description),
                updated_at = CURRENT_TIMESTAMP

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$name, $displayName, $description, $isSystem ? 1 : 0]);

            $roleId = $this->pdo->lastInsertId();

            // Clear cache
            $this->clearCache();

            // Log role creation
            if (isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'role_created',
                    "Role '{$name}' created with ID {$roleId}",
                    'low',
                    null,
                    ['role_name' => $name, 'role_id' => $roleId],
                );
            }

            return (int) $roleId;
        } catch (\Exception $e) {
            error_log('Failed to create role: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Create a new permission.
     *
     * @param string      $name        Permission name (unique identifier)
     * @param null|string $description Permission description
     * @param null|string $resource    Resource name
     * @param null|string $action      Action name
     * @param bool        $isSystem    Whether this is a system permission
     *
     * @return null|int Permission ID or null on failure
     */
    public function createPermission(string $name, ?string $description = null, ?string $resource = null, ?string $action = null, bool $isSystem = false): ?int
    {
        try {
            $sql = <<<EOD

                INSERT INTO `{$this->tables['permissions']}`
                (name, description, resource, action, is_system)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                description = VALUES(description),
                resource = VALUES(resource),
                action = VALUES(action),
                updated_at = CURRENT_TIMESTAMP

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$name, $description, $resource, $action, $isSystem ? 1 : 0]);

            $lastId = $this->pdo->lastInsertId();

            return $lastId ? (int) $lastId : null;
        } catch (\Exception $e) {
            error_log('Failed to create permission: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Assign a permission to a role.
     *
     * @param int      $roleId         Role ID
     * @param string   $permissionName Permission name
     * @param null|int $grantedBy      User ID who granted this permission
     *
     * @return bool Success status
     */
    public function assignPermissionToRole(int $roleId, string $permissionName, ?int $grantedBy = null): bool
    {
        try {
            $permissionId = $this->getPermissionIdByName($permissionName);

            if (!$permissionId) {
                return false;
            }

            $sql = <<<EOD

                INSERT INTO `{$this->tables['role_permissions']}`
                (role_id, permission_id, granted_by)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE granted_at = CURRENT_TIMESTAMP

EOD;

            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([$roleId, $permissionId, $grantedBy]);

            if ($success) {
                $this->clearCache();
            }

            return $success;
        } catch (\Exception $e) {
            error_log('Failed to assign permission to role: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Assign a role to a user.
     *
     * @param int         $userId     User ID
     * @param int         $roleId     Role ID
     * @param null|int    $assignedBy User ID who assigned this role
     * @param null|string $expiresAt  Expiration date (Y-m-d H:i:s format)
     *
     * @return bool Success status
     */
    public function assignRoleToUser(int $userId, int $roleId, ?int $assignedBy = null, ?string $expiresAt = null): bool
    {
        try {
            $sql = <<<EOD

                INSERT INTO `{$this->tables['user_roles']}`
                (user_id, role_id, assigned_by, expires_at)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                assigned_by = VALUES(assigned_by),
                assigned_at = CURRENT_TIMESTAMP,
                expires_at = VALUES(expires_at)

EOD;

            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([$userId, $roleId, $assignedBy, $expiresAt]);

            if ($success) {
                $this->clearCache();

                // Log role assignment
                if (isset($GLOBALS['securityAuditLogger'])) {
                    $roleName = $this->getRoleById($roleId)['name'] ?? "ID:{$roleId}";
                    $GLOBALS['securityAuditLogger']->logEvent(
                        'role_assigned',
                        "Role '{$roleName}' assigned to user {$userId}",
                        'medium',
                        $assignedBy,
                        ['user_id' => $userId, 'role_id' => $roleId, 'role_name' => $roleName],
                    );
                }
            }

            return $success;
        } catch (\Exception $e) {
            error_log('Failed to assign role to user: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Check if a user has a specific permission.
     *
     * @param int    $userId         User ID
     * @param string $permissionName Permission name
     *
     * @return bool Whether user has the permission
     */
    public function userHasPermission(int $userId, string $permissionName): bool
    {
        $cacheKey = "user_permission_{$userId}_{$permissionName}";

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        try {
            $sql = <<<EOD

                SELECT COUNT(*) as count
                FROM `{$this->tables['user_roles']}` ur
                JOIN `{$this->tables['role_permissions']}` rp ON ur.role_id = rp.role_id
                JOIN `{$this->tables['permissions']}` p ON rp.permission_id = p.id
                WHERE ur.user_id = ?
                AND p.name = ?
                AND (ur.expires_at IS NULL OR ur.expires_at > NOW())

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $permissionName]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $hasPermission = $result && $result['count'] > 0;
            $this->cache[$cacheKey] = $hasPermission;

            return $hasPermission;
        } catch (\Exception $e) {
            error_log('Failed to check user permission: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Check if a user has a specific role.
     *
     * @param int    $userId   User ID
     * @param string $roleName Role name
     *
     * @return bool Whether user has the role
     */
    public function userHasRole(int $userId, string $roleName): bool
    {
        $cacheKey = "user_role_{$userId}_{$roleName}";

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        try {
            $sql = <<<EOD

                SELECT COUNT(*) as count
                FROM `{$this->tables['user_roles']}` ur
                JOIN `{$this->tables['roles']}` r ON ur.role_id = r.id
                WHERE ur.user_id = ?
                AND r.name = ?
                AND r.is_active = 1
                AND (ur.expires_at IS NULL OR ur.expires_at > NOW())

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $roleName]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $hasRole = $result && $result['count'] > 0;
            $this->cache[$cacheKey] = $hasRole;

            return $hasRole;
        } catch (\Exception $e) {
            error_log('Failed to check user role: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get all roles assigned to a user.
     *
     * @param int $userId User ID
     *
     * @return array Array of role data
     */
    public function getUserRoles(int $userId): array
    {
        try {
            $sql = <<<EOD

                SELECT r.*, ur.assigned_at, ur.expires_at
                FROM `{$this->tables['roles']}` r
                JOIN `{$this->tables['user_roles']}` ur ON r.id = ur.role_id
                WHERE ur.user_id = ?
                AND r.is_active = 1
                AND (ur.expires_at IS NULL OR ur.expires_at > NOW())
                ORDER BY r.name

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Failed to get user roles: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get all permissions for a user (including inherited from roles).
     *
     * @param int $userId User ID
     *
     * @return array Array of permission data
     */
    public function getUserPermissions(int $userId): array
    {
        try {
            $sql = <<<EOD

                SELECT DISTINCT p.name, p.description, p.resource, p.action
                FROM `{$this->tables['permissions']}` p
                JOIN `{$this->tables['role_permissions']}` rp ON p.id = rp.permission_id
                JOIN `{$this->tables['user_roles']}` ur ON rp.role_id = ur.role_id
                WHERE ur.user_id = ?
                AND (ur.expires_at IS NULL OR ur.expires_at > NOW())
                ORDER BY p.resource, p.action, p.name

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Failed to get user permissions: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Remove a role from a user.
     *
     * @param int $userId User ID
     * @param int $roleId Role ID
     *
     * @return bool Success status
     */
    public function removeRoleFromUser(int $userId, int $roleId): bool
    {
        try {
            $sql = "DELETE FROM `{$this->tables['user_roles']}` WHERE user_id = ? AND role_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([$userId, $roleId]);

            if ($success) {
                $this->clearCache();

                // Log role removal
                if (isset($GLOBALS['securityAuditLogger'])) {
                    $roleName = $this->getRoleById($roleId)['name'] ?? "ID:{$roleId}";
                    $GLOBALS['securityAuditLogger']->logEvent(
                        'role_removed',
                        "Role '{$roleName}' removed from user {$userId}",
                        'medium',
                        null,
                        ['user_id' => $userId, 'role_id' => $roleId, 'role_name' => $roleName],
                    );
                }
            }

            return $success;
        } catch (\Exception $e) {
            error_log('Failed to remove role from user: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get all available roles.
     *
     * @param bool $includeInactive Include inactive roles
     *
     * @return array Array of role data
     */
    public function getAllRoles(bool $includeInactive = false): array
    {
        try {
            $sql = "SELECT * FROM `{$this->tables['roles']}`";

            if (!$includeInactive) {
                $sql .= ' WHERE is_active = 1';
            }

            $sql .= ' ORDER BY name';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Failed to get all roles: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get all available permissions.
     *
     * @return array Array of permission data
     */
    public function getAllPermissions(): array
    {
        try {
            $sql = "SELECT * FROM `{$this->tables['permissions']}` ORDER BY resource, action, name";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Failed to get all permissions: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get permissions for a specific role.
     *
     * @param int $roleId Role ID
     *
     * @return array Array of permission data
     */
    public function getRolePermissions(int $roleId): array
    {
        try {
            $sql = <<<EOD

                SELECT p.*
                FROM `{$this->tables['permissions']}` p
                JOIN `{$this->tables['role_permissions']}` rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?
                ORDER BY p.resource, p.action, p.name

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$roleId]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Failed to get role permissions: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get RBAC statistics.
     *
     * @return array Statistics data
     */
    public function getStatistics(): array
    {
        try {
            $stats = [];

            // Total roles
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `{$this->tables['roles']}` WHERE is_active = 1");
            $stats['total_roles'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            // Total permissions
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `{$this->tables['permissions']}`");
            $stats['total_permissions'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            // Users with roles assigned
            $stmt = $this->pdo->query("SELECT COUNT(DISTINCT user_id) as count FROM `{$this->tables['user_roles']}`");
            $stats['users_with_roles'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            // Most common roles
            $stmt = $this->pdo->query(<<<EOD

                SELECT r.name, r.display_name, COUNT(ur.user_id) as user_count
                FROM `{$this->tables['roles']}` r
                LEFT JOIN `{$this->tables['user_roles']}` ur ON r.id = ur.role_id
                WHERE r.is_active = 1
                GROUP BY r.id, r.name, r.display_name
                ORDER BY user_count DESC
                LIMIT 10

EOD);
            $stats['popular_roles'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $stats;
        } catch (\Exception $e) {
            error_log('Failed to get RBAC statistics: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Enforce permission check - throws exception if user lacks permission.
     *
     * @param int         $userId         User ID
     * @param string      $permissionName Permission name
     * @param null|string $errorMessage   Custom error message
     *
     * @throws \Exception If user lacks permission
     */
    public function enforcePermission(int $userId, string $permissionName, ?string $errorMessage = null): void
    {
        if (!$this->userHasPermission($userId, $permissionName)) {
            $message = $errorMessage ?: "Access denied: Missing permission '{$permissionName}'";

            // Log access denied
            if (isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'access_denied',
                    "Access denied for user {$userId}, missing permission: {$permissionName}",
                    'medium',
                    $userId,
                    ['permission' => $permissionName],
                );
            }

            throw new \Exception($message, 403);
        }
    }

    /**
     * Check if current user (from session) has permission.
     *
     * @param string $permissionName Permission name
     *
     * @return bool Whether current user has permission
     */
    public function currentUserHasPermission(string $permissionName): bool
    {
        $userId = self::getCurrentUserId();

        return $userId ? $this->userHasPermission($userId, $permissionName) : false;
    }
    /**
     * Check if the current user has a specific role.
     *
     * @param string $roleName Role name
     *
     * @return bool Whether current user has the role
     */
    public function hasRole(string $roleName): bool
    {
        $userId = self::getCurrentUserId();

        return $userId ? $this->userHasRole($userId, $roleName) : false;
    }

    /**
     * Check if a specific role is assigned to ANY user in the system.
     * This is useful for first-run detection or system-wide checks.
     *
     * @param string $roleName Role name
     *
     * @return bool Whether any user has the role
     */
    public function isRoleAssigned(string $roleName): bool
    {
        try {
            $sql = <<<EOD

                SELECT COUNT(*) as count
                FROM `{$this->tables['user_roles']}` ur
                JOIN `{$this->tables['roles']}` r ON ur.role_id = r.id
                WHERE r.name = ?
                AND r.is_active = 1
                AND (ur.expires_at IS NULL OR ur.expires_at > NOW())

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$roleName]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result && (int) $result['count'] > 0;
        } catch (\Exception $e) {
            error_log('Failed to check if role is assigned: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Initialize RBAC tables.
     */
    private function initializeTables(): void
    {
        // Roles table
        $this->pdo->exec(<<<EOD

            CREATE TABLE IF NOT EXISTS `{$this->tables['roles']}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(50) NOT NULL,
                `display_name` varchar(100) NOT NULL,
                `description` text DEFAULT NULL,
                `is_system` tinyint(1) NOT NULL DEFAULT 0,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_name` (`name`),
                KEY `idx_active` (`is_active`),
                KEY `idx_system` (`is_system`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

EOD);

        // Permissions table
        $this->pdo->exec(<<<EOD

            CREATE TABLE IF NOT EXISTS `{$this->tables['permissions']}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `description` text DEFAULT NULL,
                `resource` varchar(50) DEFAULT NULL,
                `action` varchar(50) DEFAULT NULL,
                `is_system` tinyint(1) NOT NULL DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_name` (`name`),
                KEY `idx_resource_action` (`resource`, `action`),
                KEY `idx_system` (`is_system`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

EOD);

        // Role-Permission mapping table
        $this->pdo->exec(<<<EOD

            CREATE TABLE IF NOT EXISTS `{$this->tables['role_permissions']}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `role_id` int(11) NOT NULL,
                `permission_id` int(11) NOT NULL,
                `granted_by` int(11) DEFAULT NULL,
                `granted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_role_permission` (`role_id`, `permission_id`),
                KEY `idx_role_id` (`role_id`),
                KEY `idx_permission_id` (`permission_id`),
                KEY `idx_granted_by` (`granted_by`),
                FOREIGN KEY (`role_id`) REFERENCES `{$this->tables['roles']}` (`id`) ON DELETE CASCADE,
                FOREIGN KEY (`permission_id`) REFERENCES `{$this->tables['permissions']}` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

EOD);

        // User-Role mapping table
        $this->pdo->exec(<<<EOD

            CREATE TABLE IF NOT EXISTS `{$this->tables['user_roles']}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `role_id` int(11) NOT NULL,
                `assigned_by` int(11) DEFAULT NULL,
                `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `expires_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_user_role` (`user_id`, `role_id`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_role_id` (`role_id`),
                KEY `idx_assigned_by` (`assigned_by`),
                KEY `idx_expires_at` (`expires_at`),
                FOREIGN KEY (`role_id`) REFERENCES `{$this->tables['roles']}` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

EOD);

        // Role hierarchy table (for role inheritance)
        $this->pdo->exec(<<<EOD

            CREATE TABLE IF NOT EXISTS `{$this->tables['role_hierarchy']}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `parent_role_id` int(11) NOT NULL,
                `child_role_id` int(11) NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_hierarchy` (`parent_role_id`, `child_role_id`),
                KEY `idx_parent_role` (`parent_role_id`),
                KEY `idx_child_role` (`child_role_id`),
                FOREIGN KEY (`parent_role_id`) REFERENCES `{$this->tables['roles']}` (`id`) ON DELETE CASCADE,
                FOREIGN KEY (`child_role_id`) REFERENCES `{$this->tables['roles']}` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

EOD);
    }

    /**
     * Initialize default roles and permissions.
     */
    private function initializeDefaultData(): void
    {
        // Create default permissions
        foreach ($this->defaultPermissions as $name => $description) {
            [$resource, $action] = explode('.', $name, 2);
            $this->createPermission($name, $description, $resource, $action, true);
        }

        // Create default roles
        foreach ($this->defaultRoles as $roleName => $roleData) {
            $this->createRole(
                $roleName,
                $roleData['name'],
                $roleData['description'],
                $roleData['is_system'],
            );
        }

        // Assign default permissions to roles
        $this->assignDefaultPermissions();
    }

    /**
     * Assign default permissions to system roles.
     */
    private function assignDefaultPermissions(): void
    {
        $rolePermissions = [
            'super_admin' => array_keys($this->defaultPermissions), // All permissions
            'admin' => [
                'users.create', 'users.read', 'users.update', 'users.delete',
                'roles.read', 'roles.assign',
                'companies.create', 'companies.read', 'companies.update', 'companies.delete',
                'applications.create', 'applications.read', 'applications.update', 'applications.delete',
                'jobs.create', 'jobs.read', 'jobs.update', 'jobs.delete', 'jobs.execute',
                'security.audit', 'security.config',
                'profile.read', 'profile.update',
                'system.config', 'system.logs',
            ],
            'editor' => [
                'companies.read', 'companies.update',
                'applications.read', 'applications.update',
                'jobs.create', 'jobs.read', 'jobs.update', 'jobs.execute',
                'profile.read', 'profile.update',
            ],
            'user' => [
                'companies.read',
                'applications.read',
                'jobs.create', 'jobs.read', 'jobs.update',
                'profile.read', 'profile.update',
            ],
            'viewer' => [
                'companies.read',
                'applications.read',
                'jobs.read',
                'profile.read',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $roleId = $this->getRoleIdByName($roleName);

            if ($roleId) {
                foreach ($permissions as $permissionName) {
                    $this->assignPermissionToRole($roleId, $permissionName);
                }
            }
        }
    }

    /**
     * Get role ID by name.
     *
     * @param string $name Role name
     *
     * @return null|int Role ID or null if not found
     */
    private function getRoleIdByName(string $name): ?int
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM `{$this->tables['roles']}` WHERE name = ? LIMIT 1");
            $stmt->execute([$name]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ? (int) $result['id'] : null;
        } catch (\Exception $e) {
            error_log('Failed to get role ID: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get role by ID.
     *
     * @param int $id Role ID
     *
     * @return null|array Role data or null if not found
     */
    private function getRoleById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `{$this->tables['roles']}` WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            error_log('Failed to get role by ID: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get permission ID by name.
     *
     * @param string $name Permission name
     *
     * @return null|int Permission ID or null if not found
     */
    private function getPermissionIdByName(string $name): ?int
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM `{$this->tables['permissions']}` WHERE name = ? LIMIT 1");
            $stmt->execute([$name]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result ? (int) $result['id'] : null;
        } catch (\Exception $e) {
            error_log('Failed to get permission ID: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Clear internal cache.
     */
    private function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Get current user ID from session.
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
