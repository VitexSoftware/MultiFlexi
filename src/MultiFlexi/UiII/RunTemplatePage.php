<?php

declare(strict_types=1);

namespace MultiFlexi\UiII;

class RunTemplatePage extends \Ease\Html\DivTag
{
    public function __construct()
    {
        parent::__construct();

        // Header
        $this->addItem($this->createHeader());

        // Filters card
        $this->addItem($this->createFiltersCard());

        // Table + chart row
        $row = $this->addItem(new \Ease\TWB4\Row());
        $col1 = $row->addColumn('12');
        $col1->addItem($this->createTableCard());
        $col2 = $row->addColumn('12');
        $col2->addItem($this->createChartCard());

        // Footer actions
        $this->addItem($this->createFooterActions());

        // Modal
        \Ease\TWB4\WebPage::singleton()->body->addItem($this->createDetailModal());
    }

    private function createHeader()
    {
        $header = new \Ease\Html\DivTag(null, ['class' => 'd-flex align-items-center mb-3']);
        $toggleBtn = '<button class="btn btn-light mr-3 sidebar-toggle-btn"><i class="fas fa-bars"></i></button>';
        $header->addItem($toggleBtn . '<div class="mr-auto"><h2 class="mb-0">Výpisy do Pohody <small class="text-muted d-block">925 – 5230011904</small></h2></div>');
        $actions = $header->addItem(new \Ease\Html\DivTag(null, ['class' => 'card-header-actions']));
        $actions->addItem(new \Ease\TWB4\SubmitButton('Spustit', 'primary', ['id' => 'btnRun']));
        $actions->addItem(new \Ease\TWB4\LinkButton('?refresh=1', 'Obnovit', 'outline-secondary', ['id' => 'btnRefresh']));

        return $header;
    }

    private function createFiltersCard()
    {
        $card = new \Ease\TWB4\Card(null, ['class' => 'mb-3']);
        $form = '
            <form class="filters form-row align-items-center">
              <div class="form-group col-md-3">
                <label class="sr-only" for="dateFrom">Datum od</label>
                <input id="dateFrom" type="date" class="form-control" placeholder="Datum od">
              </div>
              <div class="form-group col-md-3">
                <label class="sr-only" for="dateTo">Datum do</label>
                <input id="dateTo" type="date" class="form-control" placeholder="Datum do">
              </div>
              <div class="form-group col-md-3">
                <label class="sr-only" for="type">Typ</label>
                <select id="type" class="custom-select">
                  <option value="">Typ transakce</option>
                  <option value="prijem">Příjem</option>
                  <option value="vydaj">Výdaj</option>
                </select>
              </div>
              <div class="form-group col-md-2">
                <label class="sr-only" for="q">Hledat</label>
                <input id="q" type="text" class="form-control" placeholder="Hledat...">
              </div>
              <div class="form-group col-md-1 text-right">
                <button type="button" class="btn btn-success btn-block">Použít</button>
              </div>
              <div class="w-100"></div>
              <div class="form-group col-md-3 mt-2">
                <label class="sr-only" for="amountFrom">Částka od</label>
                <input id="amountFrom" type="number" class="form-control" placeholder="Částka od">
              </div>
              <div class="form-group col-md-3 mt-2">
                <label class="sr-only" for="amountTo">Částka do</label>
                <input id="amountTo" type="number" class="form-control" placeholder="Částka do">
              </div>
            </form>';
        $card->addItem(new \Ease\TWB4\CardBody($form));

        return $card;
    }

    private function createTableCard()
    {
        $table = '
        <div class="table-wrap">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:160px">Datum</th>
                        <th>Protiúčet / Popis</th>
                        <th style="width:160px">Variabilní symbol</th>
                        <th style="width:120px">Částka</th>
                        <th style="width:140px">Stav</th>
                        <th style="width:120px" class="text-center">Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>13.4.2023</td>
                        <td>T-shirt s potiskem</td>
                        <td>123456</td>
                        <td class="amount-negative">-12,00</td>
                        <td><span class="badge badge-success">Exportováno</span></td>
                        <td class="text-center"><button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#detailModal">Detail</button></td>
                    </tr>
                    <tr>
                        <td>21.3.2023</td>
                        <td>Trhovin lenyd</td>
                        <td>654321</td>
                        <td class="amount-negative">-20,00</td>
                        <td><span class="badge badge-secondary">Čeká</span></td>
                        <td class="text-center"><button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#detailModal">Detail</button></td>
                    </tr>
                    <tr>
                        <td>31.3.2023</td>
                        <td>Počtávea</td>
                        <td>987654</td>
                        <td class="amount-positive">51,00</td>
                        <td><span class="badge badge-success">Exportováno</span></td>
                        <td class="text-center"><button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#detailModal">Detail</button></td>
                    </tr>
                </tbody>
            </table>
        </div>';

        $card = new \Ease\TWB4\Card(null, ['class' => 'mb-3']);
        $card->addItem(new \Ease\TWB4\CardBody($table));

        return $card;
    }

    private function createChartCard()
    {
        $chartHeader = '
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Statistiky za období</h5>
                <div class="btn-group btn-group-sm" role="group" aria-label="Period">
                    <button class="btn btn-outline-secondary active">7 dní</button>
                    <button class="btn btn-outline-secondary">1 měsíc</button>
                    <button class="btn btn-outline-secondary">1 rok</button>
                </div>
            </div>';

        $chartBody = '<canvas id="runsChart" height="120"></canvas>';
        $card = new \Ease\TWB4\Card(null, ['class' => 'chart-card mb-4']);
        $card->addItem(new \Ease\TWB4\CardBody($chartHeader.$chartBody));

        return $card;
    }

    private function createFooterActions()
    {
        $footer = new \Ease\Html\DivTag(null, ['class' => 'd-flex justify-content-between align-items-center mt-3']);
        $footer->addItem('<div class="small text-muted">Zobrazeno 1–20 z 456 položek</div>');
        $footer->addItem('
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">«</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">»</a></li>
                </ul>
            </nav>');

        return $footer;
    }

    private function createDetailModal()
    {
        $modalContent = '
          <dl class="row">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9">925-5230011904-20230413-1</dd>
            <dt class="col-sm-3">Datum</dt>
            <dd class="col-sm-9">13.4.2023 10:07</dd>
            <dt class="col-sm-3">Trvání</dt>
            <dd class="col-sm-9">0.2s</dd>
            <dt class="col-sm-3">Výstup</dt>
            <dd class="col-sm-9">Soubor: výpis-20230413.xml</dd>
          </dl>
          <hr>
          <h6>Log</h6>
          <pre style="background:#f6f7f8;padding:12px;border-radius:4px;max-height:240px;overflow:auto;">
[INFO] 2023-04-13 10:07: Started...
[INFO] 2023-04-13 10:07: Exported 12 items
[ERROR] 2023-04-13 10:07: Missing mapping for account ...
          </pre>';

        $modal = new \Ease\TWB4\Modal(
            'Detail běhu',
            $modalContent,
            ['id' => 'detailModal', 'size' => 'lg']
        );
        $modal->modalFooter->addItem(new \Ease\TWB4\LinkButton('#', 'Stáhnout výstup', 'primary'));

        return $modal;
    }
}
