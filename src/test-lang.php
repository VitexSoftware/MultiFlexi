<?php

declare(strict_types=1);

/**
 * Language Selector Test Page
 * 
 * This page tests the language switching functionality
 */

namespace MultiFlexi;

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/ui.php';

$oPage->addItem(new UI\PageTop(_('Language Test')));

// Display current locale information
$currentLocale = \Ease\Locale::$localeUsed;
$oPage->container->addItem(new \Ease\Html\H1Tag(_('Language Selector Test')));

// Show current locale
$infoPanel = new \Ease\TWB4\Panel(
    _('Current Locale Information'),
    'info'
);

$infoPanel->addItem(new \Ease\Html\PTag(_('Current Locale') . ': ' . $currentLocale));
$infoPanel->addItem(new \Ease\Html\PTag(_('Session Locale') . ': ' . (\Ease\Locale::sessionLocale() ?? 'not set')));
$infoPanel->addItem(new \Ease\Html\PTag(_('Request Locale') . ': ' . (\Ease\Locale::requestLocale() ?? 'not set')));

$oPage->container->addItem($infoPanel);

// Test some translated strings
$testPanel = new \Ease\TWB4\Panel(
    _('Translated Strings Test'),
    'success'
);

$testStrings = [
    'Applications' => _('Applications'),
    'Companies' => _('Companies'),
    'Users' => _('Users'),
    'Login' => _('Login'),
    'Password' => _('Password'),
    'Welcome' => _('Welcome'),
    'Save' => _('Save'),
    'Cancel' => _('Cancel'),
    'Delete' => _('Delete'),
    'Edit' => _('Edit'),
];

$table = new \Ease\Html\TableTag();
$table->addRowHeaderColumns([_('English Key'), _('Translated Value')]);

foreach ($testStrings as $key => $translated) {
    $table->addRowColumns([$key, $translated]);
}

$testPanel->addItem($table);
$oPage->container->addItem($testPanel);

// Available locales
$localeObj = \Ease\Locale::singleton();
$availableLocales = $localeObj->availble();

$localesPanel = new \Ease\TWB4\Panel(
    _('Available Locales'),
    'warning'
);

$localesList = new \Ease\Html\UlTag();
foreach ($availableLocales as $code => $name) {
    $localesList->addItem($code . ' - ' . $name);
}

$localesPanel->addItem($localesList);
$oPage->container->addItem($localesPanel);

$oPage->container->addItem(new \Ease\Html\PTag(_('Use the language selector in the navigation menu to switch languages.')));

// Test Application translations
$appPanel = new \Ease\TWB4\Panel(
    _('Application Translations Test'),
    'primary'
);

// Load a test application if available
try {
    $app = new LocalizedApplication();
    $apps = $app->listingQuery();
    if (!empty($apps)) {
        $firstApp = reset($apps);
        $app->loadFromSQL($firstApp['id']);
        
        $appInfo = new \Ease\Html\DivTag();
        $appInfo->addItem(new \Ease\Html\H4Tag(_('First Application')));
        $appInfo->addItem(new \Ease\Html\PTag(_('Name') . ' (default): ' . $app->getDataValue('name')));
        $appInfo->addItem(new \Ease\Html\PTag(_('Name') . ' (localized): ' . $app->getLocalizedName()));
        $appInfo->addItem(new \Ease\Html\PTag(_('Description') . ' (default): ' . $app->getDataValue('description')));
        $appInfo->addItem(new \Ease\Html\PTag(_('Description') . ' (localized): ' . $app->getLocalizedDescription()));
        
        $appPanel->addItem($appInfo);
    } else {
        $appPanel->addItem(new \Ease\Html\PTag(_('No applications found to test translations.')));
    }
} catch (\Exception $e) {
    $appPanel->addItem(new \Ease\Html\PTag(_('Error loading applications: ') . $e->getMessage()));
}

$oPage->container->addItem($appPanel);

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
