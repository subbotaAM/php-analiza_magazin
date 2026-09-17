<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';
 

 
page_start('Analiză suplimentară', 'extra');
page_header(
    'Analiză suplimentară',
    'Sarcini adiționale pentru suport decizional',
    'Consistență operațional–managerial · Mix de produse · Sinteză executivă'
);
 

$retail = db_query('magazin', "
    SELECT
        COUNT(c.id_client)               AS total_clienti,
        ROUND(AVG(t.pret), 2)            AS pret_mediu,
        ROUND(SUM(t.pret), 2)            AS valoare_portofoliu
    FROM client c
    LEFT JOIN telefon t ON c.id_telefon = t.id_telefon
");
$r_retail = $retail[0] ?? [];
 
$org = db_query('organizatii', "
    SELECT
        COUNT(DISTINCT co.id_companie)          AS total_clienti,
        ROUND(AVG(p.pret), 2)                   AS pret_mediu,
        ROUND(SUM(co.cantitate * p.pret), 2)    AS venit_total,
        COUNT(co.id_comanda)                    AS total_comenzi
    FROM comanda_org co
    JOIN produs_org p ON co.id_produs = p.id_produs
");
$r_org = $org[0] ?? [];
 

$manager = db_query('analiza_manager', "SELECT * FROM manager ORDER BY id_manager");
$mgr_retail = $manager[0] ?? [];   
$mgr_org    = $manager[1] ?? [];   
 
?>
 
<div class="card">
  <h3>Sarcina 1 — Verificarea consistenței: operațional vs. managerial</h3>
  <p style="color:var(--muted);margin-bottom:12px">
    Comparăm valorile calculate direct din bazele operaționale (<em>magazin</em>, <em>organizatii</em>)
    cu înregistrările agregate din <em>analiza_manager</em>. Orice diferență semnalează
    o posibilă desincronizare a raportării manageriale.
  </p>
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
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $segments = [
          [
              'label'  => 'Persoană fizică (retail)',
              'c_op'   => (int)($r_retail['total_clienti'] ?? 0),
              'c_mg'   => (int)($mgr_retail['total_clienti'] ?? 0),
              'pm_op'  => (float)($r_retail['pret_mediu'] ?? 0),
              'pm_mg'  => (float)($mgr_retail['pret_mediu'] ?? 0),
              'v_op'   => (float)($r_retail['valoare_portofoliu'] ?? 0),
              'v_mg'   => (float)($mgr_retail['venit_total'] ?? 0),
          ],
          [
              'label'  => 'Organizație (B2B)',
              'c_op'   => (int)($r_org['total_clienti'] ?? 0),
              'c_mg'   => (int)($mgr_org['total_clienti'] ?? 0),
              'pm_op'  => (float)($r_org['pret_mediu'] ?? 0),
              'pm_mg'  => (float)($mgr_org['pret_mediu'] ?? 0),
              'v_op'   => (float)($r_org['venit_total'] ?? 0),
              'v_mg'   => (float)($mgr_org['venit_total'] ?? 0),
          ],
      ];
 
      foreach ($segments as $seg):
          $ok_clienti = ($seg['c_op'] === $seg['c_mg']);
          $ok_venit   = (abs($seg['v_op'] - $seg['v_mg']) < 0.01);
          $consistent = $ok_clienti && $ok_venit;
      ?>
      <tr>
        <td><strong><?= h($seg['label']) ?></strong></td>
        <td><?= $seg['c_op'] ?></td>
        <td><?= $seg['c_mg'] ?></td>
        <td><?= number_format($seg['pm_op'], 2) ?></td>
        <td><?= number_format($seg['pm_mg'], 2) ?></td>
        <td><?= number_format($seg['v_op'], 2) ?></td>
        <td><?= number_format($seg['v_mg'], 2) ?></td>
        <td>
          <?php if ($consistent): ?>
            <span class="badge badge-green">✓ Consistent</span>
          <?php else: ?>
            <span class="badge badge-yellow">⚠ Diferență</span>
            <?php if (!$ok_clienti): ?>
              <div style="font-size:11px;color:var(--warn);margin-top:3px">
                Clienți: op=<?= $seg['c_op'] ?> ≠ mgr=<?= $seg['c_mg'] ?>
              </div>
            <?php endif; ?>
            <?php if (!$ok_venit): ?>
              <div style="font-size:11px;color:var(--warn);margin-top:3px">
                Venit: op=<?= number_format($seg['v_op'],2) ?> ≠ mgr=<?= number_format($seg['v_mg'],2) ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
 
  <?php
  
  $all_consistent = true;
  foreach ($segments as $seg) {
      if ($seg['c_op'] !== $seg['c_mg'] || abs($seg['v_op'] - $seg['v_mg']) >= 0.01) {
          $all_consistent = false;
          break;
      }
  }
  if ($all_consistent): ?>
  <div class="alert alert-info" style="margin-top:12px">
    Datele operaționale și cele manageriale sunt <strong>complet consistente</strong>.
    Nu sunt necesare corecții.
  </div>
  <?php else: ?>
  <div class="alert alert-warn" style="margin-top:12px">
    S-au detectat <strong>diferențe</strong> între datele operaționale și cele manageriale.
    Actualizați tabelul <code>analiza_manager.manager</code> pentru a reflecta realitatea curentă.
  </div>
  <?php endif; ?>
</div>
 
<?php
 
$cat_summary = db_query('organizatii', "
    SELECT
        COALESCE(
            JSON_UNQUOTE(JSON_EXTRACT(p.specificatii, '$.categorie')),
            'necategorizat'
        )                                           AS categorie,
        ROUND(SUM(co.cantitate * p.pret), 2)        AS venit_total,
        SUM(co.cantitate)                           AS unitati_vandute,
        COUNT(DISTINCT p.id_produs)                 AS produse_distincte,
        COUNT(co.id_comanda)                        AS nr_comenzi,
        ROUND(AVG(p.pret), 2)                       AS pret_mediu_categorie
    FROM comanda_org co
    JOIN produs_org p ON co.id_produs = p.id_produs
    GROUP BY categorie
    ORDER BY venit_total DESC
");
 
$search_s2 = trim($_GET['q2'] ?? '');
if ($search_s2) {
    $cat_summary = search_filter($cat_summary, $search_s2, ['categorie']);
}
 
$total_venit_b2b = array_sum(array_column($cat_summary, 'venit_total'));
?>
 
<div class="card">
  <h3>Sarcina 2 — Mix de produse B2B pe categorii JSON</h3>
  <p style="color:var(--muted);margin-bottom:12px">
    Câmpul <code>specificatii.categorie</code> (stocat ca JSON în <em>produs_org</em>) este
    extras cu <code>JSON_UNQUOTE / JSON_EXTRACT</code> și agregat pentru a identifica
    ce categorie de produse generează cel mai mare venit în segmentul B2B.
  </p>
 
  <div class="search-bar">
    <form method="get" style="display:flex;gap:8px;align-items:center">
      <input name="q2" value="<?= h($search_s2) ?>"
             placeholder="Filtrează categorie…" style="max-width:220px">
      <button class="btn btn-ghost btn-sm" type="submit">Caută</button>
      <?php if ($search_s2): ?>
        <a href="?" class="btn btn-ghost btn-sm">✕ Reset</a>
      <?php endif; ?>
    </form>
  </div>
 
  <?php if ($cat_summary): ?>
  <div class="tbl-wrap">
  <table>
    <thead>
      <tr>
        <th>Rang</th>
        <th>Categorie JSON</th>
        <th>Venit total (lei)</th>
        <th>Cotă din total (%)</th>
        <th>Unități vândute</th>
        <th>Comenzi</th>
        <th>Produse distincte</th>
        <th>Preț mediu (lei)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cat_summary as $i => $r):
            $cota = $total_venit_b2b > 0 ? round($r['venit_total'] / $total_venit_b2b * 100, 1) : 0;
      ?>
      <tr>
        <td>
          <?php if ($i===0): ?><span class="badge badge-yellow">1</span>
          <?php elseif ($i===1): ?><span class="badge badge-blue">2</span>
          <?php elseif ($i===2): ?><span class="badge badge-green">3</span>
          <?php else: ?><span style="color:var(--muted)"><?= $i+1 ?></span>
          <?php endif; ?>
        </td>
        <td><span class="badge badge-blue"><?= h($r['categorie']) ?></span></td>
        <td><strong><?= number_format($r['venit_total'], 2) ?></strong></td>
        <td>
          <div style="display:flex;align-items:center;gap:6px">
            <div style="width:60px;height:8px;background:var(--border);border-radius:4px;overflow:hidden">
              <div style="width:<?= $cota ?>%;height:100%;background:var(--accent2)"></div>
            </div>
            <?= $cota ?>%
          </div>
        </td>
        <td><?= h($r['unitati_vandute']) ?></td>
        <td><?= h($r['nr_comenzi']) ?></td>
        <td><?= h($r['produse_distincte']) ?></td>
        <td><?= number_format($r['pret_mediu_categorie'], 2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
 
  <?php

  $chart = [];
  foreach ($cat_summary as $r) $chart[$r['categorie']] = (float)$r['venit_total'];
  bar_chart($chart, '--accent2');
  ?>
 
  <div class="alert alert-info" style="margin-top:14px">
     Categoria cu cel mai mare aport de venit este
    <strong><?= h($cat_summary[0]['categorie']) ?></strong>
    (<?= number_format($cat_summary[0]['venit_total'], 2) ?> lei,
    cotă <?= $total_venit_b2b > 0 ? round($cat_summary[0]['venit_total']/$total_venit_b2b*100,1) : 0 ?>%).
    Prioritizați stocul și ofertele pentru această categorie.
  </div>
  <?php else: ?>
  <div class="alert alert-warn">Nu există date B2B pentru această filtrare.</div>
  <?php endif; ?>
</div>
 
<?php

 
$sinteza = [
    ['grup' => 'Retail',  'Indicator' => 'Clienți retail (persoane fizice)',      'Valoare' => $r_retail['total_clienti'] ?? 0,       'UM' => 'persoane'],
    ['grup' => 'Retail',  'Indicator' => 'Preț mediu telefon alocat',             'Valoare' => number_format($r_retail['pret_mediu'] ?? 0, 2), 'UM' => 'lei'],
    ['grup' => 'Retail',  'Indicator' => 'Valoare totală portofoliu retail',      'Valoare' => number_format($r_retail['valoare_portofoliu'] ?? 0, 2), 'UM' => 'lei'],
    // — B2B —
    ['grup' => 'B2B',     'Indicator' => 'Companii partenere cu comenzi active', 'Valoare' => $r_org['total_clienti'] ?? 0,          'UM' => 'companii'],
    ['grup' => 'B2B',     'Indicator' => 'Total comenzi B2B înregistrate',       'Valoare' => $r_org['total_comenzi'] ?? 0,          'UM' => 'comenzi'],
    ['grup' => 'B2B',     'Indicator' => 'Venit total B2B (cantitate × preț)',   'Valoare' => number_format($r_org['venit_total'] ?? 0, 2), 'UM' => 'lei'],
    ['grup' => 'B2B',     'Indicator' => 'Preț mediu produs B2B',                'Valoare' => number_format($r_org['pret_mediu'] ?? 0, 2), 'UM' => 'lei'],
    // — Comparativ —
    ['grup' => 'Comparativ', 'Indicator' => 'Raport venit B2B / Retail',
        'Valoare' => ($r_retail['valoare_portofoliu'] ?? 0) > 0
            ? number_format(($r_org['venit_total'] ?? 0) / ($r_retail['valoare_portofoliu'] ?? 1), 2).'×'
            : 'N/A',
        'UM' => 'multiplicator'],
    ['grup' => 'Comparativ', 'Indicator' => 'Categorie B2B cu venit maxim',
        'Valoare' => !empty($cat_summary) ? $cat_summary[0]['categorie'] : 'N/A',
        'UM' => '—'],
];
?>
 
<div class="card-grid-2">
 
  <div class="card">
    <h3>Sarcina 3 — Sinteză executivă integrată</h3>
    <p style="color:var(--muted);margin-bottom:12px;font-size:13px">
      Indicatori cheie agregați din toate cele 3 baze de date, gata de export.
    </p>
    <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Grup</th><th>Indicator</th><th>Valoare</th><th>U.M.</th></tr>
      </thead>
      <tbody>
        <?php
        $last_grup = '';
        foreach ($sinteza as $s):
            $is_new = ($s['grup'] !== $last_grup);
            $last_grup = $s['grup'];
        ?>
        <tr <?= $is_new ? 'style="border-top:2px solid var(--border)"' : '' ?>>
          <td>
            <?php if ($is_new): ?>
              <?php if ($s['grup'] === 'Retail'): ?>
                <span class="badge badge-blue">Retail</span>
              <?php elseif ($s['grup'] === 'B2B'): ?>
                <span class="badge badge-green">B2B</span>
              <?php else: ?>
                <span class="badge badge-yellow">Comp.</span>
              <?php endif; ?>
            <?php endif; ?>
          </td>
          <td><?= h($s['Indicator']) ?></td>
          <td><strong><?= h((string)$s['Valoare']) ?></strong></td>
          <td style="color:var(--muted);font-size:12px"><?= h($s['UM']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
 
  
  <div class="card">
    <h3>Recomandări bazate pe date</h3>
    <?php
    $recs = [];
 
    
    $ratio = ($r_retail['valoare_portofoliu'] ?? 0) > 0
        ? round(($r_org['venit_total'] ?? 0) / ($r_retail['valoare_portofoliu'] ?? 1), 1)
        : 0;
    if ($ratio > 1) {
        $recs[] = [
            'tip'  => 'success',
            'text' => "Segmentul B2B generează de <strong>{$ratio}×</strong> mai mult venit
                       față de retail. Investiți în extinderea rețelei de parteneri B2B."
        ];
    }
 
    
    if (!empty($cat_summary)) {
        $recs[] = [
            'tip'  => 'info',
            'text' => "Categoria <strong>{$cat_summary[0]['categorie']}</strong> domină
                       vânzările B2B. Asigurați disponibilitate stoc și oferte preferențiale."
        ];
    }
 
    
    $recs[] = [
        'tip'  => 'warn',
        'text' => 'Instituiți un proces lunar de reconciliere între datele operaționale
                   și agregările din <code>analiza_manager</code> pentru a preveni
                   raportări eronate.'
    ];
 
    
    if (($r_org['pret_mediu'] ?? 0) > ($r_retail['pret_mediu'] ?? 0)) {
        $dif = number_format(($r_org['pret_mediu'] ?? 0) - ($r_retail['pret_mediu'] ?? 0), 2);
        $recs[] = [
            'tip'  => 'info',
            'text' => "Prețul mediu B2B depășește retail-ul cu <strong>{$dif} lei</strong>
                       — clienții organizaționali achiziționează produse de valoare
                       mai mare, cu marjă superioară."
        ];
    }
 
    foreach ($recs as $rec): ?>
    <div class="alert alert-<?= $rec['tip'] ?>" style="margin-bottom:10px">
      <?php echo ($rec['tip']==='success')?'':($rec['tip']==='warn'?'':'') ?>
      <?= $rec['text'] ?>
    </div>
    <?php endforeach; ?>
 
    <h4 style="margin-top:18px;margin-bottom:6px">Venit: Retail vs. B2B</h4>
    <?php
    bar_chart([
        'Retail' => (float)($r_retail['valoare_portofoliu'] ?? 0),
        'B2B'    => (float)($r_org['venit_total'] ?? 0),
    ], '--accent');
    ?>
  </div>
 
</div>
 
<?php page_end(); ?>
