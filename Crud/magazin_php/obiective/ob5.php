<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';
page_start('Obiectivul 5', 'ob5');
page_header('Obiectivul 5', 'Extragerea atributelor semi-structurate JSON',
    'Produse organizaționale cu specificații stocate în format JSON');

$search = trim($_GET['q'] ?? '');

$rows = db_query('organizatii', "
    SELECT id_produs, denumire, pret,
           JSON_UNQUOTE(JSON_EXTRACT(specificatii, '$.categorie')) AS categorie,
           JSON_UNQUOTE(JSON_EXTRACT(specificatii, '$.garantie'))  AS garantie,
           JSON_UNQUOTE(JSON_EXTRACT(specificatii, '$.tip'))       AS tip,
           JSON_UNQUOTE(JSON_EXTRACT(specificatii, '$.greutate'))  AS greutate,
           JSON_UNQUOTE(JSON_EXTRACT(specificatii, '$.utilizatori')) AS utilizatori,
           specificatii
    FROM produs_org
    ORDER BY id_produs
");

if ($search) {
    $rows = search_filter($rows, $search, ['denumire','categorie','garantie','tip']);
}

$cat_count = [];
foreach ($rows as $r) {
    $cat = $r['categorie'] ?: 'necategorizat';
    $cat_count[$cat] = ($cat_count[$cat] ?? 0) + 1;
}
?>

<?php kpi_row([
    ['val' => count($rows), 'lbl' => 'Produse cu JSON'],
    ['val' => count($cat_count), 'lbl' => 'Categorii'],
    ['val' => number_format(array_sum(array_column($rows,'pret'))/max(1,count($rows)),2).' lei', 'lbl' => 'Preț mediu'],
]); ?>

<div class="card">
  <h3>Produse cu atribute JSON extrase</h3>
  <div class="search-bar">
    <form method="get" style="display:flex;gap:8px">
      <input name="q" value="<?= h($search) ?>" placeholder="Caută produs/categorie/tip…" style="max-width:260px">
      <button class="btn btn-ghost btn-sm" type="submit">Cauta</button>
      <?php if ($search): ?><a href="?" class="btn btn-ghost btn-sm">✕</a><?php endif; ?>
    </form>
  </div>
  <div class="tbl-wrap">
  <table>
    <thead>
      <tr><th>ID</th><th>Denumire</th><th>Preț (lei)</th><th>Categorie</th><th>Garanție</th><th>Tip</th><th>Greutate</th><th>Utilizatori</th><th>JSON raw</th></tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= h($r['id_produs']) ?></td>
        <td><strong><?= h($r['denumire']) ?></strong></td>
        <td><?= number_format($r['pret'],2) ?></td>
        <td><?php if ($r['categorie']): ?><span class="badge badge-blue"><?= h($r['categorie']) ?></span><?php else: ?>—<?php endif; ?></td>
        <td><?= h($r['garantie'] ?? '—') ?></td>
        <td><?= h($r['tip'] ?? '—') ?></td>
        <td><?= h($r['greutate'] ?? '—') ?></td>
        <td><?= h($r['utilizatori'] ?? '—') ?></td>
        <td><span class="json-val"><?= h($r['specificatii']) ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="card">
  <h3>Distribuție produse pe categorie JSON</h3>
  <?php bar_chart($cat_count); ?>
</div>

<?php page_end(); ?>
