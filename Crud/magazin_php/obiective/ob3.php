<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';
page_start('Obiectivul 3', 'ob3');
page_header('Obiectivul 3', 'Evaluarea valorii comerciale a produselor retail',
    'Prețuri minim, mediu și maxim pe producător');

$search = trim($_GET['q'] ?? '');

$rows = db_query('magazin', "
    SELECT p.nume AS producator,
           MIN(t.pret) AS pret_minim,
           ROUND(AVG(t.pret),2) AS pret_mediu,
           MAX(t.pret) AS pret_maxim,
           COUNT(t.id_telefon) AS nr_modele
    FROM producator p
    LEFT JOIN telefon t ON t.id_producator = p.id_producator
    GROUP BY p.id_producator, p.nume
    ORDER BY pret_mediu DESC
");

$all_phones = db_query('magazin', "
    SELECT t.id_telefon, t.model, t.pret, p.nume AS producator
    FROM telefon t
    LEFT JOIN producator p ON t.id_producator = p.id_producator
    ORDER BY t.pret DESC
");

if ($search) {
    $all_phones = search_filter($all_phones, $search, ['model','producator']);
}

$global_min = !empty($rows) ? min(array_column($rows,'pret_minim')) : 0;
$global_avg = !empty($rows) ? round(array_sum(array_column($rows,'pret_mediu'))/count($rows),2) : 0;
$global_max = !empty($rows) ? max(array_column($rows,'pret_maxim')) : 0;
?>

<?php kpi_row([
    ['val' => number_format($global_min,2).' lei', 'lbl' => 'Preț minim global'],
    ['val' => number_format($global_avg,2).' lei', 'lbl' => 'Preț mediu global'],
    ['val' => number_format($global_max,2).' lei', 'lbl' => 'Preț maxim global'],
]); ?>

<div class="card-grid-2">
  <div class="card">
    <h3>Statistici prețuri pe producător</h3>
    <div class="tbl-wrap">
    <table>
      <thead><tr><th>Producător</th><th>Min (lei)</th><th>Mediu (lei)</th><th>Max (lei)</th><th>Modele</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= h($r['producator']) ?></strong></td>
          <td><?= number_format($r['pret_minim'],2) ?></td>
          <td><?= number_format($r['pret_mediu'],2) ?></td>
          <td><?= number_format($r['pret_maxim'],2) ?></td>
          <td><?= $r['nr_modele'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
  <div class="card">
    <h3>Comparativ prețuri medii</h3>
    <?php
    $chart = [];
    foreach ($rows as $r) $chart[$r['producator']] = (float)$r['pret_mediu'];
    bar_chart($chart, '--accent2');
    ?>
  </div>
</div>

<div class="card">
  <h3>Toate modelele — detaliu prețuri</h3>
  <?php render_table($all_phones, 'q'); ?>
</div>

<?php page_end(); ?>
