<?php
/**
 * KITA-HRM Web-Installer
 *
 * Öffne diese Seite im Browser nach dem Upload.
 * WICHTIG: Diese Datei nach der Installation löschen!
 * → https://deine-domain.de/setup.php?delete=1
 */

declare(strict_types=1);
session_start();

define('BASE_PATH', dirname(__DIR__));
define('ENV_PATH',  BASE_PATH . '/.env');

$step   = $_GET['step'] ?? 'check';
$errors = [];

/* ─── Aktion: Installieren ──────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($step === 'install')) {

    $dbHost       = trim($_POST['db_host']        ?? '');
    $dbPort       = trim($_POST['db_port']        ?? '3306');
    $dbName       = trim($_POST['db_name']        ?? '');
    $dbUser       = trim($_POST['db_user']        ?? '');
    $dbPass       =      $_POST['db_pass']        ?? '';
    $appUrl       = rtrim(trim($_POST['app_url']  ?? ''), '/');
    $adminEmail   = trim($_POST['admin_email']    ?? 'admin@kita-traeger.de');
    $adminPw      =      $_POST['admin_password'] ?? 'Admin123!';
    $managerPw    =      $_POST['manager_password'] ?? 'Manager123!';

    /* Pflichtfelder */
    if (!$dbHost)  $errors[] = 'Datenbankhost fehlt.';
    if (!$dbName)  $errors[] = 'Datenbankname fehlt.';
    if (!$dbUser)  $errors[] = 'Datenbankbenutzer fehlt.';
    if (!$appUrl)  $errors[] = 'App-URL fehlt.';
    if (strlen($adminPw) < 8) $errors[] = 'Admin-Passwort muss mindestens 8 Zeichen haben.';

    /* DB-Verbindung testen */
    $pdo = null;
    if (empty($errors)) {
        try {
            $pdo = new PDO(
                "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4",
                $dbUser, $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            $errors[] = 'Datenbankverbindung fehlgeschlagen: ' . htmlspecialchars($e->getMessage());
        }
    }

    /* Installation durchführen */
    if (empty($errors) && $pdo) {

        /* 1. Tabellen erstellen */
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `kitas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_code` varchar(20) NOT NULL,
  `address` text,
  `phone` varchar(50),
  `email` varchar(255),
  `min_first_aid` int NOT NULL DEFAULT 2,
  `min_staff_total` int NOT NULL DEFAULT 0,
  `min_skilled_staff` int NOT NULL DEFAULT 0,
  `target_weekly_hours` decimal(6,1) NOT NULL DEFAULT 0.0,
  `notes` text,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kitas_short_code_unique` (`short_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'KITA_STAFF',
  `kita_id` bigint unsigned NULL,
  `remember_token` varchar(100),
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_kita_id_index` (`kita_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255),
  `phone` varchar(50),
  `address` text,
  `birth_date` date,
  `position` varchar(100) NULL,
  `start_date` date NOT NULL,
  `end_date` date,
  `contract_type` varchar(30) NOT NULL DEFAULT 'UNBEFRISTET',
  `weekly_hours` decimal(5,2) NOT NULL DEFAULT 39.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text,
  `kita_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  KEY `employees_kita_id_index` (`kita_id`),
  KEY `employees_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employee_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `storage_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` int NOT NULL,
  `label` varchar(255),
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_documents_employee_id_index` (`employee_id`),
  CONSTRAINT `fk_docs_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `training_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `validity_months` int,
  `is_first_aid` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `training_categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `training_completions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `completed_date` date NOT NULL,
  `expiry_date` date,
  `notes` text,
  `certificate_path` varchar(500),
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  KEY `training_completions_employee_id_index` (`employee_id`),
  KEY `training_completions_category_id_index` (`category_id`),
  KEY `training_completions_expiry_date_index` (`expiry_date`),
  CONSTRAINT `fk_tc_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tc_category` FOREIGN KEY (`category_id`) REFERENCES `training_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `kita_training_requirements` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `kita_closing_days` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kita_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `label` varchar(255),
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kcd_kita_date_unique` (`kita_id`,`date`),
  KEY `kcd_kita_id_index` (`kita_id`),
  CONSTRAINT `fk_kcd_kita` FOREIGN KEY (`kita_id`) REFERENCES `kitas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `kita_events` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned,
  `ip_address` varchar(45),
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        try {
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                if ($stmt) $pdo->exec($stmt);
            }
        } catch (PDOException $e) {
            $errors[] = 'Fehler beim Erstellen der Tabellen: ' . htmlspecialchars($e->getMessage());
        }

        /* Migrations-Einträge einfügen */
        if (empty($errors)) {
            $migrations = [
                '2024_01_01_000001_create_kitas_table',
                '2024_01_01_000002_create_users_table',
                '2024_01_01_000003_create_employees_table',
                '2024_01_01_000004_create_employee_documents_table',
                '2024_01_01_000005_create_training_categories_table',
                '2024_01_01_000006_create_training_completions_table',
                '2024_01_01_000007_add_staffing_fields_to_kitas_table',
                '2024_01_01_000008_create_kita_training_requirements_table',
                '2024_01_01_000009_add_target_hours_to_kitas_table',
                '2024_01_01_000010_create_kita_closing_days_table',
                '2024_01_01_000011_create_kita_events_table',
            ];
            $stmt = $pdo->prepare('INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES (?, 1)');
            foreach ($migrations as $m) $stmt->execute([$m]);
        }

        /* 2. Seed-Daten */
        if (empty($errors)) {
            try {
                $now = date('Y-m-d H:i:s');

                /* Kitas */
                $kitaStmt = $pdo->prepare(
                    'INSERT IGNORE INTO `kitas` (`name`,`short_code`,`min_first_aid`,`min_staff_total`,`min_skilled_staff`,`created_at`,`updated_at`) VALUES (?,?,2,0,0,?,?)'
                );
                $kitasData = [
                    ['Kita Sonnenschein',  'SONN'],
                    ['Kita Regenbogen',    'REGEN'],
                    ['Kita Schmetterlinge','SCHM'],
                    ['Kita Sternchen',     'STERN'],
                    ['Kita Löwenzahn',     'LOEWE'],
                ];
                foreach ($kitasData as [$name, $code]) {
                    $kitaStmt->execute([$name, $code, $now, $now]);
                }

                /* Admin-User */
                $adminHash = password_hash($adminPw, PASSWORD_BCRYPT, ['cost' => 12]);
                $pdo->prepare(
                    'INSERT IGNORE INTO `users` (`name`,`email`,`password`,`role`,`created_at`,`updated_at`) VALUES (?,?,?,?,?,?)'
                )->execute(['Admin Träger', $adminEmail, $adminHash, 'ADMIN', $now, $now]);

                /* Kita-Manager */
                $managerHash = password_hash($managerPw, PASSWORD_BCRYPT, ['cost' => 12]);
                foreach ($kitasData as [$kitaName, $code]) {
                    $kitaRow = $pdo->query("SELECT id FROM kitas WHERE short_code='$code' LIMIT 1")->fetch();
                    if ($kitaRow) {
                        $email = 'leitung.' . strtolower($code) . '@kita-traeger.de';
                        $pdo->prepare(
                            'INSERT IGNORE INTO `users` (`name`,`email`,`password`,`role`,`kita_id`,`created_at`,`updated_at`) VALUES (?,?,?,?,?,?,?)'
                        )->execute(["Leitung $kitaName", $email, $managerHash, 'KITA_MANAGER', $kitaRow['id'], $now, $now]);
                    }
                }

                /* Schulungskategorien */
                $catStmt = $pdo->prepare(
                    'INSERT IGNORE INTO `training_categories` (`name`,`description`,`validity_months`,`is_first_aid`,`is_active`,`sort_order`,`created_at`,`updated_at`) VALUES (?,?,?,?,1,?,?,?)'
                );
                $cats = [
                    ['Erste Hilfe',  'Erste-Hilfe-Kurs (mind. 9 UE)', 24, 1, 1],
                    ['Brandschutz',  'Brandschutzunterweisung',        12, 0, 2],
                    ['Datenschutz',  'DSGVO Schulung',                 24, 0, 3],
                    ['Kinderschutz', 'Kinderschutzschulung §8a SGB VIII', 36, 0, 4],
                ];
                foreach ($cats as [$name, $desc, $months, $firstAid, $sort]) {
                    $catStmt->execute([$name, $desc, $months, $firstAid, $sort, $now, $now]);
                }

            } catch (PDOException $e) {
                $errors[] = 'Fehler beim Einfügen der Startdaten: ' . htmlspecialchars($e->getMessage());
            }
        }

        /* 3. .env schreiben */
        if (empty($errors)) {
            $appKey = 'base64:' . base64_encode(random_bytes(32));
            $env = <<<ENV
APP_NAME="KITA-HRM"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_URL={$appUrl}

DB_CONNECTION=mysql
DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPass}

SESSION_DRIVER=file
SESSION_LIFETIME=480
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=file
FILESYSTEM_DISK=local
LOG_CHANNEL=single
LOG_LEVEL=error
ENV;
            if (!file_put_contents(ENV_PATH, $env)) {
                $errors[] = '.env konnte nicht geschrieben werden – bitte Schreibrechte auf das Hauptverzeichnis prüfen.';
            }
        }

        /* 4. Verzeichnisse sicherstellen */
        if (empty($errors)) {
            $dirs = [
                BASE_PATH . '/storage/app/documents',
                BASE_PATH . '/storage/framework/sessions',
                BASE_PATH . '/storage/framework/views',
                BASE_PATH . '/storage/framework/cache/data',
                BASE_PATH . '/storage/logs',
                BASE_PATH . '/bootstrap/cache',
            ];
            foreach ($dirs as $dir) {
                if (!is_dir($dir)) mkdir($dir, 0755, true);
            }
        }

        if (empty($errors)) {
            $_SESSION['install_success'] = [
                'admin_email'    => $adminEmail,
                'admin_password' => $adminPw,
                'app_url'        => $appUrl,
            ];
            header('Location: setup.php?step=done');
            exit;
        }
    }
}

/* ─── Hilfsfunktionen ───────────────────────────────────────────────────── */
function checkReq(): array {
    $list = [
        'PHP ≥ 8.2'          => version_compare(PHP_VERSION, '8.2.0', '>='),
        'PDO'                 => extension_loaded('pdo'),
        'PDO MySQL'           => extension_loaded('pdo_mysql'),
        'OpenSSL'             => extension_loaded('openssl'),
        'Mbstring'            => extension_loaded('mbstring'),
        'JSON'                => extension_loaded('json'),
        'fileinfo'            => extension_loaded('fileinfo'),
        'vendor/ vorhanden'   => is_dir(BASE_PATH . '/vendor'),
        'storage/ schreibbar' => is_writable(BASE_PATH . '/storage') || @mkdir(BASE_PATH . '/storage', 0755, true),
        'Hauptverz. schreibbar (für .env)' => is_writable(BASE_PATH),
    ];
    return $list;
}

$checks = checkReq();
$allOk  = !in_array(false, $checks, true);
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KITA-HRM Installation</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4">
<div class="w-full max-w-lg">

  <!-- Header -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4">
      <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 8v-3a1 1 0 011-1h2a1 1 0 011 1v3"/>
      </svg>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">KITA-HRM</h1>
    <p class="text-gray-500 text-sm mt-1">Web-Installer</p>
  </div>

<?php if ($step === 'done'): ?>
  <?php $s = $_SESSION['install_success'] ?? []; unset($_SESSION['install_success']); ?>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
      </div>
      <h2 class="text-xl font-semibold text-gray-900">Installation erfolgreich!</h2>
    </div>

    <div class="bg-gray-50 rounded-xl p-4 mb-6 text-sm space-y-2">
      <div class="flex justify-between">
        <span class="text-gray-500">Login-URL</span>
        <a href="<?= htmlspecialchars($s['app_url'] ?? '') ?>/login"
           class="text-blue-600 font-medium hover:underline"><?= htmlspecialchars($s['app_url'] ?? '') ?>/login</a>
      </div>
      <div class="flex justify-between">
        <span class="text-gray-500">E-Mail</span>
        <span class="font-mono font-medium"><?= htmlspecialchars($s['admin_email'] ?? '') ?></span>
      </div>
      <div class="flex justify-between">
        <span class="text-gray-500">Passwort</span>
        <span class="font-mono font-medium"><?= htmlspecialchars($s['admin_password'] ?? '') ?></span>
      </div>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-800">
      <strong>Wichtig:</strong> Bitte den Installer jetzt löschen:
    </div>

    <a href="setup.php?delete=1"
       class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-xl transition"
       onclick="return confirm('setup.php jetzt löschen?')">
      Installer löschen &amp; zur App
    </a>
  </div>

<?php elseif ($step === 'install'): ?>
  <!-- Installation Form -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-6">Konfiguration</h2>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
      <p class="font-medium text-red-800 text-sm mb-1">Bitte korrigiere folgende Fehler:</p>
      <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
        <?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach ?>
      </ul>
    </div>
    <?php endif ?>

    <form method="POST" action="setup.php?step=install" class="space-y-5">

      <div>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Datenbank</p>
        <div class="grid grid-cols-3 gap-3 mb-3">
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
            <input name="db_host" type="text" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
            <input name="db_port" type="text" value="<?= htmlspecialchars($_POST['db_port'] ?? '3306') ?>"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Datenbankname</label>
            <input name="db_name" type="text" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" placeholder="z.B. kitahrm"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Benutzer</label>
            <input name="db_user" type="text" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Passwort</label>
            <input name="db_pass" type="password"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
      </div>

      <hr class="border-gray-100">

      <div>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">App-Einstellungen</p>
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">App-URL</label>
            <input name="app_url" type="text" value="<?= htmlspecialchars($_POST['app_url'] ?? 'https://') ?>" placeholder="https://deine-domain.de"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Admin E-Mail</label>
            <input name="admin_email" type="email" value="<?= htmlspecialchars($_POST['admin_email'] ?? 'admin@kita-traeger.de') ?>"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Admin-Passwort <span class="text-gray-400">(min. 8 Zeichen)</span></label>
            <input name="admin_password" type="text" value="<?= htmlspecialchars($_POST['admin_password'] ?? 'Admin123!') ?>"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kita-Manager Passwort</label>
            <input name="manager_password" type="text" value="<?= htmlspecialchars($_POST['manager_password'] ?? 'Manager123!') ?>"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
      </div>

      <button type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition text-sm">
        Installation starten →
      </button>
    </form>
  </div>

<?php else: ?>
  <!-- Anforderungscheck -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-5">Systemanforderungen</h2>

    <div class="space-y-2 mb-6">
      <?php foreach ($checks as $label => $ok): ?>
      <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
        <span class="text-sm text-gray-700"><?= htmlspecialchars($label) ?></span>
        <?php if ($ok): ?>
          <span class="inline-flex items-center gap-1 text-green-700 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>OK</span>
        <?php else: ?>
          <span class="inline-flex items-center gap-1 text-red-600 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>Fehlt</span>
        <?php endif ?>
      </div>
      <?php endforeach ?>
    </div>

    <?php if ($allOk): ?>
    <a href="setup.php?step=install"
       class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition text-sm">
      Weiter zur Konfiguration →
    </a>
    <?php else: ?>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
      Bitte behebe die fehlenden Anforderungen und lade die Seite neu.
      <?php if (!is_dir(BASE_PATH . '/vendor')): ?>
      <br><br><strong>vendor/ fehlt:</strong> Die Datei <code>composer.json</code> ist im Paket enthalten.
      Verbinde dich per SSH und führe aus: <code class="bg-red-100 px-1 rounded">composer install --no-dev</code>
      <?php endif ?>
    </div>
    <?php endif ?>
  </div>
<?php endif ?>

  <p class="text-center text-xs text-gray-400 mt-6">KITA-HRM · Laravel 11 · PHP <?= PHP_VERSION ?></p>
</div>

<?php
/* Installer löschen */
if (isset($_GET['delete']) && $_GET['delete'] === '1') {
    @unlink(__FILE__);
    header('Location: /');
    exit;
}
?>
</body>
</html>
