<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';
page_start('Obiectivul 2', 'ob2');
page_header('Obiectivul 2', 'Analiza producătorilor și portofoliul de telefoane',
    'Distribuția modelelor pe branduri, cu prețuri');

$search = trim($_GET['q'] ?? '');

$rows = db_query('magazin', "
    SELECT p.id_producator, p.nume AS producator, p.tara,
           t.model, t.pret
    FROM producator p
    LEFT JOIN telefon t ON t.id_producator = p.id_producator
    ORDER BY p.nume, t.model
");

if ($search) {
    $rows = search_filter($rows, $search, ['producator','tara','model']);
}

$producatori = [];
foreach ($rows as $r) {
    $k = $r['producator'];
    if (!isset($producatori[$k])) $producatori[$k] = ['tara' => $r['tara'], 'modele' => 0, 'preturi' => []];
    if ($r['model']) {
        $producatori[$k]['modele']++;
        $producatori[$k]['preturi'][] = (float)$r['pret'];
    }
}
?>

<?php kpi_row([
    ['val' => count($producatori), 'lbl' => 'Producători'],
    ['val' => count(array_filter($rows, fn($r) => $r['model'])), 'lbl' => 'Modele totale'],
]); ?>

<div class="card-grid-2">
  <div class="card">
    <h3>Producători și modele</h3>
    <div class="search-bar">
      <form method="get" style="display:flex;gap:8px">
        <input name="q" value="<?= h($search) ?>" placeholder="Caută producător/model…" style="max-width:250px">
        <button class="btn btn-ghost btn-sm" type="submit">🔍</button>
        <?php if ($search): ?><a href="?" class="btn btn-ghost btn-sm">✕</a><?php endif; ?>
      </form>
    </div>
    <div class="tbl-wrap">
    <table>
      <thead><tr><th>Producător</th><th>Țară</th><th>Modele</th><th>Preț mediu</th></tr></thead>
      <tbody>
        <?php foreach ($producatori as $name => $info): ?>
        <tr>
          <td><strong><?= h($name) ?></strong></td>
          <td><?= h($info['tara']) ?></td>
          <td><?= $info['modele'] ?></td>
          <td><?= $info['preturi'] ? number_format(array_sum($info['preturi'])/count($info['preturi']),2) . ' lei' : '—' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
  <div class="card">
    <h3>Număr modele pe producător</h3>
    <?php
    $chart = [];
    foreach ($producatori as $name => $info) $chart[$name] = $info['modele'];
    bar_chart($chart);
    ?>
  </div>
</div>

<div class="card">
  <h3>Toate modelele</h3>
  <?php render_table($rows, 'q'); ?>
</div>

<?php page_end(); ?>
