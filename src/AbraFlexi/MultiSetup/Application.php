<?php

/**
 * Multi Flexi  - App class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup;

/**
 * Description of Application
 *
 * @author vitex
 */
class Application extends Engine {

    public $lastModifiedColumn;
    public $keyword;

    /**
     * 
     * 
     * @param mixed $identifier
     * @param array $options
     */
    public function __construct($identifier = null, $options = array()) {
        $this->myTable = 'apps';
        $this->createColumn = 'DatCreate';
        $this->lastModifiedColumn = 'DatUpdate';
        $this->keyword = 'app';
        $this->nameColumn = 'nazev';
        parent::__construct($identifier, $options);
    }

    /**
     * Check data before accepting
     * 
     * @param array $data
     * 
     * @return boolean
     */
    public function takeData($data) {
        $check = true;
        $data['enabled'] = (($data['enabled'] == 'on') || ($data['enabled'] == 1) );
        if (array_key_exists('nazev', $data) && empty($data['nazev'])) {
            $this->addStatusMessage(_('Name is empty'), 'warning');
            $check = false;
        }

        if (array_key_exists('executable', $data)) {
            $executable = self::findBinaryInPath($data['executable']);
            if (is_null($executable)) {
                $this->addStatusMessage(sprintf(_('Executable %s does not exist in search PATH'), $data['executable']), 'warning');
                $data['enabled'] = false;
            } else {
                if ($data['executable'] != $executable) {
                    $this->addStatusMessage(sprintf(_('Executable %s found as %s'), $data['executable'], $executable), 'success');
                    $data['executable'] = $executable;
                }
            }
        }

        if (array_key_exists('imageraw', $_FILES) && !empty($_FILES['imageraw']['name'])) {
            $uploadfile = sys_get_temp_dir() . '/' . basename($_FILES['imageraw']['name']);
            if (move_uploaded_file($_FILES['imageraw']['tmp_name'], $uploadfile)) {
                $data['image'] = 'data:' . mime_content_type($uploadfile) . ';base64,' . base64_encode(file_get_contents($uploadfile));
                unlink($uploadfile);
                unset($data['imageraw']);
            }
        }

        parent::takeData($data);
        return $check;
    }

    /**
     * Find real path for given binary name
     * 
     * @param sring $binary
     * 
     * @return string
     */
    public static function findBinaryInPath($binary) {
        $found = null;
        if ($binary[0] == '/') {
            $found = file_exists($binary) && is_executable($binary) ? $binary : null;
        } else {
            foreach (strstr(getenv('PATH'), ':') ? explode(':', getenv('PATH')) : [getenv('PATH')] as $pathDir) {
                $candidat = ((substr($pathDir, -1) == '/') ? $pathDir : $pathDir . '/') . $binary;
                if (file_exists($candidat) && is_executable($candidat)) {
                    $found = $candidat;
                }
            }
        }
        return $found;
    }

    /**
     * 
     * @param string $binary
     * 
     * @return boolean
     */
    public static function doesBinaryExist($binary) {
        return ($binary[0] == '/') ? file_exists($binary) : self::isBinaryInPath($binary);
    }

    /**
     * 
     * @param string $binary
     * 
     * @return boolean
     */
    public static function isBinaryInPath($binary) {
        return !empty(self::findBinaryInPath($binary));
    }

}
