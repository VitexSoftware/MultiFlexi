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

namespace MultiFlexi\Ui;

use Ease\Html\DivTag;
use Ease\Html\H2Tag;
use Ease\Html\InputHiddenTag;
use Ease\Html\CheckboxTag;
use Ease\Html\LabelTag;
use Ease\Html\PTag;
use Ease\Html\SmallTag;
use Ease\TWB4\Card;
use Ease\TWB4\Row;
use Ease\TWB4\SubmitButton;

require_once './init.php';

WebPage::singleton()->onlyForLogged();

$companer = new \MultiFlexi\Company(\Ease\WebPage::getRequestValue('company_id', 'int'));

if (null === $companer->getMyKey()) {
    WebPage::singleton()->redirect('companies.php');
}

$companyApp = new \MultiFlexi\CompanyApp($companer);

// Handle form submission
if (\Ease\WebPage::isPosted()) {
    $selectedApps = \Ease\WebPage::getRequestValue('apps', 'array');
    $appIds = $selectedApps ? array_map('intval', $selectedApps) : [];
    $companyApp->assignApps($appIds);
    WebPage::singleton()->addStatusMessage(_('Applications updated successfully'), 'success');
}

WebPage::singleton()->addItem(new PageTop(_('Applications used by Company')));

// Get all applications and currently assigned ones with localized names and descriptions
$apper = new \MultiFlexi\Application();
$currentLang = substr(\Ease\Locale::$localeUsed ?? 'en_US', 0, 2);

$allApps = $apper->getFluentPDO()
    ->from('apps')
    ->select('apps.id, apps.name, apps.description, apps.uuid, apps.image, apps.topics')
    ->select('COALESCE(app_translations.name, apps.name) AS localized_name')
    ->select('COALESCE(app_translations.description, apps.description) AS localized_description')
    ->leftJoin('app_translations ON app_translations.app_id = apps.id AND app_translations.lang = ?', $currentLang)
    ->orderBy('COALESCE(app_translations.name, apps.name)')
    ->fetchAll();

$assignedRaw = $companyApp->getAssigned()->fetchAll('app_id');
$assigned = empty($assignedRaw) ? [] : array_keys($assignedRaw);

// Collect all unique topics from applications
$allTopics = [];
foreach ($allApps as $app) {
    if (!empty($app['topics'])) {
        $topics = explode(',', $app['topics']);
        foreach ($topics as $topic) {
            $topic = trim($topic);
            if (!empty($topic) && !isset($allTopics[$topic])) {
                $allTopics[$topic] = ['id' => $topic, 'name' => $topic];
            }
        }
    }
}
// Sort topics alphabetically
ksort($allTopics);
$allTopics = array_values($allTopics);

// Create container for filter controls (outside form to avoid CSRF issues)
$filterContainer = new DivTag();
$filterContainer->addItem(new H2Tag(sprintf(_('Choose Applications for %s'), $companer->getRecordName())));

// Include Selectize assets for topic filtering
if (!empty($allTopics)) {
    WebPage::singleton()->includeJavaScript('js/selectize.min.js');
    WebPage::singleton()->includeCss('css/selectize.bootstrap4.css');
}

// Add topic filter using PillBox if topics are available
if (!empty($allTopics)) {
    $filterContainer->addItem(new \Ease\Html\H4Tag(_('Filter by Topics')));
    $filterContainer->addItem(new PTag(_('Select topics to filter applications. All topics are selected by default to show all applications.')));
    
    $filterRow = new Row();
    
    // Pre-select all topics by default
    $allTopicIds = array_column($allTopics, 'id');
    $topicFilter = new PillBox('topic_filter', $allTopics, $allTopicIds, ['class' => 'form-control mb-3', 'placeholder' => _('Select topics to filter applications...')]);
    $filterRow->addColumn(10, $topicFilter);
    
    // Add reset filter button
    $resetButton = new \Ease\Html\ButtonTag(_('Reset Filter'), [
        'class' => 'btn btn-outline-secondary mb-3',
        'type' => 'button',
        'id' => 'reset-topic-filter',
        'title' => _('Select all topics to show all applications'),
    ]);
    $filterRow->addColumn(2, $resetButton);
    
    $filterContainer->addItem($filterRow);
}

// Add search box
$searchBox = new \Ease\Html\InputSearchTag('app_search', '', ['placeholder' => _('Search applications...'), 'class' => 'form-control form-control-lg mb-3']);
$filterContainer->addItem($searchBox);

// Show count of selected apps
$countDiv = new DivTag(
    new SmallTag(['<strong id="selected-count">'.count($assigned).'</strong> ', _('applications selected')], ['class' => 'text-muted']),
    ['class' => 'mb-3']
);
$filterContainer->addItem($countDiv);

// Create form with card grid
$addAppForm = new SecureForm();
$addAppForm->addItem(new InputHiddenTag('company_id', $companer->getMyKey()));

// Create cards grid
$cardsRow = new Row();

foreach ($allApps as $app) {
    $isAssigned = in_array($app['id'], $assigned);
    
    // Add data-topics attribute for JavaScript filtering
    $topicsList = !empty($app['topics']) ? explode(',', $app['topics']) : [];
    $topicsDataAttr = implode(',', array_map('trim', $topicsList));
    
    $cardDiv = new DivTag(null, ['class' => 'col-md-4 col-lg-3 mb-3 app-card-wrapper', 'data-app-name' => strtolower($app['name']), 'data-app-desc' => strtolower($app['description'] ?? ''), 'data-topics' => $topicsDataAttr]);
    
    $card = new Card(
        null,
        ['class' => 'h-100 app-card '.($isAssigned ? 'border-primary' : ''), 'style' => $isAssigned ? 'background-color: #e7f3ff;' : '']
    );
    
    $cardBody = new DivTag(null, ['class' => 'card-body']);
    
    // Checkbox at top
    $checkboxDiv = new DivTag(null, ['class' => 'form-check']);
    $checkbox = new CheckboxTag('apps[]', $isAssigned, (string)$app['id'], ['class' => 'form-check-input app-checkbox', 'id' => 'app_'.$app['id']]);
    $checkboxLabel = new LabelTag('app_'.$app['id'], '', ['class' => 'form-check-label']);
    $checkboxDiv->addItem($checkbox);
    $checkboxDiv->addItem($checkboxLabel);
    $cardBody->addItem($checkboxDiv);
    
    // App logo centered
    $logoDiv = new DivTag(null, ['class' => 'text-center my-3']);
    $appImage = empty($app['image']) ? 'appimage.php?uuid='.$app['uuid'] : $app['image'];
    $displayName = $app['localized_name'] ?? $app['name'];
    $logoDiv->addItem(new \Ease\Html\ImgTag($appImage, $displayName, ['style' => 'max-width: 80px; max-height: 80px;']));
    $cardBody->addItem($logoDiv);
    
    // App name (localized) with link to detail
    $nameWithLink = new \Ease\Html\H5Tag(null, ['class' => 'card-title text-center']);
    $nameWithLink->addItem(new \Ease\Html\ATag('app.php?id='.$app['id'], $displayName, ['class' => 'text-decoration-none app-detail-link', 'title' => _('View application details'), 'onclick' => 'event.stopPropagation();']));
    $cardBody->addItem($nameWithLink);
    
    // App description (localized)
    $displayDescription = $app['localized_description'] ?? $app['description'] ?? '';
    if (!empty($displayDescription)) {
        $desc = mb_strlen($displayDescription) > 100 ? mb_substr($displayDescription, 0, 97).'...' : $displayDescription;
        $cardBody->addItem(new PTag(new SmallTag($desc, ['class' => 'text-muted']), ['class' => 'card-text text-center']));
    }
    
    // Show topics as badges
    if (!empty($app['topics'])) {
        $topicBadges = new DivTag(null, ['class' => 'mb-2 topic-badges text-center']);
        foreach ($topicsList as $topic) {
            $topic = trim($topic);
            if (!empty($topic)) {
                $badge = new \Ease\TWB4\Badge('secondary', $topic, ['class' => 'mr-1 mb-1 topic-badge']);
                $topicBadges->addItem($badge);
            }
        }
        $cardBody->addItem($topicBadges);
    }
    
    $card->addItem($cardBody);
    $cardDiv->addItem($card);
    $cardsRow->addItem($cardDiv);
}

$addAppForm->addItem($cardsRow);

// Fixed submit button
$addAppForm->addItem(new \Ease\Html\HrTag());
$addAppForm->addItem(new SubmitButton('🍏 '._('Apply Changes'), 'success btn-lg btn-block', ['style' => 'position: sticky; bottom: 10px; z-index: 100;']));

// Create a container with filters and form
$contentContainer = new DivTag();
$contentContainer->addItem($filterContainer);
$contentContainer->addItem($addAppForm);

WebPage::singleton()->container->addItem(new CompanyPanel($companer, $contentContainer));

// Add CSS
WebPage::singleton()->addCSS(<<<'CSS'
.app-card {
    cursor: pointer;
    transition: all 0.2s;
}
.app-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
.app-card-wrapper[data-hidden="true"] {
    display: none;
}
.app-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
}
.topic-badge {
    transition: all 0.3s ease-in-out;
    font-size: 0.75rem;
}
.app-detail-link {
    color: inherit;
}
.app-detail-link:hover {
    color: #007bff;
    text-decoration: underline !important;
}
CSS);

// Add JavaScript for interactivity
WebPage::singleton()->addJavaScript(<<<'JS'
$(document).ready(function() {
    // Click on card to toggle checkbox
    $('.app-card').click(function(e) {
        if (!$(e.target).is('input[type="checkbox"]')) {
            var checkbox = $(this).find('.app-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });
    
    // Update card styling when checkbox changes
    $('.app-checkbox').change(function() {
        var card = $(this).closest('.app-card');
        if ($(this).is(':checked')) {
            card.addClass('border-primary').css('background-color', '#e7f3ff');
        } else {
            card.removeClass('border-primary').css('background-color', '');
        }
        updateCount();
    });
    
    // Search functionality
    $('#app_search').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        $('.app-card-wrapper').each(function() {
            var appName = $(this).data('app-name');
            var appDesc = $(this).data('app-desc') || '';
            var isVisible = $(this).css('display') !== 'none';
            if (appName.includes(searchText) || appDesc.includes(searchText)) {
                $(this).attr('data-hidden', 'false').show();
            } else {
                $(this).attr('data-hidden', 'true').hide();
            }
        });
    });
    
    // Topic filtering functionality with localStorage support
    const STORAGE_KEY = 'multiflexi_companyapps_topic_filter';
    const DEFAULT_ALL_SELECTED = 'all_topics_selected';
    
    var topicFilterSelectize = null;
    var allAvailableTopics = [];
    
    // Function to save topic selection to localStorage
    function saveTopicSelection(selectedTopics) {
        try {
            if (selectedTopics.length === allAvailableTopics.length) {
                localStorage.setItem(STORAGE_KEY, DEFAULT_ALL_SELECTED);
            } else if (selectedTopics.length === 0) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify([]));
            } else {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(selectedTopics));
            }
        } catch (e) {
            console.warn('Failed to save topic selection to localStorage:', e);
        }
    }
    
    // Function to load topic selection from localStorage
    function loadTopicSelection() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (!saved || saved === DEFAULT_ALL_SELECTED) {
                return allAvailableTopics.slice();
            }
            const parsed = JSON.parse(saved);
            if (Array.isArray(parsed)) {
                return parsed.filter(topic => allAvailableTopics.includes(topic));
            }
            return allAvailableTopics.slice();
        } catch (e) {
            console.warn('Failed to load topic selection from localStorage:', e);
            return allAvailableTopics.slice();
        }
    }
    
    // Function to filter applications based on selected topics
    function filterApplicationsByTopics(selectedTopics) {
        var topicsArray = [];
        if (typeof selectedTopics === 'string' && selectedTopics.length > 0) {
            topicsArray = selectedTopics.split(',');
        } else if (Array.isArray(selectedTopics)) {
            topicsArray = selectedTopics;
        }
        
        var visibleCount = 0;
        $('.app-card-wrapper').each(function() {
            var cardTopics = $(this).attr('data-topics') || '';
            var cardTopicsArray = cardTopics.split(',').map(function(topic) {
                return topic.trim();
            }).filter(function(topic) {
                return topic.length > 0;
            });
            
            var shouldShow = true;
            
            // If no topics are selected, hide all applications
            if (topicsArray.length === 0) {
                shouldShow = false;
            } else {
                // Check if card has at least one matching topic
                shouldShow = false;
                for (var i = 0; i < topicsArray.length; i++) {
                    if (cardTopicsArray.indexOf(topicsArray[i]) !== -1) {
                        shouldShow = true;
                        break;
                    }
                }
            }
            
            if (shouldShow) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
                // Uncheck checkbox if hidden card was selected
                var checkbox = $(this).find('.app-checkbox');
                if (checkbox.is(':checked')) {
                    checkbox.prop('checked', false).trigger('change');
                }
            }
        });
        
        // Highlight selected topics
        highlightSelectedTopics(topicsArray);
        updateCount();
    }
    
    // Function to highlight selected topics on application cards
    function highlightSelectedTopics(selectedTopics) {
        $('.topic-badge').each(function() {
            var topicText = $(this).text().trim();
            
            // Reset badge styling
            $(this).removeClass('badge-primary badge-warning badge-success badge-info').addClass('badge-secondary');
            $(this).css({
                'font-weight': 'normal',
                'box-shadow': 'none',
                'transform': 'scale(1)',
                'border': 'none'
            });
            
            // Highlight if this topic is selected
            if (selectedTopics.includes(topicText)) {
                $(this).removeClass('badge-secondary').addClass('badge-primary');
                $(this).css({
                    'font-weight': 'bold',
                    'box-shadow': '0 2px 6px rgba(0,123,255,0.4)',
                    'transform': 'scale(1.1)',
                    'border': '2px solid #0056b3',
                    'background-color': '#007bff',
                    'color': '#ffffff'
                });
            }
        });
    }
    
    // Initialize topic filter selectize
    setTimeout(function() {
        var element = $('#topic_filterpillBox');
        if (element.length > 0 && element[0].selectize) {
            topicFilterSelectize = element[0].selectize;
            var options = topicFilterSelectize.options;
            allAvailableTopics = Object.keys(options);
            
            // Load saved selection or use all topics for first visit
            var savedSelection = loadTopicSelection();
            topicFilterSelectize.setValue(savedSelection, true);
            filterApplicationsByTopics(savedSelection);
            
            // Listen for changes in topic selection
            topicFilterSelectize.on('change', function(value) {
                var selectedTopics = Array.isArray(value) ? value : (value ? value.split(',') : []);
                saveTopicSelection(selectedTopics);
                filterApplicationsByTopics(selectedTopics);
            });
            
            // Handle reset filter button
            $('#reset-topic-filter').on('click', function() {
                topicFilterSelectize.setValue(allAvailableTopics, true);
                saveTopicSelection(allAvailableTopics);
                filterApplicationsByTopics(allAvailableTopics);
            });
        } else {
            setTimeout(arguments.callee, 500);
        }
    }, 1000);
    
    // Update selected count
    function updateCount() {
        var count = $('.app-checkbox:checked').length;
        $('#selected-count').text(count);
    }
});
JS);

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
