<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
page_start('Panou principal', 'home');
page_header('Aplicație integrată', 'Platformă de administrare și analiză a datelor comerciale',
    'Soluție client-server pentru operațiuni, analiză managerială și suport decizional');
?>

<div class="card-grid-2">
  <div class="card">
    <h3>Formula BD</h3>
    <table style="font-size:13px;width:100%">
      <thead><tr><th>Componentă</th><th>Descriere</th></tr></thead>
      <tbody>
        <tr><td>Tip BD</td><td>Relaționale + semi-structurate JSON</td></tr>
        <tr><td>Schema logică</td><td>magazin + organizatii + analiza_manager</td></tr>
        <tr><td>SGBD</td><td>MySQL / MariaDB (XAMPP)</td></tr>
        <tr><td>Limbaje</td><td>PHP 8, MySQLi, HTML5, CSS3</td></tr>
        <tr><td>Arhitectură</td><td>Client-server, MVC simplificat</td></tr>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h3>Arhitectură client-server</h3>
    <table style="font-size:13px;width:100%">
      <thead><tr><th>Nivel</th><th>Rol</th></tr></thead>
      <tbody>
        <tr><td>Client</td><td>Browser — interfață PHP renderată</td></tr>
        <tr><td>Aplicație</td><td>PHP procesează cereri și interogări</td></tr>
        <tr><td>Date</td><td>MySQL stochează tabele + atribute JSON</td></tr>
        <tr><td>Management</td><td>analiza_manager agregă indicatori</td></tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <h3>Obiectivele sistemului</h3>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:13px">
    <?php
    $objectives = [
      'Ob.1' => 'Monitorizarea portofoliului de clienți retail și a produselor alocate.',
      'Ob.2' => 'Analiza producătorilor și distribuției portofoliului de telefoane pe branduri.',
      'Ob.3' => 'Evaluarea valorii comerciale a produselor prin indicatori de preț minim, mediu și maxim.',
      'Ob.4' => 'Administrarea companiilor partenere și comenzilor B2B.',
      'Ob.5' => 'Extragerea și interpretarea atributelor semi-structurate JSON pentru produse organizaționale.',
      'Ob.6' => 'Identificarea companiilor cu cea mai mare contribuție la venituri.',
      'Ob.7' => 'Compararea segmentelor de piață prin indicatori manageriali pentru suport decizional.',
      'Ob.8' => 'Corelarea datelor operaționale cu sinteza executivă pentru fundamentarea deciziilor.',
    ];
    foreach ($objectives as $nr => $desc): ?>
      <div style="background:var(--surface2);border:1px solid var(--border);border-radius:7px;padding:10px 13px">
        <span class="badge badge-blue" style="margin-bottom:5px"><?= h($nr) ?></span><br>
        <?= h($desc) ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
  <h3>Status conexiuni și structura bazelor de date</h3>
  <?php
  $dbs = ['magazin' => [], 'organizatii' => [], 'analiza_manager' => []];
  foreach (array_keys($dbs) as $db):
    $ok = db_ok($db);
    ?>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
      <span class="dot <?= $ok ? 'dot-ok' : 'dot-err' ?>"></span>
      <strong><?= h($db) ?></strong>
      <?php if ($ok): ?>
        <span class="badge badge-green">Conectat</span>
        <?php $tables = get_tables($db); ?>
        <span style="font-size:12px;color:var(--muted)"><?= implode(', ', array_map('h', $tables)) ?></span>
      <?php else: ?>
        <span class="badge badge-red">Eroare</span>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <h3>Navigare rapidă la obiective</h3>
  <div class="obj-tabs">
    <?php for ($i=1; $i<=8; $i++): ?>
      <a class="obj-tab" href="/magazin_php/obiective/ob<?= $i ?>.php">Obiectivul <?= $i ?></a>
    <?php endfor; ?>
    <a class="obj-tab" href="/magazin_php/crud/index.php"> CRUD</a>
    <a class="obj-tab" href="/magazin_php/analiza/suplimentar.php"> Analiză suplimentară</a>
  </div>
</div>

<?php page_end(); ?>
