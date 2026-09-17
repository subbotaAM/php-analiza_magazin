<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';
page_start('Obiectivul 7', 'ob7');
page_header('Obiectivul 7', 'Raport managerial comparativ',
    'Compararea segmentelor de piață: persoane fizice vs. organizații');

$search = trim($_GET['q'] ?? '');

$manager = db_query('analiza_manager', "SELECT * FROM manager ORDER BY id_manager");

if ($search) {
    $manager = search_filter($manager, $search, ['tip_utilizator']);
}

$total_venit  = array_sum(array_column($manager, 'venit_total'));
$total_clienti= array_sum(array_column($manager, 'total_clienti'));
$total_comenzi= array_sum(array_column($manager, 'total_comenzi'));
?>

<?php kpi_row([
    ['val' => $total_clienti, 'lbl' => 'Total clienți (ambele segmente)'],
    ['val' => $total_comenzi, 'lbl' => 'Total comenzi'],
    ['val' => number_format($total_venit,2).' lei', 'lbl' => 'Venit agregat total'],
    ['val' => count($manager), 'lbl' => 'Segmente analizate'],
]); ?>

<div class="card">
  <h3>Tabel comparativ managerial — analiza_manager</h3>
  <div class="search-bar">
    <form method="get" style="display:flex;gap:8px">
      <input name="q" value="<?= h($search) ?>" placeholder="Caută segment…" style="max-width:260px">
      <button class="btn btn-ghost btn-sm" type="submit">Cauta</button>
      <?php if ($search): ?><a href="?" class="btn btn-ghost btn-sm">✕</a><?php endif; ?>
    </form>
  </div>
  <div class="tbl-wrap">
  <table>
    <thead><tr><th>ID</th><th>Tip utilizator</th><th>Total clienți</th><th>Preț mediu (lei)</th><th>Venit total (lei)</th><th>Total comenzi</th></tr></thead>
    <tbody>
      <?php foreach ($manager as $r): ?>
      <tr>
        <td><?= h($r['id_manager']) ?></td>
        <td>
          <?php if ($r['tip_utilizator'] === 'persoana_fizica'): ?>
            <span class="badge badge-blue"> Persoană fizică</span>
          <?php else: ?>
            <span class="badge badge-green"> Organizație</span>
          <?php endif; ?>
        </td>
        <td><?= h($r['total_clienti']) ?></td>
        <td><?= number_format($r['pret_mediu'],2) ?></td>
        <td><strong><?= number_format($r['venit_total'],2) ?></strong></td>
        <td><?= h($r['total_comenzi']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="card-grid-2">
  <div class="card">
    <h3>Venit total pe segment</h3>
    <?php
    $chart = [];
    foreach ($manager as $r) $chart[$r['tip_utilizator']] = (float)$r['venit_total'];
    bar_chart($chart, '--accent2');
    ?>
  </div>
  <div class="card">
    <h3>Preț mediu pe segment</h3>
    <?php
    $chart2 = [];
    foreach ($manager as $r) $chart2[$r['tip_utilizator']] = (float)$r['pret_mediu'];
    bar_chart($chart2, '--accent');
    ?>
  </div>
</div>

<?php page_end(); ?>
