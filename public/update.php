<?php
/**
 * KITA-HRM Online-Updater
 *
 * Lädt die neueste Version von GitHub und aktualisiert die Installation.
 * .env, storage/ und uploads/ werden dabei NICHT überschrieben.
 *
 * Aufruf: https://deine-domain.de/update.php
 */

declare(strict_types=1);
session_start();
@set_time_limit(600);
@ini_set('memory_limit', '256M');

define('BASE_PATH',     realpath(__DIR__ . '/..'));
define('ENV_PATH',      BASE_PATH . '/.env');
define('GITHUB_REPO',   'nicigrv/kitahrm');
define('GITHUB_BRANCH', 'main');
define('PROTECTED',     ['.env', 'storage', 'uploads', 'public/update.php']);

// ── .env parser ──────────────────────────────────────────────────────────────
function readEnv(): array
{
    $out = [];
    if (!file_exists(ENV_PATH)) return $out;
    foreach (file(ENV_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $out[trim($k)] = trim($v, " \t\"'");
    }
    return $out;
}

// ── DB connection ─────────────────────────────────────────────────────────────
function db(array $env): PDO
{
    return new PDO(
        "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4",
        $env['DB_USERNAME'], $env['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}

// ── Known migrations with SQL ─────────────────────────────────────────────────
function allMigrations(): array
{
    return [
        '2024_01_01_000007_add_staffing_fields_to_kitas_table' => [
            "ALTER TABLE `kitas` ADD COLUMN `min_staff_total` int NOT NULL DEFAULT 0 AFTER `min_first_aid`",
            "ALTER TABLE `kitas` ADD COLUMN `min_skilled_staff` int NOT NULL DEFAULT 0 AFTER `min_staff_total`",
            "ALTER TABLE `kitas` ADD COLUMN `notes` text AFTER `min_skilled_staff`",
        ],
        '2024_01_01_000008_create_kita_training_requirements_table' => [
            "CREATE TABLE IF NOT EXISTS `kita_training_requirements` (
              `id` bigint unsigned NOT NULL AUTO_INCREMENT,
              `kita_id` bigint unsigned NOT NULL,
              `category_id` bigint unsigned NOT NULL,
              `min_count` int NOT NULL DEFAULT 1,
              `created_at` timestamp NULL,
              `updated_at` timestamp NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `ktr_kita_cat_unique` (`kita_id`,`category_id`),
              KEY `ktr_kita_id_index` (`kita_id`),
              KEY `ktr_category_id_index` (`category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ],
        '2024_01_01_000009_add_target_hours_to_kitas_table' => [
            "ALTER TABLE `kitas` ADD COLUMN `target_weekly_hours` decimal(6,1) NOT NULL DEFAULT 0.0 AFTER `min_skilled_staff`",
        ],
        '2024_01_01_000010_create_kita_closing_days_table' => [
            "CREATE TABLE IF NOT EXISTS `kita_closing_days` (
              `id` bigint unsigned NOT NULL AUTO_INCREMENT,
              `kita_id` bigint unsigned NOT NULL,
              `date` date NOT NULL,
              `label` varchar(255) NULL,
              `created_at` timestamp NULL,
              `updated_at` timestamp NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `kcd_kita_date_unique` (`kita_id`,`date`),
              KEY `kcd_kita_id_index` (`kita_id`),
              KEY `kcd_date_index` (`date`),
              CONSTRAINT `fk_closing_kita` FOREIGN KEY (`kita_id`) REFERENCES `kitas` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ],
        '2024_01_01_000011_create_kita_events_table' => [
            "CREATE TABLE IF NOT EXISTS `kita_events` (
              `id` bigint unsigned NOT NULL AUTO_INCREMENT,
              `kita_id` bigint unsigned NOT NULL,
              `date` date NOT NULL,
              `end_date` date NULL,
              `event_type` varchar(20) NOT NULL DEFAULT 'SCHLIESSTAG',
              `title` varchar(255) NOT NULL,
              `description` text NULL,
              `start_time` varchar(5) NULL,
              `end_time` varchar(5) NULL,
              `created_at` timestamp NULL,
              `updated_at` timestamp NULL,
              PRIMARY KEY (`id`),
              KEY `ke_kita_date_index` (`kita_id`,`date`),
              KEY `ke_date_index` (`date`),
              CONSTRAINT `fk_ke_kita` FOREIGN KEY (`kita_id`) REFERENCES `kitas` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ],
    ];
}

function pendingMigrations(PDO $pdo): array
{
    try {
        $done = $pdo->query("SELECT `migration` FROM `migrations`")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable) {
        $done = [];
    }
    return array_diff_key(allMigrations(), array_flip($done));
}

// ── Protected path check ──────────────────────────────────────────────────────
function isProtected(string $rel): bool
{
    $rel = str_replace('\\', '/', $rel);
    foreach (PROTECTED as $p) {
        if ($rel === $p || str_starts_with($rel, $p . '/')) return true;
    }
    return false;
}

// ── Recursive delete ──────────────────────────────────────────────────────────
function rmdirAll(string $dir): void
{
    if (!is_dir($dir)) return;
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $f) $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    rmdir($dir);
}

// ── Auth ──────────────────────────────────────────────────────────────────────
$env      = readEnv();
$authed   = !empty($_SESSION['updater_ok']);
$authErr  = '';

if (isset($_GET['logout'])) { session_destroy(); header('Location: update.php'); exit; }

if (!$authed && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pw'])) {
    try {
        $row = db($env)->query("SELECT password FROM users WHERE role='ADMIN' ORDER BY id LIMIT 1")->fetch();
        if ($row && password_verify($_POST['pw'], $row['password'])) {
            $_SESSION['updater_ok'] = true;
            $authed = true;
        } else {
            $authErr = 'Falsches Passwort.';
        }
    } catch (Throwable $e) {
        $authErr = 'Datenbankfehler: ' . $e->getMessage();
    }
}

// ── Update action ─────────────────────────────────────────────────────────────
$log  = [];
$done = false;

function logEntry(string $type, string $msg): void { global $log; $log[] = [$type, $msg]; }

if ($authed && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_update'])) {

    $token   = trim($_POST['token'] ?? $env['GITHUB_TOKEN'] ?? '');
    $tmpDir  = BASE_PATH . '/storage/app/upd_' . time();
    $zipFile = $tmpDir . '.zip';

    try {
        // 1. Download ─────────────────────────────────────────────────────────
        logEntry('info', 'Verbinde mit GitHub …');

        if (empty($token)) {
            throw new RuntimeException('Kein GitHub-Token angegeben (Formular oder GITHUB_TOKEN in .env).');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL-Erweiterung fehlt auf diesem Server.');
        }

        $apiUrl = 'https://api.github.com/repos/' . GITHUB_REPO . '/zipball/' . GITHUB_BRANCH;
        $fp     = fopen($zipFile, 'wb');

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 180,
            CURLOPT_USERAGENT      => 'KITA-HRM-Updater/1.0',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: application/vnd.github+json',
                'X-GitHub-Api-Version: 2022-11-28',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if ($curlErr)       throw new RuntimeException('Netzwerkfehler: ' . $curlErr);
        if ($httpCode===401) throw new RuntimeException('GitHub-Token ungültig oder abgelaufen (HTTP 401).');
        if ($httpCode===403) throw new RuntimeException('Zugriff verweigert – Token-Rechte prüfen (HTTP 403).');
        if ($httpCode===404) throw new RuntimeException('Repository nicht gefunden (HTTP 404).');
        if ($httpCode!==200) throw new RuntimeException("Download fehlgeschlagen: HTTP {$httpCode}.");

        $mb = round(filesize($zipFile) / 1024 / 1024, 1);
        logEntry('ok', "Download erfolgreich ({$mb} MB).");

        // 2. Extract ──────────────────────────────────────────────────────────
        logEntry('info', 'Entpacke Archiv …');
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('PHP ZipArchive-Erweiterung fehlt auf diesem Server.');
        }
        mkdir($tmpDir, 0755, true);
        $zip = new ZipArchive();
        if ($zip->open($zipFile) !== true) throw new RuntimeException('ZIP konnte nicht geöffnet werden.');
        $zip->extractTo($tmpDir);
        $zip->close();
        unlink($zipFile);

        $subdirs = array_filter(glob($tmpDir . '/*') ?: [], 'is_dir');
        if (empty($subdirs)) throw new RuntimeException('Kein Verzeichnis im Archiv gefunden.');
        $srcRoot = reset($subdirs);
        logEntry('ok', 'Extrahiert: ' . basename($srcRoot));

        // 3. Copy files ───────────────────────────────────────────────────────
        logEntry('info', 'Kopiere Dateien …');
        $copied = $skipped = 0;
        $iter   = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($srcRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iter as $item) {
            $rel = str_replace('\\', '/', substr($item->getPathname(), strlen($srcRoot) + 1));
            if (isProtected($rel)) { $skipped++; continue; }

            $dst = BASE_PATH . DIRECTORY_SEPARATOR . $rel;
            if ($item->isDir()) {
                if (!is_dir($dst)) mkdir($dst, 0755, true);
            } else {
                $dir = dirname($dst);
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                copy($item->getPathname(), $dst);
                $copied++;
            }
        }
        logEntry('ok', "{$copied} Dateien kopiert, {$skipped} Pfade übersprungen.");

        // 4. Migrations ───────────────────────────────────────────────────────
        logEntry('info', 'Datenbank-Migrationen …');
        $pdo     = db($env);
        $pending = pendingMigrations($pdo);

        if (empty($pending)) {
            logEntry('info', 'Keine ausstehenden Migrationen.');
        } else {
            foreach ($pending as $name => $stmts) {
                $ok = true;
                foreach ($stmts as $sql) {
                    try {
                        $pdo->exec($sql);
                    } catch (PDOException $e) {
                        // Duplicate column / table already exists → skip silently
                        if (!preg_match('/1060|1050|already exists|Duplicate column/i', $e->getMessage())) {
                            logEntry('warn', "{$name}: " . $e->getMessage());
                            $ok = false;
                        }
                    }
                }
                if ($ok) {
                    $pdo->prepare("INSERT IGNORE INTO `migrations` (`migration`,`batch`) VALUES (?,2)")->execute([$name]);
                    logEntry('ok', "Migration: {$name}");
                }
            }
        }

        // 5. Clear caches ─────────────────────────────────────────────────────
        logEntry('info', 'Leere Caches …');
        $cleared = 0;
        foreach ([
            BASE_PATH . '/storage/framework/views',
            BASE_PATH . '/storage/framework/cache/data',
            BASE_PATH . '/bootstrap/cache',
        ] as $dir) {
            foreach (array_merge(glob($dir . '/*.php') ?: [], glob($dir . '/*.json') ?: []) as $f) {
                unlink($f);
                $cleared++;
            }
        }
        logEntry('ok', "{$cleared} Cache-Dateien gelöscht.");

        // 6. Done ─────────────────────────────────────────────────────────────
        rmdirAll($tmpDir);
        $newVer = file_exists(BASE_PATH . '/version.txt') ? trim(file_get_contents(BASE_PATH . '/version.txt')) : '?';
        logEntry('ok', "Installierte Version: {$newVer}");
        $done = true;

    } catch (Throwable $e) {
        logEntry('error', $e->getMessage());
        if (is_dir($tmpDir))  rmdirAll($tmpDir);
        if (file_exists($zipFile)) unlink($zipFile);
    }
}

// ── Pre-flight checks ─────────────────────────────────────────────────────────
$checks = [
    ['.env vorhanden',        file_exists(ENV_PATH)],
    ['PHP cURL',              function_exists('curl_init')],
    ['PHP ZipArchive',        class_exists('ZipArchive')],
    ['storage/ schreibbar',   is_writable(BASE_PATH . '/storage/app')],
];
$preOk = array_reduce($checks, fn($c, $i) => $c && $i[1], true);

$dbOk = false;
$pending = [];
try {
    $pdo   = db($env);
    $dbOk  = true;
    if ($authed) $pending = array_keys(pendingMigrations($pdo));
} catch (Throwable) {}

$curVer  = file_exists(BASE_PATH . '/version.txt') ? trim(file_get_contents(BASE_PATH . '/version.txt')) : 'unbekannt';
$ghToken = $env['GITHUB_TOKEN'] ?? '';
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>KITA-HRM Updater</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-lg space-y-5">

  <!-- Header -->
  <div class="text-center">
    <div class="inline-flex w-14 h-14 bg-indigo-600 rounded-2xl items-center justify-center mb-3 shadow-md">
      <span class="text-white font-bold text-xl">K</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">KITA-HRM Updater</h1>
    <p class="text-sm text-gray-500 mt-1">Installierte Version: <span class="font-mono font-medium text-gray-700"><?= htmlspecialchars($curVer) ?></span></p>
  </div>

<?php if (!$authed): ?>
  <!-- Login form -->
  <div class="bg-white rounded-2xl shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-800 mb-4">Anmelden</h2>
    <?php if ($authErr): ?>
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"><?= htmlspecialchars($authErr) ?></div>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Admin-Passwort</label>
        <input type="password" name="pw" autofocus required
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>
      <button type="submit"
              class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors">
        Anmelden
      </button>
    </form>
  </div>

<?php elseif (!empty($log)): ?>
  <!-- Result -->
  <div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-base font-semibold text-gray-800">Update-Protokoll</h2>
      <span class="px-2.5 py-1 text-xs font-semibold rounded-full <?= $done ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
        <?= $done ? 'Erfolgreich' : 'Fehler' ?>
      </span>
    </div>
    <div class="bg-gray-950 rounded-xl p-4 space-y-1 font-mono text-xs overflow-auto max-h-72">
      <?php foreach ($log as [$t, $m]): ?>
      <div class="flex items-start gap-2 <?= match($t) { 'ok'=>'text-green-400', 'error'=>'text-red-400', 'warn'=>'text-yellow-400', default=>'text-gray-400' } ?>">
        <span class="flex-shrink-0"><?= match($t) { 'ok'=>'✓', 'error'=>'✗', 'warn'=>'⚠', default=>'›' } ?></span>
        <span><?= htmlspecialchars($m) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-5 flex gap-3">
      <a href="/dashboard"
         class="flex-1 text-center py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors">
        Zum Dashboard
      </a>
      <a href="update.php"
         class="flex-1 text-center py-2.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
        Erneut prüfen
      </a>
    </div>
  </div>

<?php else: ?>
  <!-- Pre-flight -->
  <div class="bg-white rounded-2xl shadow-sm p-5">
    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Systemvoraussetzungen</h2>
    <div class="space-y-2">
      <?php foreach ($checks as [$label, $ok]): ?>
      <div class="flex items-center justify-between text-sm">
        <span class="text-gray-600"><?= htmlspecialchars($label) ?></span>
        <?php if ($ok): ?>
          <span class="text-green-600 font-semibold flex items-center gap-1">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>OK
          </span>
        <?php else: ?>
          <span class="text-red-600 font-semibold">Fehlt</span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
      <div class="flex items-center justify-between text-sm">
        <span class="text-gray-600">Datenbankverbindung</span>
        <span class="<?= $dbOk ? 'text-green-600' : 'text-red-600' ?> font-semibold"><?= $dbOk ? 'OK' : 'Fehler' ?></span>
      </div>
    </div>
  </div>

  <!-- Pending migrations badge -->
  <?php if (!empty($pending)): ?>
  <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm">
    <p class="font-semibold text-amber-800"><?= count($pending) ?> ausstehende Datenbankmigrationen</p>
    <ul class="mt-1 space-y-0.5 font-mono text-xs text-amber-700">
      <?php foreach ($pending as $m): ?><li><?= htmlspecialchars($m) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <?php else: ?>
  <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-sm text-green-700 flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
    Datenbank ist aktuell.
  </div>
  <?php endif; ?>

  <!-- Update form -->
  <div class="bg-white rounded-2xl shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-800 mb-1">Update durchführen</h2>
    <p class="text-xs text-gray-400 mb-5">
      Lädt <code class="bg-gray-100 px-1 rounded font-mono"><?= GITHUB_REPO ?></code> (Branch: <code class="bg-gray-100 px-1 rounded font-mono"><?= GITHUB_BRANCH ?></code>) von GitHub.<br>
      <strong class="text-gray-600">.env, storage/ und uploads/ bleiben unberührt.</strong>
    </p>

    <form method="POST" onsubmit="this.querySelector('[type=submit]').disabled=true;this.querySelector('[type=submit]').textContent='Bitte warten …';" class="space-y-4">
      <input type="hidden" name="run_update" value="1">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          GitHub Personal Access Token
          <?php if ($ghToken): ?><span class="text-xs text-green-600 font-normal ml-1">(aus .env)</span><?php endif; ?>
        </label>
        <input type="password" name="token" value="<?= htmlspecialchars($ghToken) ?>"
               placeholder="ghp_xxxxxxxxxxxxxxxxxxxx"
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <p class="mt-1 text-xs text-gray-400">
          Token dauerhaft in <code class="bg-gray-100 px-1 rounded font-mono">.env</code> als <code class="bg-gray-100 px-1 rounded font-mono">GITHUB_TOKEN=ghp_xxx</code> eintragen.
        </p>
      </div>

      <button type="submit" <?= !$preOk ? 'disabled' : '' ?>
              class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors">
        Jetzt aktualisieren
      </button>
    </form>
  </div>

  <p class="text-center text-xs text-gray-400">
    <a href="update.php?logout=1" class="hover:text-gray-600 underline">Abmelden</a>
    &nbsp;·&nbsp;
    <a href="/dashboard" class="hover:text-gray-600 underline">Dashboard</a>
  </p>
<?php endif; ?>

</div>
</body>
</html>
