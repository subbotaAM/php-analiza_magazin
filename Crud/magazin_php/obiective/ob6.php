<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';
page_start('Obiectivul 6', 'ob6');
page_header('Obiectivul 6', 'Top companii după contribuția la venituri',
    'Ranking companii partenere B2B pe venit și volum de achiziție');

$search = trim($_GET['q'] ?? '');

$rows = db_query('organizatii', "
    SELECT c.id_companie, c.nume_companie, c.domeniu, c.tara,
           COUNT(co.id_comanda) AS nr_comenzi,
           SUM(co.cantitate) AS total_unitati,
           ROUND(SUM(co.cantitate * p.pret),2) AS venit_total,
           ROUND(AVG(p.pret),2) AS pret_mediu_produs
    FROM companie c
    LEFT JOIN comanda_org co ON co.id_companie = c.id_companie
    LEFT JOIN produs_org p ON co.id_produs = p.id_produs
    GROUP BY c.id_companie, c.nume_companie, c.domeniu, c.tara
    ORDER BY venit_total DESC
");

if ($search) {
    $rows = search_filter($rows, $search, ['nume_companie','domeniu','tara']);
}

$total_venit = array_sum(array_column($rows, 'venit_total'));
?>

<?php kpi_row([
    ['val' => count($rows), 'lbl' => 'Companii analizate'],
    ['val' => number_format($total_venit,2).' lei', 'lbl' => 'Venit total B2B'],
    ['val' => !empty($rows) ? h($rows[0]['nume_companie']) : '—', 'lbl' => 'Top companie'],
]); ?>

<div class="card">
  <h3>Ranking companii după venit</h3>
  <div class="search-bar">
    <form method="get" style="display:flex;gap:8px">
      <input name="q" value="<?= h($search) ?>" placeholder="Caută companie/domeniu…" style="max-width:260px">
      <button class="btn btn-ghost btn-sm" type="submit">Cauta</button>
      <?php if ($search): ?><a href="?" class="btn btn-ghost btn-sm">✕</a><?php endif; ?>
    </form>
  </div>
  <div class="tbl-wrap">
  <table>
    <thead><tr><th>Rang</th><th>Companie</th><th>Domeniu</th><th>Țară</th><th>Comenzi</th><th>Unități</th><th>Venit (lei)</th><th>Preț mediu (lei)</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $i => $r): ?>
      <tr>
        <td>
          <?php if ($i===0): ?><span class="badge badge-yellow"> 1</span>
          <?php elseif ($i===1): ?><span class="badge badge-blue"> 2</span>
          <?php elseif ($i===2): ?><span class="badge badge-green"> 3</span>
          <?php else: ?><span style="color:var(--muted)"><?= $i+1 ?></span>
          <?php endif; ?>
        </td>
        <td><strong><?= h($r['nume_companie']) ?></strong></td>
        <td><?= h($r['domeniu']) ?></td>
        <td><?= h($r['tara']) ?></td>
        <td><?= $r['nr_comenzi'] ?></td>
        <td><?= $r['total_unitati'] ?></td>
        <td><strong><?= number_format($r['venit_total'],2) ?></strong></td>
        <td><?= number_format($r['pret_mediu_produs'],2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="card-grid-2">
  <div class="card">
    <h3>Venit pe companie</h3>
    <?php
    $chart = [];
    foreach ($rows as $r) $chart[$r['nume_companie']] = (float)$r['venit_total'];
    bar_chart($chart, '--accent2');
    ?>
  </div>
  <div class="card">
    <h3>Unități pe companie</h3>
    <?php
    $chart2 = [];
    foreach ($rows as $r) $chart2[$r['nume_companie']] = (int)$r['total_unitati'];
    bar_chart($chart2, '--warn');
    ?>
  </div>
</div>

<?php page_end(); ?>
