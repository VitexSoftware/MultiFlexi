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

namespace MultiFlexi\Api\Server;

/**
 * @no-named-arguments
 */
class UserApi
{
    public function __construct()
    {
        // Constructor code here
    }

    public function getUser($userId): void
    {
        // Code to get user by ID
    }

    public function createUser($userData): void
    {
        // Code to create a new user
    }

    public function updateUser($userId, $userData): void
    {
        // Code to update user by ID
    }

    public function deleteUser($userId): void
    {
        // Code to delete user by ID
    }
}
