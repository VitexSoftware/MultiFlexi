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

use Ease\Html\ButtonTag;
use Ease\Html\DivTag;
use Ease\Html\PTag;
use Ease\TWB4\Badge;
use Ease\TWB4\Card;
use Ease\TWB4\Col;
use Ease\TWB4\Row;
use Ease\TWB4\Widgets\FaIcon;

/**
 * GDPR Data Export Widget.
 *
 * Provides users with ability to export their personal data
 * as required by GDPR Article 15 - Right of Access
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class DataExportWidget extends Card
{
    private ?\MultiFlexi\User $user;

    public function __construct(array $properties = [])
    {
        $this->user = \Ease\Shared::user();

        parent::__construct(null, $properties);

        $this->addTagClass('data-export-widget');
        $this->setTagID('gdpr-data-export-card');

        // Card header
        $header = new DivTag(null, ['class' => 'card-header']);
        $header->addItem(new FaIcon('download', ['class' => 'me-2']));
        $header->addItem(_('Personal Data Export'));
        $header->addItem(new Badge('GDPR', 'secondary ms-2'));
        $this->addItem($header);

        // Card body
        $this->addCardBody();
    }

    private function addCardBody(): void
    {
        $body = new DivTag(null, ['class' => 'card-body']);

        // Description
        $description = new PTag(_('Download all your personal data stored in MultiFlexi. This includes your profile information, activity logs, consent records, and associated data.'));
        $description->addTagClass('text-muted mb-3');
        $body->addItem($description);

        // GDPR Info
        $gdprInfo = new DivTag();
        $gdprInfo->addTagClass('alert alert-info');
        $gdprIcon = new FaIcon('info-circle', ['class' => 'me-2']);
        $gdprInfo->addItem($gdprIcon);
        $gdprInfo->addItem(_('This feature complies with GDPR Article 15 - Right of Access. Your data will be provided in a structured, machine-readable format.'));
        $body->addItem($gdprInfo);

        // Export options row
        $exportRow = new Row();

        // JSON Export
        $jsonCol = new Col(6);
        $jsonCard = new Card();
        $jsonCard->addTagClass('h-100 text-center');

        $jsonBody = new DivTag();
        $jsonBody->addTagClass('card-body d-flex flex-column');

        $jsonIcon = new FaIcon('code', ['class' => 'fa-2x text-primary mb-3']);
        $jsonBody->addItem($jsonIcon);

        $jsonTitle = new DivTag(_('JSON Format'));
        $jsonTitle->addTagClass('h5 card-title');
        $jsonBody->addItem($jsonTitle);

        $jsonDesc = new PTag(_('Machine-readable format ideal for technical users and data processing'));
        $jsonDesc->addTagClass('card-text flex-grow-1');
        $jsonBody->addItem($jsonDesc);

        $jsonButton = new ButtonTag(
            [new FaIcon('download', ['class' => 'me-2']), _('Export as JSON')],
            ['id' => 'export-json-btn', 'class' => 'btn btn-primary export-btn', 'data-format' => 'json', 'type' => 'button'],
        );
        $jsonBody->addItem($jsonButton);

        $jsonCard->addItem($jsonBody);
        $jsonCol->addItem($jsonCard);
        $exportRow->addColumn($jsonCol);

        // PDF/Text Export
        $pdfCol = new Col(6);
        $pdfCard = new Card();
        $pdfCard->addTagClass('h-100 text-center');

        $pdfBody = new DivTag();
        $pdfBody->addTagClass('card-body d-flex flex-column');

        $pdfIcon = new FaIcon('file-alt', ['class' => 'fa-2x text-success mb-3']);
        $pdfBody->addItem($pdfIcon);

        $pdfTitle = new DivTag(_('Text Format'));
        $pdfTitle->addTagClass('h5 card-title');
        $pdfBody->addItem($pdfTitle);

        $pdfDesc = new PTag(_('Human-readable format suitable for review and printing'));
        $pdfDesc->addTagClass('card-text flex-grow-1');
        $pdfBody->addItem($pdfDesc);

        $pdfButton = new ButtonTag(
            [new FaIcon('download', ['class' => 'me-2']), _('Export as Text')],
            ['id' => 'export-pdf-btn', 'class' => 'btn btn-success export-btn', 'data-format' => 'pdf', 'type' => 'button'],
        );
        $pdfBody->addItem($pdfButton);

        $pdfCard->addItem($pdfBody);
        $pdfCol->addItem($pdfCard);
        $exportRow->addColumn($pdfCol);

        $body->addItem($exportRow);

        // Status area
        $statusDiv = new DivTag('', ['id' => 'export-status', 'class' => 'mt-3 d-none']);
        $body->addItem($statusDiv);

        // Recent exports
        $this->addRecentExports($body);

        // Add JavaScript for functionality
        self::addExportJavaScript();

        $this->addItem($body);
    }

    private function addRecentExports(DivTag $body): void
    {
        if (!$this->user || !$this->user->getUserID()) {
            return;
        }

        // Get recent export requests
        $logEngine = new \Ease\SQL\Engine();
        $logEngine->myTable = 'log';

        $recentExports = $logEngine->listingQuery()
            ->select('message, created')
            ->where(['user_id' => (int) $this->user->getUserID(), 'venue' => 'DataExportApi'])
            ->orderBy('created DESC')
            ->limit(5)
            ->fetchAll();

        if (!empty($recentExports)) {
            $body->addItem(new \Ease\Html\HrTag());

            $recentTitle = new DivTag(_('Recent Export Requests'));
            $recentTitle->addTagClass('h6 mt-3 mb-2');
            $body->addItem($recentTitle);

            $recentList = new \Ease\Html\UlTag();
            $recentList->addTagClass('list-group list-group-flush');

            foreach ($recentExports as $export) {
                $listItem = new \Ease\Html\LiTag();
                $listItem->addTagClass('list-group-item d-flex justify-content-between align-items-center');

                $exportInfo = new DivTag();
                $exportInfo->addItem(new \Ease\Html\SmallTag($export['message']));
                $listItem->addItem($exportInfo);

                $timestamp = new Badge(
                    date('M j, H:i', strtotime($export['created'])),
                    'secondary',
                );
                $listItem->addItem($timestamp);

                $recentList->addItem($listItem);
            }

            $body->addItem($recentList);
        }
    }

    private static function addExportJavaScript(): void
    {
        $js = <<<'EOD'
<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportButtons = document.querySelectorAll('.export-btn');
    const statusDiv = document.getElementById('export-status');

    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const format = this.dataset.format;
            requestDataExport(format);
        });
    });

    function requestDataExport(format) {
        // Show loading state
        showStatus('info', 'Preparing your data export...', true);

        // Disable buttons
        exportButtons.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Processing...';
        });

        // Make API request
        fetch('/api/data-export.php?action=export&format=' + format, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showStatus('success', 'Data export prepared successfully! Your download will start automatically.');

                // Create download link and trigger download
                const downloadLink = document.createElement('a');
                downloadLink.href = data.download_url;
                downloadLink.download = true;
                downloadLink.style.display = 'none';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);

                // Show additional info
                setTimeout(() => {
                    showStatus('info', `Export expires at: ${new Date(data.expires_at).toLocaleString()}`);
                }, 2000);

            } else {
                showStatus('danger', data.error || 'Export failed. Please try again later.');
            }
        })
        .catch(error => {
            console.error('Export error:', error);
            showStatus('danger', 'Network error. Please check your connection and try again.');
        })
        .finally(() => {
            // Re-enable buttons
            setTimeout(() => {
                exportButtons.forEach(btn => {
                    btn.disabled = false;
                    if (btn.dataset.format === 'json') {
                        btn.innerHTML = '<i class="fa-solid fa-download me-2"></i>Export as JSON';
                    } else {
                        btn.innerHTML = '<i class="fa-solid fa-download me-2"></i>Export as Text';
                    }
                });
            }, 1000);
        });
    }

    function showStatus(type, message, loading = false) {
        statusDiv.className = `mt-3 alert alert-${type}`;
        statusDiv.innerHTML = loading ?
            `<i class="fa-solid fa-spinner fa-spin me-2"></i>${message}` :
            `<i class="fa-solid fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>${message}`;
        statusDiv.classList.remove('d-none');

        if (!loading && type !== 'danger') {
            setTimeout(() => {
                statusDiv.classList.add('d-none');
            }, 10000);
        }
    }
});
</script>
EOD;

        WebPage::singleton()->addItem($js);
    }
}
