<?php
/**
 * Fix Primary Keys Script v2
 * Upload to: /home/u10-63tswljobfe4/www/jazzedge.academy/public_html/fix-pks.php
 * Run ONCE via SSH CLI, then DELETE immediately.
 */

// ── CLI or browser? ───────────────────────────────────────────────────────────
$is_cli = (php_sapi_name() === 'cli');

// ── Security ──────────────────────────────────────────────────────────────────
if (!$is_cli) {
    $secret = 'jazzedge-pk-fix-2025';
    if (!isset($_GET['token']) || $_GET['token'] !== $secret) {
        http_response_code(403);
        die('Access denied.');
    }
}

// ── PHP config ────────────────────────────────────────────────────────────────
set_time_limit(0);
ignore_user_abort(true);
if (!$is_cli) {
    ob_implicit_flush(true);
    if (ob_get_level()) ob_end_flush();
}

// ── Load WordPress DB credentials ─────────────────────────────────────────────
require_once(__DIR__ . '/wp-config.php');

// ── Settings ──────────────────────────────────────────────────────────────────
$databases = ['dbywtz4fzmjxwu', 'dbt2yxek4je7xk'];

// Columns that look like integers but are NOT auto-increment primary keys
$skip_columns = ['eMin', 'user_id', 'customer_id', 'order_id', 'post_id', 'object_id'];

// Tables to skip entirely (handle manually later)
$skip_tables = ['wp_alm_transcript_embeddings'];

// ── Connect ───────────────────────────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}
$conn->query("SET SESSION sql_mode = ''");
$conn->query("SET SESSION wait_timeout = 600");
$conn->query("SET SESSION interactive_timeout = 600");

// ── HTML output ───────────────────────────────────────────────────────────────
// ── Output helpers ────────────────────────────────────────────────────────────
function out($msg, $type = 'info') {
    global $is_cli;
    if ($is_cli) {
        $prefix = ['ok' => '[OK]   ', 'err' => '[ERR]  ', 'skip' => '[SKIP] ', 'db' => '\n===', 'info' => '       '];
        echo ($prefix[$type] ?? '       ') . strip_tags($msg) . "\n";
    } else {
        $classes = ['ok' => 'ok', 'err' => 'err', 'skip' => 'skip', 'db' => 'db', 'info' => ''];
        $cls = $classes[$type] ?? '';
        $tag = $type === 'db' ? 'div' : 'span';
        echo "<{$tag}" . ($cls ? " class='{$cls}'" : '') . ">{$msg}</{$tag}>" . ($type !== 'db' ? '<br>' : '') . "\n";
    }
    flush();
}

if (!$is_cli): ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Fix Primary Keys v2</title>
<style>
  body  { background: #1e1e1e; color: #d4d4d4; font-family: monospace; font-size: 13px; padding: 20px; }
  h2    { color: #569cd6; }
  .ok   { color: #4ec9b0; }
  .err  { color: #f44747; }
  .skip { color: #808080; }
  .warn { color: #ce9178; }
  .db   { color: #569cd6; font-size: 15px; margin-top: 20px; border-top: 1px solid #444; padding-top: 10px; }
  .summary { background: #252526; border: 1px solid #444; padding: 15px; margin-top: 20px; font-size: 14px; }
</style>
</head>
<body>
<h2>Fix Primary Keys v2 — <?= date('Y-m-d H:i:s') ?></h2>
<?php endif;
if ($is_cli) echo "=== Fix Primary Keys v2 — " . date('Y-m-d H:i:s') . " ===\n\n";

$total_fixed  = 0;
$total_skip   = 0;
$total_errors = 0;

// ── Helper: count duplicates in a column ──────────────────────────────────────
function count_duplicates($conn, $db, $table, $column) {
    $r = $conn->query("
        SELECT IFNULL(SUM(cnt - 1), 0) AS dups
        FROM (
            SELECT COUNT(*) AS cnt
            FROM `{$db}`.`{$table}`
            GROUP BY `{$column}`
            HAVING cnt > 1
        ) t
    ");
    return $r ? (int)$r->fetch_assoc()['dups'] : 0;
}

// ── Helper: fix duplicates using a temp rowid column ─────────────────────────
function fix_duplicates($conn, $db, $table, $column) {
    // Add temp auto-increment column so we can uniquely identify rows
    $conn->query("ALTER TABLE `{$db}`.`{$table}` ADD `_tmp_rowid` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY");

    // Get max positive value of the real PK column
    $r = $conn->query("SELECT IFNULL(MAX(`{$column}`), 0) AS mx FROM `{$db}`.`{$table}` WHERE `{$column}` > 0");
    $max_val = $r ? (int)$r->fetch_assoc()['mx'] : 0;

    // For every group of duplicate values, keep the row with the lowest _tmp_rowid
    // and assign new unique values to the rest
    $conn->query("SET @pk_counter = {$max_val}");
    $conn->query("
        UPDATE `{$db}`.`{$table}` t
        JOIN (
            SELECT `_tmp_rowid`,
                   ROW_NUMBER() OVER (PARTITION BY `{$column}` ORDER BY `_tmp_rowid`) AS rn
            FROM `{$db}`.`{$table}`
        ) ranked ON t.`_tmp_rowid` = ranked.`_tmp_rowid`
        SET t.`{$column}` = (@pk_counter := @pk_counter + 1)
        WHERE ranked.rn > 1
           OR t.`{$column}` = 0
           OR t.`{$column}` IS NULL
    ");

    // Remove temp column (also drops its PK so we can add the real one)
    $conn->query("ALTER TABLE `{$db}`.`{$table}` DROP PRIMARY KEY, DROP COLUMN `_tmp_rowid`");
}

foreach ($databases as $db) {
    out("▶ DATABASE: {$db}", 'db');

    // Find ALL tables in this DB that have an integer first column
    // (regardless of whether they have a PK — we'll handle both cases)
    $sql = "
        SELECT c.TABLE_NAME, c.COLUMN_NAME, c.COLUMN_TYPE,
               MAX(CASE WHEN tc.CONSTRAINT_TYPE = 'PRIMARY KEY' THEN 1 ELSE 0 END) AS has_pk
        FROM information_schema.COLUMNS c
        LEFT JOIN information_schema.TABLE_CONSTRAINTS tc
            ON  c.TABLE_SCHEMA = tc.TABLE_SCHEMA
            AND c.TABLE_NAME   = tc.TABLE_NAME
            AND tc.CONSTRAINT_TYPE = 'PRIMARY KEY'
        WHERE c.TABLE_SCHEMA   = '{$db}'
          AND c.ORDINAL_POSITION = 1
          AND c.DATA_TYPE IN ('int','bigint','mediumint','smallint','tinyint')
          AND c.EXTRA NOT LIKE '%auto_increment%'
        GROUP BY c.TABLE_NAME, c.COLUMN_NAME, c.COLUMN_TYPE
        ORDER BY c.TABLE_NAME
    ";

    $result = $conn->query($sql);
    if (!$result) {
        out("ERROR querying information_schema: " . $conn->error, 'err');
        continue;
    }

    if ($result->num_rows === 0) {
        out("  No tables need fixing.", 'ok');
        continue;
    }

    while ($row = $result->fetch_assoc()) {
        $table  = $row['TABLE_NAME'];
        $column = $row['COLUMN_NAME'];
        $type   = strtoupper($row['COLUMN_TYPE']);
        $has_pk = (bool)$row['has_pk'];

        // Skip known non-PK columns
        if (in_array($column, $skip_columns)) {
            out("  SKIP `{$table}` — `{$column}` is not a PK column", 'skip');
            $total_skip++;
            continue;
        }

        // Skip tables flagged for manual handling
        if (in_array($table, $skip_tables)) {
            out("  SKIP `{$table}` — flagged for manual handling", 'skip');
            $total_skip++;
            continue;
        }

        if ($is_cli) echo "  Fixing `{$db}`.`{$table}` (`{$column}` {$type})... ";
        else echo "  <span style='color:#d4d4d4;'>Fixing `{$db}`.`{$table}` (`{$column}` {$type})</span> ";
        flush();

        $notes = [];

        // ── Step 1: Drop existing PK if present (so we can re-add with AUTO_INCREMENT)
        if ($has_pk) {
            $conn->query("ALTER TABLE `{$db}`.`{$table}` DROP PRIMARY KEY");
            $notes[] = 'dropped existing PK';
        }

        // ── Step 2: Check for zeros/nulls
        $r = $conn->query("SELECT COUNT(*) AS cnt FROM `{$db}`.`{$table}` WHERE `{$column}` = 0 OR `{$column}` IS NULL");
        $zeros = $r ? (int)$r->fetch_assoc()['cnt'] : 0;

        // ── Step 3: Check for duplicates
        $dups = count_duplicates($conn, $db, $table, $column);

        if ($dups > 0 || $zeros > 0) {
            if ($dups > 0) {
                // Use temp-column method to safely de-duplicate
                fix_duplicates($conn, $db, $table, $column);
                $notes[] = "fixed {$dups} duplicate IDs";
                if ($zeros > 0) $notes[] = "fixed {$zeros} zero/null IDs";
            } else {
                // Only zeros — simpler fix
                $r2 = $conn->query("SELECT IFNULL(MAX(`{$column}`), 0) AS mx FROM `{$db}`.`{$table}` WHERE `{$column}` > 0");
                $max_val = $r2 ? (int)$r2->fetch_assoc()['mx'] : 0;
                $conn->query("SET @pk_max = {$max_val}");
                $conn->query("UPDATE `{$db}`.`{$table}` SET `{$column}` = (@pk_max := @pk_max + 1) WHERE `{$column}` = 0 OR `{$column}` IS NULL");
                $notes[] = "fixed {$zeros} zero/null IDs";
            }
        }

        // ── Step 4: Add AUTO_INCREMENT + PRIMARY KEY
        $alter = "ALTER TABLE `{$db}`.`{$table}` MODIFY `{$column}` {$type} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`{$column}`)";

        if ($conn->query($alter)) {
            $note_str = count($notes) ? ' (' . implode(', ', $notes) . ')' : '';
            if ($is_cli) echo "OK{$note_str}\n";
            else echo "<span class='ok'>OK" . ($note_str ? "<span class='warn'>{$note_str}</span>" : '') . "</span><br>\n";
            $total_fixed++;
        } else {
            if ($is_cli) echo "ERROR: " . $conn->error . "\n";
            else echo "<span class='err'>ERROR: " . htmlspecialchars($conn->error) . "</span><br>\n";
            $total_errors++;
        }

        flush();
    }
}

$conn->close();

if ($is_cli) {
    echo "\n=== DONE — " . date('Y-m-d H:i:s') . " ===\n";
    echo "  Fixed   : {$total_fixed} tables\n";
    echo "  Skipped : {$total_skip} tables\n";
    echo "  Errors  : {$total_errors} tables\n";
    echo "\n*** DELETE this file from the server immediately! ***\n";
} else { ?>
<div class="summary">
    <strong>═══ DONE — <?= date('Y-m-d H:i:s') ?> ═══</strong><br><br>
    <span class="ok">  ✔ Fixed   : <?= $total_fixed ?> tables</span><br>
    <span class="skip">  ⊘ Skipped : <?= $total_skip ?> tables</span><br>
    <span class="err">  ✘ Errors  : <?= $total_errors ?> tables</span><br><br>
    <span style="color:#f44747;font-weight:bold;">⚠ DELETE this file from the server immediately!</span>
</div>
</body>
</html>
<?php } ?>
