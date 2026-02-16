<?php

declare(strict_types=1);

namespace MultiFlexi\UiII;

require_once './init.php';

// Inicializace nové WebPage
$page = new WebPage('MultiFlexi — Mockup Runs');

// Přidání hlavní obsahové komponenty
$page->appShell->addContent(new RunTemplatePage());

// Přidání JavaScriptu pro graf a sbalování sidebaru
$page->addJavaScript("
    // Sample Chart.js config
    var ctx = document.getElementById('runsChart').getContext('2d');
    var runsChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Fri','Mon','Tue','Wed','Thu','Fri','Sat','Tue'],
        datasets: [{
          label: 'Běhy',
          data: [2, 12, 8, 10, 22, 18, 24, 12],
          fill: false,
          borderWidth: 2,
          pointRadius: 3,
          lineTension: 0.2
        }]
      },
      options: {
        legend: { display: false },
        scales: {
          yAxes: [{ ticks: { beginAtZero: true } }]
        },
        responsive: true,
        maintainAspectRatio: false
      }
    });

    // Sidebar toggle logic
    $(document).on('click', '.sidebar-toggle-btn', function() {
      $('.sidebar').toggleClass('collapsed');
      $('.content-area').toggleClass('expanded');
    });
");

// Vykreslení stránky do souboru
file_put_contents('/home/jules/verification/runtemplate.html', $page->draw(false));
