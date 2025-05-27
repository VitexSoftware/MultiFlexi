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

namespace MultiFlexi;

/**
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
interface credentialTypeInterface
{
    public static function name(): string;

    public static function description(): string;

    public static function logo(): string;

    public function prepareConfigForm();

    public function fieldsProvided(): ConfigFields;

    public function fieldsInternal(): ConfigFields;

    public function save(): bool;

    public function query(): ConfigFields;

    // TODO: public function validate(): bool;
}
