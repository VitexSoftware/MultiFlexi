<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of OpenClipart
 *
 * @author vitex
 */
class OpenClipart extends \Ease\Html\ATag
{
    public function __construct($directory, $alt, $properties = [])
    {
        $randomImage = self::randomImage($directory);
        if (array_key_exists('title', $properties) === false) {
            $properties['title'] = _('Visit OpenClipart.org - the source of most of our images');
        }
        parent::__construct('https://openclipart.org/detail/' . str_replace('.svg', '', $randomImage), new \Ease\Html\ImgTag($directory . '/' . $randomImage, $alt, $properties));
    }

    public static function randomImage($dir)
    {
        $files = scandir($dir);
        if (($key = array_search('.', $files)) !== false) {
            unset($files[$key]);
        }
        if (($key = array_search('..', $files)) !== false) {
            unset($files[$key]);
        }
        $file = array_rand($files);
        return $files[$file];
    }
}
