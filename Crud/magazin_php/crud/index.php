<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';

$dbs = ['magazin', 'organizatii', 'analiza_manager'];

// ── Inputs ────────────────────────────────────────────────────────────────
$db_sel  = $_GET['db'] ?? $_POST['db'] ?? 'magazin';
if (!in_array($db_sel, $dbs)) $db_sel = 'magazin';

$tables     = get_tables($db_sel);
$table_sel  = $_GET['tbl'] ?? $_POST['tbl'] ?? ($tables[0] ?? '');
if (!in_array($table_sel, $tables)) $table_sel = $tables[0] ?? '';

$action  = $_POST['action'] ?? $_GET['action'] ?? 'read';
$search  = trim($_GET['q'] ?? '');
$message = '';
$msg_type = 'info';

// ── Helpers ───────────────────────────────────────────────────────────────
function coerce($val, string $dataType, bool $isJson): mixed {
    if ($val === '' || $val === null) return null;
    if ($isJson) {
        $decoded = json_decode($val, true);
        if (json_last_error() !== JSON_ERROR_NONE) throw new \RuntimeException('JSON invalid: '.json_last_error_msg());
        return json_encode($decoded, JSON_UNESCAPED_UNICODE);
    }
    if (in_array($dataType, ['int','bigint','smallint','mediumint','tinyint'])) return (int)$val;
    if (in_array($dataType, ['decimal','float','double']))                      return (float)$val;
    return $val;
}

// ── Process POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $table_sel) {

    $cols = get_columns($db_sel, $table_sel);
    $pk   = get_pk($db_sel, $table_sel);
    $non_pk = array_filter($cols, fn($c) => $c['COLUMN_KEY'] !== 'PRI');

    if ($action === 'create') {
        $fields = [];
        $params = [];
        foreach ($non_pk as $col) {
            $name  = $col['COLUMN_NAME'];
            $raw   = $_POST['field_'.$name] ?? '';
            $isJson = is_json_col($db_sel, $table_sel, $name);
            try {
                $v = coerce($raw, $col['DATA_TYPE'], $isJson);
            } catch (\RuntimeException $e) {
                $message = 'Eroare date: '.$e->getMessage(); $msg_type = 'error'; goto done;
            }
            $fields[] = "`$name`";
            $params[] = $v;
        }
        if ($fields) {
            $placeholders = implode(',', array_fill(0, count($fields), '?'));
            [$aff, $err] = db_exec($db_sel, "INSERT INTO `$table_sel` (".implode(',',$fields).") VALUES ($placeholders)", $params);
            if ($err) { $message = "Eroare SQL: $err"; $msg_type = 'error'; }
            else { $message = "Înregistrare adăugată (rânduri afectate: $aff)."; $msg_type = 'success'; }
        }

    } elseif ($action === 'update') {
        $pk_val = $_POST['pk_val'] ?? '';
        $upd_col = $_POST['upd_col'] ?? '';
        $upd_val = $_POST['upd_val'] ?? '';
        $col_meta = null;
        foreach ($cols as $c) if ($c['COLUMN_NAME'] === $upd_col) { $col_meta = $c; break; }
        $pk_meta = null;
        foreach ($cols as $c) if ($c['COLUMN_NAME'] === $pk) { $pk_meta = $c; break; }

        if ($col_meta && $pk_meta) {
            try {
                $parsed_pk  = coerce($pk_val,  $pk_meta['DATA_TYPE'],  false);
                $parsed_val = coerce($upd_val, $col_meta['DATA_TYPE'], is_json_col($db_sel,$table_sel,$upd_col));
            } catch (\RuntimeException $e) {
                $message = 'Eroare date: '.$e->getMessage(); $msg_type = 'error'; goto done;
            }
            [$aff, $err] = db_exec($db_sel, "UPDATE `$table_sel` SET `$upd_col`=? WHERE `$pk`=?", [$parsed_val, $parsed_pk]);
            if ($err) { $message = "Eroare SQL: $err"; $msg_type = 'error'; }
            else { $message = "Actualizat — rânduri afectate: $aff."; $msg_type = 'success'; }
        }

    } elseif ($action === 'delete') {
        $pk_val = $_POST['pk_del'] ?? '';
        $pk_meta = null;
        foreach ($cols as $c) if ($c['COLUMN_NAME'] === $pk) { $pk_meta = $c; break; }
        if ($pk_meta) {
            $parsed = coerce($pk_val, $pk_meta['DATA_TYPE'], false);
            [$aff, $err] = db_exec($db_sel, "DELETE FROM `$table_sel` WHERE `$pk`=?", [$parsed]);
            if ($err) { $message = "Eroare SQL: $err"; $msg_type = 'error'; }
            else { $message = "Șters — rânduri afectate: $aff."; $msg_type = 'success'; }
        }
    }
}
done:


$data_rows = $table_sel ? db_query($db_sel, "SELECT * FROM `$table_sel`") : [];
if ($search && $data_rows) {
    $search_cols = $data_rows ? array_keys($data_rows[0]) : [];
    $data_rows = search_filter($data_rows, $search, $search_cols);
}

$cols     = $table_sel ? get_columns($db_sel, $table_sel) : [];
$pk       = $table_sel ? get_pk($db_sel, $table_sel) : '';
$non_pk   = array_filter($cols, fn($c) => $c['COLUMN_KEY'] !== 'PRI');

page_start('CRUD Administrator', 'crud');
page_header('Administrare CRUD', 'Interfață administrativă completă',
    'Create · Read · Update · Delete pentru toate tabelele și bazele de date');
?>

<?php if ($message): ?>
  <div class="alert alert-<?= h($msg_type) ?>"><?= h($message) ?></div>
<?php endif; ?>

<!-- DB + Table selector -->
<div class="card" style="padding:16px 22px">
  <form method="get" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
    <div class="form-group" style="min-width:160px">
      <label>Baza de date</label>
      <select name="db" onchange="this.form.submit()">
        <?php foreach ($dbs as $d): ?>
          <option value="<?= h($d) ?>" <?= $d===$db_sel?'selected':'' ?>><?= h($d) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" style="min-width:160px">
      <label>Tabel</label>
      <select name="tbl" onchange="this.form.submit()">
        <?php foreach ($tables as $t): ?>
          <option value="<?= h($t) ?>" <?= $t===$table_sel?'selected':'' ?>><?= h($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Caută</label>
      <input name="q" value="<?= h($search) ?>" placeholder="Filtrează rânduri…" style="max-width:200px">
    </div>
    <input type="hidden" name="action" value="read">
    <button class="btn btn-ghost" type="submit"> Aplică</button>
    <?php if ($search): ?><a href="?db=<?= h($db_sel) ?>&tbl=<?= h($table_sel) ?>" class="btn btn-ghost">✕ Reset</a><?php endif; ?>
  </form>
</div>

<!-- Metadata row -->
<div class="card-grid-3" style="margin-bottom:18px">
  <div class="kpi"><div class="val"><?= h($db_sel) ?></div><div class="lbl">Baza de date</div></div>
  <div class="kpi"><div class="val"><?= h($table_sel) ?></div><div class="lbl">Tabel</div></div>
  <div class="kpi"><div class="val"><?= h($pk) ?></div><div class="lbl">Cheie primară</div></div>
</div>

<!-- Current data -->
<div class="card">
  <h3>Date curente — <?= h($db_sel) ?>.<?= h($table_sel) ?> (<?= count($data_rows) ?> rânduri<?= $search ? ', filtrate' : '' ?>)</h3>
  <?php if ($data_rows): ?>
  <div class="tbl-wrap">
  <table>
    <thead><tr><?php foreach (array_keys($data_rows[0]) as $c): ?><th><?= h($c) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <?php foreach ($data_rows as $row): ?>
        <tr><?php foreach ($row as $val): ?><td><?= h((string)($val ?? '')) ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php else: ?>
  <div class="alert alert-warn">Tabelul este gol sau nu există date.</div>
  <?php endif; ?>
</div>

<!-- Schema / column info -->
<div class="card">
  <h3>Schema tabelului</h3>
  <div class="tbl-wrap">
  <table>
    <thead><tr><th>Coloană</th><th>Tip date</th><th>Cheie</th><th>Nullable</th><th>JSON?</th></tr></thead>
    <tbody>
      <?php foreach ($cols as $c): ?>
      <tr>
        <td><strong><?= h($c['COLUMN_NAME']) ?></strong></td>
        <td><span class="badge badge-blue"><?= h($c['DATA_TYPE']) ?></span></td>
        <td><?= $c['COLUMN_KEY']==='PRI' ? '<span class="badge badge-yellow">PK</span>' : h($c['COLUMN_KEY']) ?></td>
        <td><?= h($c['IS_NULLABLE']) ?></td>
        <td><?= is_json_col($db_sel,$table_sel,$c['COLUMN_NAME']) ? '<span class="badge badge-green">JSON</span>' : '—' ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- ── CREATE ── -->
<div class="card">
  <h3> Create — Adaugă înregistrare</h3>
  <?php if ($db_sel==='organizatii' && $table_sel==='produs_org'): ?>
  <div class="alert alert-info">Câmpul <code>specificatii</code> acceptă JSON valid, ex: <code>{"categorie":"Laptop","garantie":"24 luni"}</code></div>
  <?php endif; ?>
  <form method="post">
    <input type="hidden" name="db" value="<?= h($db_sel) ?>">
    <input type="hidden" name="tbl" value="<?= h($table_sel) ?>">
    <input type="hidden" name="action" value="create">
    <div class="form-row">
      <?php foreach ($non_pk as $col):
        $n = $col['COLUMN_NAME'];
        $isJson = is_json_col($db_sel, $table_sel, $n);
      ?>
      <div class="form-group">
        <label><?= h($n) ?> <span style="color:var(--muted);font-weight:400">(<?= h($col['DATA_TYPE']) ?><?= $isJson?' · JSON':'' ?>)</span></label>
        <?php if ($isJson): ?>
          <textarea name="field_<?= h($n) ?>" placeholder='{"cheie":"valoare"}'></textarea>
        <?php else: ?>
          <input name="field_<?= h($n) ?>" placeholder="<?= h($n) ?>">
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <button class="btn btn-success" type="submit"> Inserează</button>
  </form>
</div>

<!-- ── UPDATE ── -->
<div class="card">
  <h3> Update — Modifică înregistrare</h3>
  <form method="post">
    <input type="hidden" name="db" value="<?= h($db_sel) ?>">
    <input type="hidden" name="tbl" value="<?= h($table_sel) ?>">
    <input type="hidden" name="action" value="update">
    <div class="form-row">
      <div class="form-group" style="max-width:180px">
        <label>Valoare <?= h($pk) ?></label>
        <input name="pk_val" placeholder="ID rând">
      </div>
      <div class="form-group" style="max-width:200px">
        <label>Coloana de modificat</label>
        <select name="upd_col">
          <?php foreach ($non_pk as $col): ?>
            <option value="<?= h($col['COLUMN_NAME']) ?>"><?= h($col['COLUMN_NAME']) ?> (<?= h($col['DATA_TYPE']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Valoare nouă</label>
        <input name="upd_val" placeholder="Valoare nouă">
      </div>
    </div>
    <button class="btn btn-warn" type="submit"> Actualizează</button>
  </form>
</div>

<!-- ── DELETE ── -->
<div class="card">
  <h3> Delete — Șterge înregistrare</h3>
  <form method="post" onsubmit="return confirm('Ești sigur că vrei să ștergi această înregistrare?')">
    <input type="hidden" name="db" value="<?= h($db_sel) ?>">
    <input type="hidden" name="tbl" value="<?= h($table_sel) ?>">
    <input type="hidden" name="action" value="delete">
    <div class="form-row" style="align-items:flex-end">
      <div class="form-group" style="max-width:200px">
        <label>Valoare <?= h($pk) ?> pentru ștergere</label>
        <input name="pk_del" placeholder="ID de șters">
      </div>
      <button class="btn btn-danger" type="submit">Șterge</button>
    </div>
  </form>
</div>

<?php page_end(); ?>
