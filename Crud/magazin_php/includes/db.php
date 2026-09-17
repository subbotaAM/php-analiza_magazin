<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);

$DATABASES = ['magazin', 'organizatii', 'analiza_manager'];


function db_connect(string $dbName): ?mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, $dbName, DB_PORT);
    if ($conn->connect_error) return null;
    $conn->set_charset('utf8mb4');
    return $conn;
}


function db_query(string $dbName, string $sql, array $params = []): array {
    $conn = db_connect($dbName);
    if (!$conn) return [];
    if ($params) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) { $conn->close(); return []; }
        $types = '';
        $vals  = [];
        foreach ($params as $p) {
            if (is_int($p))    { $types .= 'i'; $vals[] = $p; }
            elseif (is_float($p)) { $types .= 'd'; $vals[] = $p; }
            else               { $types .= 's'; $vals[] = $p; }
        }
        $stmt->bind_param($types, ...$vals);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    } else {
        $res  = $conn->query($sql);
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    $conn->close();
    return $rows;
}

function db_exec(string $dbName, string $sql, array $params = []): array {
    $conn = db_connect($dbName);
    if (!$conn) return [0, 'Connection failed'];
    $stmt = $conn->prepare($sql);
    if (!$stmt) { $err = $conn->error; $conn->close(); return [0, $err]; }
    if ($params) {
        $types = '';
        $vals  = [];
        foreach ($params as $p) {
            if ($p === null) { $types .= 's'; $vals[] = null; }
            elseif (is_int($p))   { $types .= 'i'; $vals[] = $p; }
            elseif (is_float($p)) { $types .= 'd'; $vals[] = $p; }
            else                  { $types .= 's'; $vals[] = $p; }
        }
        $stmt->bind_param($types, ...$vals);
    }
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $err      = $stmt->error;
    $stmt->close();
    $conn->close();
    return [$affected, $err];
}

function db_ok(string $dbName): bool {
    $conn = db_connect($dbName);
    if (!$conn) return false;
    $conn->close();
    return true;
}

function get_tables(string $dbName): array {
    $rows = db_query($dbName, "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbName' ORDER BY TABLE_NAME");
    return array_column($rows, 'TABLE_NAME');
}


function get_columns(string $dbName, string $table): array {
    return db_query($dbName,
        "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_KEY, IS_NULLABLE
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
         ORDER BY ORDINAL_POSITION",
        [$dbName, $table]
    );
}

function get_pk(string $dbName, string $table): string {
    $cols = get_columns($dbName, $table);
    foreach ($cols as $c) if ($c['COLUMN_KEY'] === 'PRI') return $c['COLUMN_NAME'];
    return $cols[0]['COLUMN_NAME'] ?? 'id';
}

$JSON_COLS = ['organizatii' => ['produs_org' => ['specificatii']]];

function is_json_col(string $db, string $table, string $col): bool {
    global $JSON_COLS;
    return in_array($col, $JSON_COLS[$db][$table] ?? []);
}
