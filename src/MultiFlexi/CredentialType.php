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
 * Description of CredentialType.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CredentialType extends DBEngine {

    public function __construct($init = null, $filter = []) {
        $this->myTable = 'credential_type';
        parent::__construct(false /* TODO $init */, $filter);
    }

    /**
     * Prepare data for save.
     */
    #[\Override]
    public function takeData(array $data): int {

        unset($data['class']);

        if (!\array_key_exists('logo', $data) && \array_key_exists('imageraw', $_FILES) && !empty($_FILES['imageraw']['name'])) {
            $uploadfile = sys_get_temp_dir() . '/' . basename($_FILES['imageraw']['name']);

            if (move_uploaded_file($_FILES['imageraw']['tmp_name'], $uploadfile)) {
                $data['logo'] = 'data:' . mime_content_type($uploadfile) . ';base64,' . base64_encode(file_get_contents($uploadfile));
                unlink($uploadfile);
                unset($data['imageraw']);
            }
        }

//        if (empty($this->getDataValue('logo'))) {
//            $data['logo'] = 'data:image/svg+xml;base64,' . base64_encode(Ui\CredentialTypeLogo::$none);
//        }

        return parent::takeData($data);
    }
}
