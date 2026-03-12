<?php

/**
 * Debian autoloader for multiflexi-web
 *
 * Loads multiflexi-core library autoloader (which handles Ease, FluentPDO,
 * JsonSchema dependencies) and registers PSR-4 autoloading for web-specific
 * MultiFlexi classes installed under /usr/lib/multiflexi-web/.
 */

// Load the core library autoloader (brings in MultiFlexi core + all its deps)
require_once '/usr/share/php/MultiFlexi/autoload.php';

// Load additional dependency autoloaders needed by the web app
require_once '/usr/share/php/EaseHtml/autoload.php';
require_once '/usr/share/php/EaseHtmlWidgets/autoload.php';
require_once '/usr/share/php/EaseTWB4/autoload.php';
require_once '/usr/share/php/EaseTWB4Widgets/autoload.php';
require_once '/usr/share/php/SvgGraph/autoload.php';
require_once '/usr/share/php/SensioLabs/AnsiConverter/autoload.php';


// PSR-4 autoloader for MultiFlexi web-specific classes
// These extend/complement the core MultiFlexi namespace with Ui, GDPR, Security, etc.
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'MultiFlexi\\' => '/usr/lib/multiflexi-web/MultiFlexi/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
