<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';
page_start('Obiectivul 4', 'ob4');
page_header('Obiectivul 4', 'Administrarea companiilor partenere și comenzilor B2B',
    'Registrul companiilor și istoricul comenzilor organizaționale');

$search = trim($_GET['q'] ?? '');

$companii = db_query('organizatii', "SELECT * FROM companie ORDER BY id_companie");
$comenzi  = db_query('organizatii', "
    SELECT co.id_comanda, co.data_comanda,
           c.nume_companie, c.domeniu, c.tara AS tara_companie,
           p.denumire AS produs, p.pret,
           co.cantitate, co.cantitate * p.pret AS valoare_comanda
    FROM comanda_org co
    JOIN companie c ON co.id_companie = c.id_companie
    JOIN produs_org p ON co.id_produs = p.id_produs
    ORDER BY co.data_comanda DESC
");

if ($search) {
    $companii = search_filter($companii, $search, ['nume_companie','domeniu','tara']);
    $comenzi  = search_filter($comenzi,  $search, ['nume_companie','produs','domeniu']);
}

$total_val = array_sum(array_column($comenzi, 'valoare_comanda'));
$total_unit = array_sum(array_column($comenzi, 'cantitate'));
?>

<?php kpi_row([
    ['val' => count($companii), 'lbl' => 'Companii partenere'],
    ['val' => count($comenzi),  'lbl' => 'Comenzi B2B'],
    ['val' => $total_unit,      'lbl' => 'Unități comandate'],
    ['val' => number_format($total_val,2).' lei', 'lbl' => 'Valoare totală'],
]); ?>

<div class="card">
  <h3>Companii partenere</h3>
  <?php render_table($companii, 'q'); ?>
</div>

<div class="card">
  <h3>Comenzi B2B</h3>
  <?php render_table($comenzi, 'q'); ?>
</div>

<div class="card">
  <h3>Comenzi pe companie</h3>
  <?php
  $chart = [];
  foreach ($comenzi as $c) {
      $n = $c['nume_companie'];
      $chart[$n] = ($chart[$n] ?? 0) + 1;
  }
  bar_chart($chart, '--warn');
  ?>
</div>

<?php page_end(); ?>
