<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';
page_start('Obiectivul 8', 'ob8');
page_header('Obiectivul 8', 'Validare și corelație date integrate',
    'Compararea indicatorilor operaționali cu datele din analiza_manager');

// Calcule din date operaționale
$retail = db_query('magazin', "
    SELECT COUNT(*) AS total_clienti,
           ROUND(AVG(t.pret),2) AS pret_mediu,
           ROUND(SUM(t.pret),2) AS valoare_portofoliu
    FROM client c LEFT JOIN telefon t ON c.id_telefon = t.id_telefon
");
$org = db_query('organizatii', "
    SELECT COUNT(DISTINCT co.id_companie) AS total_clienti,
           ROUND(AVG(p.pret),2) AS pret_mediu,
           ROUND(SUM(co.cantitate * p.pret),2) AS venit_total,
           COUNT(*) AS total_comenzi
    FROM comanda_org co JOIN produs_org p ON co.id_produs = p.id_produs
");
$manager = db_query('analiza_manager', "SELECT * FROM manager ORDER BY id_manager");

$r_retail = $retail[0] ?? [];
$r_org    = $org[0] ?? [];

$validation = [
    [
        'Segment' => 'Persoană fizică (retail)',
        'Clienți operațional' => $r_retail['total_clienti'] ?? 0,
        'Clienți managerial'  => $manager[0]['total_clienti'] ?? 0,
        'Preț mediu operațional' => number_format($r_retail['pret_mediu'] ?? 0, 2),
        'Preț mediu managerial'  => number_format($manager[0]['pret_mediu'] ?? 0, 2),
        'Venit operațional' => number_format($r_retail['valoare_portofoliu'] ?? 0, 2),
        'Venit managerial'  => number_format($manager[0]['venit_total'] ?? 0, 2),
    ],
    [
        'Segment' => 'Organizație (B2B)',
        'Clienți operațional' => $r_org['total_clienti'] ?? 0,
        'Clienți managerial'  => $manager[1]['total_clienti'] ?? 0,
        'Preț mediu operațional' => number_format($r_org['pret_mediu'] ?? 0, 2),
        'Preț mediu managerial'  => number_format($manager[1]['pret_mediu'] ?? 0, 2),
        'Venit operațional' => number_format($r_org['venit_total'] ?? 0, 2),
        'Venit managerial'  => number_format($manager[1]['venit_total'] ?? 0, 2),
    ],
];

// Sinteza executivă
$sinteza = [
    ['indicator' => 'Clienți retail',          'valoare' => $r_retail['total_clienti'] ?? 0],
    ['indicator' => 'Preț mediu retail (lei)', 'valoare' => number_format($r_retail['pret_mediu'] ?? 0, 2)],
    ['indicator' => 'Valoare portofoliu retail (lei)', 'valoare' => number_format($r_retail['valoare_portofoliu'] ?? 0, 2)],
    ['indicator' => 'Companii active B2B',     'valoare' => $r_org['total_clienti'] ?? 0],
    ['indicator' => 'Comenzi B2B',             'valoare' => $r_org['total_comenzi'] ?? 0],
    ['indicator' => 'Venit total B2B (lei)',   'valoare' => number_format($r_org['venit_total'] ?? 0, 2)],
];
?>

<div class="card">
  <h3>Comparativ: Date operaționale vs. analiza_manager</h3>
  <div class="tbl-wrap">
  <table>
    <thead>
      <tr>
        <th>Segment</th>
        <th>Clienți (op.)</th>
        <th>Clienți (mgr.)</th>
        <th>Preț mediu op. (lei)</th>
        <th>Preț mediu mgr. (lei)</th>
        <th>Venit op. (lei)</th>
        <th>Venit mgr. (lei)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($validation as $row): ?>
      <tr>
        <?php foreach ($row as $k => $v): ?>
          <td><?= h((string)$v) ?></td>
        <?php endforeach; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="card">
  <h3>Sinteză executivă integrată</h3>
  <div class="tbl-wrap">
  <table>
    <thead><tr><th>Indicator</th><th>Valoare</th></tr></thead>
    <tbody>
      <?php foreach ($sinteza as $s): ?>
      <tr><td><?= h($s['indicator']) ?></td><td><strong><?= h((string)$s['valoare']) ?></strong></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="card">
  <h3>Recomandări manageriale</h3>
  <?php
  $rec = [
    'Segment B2B generează venit mult mai mare per tranzacție față de retail — prioritizați relațiile organizaționale.',
    'Verificați consistența periodică între datele operaționale și indicatorii manageriali pentru control de calitate.',
    'Prețul mediu B2B ('. number_format($r_org['pret_mediu'] ?? 0, 2) .' lei) depășește semnificativ retail-ul ('. number_format($r_retail['pret_mediu'] ?? 0, 2) .' lei).',
  ];
  foreach ($rec as $r): ?>
  <div class="alert alert-info" style="margin-bottom:8px">! <?= h($r) ?></div>
  <?php endforeach; ?>
</div>

<?php page_end(); ?>
