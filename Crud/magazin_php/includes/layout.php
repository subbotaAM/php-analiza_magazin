<?php
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function page_start(string $title, string $active = ''): void {
    global $DATABASES;
    require_once __DIR__ . '/db.php';
    ?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($title) ?> — Platformă BD</title>
<link rel="stylesheet" href="/magazin_php/style.css">
</head>
<body>
<aside class="sidebar">
  <nav>
    <div class="nav-section">General</div>
    <a href="/magazin_php/index.php" <?= $active==='home'?'class="active"':'' ?>> Panou principal</a>

    <div class="nav-section">Obiective</div>
    <a href="/magazin_php/obiective/ob1.php" <?= $active==='ob1'?'class="active"':'' ?>>1 · Clienți retail</a>
    <a href="/magazin_php/obiective/ob2.php" <?= $active==='ob2'?'class="active"':'' ?>>2 · Producători</a>
    <a href="/magazin_php/obiective/ob3.php" <?= $active==='ob3'?'class="active"':'' ?>>3 · Prețuri telefoane</a>
    <a href="/magazin_php/obiective/ob4.php" <?= $active==='ob4'?'class="active"':'' ?>>4 · Companii B2B</a>
    <a href="/magazin_php/obiective/ob5.php" <?= $active==='ob5'?'class="active"':'' ?>>5 · Produse JSON</a>
    <a href="/magazin_php/obiective/ob6.php" <?= $active==='ob6'?'class="active"':'' ?>>6 · Top companii</a>
    <a href="/magazin_php/obiective/ob7.php" <?= $active==='ob7'?'class="active"':'' ?>>7 · Raport managerial</a>
    <a href="/magazin_php/obiective/ob8.php" <?= $active==='ob8'?'class="active"':'' ?>>8 · Validare integrată</a>

    <div class="nav-section">Administrare</div>
    <a href="/magazin_php/crud/index.php" <?= $active==='crud'?'class="active"':'' ?>> CRUD complet</a>

    <div class="nav-section">Analiză</div>
    <a href="/magazin_php/analiza/suplimentar.php" <?= $active==='extra'?'class="active"':'' ?>> Analiză suplimentară</a>
  </nav>
  <div class="db-status">
    <?php foreach ($DATABASES as $db): ?>
      <?php $ok = db_ok($db); ?>
      <div style="margin-bottom:4px">
        <span class="dot <?= $ok ? 'dot-ok' : 'dot-err' ?>"></span>
        <?= h($db) ?>
      </div>
    <?php endforeach; ?>
  </div>
</aside>
<main class="main">
<?php
}

function page_end(): void {
    ?>

</main>
</body>
</html>
<?php
}

function page_header(string $eyebrow, string $title, string $subtitle = ''): void { ?>
<div class="page-header">
  <div class="eyebrow"><?= h($eyebrow) ?></div>
  <h2><?= h($title) ?></h2>
  <?php if ($subtitle): ?><p><?= h($subtitle) ?></p><?php endif; ?>
</div>
<?php }

function alert(string $msg, string $type = 'info'): void { ?>
<div class="alert alert-<?= $type ?>"><?= h($msg) ?></div>
<?php }

function kpi_row(array $items): void { ?>
<div class="card-grid-<?= count($items) ?>" style="margin-bottom:18px">
  <?php foreach ($items as $it): ?>
  <div class="kpi">
    <div class="val"><?= h((string)$it['val']) ?></div>
    <div class="lbl"><?= h($it['lbl']) ?></div>
  </div>
  <?php endforeach; ?>
</div>
<?php }

function render_table(array $rows, string $searchParam = ''): void {
    if (!$rows) { echo '<div class="alert alert-warn">Nu există date.</div>'; return; }
    $cols = array_keys($rows[0]);
    ?>
    <?php if ($searchParam): ?>
    <div class="search-bar">
      <form method="get" style="display:flex;gap:8px;align-items:center">
        <?php foreach ($_GET as $k => $v): if ($k === $searchParam) continue; ?>
          <input type="hidden" name="<?= h($k) ?>" value="<?= h($v) ?>">
        <?php endforeach; ?>
        <input name="<?= h($searchParam) ?>" value="<?= h($_GET[$searchParam] ?? '') ?>"
               placeholder="Caută…" style="max-width:260px">
        <button class="btn btn-ghost btn-sm" type="submit"> Caută</button>
        <?php if (!empty($_GET[$searchParam])): ?>
          <a href="?" class="btn btn-ghost btn-sm">✕ Șterge filtru</a>
        <?php endif; ?>
      </form>
    </div>
    <?php endif; ?>
    <div class="tbl-wrap">
    <table>
      <thead><tr><?php foreach ($cols as $c): ?><th><?= h($c) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
      <?php foreach ($rows as $row): ?>
        <tr><?php foreach ($row as $val): ?><td><?= h((string)($val ?? '')) ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
<?php }


function bar_chart(array $data, string $colorVar = '--accent'): void {
    if (!$data) return;
    $max = max(array_values($data));
    if ($max == 0) $max = 1;
    ?>
    <div class="bar-chart">
      <?php foreach ($data as $lbl => $val): ?>
      <div class="bar-col">
        <div class="bar-val"><?= h(is_float($val) ? number_format($val,0,'.',',') : (string)$val) ?></div>
        <div class="bar-fill" style="height:<?= round(($val/$max)*110) ?>px; background:var(<?= $colorVar ?>)"></div>
        <div class="bar-lbl"><?= h((string)$lbl) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
<?php }

function search_filter(array $rows, string $term, array $cols): array {
    if (!$term) return $rows;
    $term = mb_strtolower($term);
    return array_values(array_filter($rows, function($row) use ($term, $cols) {
        foreach ($cols as $c) {
            if (mb_strpos(mb_strtolower((string)($row[$c] ?? '')), $term) !== false) return true;
        }
        return false;
    }));
}
