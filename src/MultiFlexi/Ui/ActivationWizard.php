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

/**
 * Activation Wizard Component.
 *
 * Multi-step wizard for activating applications in companies.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class ActivationWizard extends \Ease\Html\DivTag
{
    /**
     * Current wizard step.
     */
    private int $currentStep;

    /**
     * Total wizard steps.
     */
    private int $totalSteps = 7;

    /**
     * Wizard data stored in session.
     */
    private array $wizardData;

    /**
     * Constructor.
     *
     * @param int $step Current step number (1-4)
     */
    public function __construct(int $step = 1)
    {
        parent::__construct(null, ['class' => 'activation-wizard']);
        $this->currentStep = max(1, min($step, $this->totalSteps));
        $this->initWizardData();
    }

    /**
     * Render wizard.
     */
    public function afterAdd(): void
    {
                // Include selectize assets for step 2 (application selection with topic filtering)
        if ($this->currentStep === 2) {
            WebPage::singleton()->includeJavaScript('js/selectize.min.js');
            WebPage::singleton()->includeCss('css/selectize.bootstrap4.css');
            
            // Add custom CSS for topic highlighting
            WebPage::singleton()->addCSS('
.topic-badge {
    transition: all 0.3s ease-in-out;
    cursor: pointer;
    position: relative;
}

.topic-badge.badge-primary {
    background-color: #007bff !important;
    border-color: #0056b3 !important;
    color: #ffffff !important;
    font-weight: bold !important;
    box-shadow: 0 2px 6px rgba(0,123,255,0.4) !important;
    transform: scale(1.1) !important;
    z-index: 2 !important;
}

.topic-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.topic-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

@keyframes highlightPulse {
    0% { box-shadow: 0 2px 6px rgba(0,123,255,0.4); }
    50% { box-shadow: 0 4px 12px rgba(0,123,255,0.6); }
    100% { box-shadow: 0 2px 6px rgba(0,123,255,0.4); }
}

.topic-badge.badge-primary {
    animation: highlightPulse 2s ease-in-out infinite;
}
            ');
        }
        
        // Include Summernote WYSIWYG editor assets for step 3 (RunTemplate creation)
        if ($this->currentStep === 3) {
            WebPage::singleton()->includeJavaScript('js/summernote-bs4.min.js');
            WebPage::singleton()->includeCss('css/summernote-bs4.min.css');
        }
        
        $this->addItem($this->renderStepIndicator());
        $this->addItem($this->renderStepContent());
        $this->addItem($this->renderNavigation());
    }

    /**
     * Get wizard data.
     */
    public function getWizardData(): array
    {
        return $this->wizardData;
    }

    /**
     * Update wizard data.
     */
    public static function updateWizardData(array $data): void
    {
        if (!isset($_SESSION['activation_wizard'])) {
            $_SESSION['activation_wizard'] = [];
        }

        $_SESSION['activation_wizard'] = array_merge($_SESSION['activation_wizard'], $data);
    }

    /**
     * Clear wizard data.
     */
    public static function clearWizardData(): void
    {
        unset($_SESSION['activation_wizard']);
    }

    /**
     * Initialize wizard data from session.
     */
    private function initWizardData(): void
    {
        if (!isset($_SESSION['activation_wizard'])) {
            $_SESSION['activation_wizard'] = [
                'company_id' => null,
                'app_id' => null,
                'runtemplate_name' => null,
                'runtemplate_id' => null,
                'configuration' => [],
            ];
        }

        $this->wizardData = $_SESSION['activation_wizard'];
    }

    /**
     * Render step indicator.
     */
    private function renderStepIndicator(): \Ease\Html\DivTag
    {
        $stepIndicator = new \Ease\Html\DivTag(null, ['class' => 'wizard-steps mb-4']);
        $steps = [
            1 => _('Choose Company'),
            2 => _('Choose Application'),
            3 => _('Create RunTemplate'),
            4 => _('Assign Credentials'),
            5 => _('Configure'),
            6 => _('Actions'),
            7 => _('Summary'),
        ];

        // Add selected company logo
        if (!empty($this->wizardData['company_id'])) {
            $company = new \MultiFlexi\Company($this->wizardData['company_id']);
            $logo = $company->getDataValue('logo');

            if ($logo) {
                $steps[1] .= ' <img src="'.$logo.'" style="height: 20px; margin-left: 5px;" />';
            }
        }

        // Add selected application logo
        if (!empty($this->wizardData['app_id'])) {
            $app = new \MultiFlexi\Application($this->wizardData['app_id']);
            $uuid = $app->getDataValue('uuid');

            if ($uuid) {
                $steps[2] .= ' <img src="appimage.php?uuid='.$uuid.'" style="height: 20px; margin-left: 5px;" />';
            }
        }

        // Add RunTemplate ID - show after creation (step 4 onwards)
        if (!empty($this->wizardData['runtemplate_id']) && $this->currentStep >= 4) {
            $runTemplate = new \MultiFlexi\RunTemplate($this->wizardData['runtemplate_id']);
            $actualId = $runTemplate->getMyKey();

            if ($actualId) {
                $steps[3] .= ' <span class="badge badge-success ml-2">⚗️ #'.$actualId.'</span>';
            }
        }

        $stepList = new \Ease\Html\UlTag(null, ['class' => 'nav nav-pills nav-fill']);

        foreach ($steps as $num => $label) {
            $itemClass = ['nav-item'];
            $linkClass = ['nav-link'];

            if ($num === $this->currentStep) {
                $linkClass[] = 'active';
            } elseif ($num < $this->currentStep) {
                $linkClass[] = 'text-success';
            } else {
                $linkClass[] = 'disabled';
            }

            $stepNumber = new \Ease\Html\SpanTag($num, ['class' => 'badge badge-pill badge-light mr-2']);
            $link = new \Ease\Html\ATag('#', [$stepNumber, $label], ['class' => implode(' ', $linkClass)]);

            $stepList->addItem(new \Ease\Html\LiTag($link, ['class' => implode(' ', $itemClass)]));
        }

        $stepIndicator->addItem($stepList);

        return $stepIndicator;
    }

    /**
     * Render current step content.
     */
    private function renderStepContent(): \Ease\Html\DivTag
    {
        $content = new \Ease\Html\DivTag(null, ['class' => 'wizard-content card']);
        $cardBody = $content->addItem(new \Ease\Html\DivTag(null, ['class' => 'card-body']));

        switch ($this->currentStep) {
            case 1:
                $cardBody->addItem($this->renderCompanySelection());

                break;
            case 2:
                $cardBody->addItem($this->renderApplicationSelection());

                break;
            case 3:
                $cardBody->addItem($this->renderRunTemplateCreation());

                break;
            case 4:
                $cardBody->addItem($this->renderCredentialSelection());

                break;
            case 5:
                $cardBody->addItem($this->renderConfiguration());

                break;
            case 6:
                $cardBody->addItem($this->renderActions());

                break;
            case 7:
                $cardBody->addItem($this->renderSummary());

                break;
        }

        return $content;
    }

    /**
     * Render company selection step.
     */
    private function renderCompanySelection(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();
        $container->addItem(new \Ease\Html\H3Tag(_('Select Company')));
        $container->addItem(new \Ease\Html\PTag(_('Choose the company where you want to activate the application.')));

        $company = new \MultiFlexi\Company();
        $companies = $company->listingQuery()->orderBy('name')->fetchAll();

        if (empty($companies)) {
            $container->addItem(new \Ease\TWB4\Alert('warning', _('No companies found. Please create a company first.')));
            $container->addItem(new \Ease\TWB4\LinkButton('companysetup.php', _('Create Company'), 'primary'));

            return $container;
        }

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=2', 'id' => 'wizardForm']);

        $companyCards = new \Ease\TWB4\Row();

        foreach ($companies as $companyData) {
            $isSelected = ($this->wizardData['company_id'] ?? null) === $companyData['id'];
            $cardClass = $isSelected ? 'border-primary bg-light' : '';

            $card = new \Ease\Html\DivTag(null, ['class' => 'card mb-3 '.$cardClass, 'style' => 'cursor: pointer;']);
            $cardBody = $card->addItem(new \Ease\Html\DivTag(null, ['class' => 'card-body']));

            if (!empty($companyData['logo'])) {
                $cardBody->addItem(new \Ease\Html\ImgTag($companyData['logo'], $companyData['name'], ['class' => 'img-fluid mb-2', 'style' => 'max-height: 60px;']));
            }

            $cardBody->addItem(new \Ease\Html\H5Tag($companyData['name'], ['class' => 'card-title']));

            if (!empty($companyData['ic'])) {
                $cardBody->addItem(new \Ease\Html\PTag(_('ID').': '.$companyData['ic'], ['class' => 'card-text small']));
            }

            $radio = new \Ease\Html\InputTag('company_id', $companyData['id'], ['type' => 'radio', 'required' => 'required']);

            if ($isSelected) {
                $radio->setTagProperty('checked', 'checked');
            }

            $cardBody->addItem($radio);
            $cardBody->addItem(' '._('Select this company'));

            $companyCards->addColumn(4, $card);

            // JavaScript will be added once after the loop
        }

        $form->addItem($companyCards);
        $container->addItem($form);

        // Add JavaScript to make company cards clickable
        WebPage::singleton()->addJavaScript(
            <<<'EOD'
document.querySelectorAll('#wizardForm .card').forEach(function(card) {
                card.addEventListener('click', function() {
                    var radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        document.querySelectorAll('#wizardForm .card').forEach(c => {
                            c.classList.remove('border-primary', 'bg-light');
                        });
                        this.classList.add('border-primary', 'bg-light');
                    }
                });
            });
EOD,
            null,
            true,
        );

        return $container;
    }

    /**
     * Render application selection step.
     */
    private function renderApplicationSelection(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['company_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('No company selected. Please go back to step 1.')));

            return $container;
        }

        $company = new \MultiFlexi\Company($this->wizardData['company_id']);
        $container->addItem(new \Ease\Html\H3Tag(_('Select Application for').' '.$company->getRecordName()));
        $container->addItem(new \Ease\Html\PTag(_('Choose an application to activate. Use topic filters to narrow down applications. The application will be assigned to the selected company.')));

        $app = new \MultiFlexi\Application();
        $applications = $app->listingQuery()->where('enabled', true)->orderBy('name')->fetchAll();

        if (empty($applications)) {
            $container->addItem(new \Ease\TWB4\Alert('warning', _('No applications available.')));

            return $container;
        }

        // Collect all unique topics from applications
        $allTopics = [];
        foreach ($applications as $appData) {
            if (!empty($appData['topics'])) {
                $topics = explode(',', $appData['topics']);
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

        // Add topic filter using PillBox
        if (!empty($allTopics)) {
            $container->addItem(new \Ease\Html\H4Tag(_('Filter by Topics')));
            $container->addItem(new \Ease\Html\PTag(_('Select topics to filter applications. All topics are selected by default to show all applications.')));
            
            $filterRow = new \Ease\TWB4\Row();
            
            // Pre-select all topics by default for first-time visitors
            $allTopicIds = array_column($allTopics, 'id');
            $topicFilter = new PillBox('topic_filter', $allTopics, $allTopicIds, ['class' => 'form-control mb-2', 'placeholder' => _('Select topics to filter applications...')]);
            $filterRow->addColumn(10, $topicFilter);
            
            // Add reset filter button
            $resetButton = new \Ease\Html\ButtonTag(_('Reset Filter'), [
                'class' => 'btn btn-outline-secondary btn-sm mb-2',
                'type' => 'button',
                'id' => 'reset-topic-filter',
                'title' => _('Select all topics to show all applications')
            ]);
            $filterRow->addColumn(2, $resetButton);
            
            $container->addItem($filterRow);
            $container->addItem(new \Ease\Html\DivTag('', ['class' => 'mb-4'])); // Add spacing
        }

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=3', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('company_id', (string) $this->wizardData['company_id']));

        $appCards = new \Ease\TWB4\Row();

        foreach ($applications as $appData) {
            $isSelected = ($this->wizardData['app_id'] ?? null) === $appData['id'];
            $cardClass = $isSelected ? 'border-primary bg-light' : '';

            // Add data-topics attribute for JavaScript filtering
            $topicsList = !empty($appData['topics']) ? explode(',', $appData['topics']) : [];
            $topicsDataAttr = implode(',', array_map('trim', $topicsList));

            $card = new \Ease\Html\DivTag(null, [
                'class' => 'card mb-3 h-100 app-card '.$cardClass, 
                'style' => 'cursor: pointer;',
                'data-topics' => $topicsDataAttr
            ]);
            $cardBody = $card->addItem(new \Ease\Html\DivTag(null, ['class' => 'card-body text-center']));

            // Show application logo/image using AppLogo component
            if (!empty($appData['uuid'])) {
                $appLogo = new \Ease\Html\ImgTag('appimage.php?uuid='.$appData['uuid'], $appData['name'], ['class' => 'img-fluid mb-3', 'style' => 'max-height: 80px; max-width: 100%;']);
                $cardBody->addItem($appLogo);
            } else {
                // Fallback icon if no uuid/image
                $cardBody->addItem(new \Ease\Html\DivTag('🧩', ['style' => 'font-size: 60px; margin-bottom: 1rem;']));
            }

            $cardBody->addItem(new \Ease\Html\H5Tag($appData['name'], ['class' => 'card-title']));

            if (!empty($appData['description'])) {
                $cardBody->addItem(new \Ease\Html\PTag($appData['description'], ['class' => 'card-text small text-muted']));
            }

            // Show topics as badges
            if (!empty($appData['topics'])) {
                $topicBadges = new \Ease\Html\DivTag(null, ['class' => 'mb-2 topic-badges']);
                foreach ($topicsList as $topic) {
                    $topic = trim($topic);
                    if (!empty($topic)) {
                        $badge = new \Ease\TWB4\Badge('secondary', $topic, ['class' => 'mr-1 mb-1 topic-badge']);
                        $badge->setTagProperty('data-topic', $topic);
                        $topicBadges->addItem($badge);
                    }
                }
                $cardBody->addItem($topicBadges);
            }

            $radio = new \Ease\Html\InputTag('app_id', $appData['id'], ['type' => 'radio', 'required' => 'required']);

            if ($isSelected) {
                $radio->setTagProperty('checked', 'checked');
            }

            $cardBody->addItem(new \Ease\Html\DivTag([$radio, ' ', _('Select this application')], ['class' => 'mt-3']));

            $appCards->addColumn(4, $card);
        }

        $form->addItem($appCards);
        $container->addItem($form);

        // Add JavaScript to make app cards clickable and handle topic filtering
        WebPage::singleton()->addJavaScript(
            <<<'EOD'
// Make app cards clickable
document.querySelectorAll('.wizard-content .app-card').forEach(function(card) {
    card.addEventListener('click', function() {
        var radio = this.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
            document.querySelectorAll('.wizard-content .app-card').forEach(c => {
                c.classList.remove('border-primary', 'bg-light');
            });
            this.classList.add('border-primary', 'bg-light');
        }
    });
});

// Topic filtering functionality with localStorage support
$(document).ready(function() {
    // Check if selectize is available
    if (typeof $.fn.selectize === 'undefined') {
        console.error('Selectize library is not loaded!');
        return;
    }
    
    // Constants for localStorage
    const STORAGE_KEY = 'multiflexi_topic_filter';
    const DEFAULT_ALL_SELECTED = 'all_topics_selected';
    
    // Get reference to the pillbox selectize instance
    var topicFilterSelectize = null;
    var allAvailableTopics = [];
    
    // Function to save topic selection to localStorage
    function saveTopicSelection(selectedTopics) {
        try {
            if (selectedTopics.length === allAvailableTopics.length) {
                // All topics selected - save special flag
                localStorage.setItem(STORAGE_KEY, DEFAULT_ALL_SELECTED);
            } else if (selectedTopics.length === 0) {
                // No topics selected - save empty array
                localStorage.setItem(STORAGE_KEY, JSON.stringify([]));
            } else {
                // Some topics selected - save the array
                localStorage.setItem(STORAGE_KEY, JSON.stringify(selectedTopics));
            }
            console.log('Saved topic selection to localStorage:', selectedTopics);
        } catch (e) {
            console.warn('Failed to save topic selection to localStorage:', e);
        }
    }
    
    // Function to load topic selection from localStorage
    function loadTopicSelection() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (!saved || saved === DEFAULT_ALL_SELECTED) {
                // First time or all selected - return all topics
                return allAvailableTopics.slice();
            }
            
            const parsed = JSON.parse(saved);
            if (Array.isArray(parsed)) {
                // Filter out topics that no longer exist
                return parsed.filter(topic => allAvailableTopics.includes(topic));
            }
            
            // Fallback - return all topics
            return allAvailableTopics.slice();
        } catch (e) {
            console.warn('Failed to load topic selection from localStorage:', e);
            return allAvailableTopics.slice();
        }
    }
    
    // Wait for selectize to be initialized
    setTimeout(function() {
        console.log('Attempting to initialize topic filter selectize...');
        var element = $('#topic_filterpillBox');
        console.log('Found element:', element.length > 0 ? 'yes' : 'no');
        
        if (element.length > 0 && element[0].selectize) {
            topicFilterSelectize = element[0].selectize;
            console.log('Selectize instance found:', !!topicFilterSelectize);
        } else {
            console.warn('Selectize not initialized yet, trying again...');
            setTimeout(arguments.callee, 500);
            return;
        }
        
        if (topicFilterSelectize) {
            // Get all available topic options
            var options = topicFilterSelectize.options;
            allAvailableTopics = Object.keys(options);
            console.log('Available topics:', allAvailableTopics);
            
            // Load saved selection or use all topics for first visit
            var savedSelection = loadTopicSelection();
            
            // Set the saved/default selection
            topicFilterSelectize.setValue(savedSelection, true);
            
            // Apply initial filter based on loaded selection
            filterApplicationsByTopics(savedSelection);
            
            console.log('Initialized topic filter with selection:', savedSelection);
            
            // Listen for changes in topic selection
            topicFilterSelectize.on('change', function(value) {
                var selectedTopics = Array.isArray(value) ? value : (value ? value.split(',') : []);
                saveTopicSelection(selectedTopics);
                filterApplicationsByTopics(selectedTopics);
            });
            
            // Listen for item removal
            topicFilterSelectize.on('item_remove', function(value) {
                setTimeout(function() {
                    var currentSelection = topicFilterSelectize.getValue();
                    var selectedTopics = Array.isArray(currentSelection) ? currentSelection : (currentSelection ? currentSelection.split(',') : []);
                    saveTopicSelection(selectedTopics);
                    filterApplicationsByTopics(selectedTopics);
                }, 10);
            });
            
            // Handle reset filter button
            $('#reset-topic-filter').on('click', function() {
                console.log('Resetting topic filter to show all applications');
                topicFilterSelectize.setValue(allAvailableTopics, true);
                saveTopicSelection(allAvailableTopics);
                filterApplicationsByTopics(allAvailableTopics);
            });
        } else {
            // Fallback: if no selectize, still highlight all topics by default
            highlightSelectedTopics(allAvailableTopics);
        }
    }, 1000);
    
    // Function to filter applications based on selected topics
    function filterApplicationsByTopics(selectedTopics) {
        console.log('Filtering by topics:', selectedTopics);
        
        var topicsArray = [];
        if (typeof selectedTopics === 'string' && selectedTopics.length > 0) {
            topicsArray = selectedTopics.split(',');
        } else if (Array.isArray(selectedTopics)) {
            topicsArray = selectedTopics;
        }
        
        // Get all application cards
        var appCards = document.querySelectorAll('.app-card');
        var visibleCount = 0;
        
        appCards.forEach(function(card) {
            var cardTopics = card.getAttribute('data-topics') || '';
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
            
            // Show/hide the entire column containing the card
            var cardColumn = card.closest('.col-4, .col-sm-4, .col-md-4, .col-lg-4, .col-xl-4');
            if (cardColumn) {
                if (shouldShow) {
                    cardColumn.style.display = '';
                    visibleCount++;
                    console.log('Showing application:', card.querySelector('.card-title').textContent);
                } else {
                    cardColumn.style.display = 'none';
                    console.log('Hiding application:', card.querySelector('.card-title').textContent);
                    
                    // Uncheck radio if hidden card was selected
                    var radio = card.querySelector('input[type="radio"]');
                    if (radio && radio.checked) {
                        radio.checked = false;
                        card.classList.remove('border-primary', 'bg-light');
                    }
                }
            }
        });
        
        console.log('Visible applications:', visibleCount, '/', appCards.length);
        
        // Highlight selected topics on visible application cards
        highlightSelectedTopics(topicsArray);
        
        // Show message if no applications are visible
        var existingMessage = document.querySelector('.no-applications-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        if (visibleCount === 0 && topicsArray.length > 0) {
            var form = document.querySelector('#wizardForm');
            if (form) {
                var noAppsMessage = document.createElement('div');
                noAppsMessage.className = 'alert alert-info no-applications-message';
                noAppsMessage.innerHTML = '<strong>No applications found</strong> for the selected topics. Try selecting different topics or clear the filter to see all applications.';
                form.insertBefore(noAppsMessage, form.querySelector('.row'));
            }
        }
    }
    
    // Function to highlight selected topics on application cards
    function highlightSelectedTopics(selectedTopics) {
        console.log('Highlighting selected topics:', selectedTopics);
        
        var appCards = document.querySelectorAll('.app-card');
        
        appCards.forEach(function(card) {
            // Find all topic badges in this card using the specific class
            var topicBadges = card.querySelectorAll('.topic-badge');
            
            topicBadges.forEach(function(badge) {
                var topicText = badge.textContent.trim();
                
                // Reset badge styling first
                badge.classList.remove('badge-primary', 'badge-warning', 'badge-success', 'badge-info');
                badge.classList.add('badge-secondary');
                badge.style.fontWeight = 'normal';
                badge.style.boxShadow = 'none';
                badge.style.transform = 'scale(1)';
                badge.style.border = 'none';
                
                // Highlight if this topic is selected
                if (selectedTopics.includes(topicText)) {
                    badge.classList.remove('badge-secondary');
                    badge.classList.add('badge-primary');
                    badge.style.fontWeight = 'bold';
                    badge.style.boxShadow = '0 2px 6px rgba(0,123,255,0.4)';
                    badge.style.transform = 'scale(1.1)';
                    badge.style.border = '2px solid #0056b3';
                    badge.style.backgroundColor = '#007bff';
                    badge.style.color = '#ffffff';
                }
                
                // Apply smooth transition for all badges
                badge.style.transition = 'all 0.3s ease-in-out';
            });
        });
    }
});
EOD,
            null,
            true,
        );

        return $container;
    }

    /**
     * Render RunTemplate creation step.
     */
    private function renderRunTemplateCreation(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['company_id']) || empty($this->wizardData['app_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('Missing company or application. Please complete previous steps.')));

            return $container;
        }

        $company = new \MultiFlexi\Company($this->wizardData['company_id']);
        $app = new \MultiFlexi\Application($this->wizardData['app_id']);

        $container->addItem(new \Ease\Html\H3Tag(_('Create RunTemplate')));
        $container->addItem(new \Ease\Html\PTag(sprintf(_('Creating RunTemplate for %s in %s'), $app->getRecordName(), $company->getRecordName())));

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=4', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('company_id', (string) $this->wizardData['company_id']));
        $form->addItem(new \Ease\Html\InputHiddenTag('app_id', (string) $this->wizardData['app_id']));

        // Default RunTemplate name: "Company / Application"
        $defaultName = $company->getRecordName().' / '.$app->getRecordName();
        $nameInput = new \Ease\Html\InputTextTag('runtemplate_name', $this->wizardData['runtemplate_name'] ?? $defaultName, ['class' => 'form-control', 'required' => 'required', 'placeholder' => _('RunTemplate name')]);
        $form->addItem(new \Ease\TWB4\FormGroup(_('RunTemplate Name'), $nameInput, '', _('Descriptive name for this configuration')));

        // Add note field with WYSIWYG editor
        $noteValue = $this->wizardData['runtemplate_note'] ?? '';
        $noteTextarea = new \Ease\Html\TextareaTag('runtemplate_note', $noteValue, [
            'class' => 'form-control summernote-editor',
            'id' => 'runtemplate_note',
            'placeholder' => _('Add notes about this RunTemplate...'),
            'rows' => 6
        ]);
        $form->addItem(new \Ease\TWB4\FormGroup(_('Notes'), $noteTextarea, '', _('Optional notes and documentation for this RunTemplate')));

        $intervalSelect = new IntervalChooser('interv', 'n', ['class' => 'form-control']);
        $form->addItem(new \Ease\TWB4\FormGroup(_('Schedule Interval'), $intervalSelect, '', _('How often should this run?')));

        $container->addItem($form);

        // Add JavaScript to initialize Summernote WYSIWYG editor for the note field
        WebPage::singleton()->addJavaScript(
            <<<'EOD'
$(document).ready(function() {
    // Initialize Summernote for the note field
    $('.summernote-editor').summernote({
        height: 200,
        placeholder: 'Add notes about this RunTemplate...',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onChange: function(contents, $editable) {
                // Update the textarea value when content changes
                $(this).val(contents);
            }
        }
    });
});
EOD,
            null,
            true,
        );

        return $container;
    }

    /**
     * Render credential selection step.
     */
    private function renderCredentialSelection(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['runtemplate_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('RunTemplate not created. Please complete previous steps.')));

            return $container;
        }

        $runTemplate = new \MultiFlexi\RunTemplate($this->wizardData['runtemplate_id']);
        $app = new \MultiFlexi\Application($this->wizardData['app_id']);
        $company = new \MultiFlexi\Company($this->wizardData['company_id']);

        $headingText = _('Assign Credentials');
        if ($runTemplate->getMyKey()) {
            $headingText .= ' <span class="badge badge-success">#'.$runTemplate->getMyKey().'</span>';
        }
        $container->addItem(new \Ease\Html\H3Tag($headingText));
        $container->addItem(new \Ease\Html\PTag(sprintf(_('Select credentials for %s'), $app->getRecordName())));

        // Get application requirements
        $requirements = $app->getRequirements();

        if (empty($requirements)) {
            $container->addItem(new \Ease\TWB4\Alert('info', _('This application does not require any credentials.')));
            // Auto-proceed button
            $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=5', 'id' => 'wizardForm']);
            $form->addItem(new \Ease\Html\InputHiddenTag('runtemplate_id', (string) $this->wizardData['runtemplate_id']));
            $container->addItem($form);

            return $container;
        }

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=5', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('runtemplate_id', (string) $this->wizardData['runtemplate_id']));

        $credentialType = new \MultiFlexi\CredentialType();
        $credential = new \MultiFlexi\Credential();

        foreach ($requirements as $requirement) {
            $form->addItem(new \Ease\Html\H4Tag($requirement));

            // Find credential type by class name
            $credType = $credentialType->listingQuery()
                ->where('class', $requirement)
                ->fetch();

            if (!$credType) {
                $alert = new \Ease\TWB4\Alert('warning', sprintf(_('Credential type %s not found.'), $requirement));
                $alert->addItem(new \Ease\TWB4\LinkButton('credentialtype.php?class='.$requirement, _('Create Credential Type'), 'primary btn-sm'));
                $form->addItem($alert);

                continue;
            }

            // Get company credentials of this type
            $companyCredentials = $credential->listingQuery()
                ->where('company_id', $company->getMyKey())
                ->where('credential_type_id', $credType['id'])
                ->fetchAll();

            if (empty($companyCredentials)) {
                $alert = new \Ease\TWB4\Alert('warning', sprintf(_('No credentials found for %s.'), $requirement));
                $alert->addItem(new \Ease\TWB4\LinkButton('credential.php?company_id='.$company->getMyKey().'&credential_type_id='.$credType['id'], _('Create Credential'), 'primary btn-sm'));
                $form->addItem($alert);

                continue;
            }

            // Create select for credentials
            $select = new \Ease\Html\SelectTag('credential['.$requirement.']');
            $select->addTagClass('form-control');
            $select->addItem(new \Ease\Html\OptionTag(_('-- Select Credential --'), ''));

            foreach ($companyCredentials as $cred) {
                $select->addItem(new \Ease\Html\OptionTag($cred['name'], (string) $cred['id']));
            }

            $formGroup = new \Ease\TWB4\FormGroup(
                sprintf(_('Credential for %s'), $requirement),
                $select,
                '',
                sprintf(_('Select which %s credential to use'), $requirement),
            );
            $form->addItem($formGroup);
        }

        $container->addItem($form);

        return $container;
    }

    /**
     * Render configuration step.
     */
    private function renderConfiguration(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['runtemplate_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('RunTemplate not created. Please complete previous steps.')));

            return $container;
        }

        $runTemplate = new \MultiFlexi\RunTemplate($this->wizardData['runtemplate_id']);
        $app = new \MultiFlexi\Application($this->wizardData['app_id']);

        $actualId = $runTemplate->getMyKey();
        $configTitle = _('Configure').' '.$runTemplate->getRecordName();
        if (!empty($actualId)) {
            $configTitle .= ' <span class="badge badge-success ml-2">#'.$actualId.'</span>';
        }
        $container->addItem(new \Ease\Html\H3Tag($configTitle, ['class' => 'd-flex align-items-center']));

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=6', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('runtemplate_id', (string) $this->wizardData['runtemplate_id']));

        // Get application environment fields and runtemplate fields
        $confField = new \MultiFlexi\Conffield();
        $appConfigs = $confField->getAppConfigs($app);
        $runTemplateFields = $runTemplate->getEnvironment();
        $customized = $runTemplate->getRuntemplateEnvironment();
        $appConfigs->addFields($customized);

        if (empty($appConfigs->getFields())) {
            $container->addItem(new \Ease\TWB4\Alert('info', _('This application does not require any configuration.')));
        } else {
            foreach ($appConfigs as $fieldName => $field) {
                $runTemplateField = $runTemplateFields->getFieldByCode($fieldName);

                if ($runTemplateField) {
                    // Field is filled by credential
                    $runTemplateFieldSource = $runTemplateField->getSource();

                    if (\Ease\Functions::isSerialized($runTemplateFieldSource)) {
                        $credential = unserialize($runTemplateFieldSource);

                        if ($credential) {
                            $credentialType = $credential->getCredentialType();
                            $credentialLink = new \Ease\Html\ATag('credential.php?id='.$credential->getMyKey(), new \Ease\Html\SmallTag($credential->getRecordName()));
                            $formIcon = new \Ease\Html\ImgTag('images/'.$runTemplateField->getLogo(), (string) $credentialType->getRecordName(), ['height' => 20, 'title' => $credentialType->getRecordName()]);
                            $credentialTypeLink = new \Ease\Html\ATag('credentialtype.php?id='.$credentialType->getMyKey(), $formIcon);
                            $inputCaption = new \Ease\Html\SpanTag([$credentialTypeLink, new \Ease\Html\StrongTag($fieldName), '&nbsp;', $credentialLink]);

                            $input = $this->createConfigInput($field, $fieldName, $runTemplateField->getValue());
                            $input->setTagProperty('disabled', '1');
                            $form->addItem(new \Ease\TWB4\FormGroup($inputCaption, $input, $field->getDescription(), ''));
                        } else {
                            $input = $this->createConfigInput($field, $fieldName);
                            $form->addItem(new \Ease\TWB4\FormGroup($fieldName, $input, $field->getDescription(), ''));
                        }
                    } else {
                        $input = $this->createConfigInput($field, $fieldName);
                        $form->addItem(new \Ease\TWB4\FormGroup($fieldName, $input, $field->getDescription(), ''));
                    }
                } else {
                    // Simple field without credential
                    $input = $this->createConfigInput($field, $fieldName);
                    $form->addItem(new \Ease\TWB4\FormGroup($fieldName, $input, $field->getDescription(), ''));
                }
            }
        }

        $container->addItem($form);

        return $container;
    }

    /**
     * Create configuration input based on field type.
     *
     * @param \MultiFlexi\ConfigField $field
     * @param string                  $fieldName
     * @param mixed                   $overrideValue Optional value to override default
     *
     * @return \Ease\Html\Tag
     */
    private function createConfigInput($field, $fieldName, $overrideValue = null)
    {
        $type = $field->getType();
        $value = $overrideValue ?? ($this->wizardData['configuration'][$fieldName] ?? $field->getValue());

        switch ($type) {
            case 'bool':
            case 'boolean':
                $input = new \Ease\Html\InputTag($fieldName, '1', ['type' => 'checkbox', 'class' => 'form-check-input']);

                if ($value) {
                    $input->setTagProperty('checked', 'checked');
                }

                return new \Ease\Html\DivTag($input, ['class' => 'form-check']);
            case 'password':
                return new \Ease\Html\InputTag($fieldName, $value, ['type' => 'password', 'class' => 'form-control']);
            case 'int':
            case 'integer':
                return new \Ease\Html\InputTag($fieldName, $value, ['type' => 'number', 'class' => 'form-control']);
            case 'file-path':
                return new \Ease\Html\InputTag($fieldName, $value, ['type' => 'file', 'class' => 'form-control-file']);

            default:
                return new \Ease\Html\InputTextTag($fieldName, $value, ['class' => 'form-control']);
        }
    }

    /**
     * Render navigation buttons.
     */
    private function renderNavigation(): \Ease\Html\DivTag
    {
        $nav = new \Ease\Html\DivTag(null, ['class' => 'wizard-navigation mt-4 d-flex justify-content-between']);

        // Previous button
        if ($this->currentStep > 1) {
            $prevButton = new \Ease\TWB4\LinkButton('activation-wizard.php?step='.($this->currentStep - 1), _('Previous'), 'secondary');
            $nav->addItem($prevButton);
        } else {
            $nav->addItem(new \Ease\Html\DivTag()); // Empty div for spacing
        }

        // Middle section - Create Company button for step 1, Create Application button for step 2
        if ($this->currentStep === 1) {
            $createCompanyButton = new \Ease\TWB4\LinkButton('companysetup.php', '➕ '._('Create Company'), 'info');
            $nav->addItem($createCompanyButton);
        } elseif ($this->currentStep === 2) {
            $createApplicationButton = new \Ease\TWB4\LinkButton('app.php', '🧩 '._('Create Application'), 'info');
            $nav->addItem($createApplicationButton);
        } else {
            $nav->addItem(new \Ease\Html\DivTag()); // Empty div for spacing
        }

        // Next/Finish button
        if ($this->currentStep < $this->totalSteps - 1) {
            $nextButton = new \Ease\Html\ButtonTag(_('Next'), ['class' => 'btn btn-primary', 'type' => 'submit', 'form' => 'wizardForm']);
            $nav->addItem($nextButton);
        } elseif ($this->currentStep === $this->totalSteps - 1) {
            // Step 6 (Actions) - button to save and go to summary
            $finishButton = new \Ease\Html\ButtonTag(_('Finish & View Summary'), ['class' => 'btn btn-success', 'type' => 'submit', 'form' => 'wizardForm']);
            $nav->addItem($finishButton);
        } elseif ($this->currentStep === $this->totalSteps) {
            // Step 7 (Summary) - no next button, only links in the content
            $nav->addItem(new \Ease\Html\DivTag()); // Empty div for spacing
        }

        return $nav;
    }

    /**
     * Render summary/completion step.
     */
    private function renderSummary(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['runtemplate_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('RunTemplate not created. Please complete previous steps.')));

            return $container;
        }

        $runTemplate = new \MultiFlexi\RunTemplate($this->wizardData['runtemplate_id']);
        $app = new \MultiFlexi\Application($this->wizardData['app_id']);
        $company = new \MultiFlexi\Company($this->wizardData['company_id']);

        $container->addItem(new \Ease\Html\H3Tag('🎉 '._('Activation Complete')));
        $container->addItem(new \Ease\Html\PTag('🚀 '._('Your RunTemplate has been successfully created and configured!')));
        $container->addItem(new \Ease\Html\HrTag());
        $container->addItem(new \Ease\Html\H4Tag(_('RunTemplate Summary')));

        // Create summary table
        $summaryTable = new \Ease\Html\TableTag(null, ['class' => 'table table-bordered']);

        // Get RunTemplate ID - use actual ID from database object
        $runtemplateId = $runTemplate->getMyKey() ?: $this->wizardData['runtemplate_id'];
        $summaryTable->addRowColumns([
            new \Ease\Html\StrongTag(_('RunTemplate ID')),
            '#'.$runtemplateId,
        ]);

        // Get RunTemplate name - use the object's name or fallback to wizard data
        $runtemplateName = $runTemplate->getRecordName() ?: ($this->wizardData['runtemplate_name'] ?? _('Unknown'));
        $summaryTable->addRowColumns([
            new \Ease\Html\StrongTag(_('Name')),
            $runtemplateName,
        ]);
        $summaryTable->addRowColumns([
            new \Ease\Html\StrongTag(_('Application')),
            new \Ease\Html\SpanTag([
                $app->getDataValue('uuid') ? new \Ease\Html\ImgTag('appimage.php?uuid='.$app->getDataValue('uuid'), $app->getRecordName(), ['height' => '20', 'style' => 'margin-right: 5px;']) : '',
                $app->getRecordName(),
            ]),
        ]);
        $summaryTable->addRowColumns([
            new \Ease\Html\StrongTag(_('Company')),
            new \Ease\Html\SpanTag([
                $company->getDataValue('logo') ? new \Ease\Html\ImgTag($company->getDataValue('logo'), $company->getRecordName(), ['height' => '20', 'style' => 'margin-right: 5px;']) : '',
                $company->getRecordName(),
            ]),
        ]);
        $summaryTable->addRowColumns([
            new \Ease\Html\StrongTag(_('Interval')),
            \MultiFlexi\RunTemplate::codeToInterval($runTemplate->getDataValue('interv')),
        ]);

        // Show assigned credentials
        $credentials = $runTemplate->getAssignedCredentials();

        if (!empty($credentials)) {
            $credList = new \Ease\Html\UlTag();

            foreach ($credentials as $cred) {
                $credObj = new \MultiFlexi\Credential($cred['credential_id']);
                $credType = $credObj->getCredentialType();
                $credList->addItem(new \Ease\Html\LiTag([
                    new \Ease\Html\ImgTag('images/'.$credType->getLogo(), $credType->getRecordName(), ['height' => '16', 'style' => 'margin-right: 5px;']),
                    $credObj->getRecordName().' ('.$credType->getRecordName().')',
                ]));
            }

            $summaryTable->addRowColumns([
                new \Ease\Html\StrongTag(_('Credentials')),
                $credList,
            ]);
        }

        // Show actions
        $successActions = $runTemplate->getDataValue('success') ? unserialize($runTemplate->getDataValue('success')) : [];
        $failActions = $runTemplate->getDataValue('fail') ? unserialize($runTemplate->getDataValue('fail')) : [];

        $actionsEnabled = [];

        foreach ($successActions as $action => $enabled) {
            if ($enabled) {
                $actionsEnabled[] = '✅ '.$action.' ('._('on success').')';
            }
        }

        foreach ($failActions as $action => $enabled) {
            if ($enabled) {
                $actionsEnabled[] = '❌ '.$action.' ('._('on failure').')';
            }
        }

        if (!empty($actionsEnabled)) {
            $actionsList = new \Ease\Html\UlTag();

            foreach ($actionsEnabled as $actionText) {
                $actionsList->addItem(new \Ease\Html\LiTag($actionText));
            }

            $summaryTable->addRowColumns([
                new \Ease\Html\StrongTag(_('Actions')),
                $actionsList,
            ]);
        }

        $container->addItem($summaryTable);
        $container->addItem(new \Ease\Html\HrTag());

        // Action buttons
        $buttonRow = new \Ease\TWB4\Row();
        $buttonRow->addColumn(
            3,
            new \Ease\TWB4\LinkButton(
                'runtemplate.php?id='.$runtemplateId,
                '⚗️ '._('View RunTemplate'),
                'primary btn-lg btn-block',
            ),
        );
        $buttonRow->addColumn(
            3,
            new \Ease\TWB4\LinkButton(
                'schedule.php?app_id='.$app->getMyKey().'&company_id='.$company->getMyKey().'&runtemplate_id='.$runtemplateId,
                '📅 '._('Schedule'),
                'info btn-lg btn-block',
            ),
        );
        $buttonRow->addColumn(
            3,
            new \Ease\TWB4\LinkButton(
                'runtemplates.php',
                '📋 '._('All RunTemplates'),
                'secondary btn-lg btn-block',
            ),
        );
        $buttonRow->addColumn(
            3,
            new \Ease\TWB4\LinkButton(
                'activation-wizard.php?reset=1',
                '🌟 '._('New Activation'),
                'success btn-lg btn-block',
            ),
        );

        $container->addItem($buttonRow);

        return $container;
    }

    /**
     * Render actions configuration step.
     */
    private function renderActions(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['runtemplate_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('RunTemplate not created. Please complete previous steps.')));

            return $container;
        }

        $runTemplate = new \MultiFlexi\RunTemplate($this->wizardData['runtemplate_id']);
        $app = new \MultiFlexi\Application($this->wizardData['app_id']);
        $company = new \MultiFlexi\Company($this->wizardData['company_id']);

        // Add status message about created RunTemplate with actual ID
        $actualId = $runTemplate->getMyKey();

        if ($actualId) {
            $runTemplate->addStatusMessage(
                sprintf(
                    _('RunTemplate #%d created for application "%s" and company "%s"'),
                    $actualId,
                    $app->getRecordName(),
                    $company->getRecordName(),
                ),
                'success',
            );
        }

        $actualId = $runTemplate->getMyKey();
        $actionsTitle = _('Configure Actions').' '.$runTemplate->getRecordName();
        if (!empty($actualId)) {
            $actionsTitle .= ' <span class="badge badge-success ml-2">#'.$actualId.'</span>';
        }
        $container->addItem(new \Ease\Html\H3Tag($actionsTitle, ['class' => 'd-flex align-items-center']));
        $container->addItem(new \Ease\Html\PTag(_('Define what happens when the job succeeds or fails.')));

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=7', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('runtemplate_id', (string) $this->wizardData['runtemplate_id']));

        // Get existing actions if any
        $failActions = $runTemplate->getDataValue('fail') ? unserialize($runTemplate->getDataValue('fail')) : [];
        $successActions = $runTemplate->getDataValue('success') ? unserialize($runTemplate->getDataValue('success')) : [];

        // Create tabs for success and fail actions
        $actionsRow = new \Ease\TWB4\Tabs();
        $actionsRow->addTab(_('Success Actions'), new ActionsChooser('success', $app, $successActions), !empty($successActions));
        $actionsRow->addTab(_('Fail Actions'), new ActionsChooser('fail', $app, $failActions), !empty($failActions));

        $form->addItem($actionsRow);
        $container->addItem($form);

        return $container;
    }
}
