<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';
page_start('Obiectivul 1', 'ob1');
page_header('Obiectivul 1', 'Monitorizarea portofoliului de clienți retail',
    'Clienți, produse alocate și distribuție după producători');

$search = trim($_GET['q'] ?? '');

$rows = db_query('magazin', "
    SELECT c.id_client, c.nume AS client, c.email,
           t.model AS telefon, t.pret,
           p.nume AS producator, p.tara
    FROM client c
    LEFT JOIN telefon t ON c.id_telefon = t.id_telefon
    LEFT JOIN producator p ON t.id_producator = p.id_producator
    ORDER BY c.id_client
");

if ($search) {
    $rows = search_filter($rows, $search, ['client','email','telefon','producator','tara']);
}

$total    = count($rows);
$cu_email = count(array_filter($rows, fn($r) => !empty($r['email'])));
$cu_prod  = count(array_filter($rows, fn($r) => !empty($r['telefon'])));
$avg_pret = $total ? round(array_sum(array_column($rows, 'pret')) / max(1, $cu_prod), 2) : 0;
?>

<?php kpi_row([
    ['val' => $total,    'lbl' => 'Clienți în listă'],
    ['val' => $cu_email, 'lbl' => 'Cu email'],
    ['val' => $cu_prod,  'lbl' => 'Cu telefon alocat'],
    ['val' => number_format($avg_pret,2) . ' lei', 'lbl' => 'Preț mediu telefon'],
]); ?>

<div class="card">
  <h3>Lista clienților retail</h3>
  <?php render_table($rows, 'q'); ?>
</div>

<?php if ($rows): ?>
<div class="card">
  <h3>Distribuție clienți pe producător</h3>
  <?php
  $chart = [];
  foreach ($rows as $r) {
      $prod = $r['producator'] ?: 'Neatribuit';
      $chart[$prod] = ($chart[$prod] ?? 0) + 1;
  }
  bar_chart($chart);
  ?>
</div>
<?php endif; ?>

<?php page_end(); ?>
