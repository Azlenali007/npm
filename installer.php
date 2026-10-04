<?php
/**
 * N.A Fresh Fruits & Coconuts - PHP Web Installer
 * Sets up MySQL connection, runs database migrations, creates admin user and initial store settings.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = (int)($_GET['step'] ?? 1);
$error = '';
$success = '';

// Check if already installed
$configFile = __DIR__ . '/config/database.php';
$isAlreadyInstalled = false;
if (file_exists($configFile)) {
    require_once $configFile;
    try {
        $db = getDB();
        $check = $db->query("SELECT COUNT(*) FROM admins");
        if ($check && $check->fetchColumn() > 0) {
            $isAlreadyInstalled = true;
        }
    } catch (Exception $e) {
        $isAlreadyInstalled = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbName = trim($_POST['db_name'] ?? 'fresh_fruits');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = $_POST['db_pass'] ?? '';
    
    $adminUser = trim($_POST['admin_username'] ?? 'admin');
    $adminEmail = trim($_POST['admin_email'] ?? 'admin@nafresh.in');
    $adminPass = $_POST['admin_password'] ?? 'admin123';
    $bizName = trim($_POST['biz_name'] ?? 'N.A Fresh Fruits & Coconuts');

    // 1. Test Connection
    try {
        $dsn = "mysql:host={$dbHost};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Create DB
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Connect to created DB
        $pdo->exec("USE `{$dbName}`");

        // 2. Import Schema
        $schemaFile = __DIR__ . '/config/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
        }

        // 3. Create Admin
        $hash = password_hash($adminPass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO admins (username, email, password, full_name, role, created_at)
            VALUES (?, ?, ?, 'Store Administrator', 'admin', NOW())
            ON DUPLICATE KEY UPDATE password = VALUES(password)
        ");
        $stmt->execute([$adminUser, $adminEmail, $hash]);

        // 4. Update Business Name Setting
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value, setting_group)
            VALUES ('business_name', ?, 'general')
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$bizName]);

        // 5. Run standard seeder for delivery areas, default juices, and coconut items
        require_once __DIR__ . '/init_db.php';

        $step = 3;
        $success = 'Installation completed successfully! MySQL database configured and initial catalog seeded.';

    } catch (PDOException $e) {
        $error = 'MySQL Connection Failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Installation Wizard – N.A Fresh Fruits & Coconuts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen py-12 px-4 flex items-center justify-center">

  <div class="max-w-xl w-full bg-white rounded-3xl p-8 sm:p-10 border border-slate-200 shadow-sm space-y-6">
    
    <div class="text-center space-y-2">
      <div class="w-12 h-12 rounded-2xl bg-emerald-800 text-white flex items-center justify-center mx-auto text-xl font-bold">
        🥥
      </div>
      <h1 class="font-bold text-2xl text-slate-900">Database & System Setup</h1>
      <p class="text-xs text-slate-500">Configure your MySQL database for N.A Fresh Fruits & Coconuts (Jaora, M.P.)</p>
    </div>

    <?php if ($isAlreadyInstalled && $step !== 3): ?>
      <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-xs">
        <p class="font-bold">System Already Installed</p>
        <p class="mt-1">Your MySQL database and admin user are already configured and running.</p>
        <div class="pt-3 flex gap-2">
          <a href="/" class="px-4 py-2 bg-blue-700 text-white rounded-xl font-bold">Go to Website</a>
          <a href="/admin/login.php" class="px-4 py-2 bg-white text-blue-800 border border-blue-300 rounded-xl font-bold">Admin Login</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($step === 3): ?>
      <!-- Success State -->
      <div class="text-center space-y-4 py-4">
        <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-2xl font-bold">
          ✓
        </div>
        <h2 class="font-bold text-xl text-slate-900">Setup Complete!</h2>
        <p class="text-xs text-slate-600 leading-relaxed">
          The MySQL tables, Jaora delivery zones, fresh tender coconuts, and cold-pressed juices have all been provisioned.
        </p>
        <div class="pt-4 flex justify-center gap-3">
          <a href="/" class="px-6 py-3 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs transition-colors">
            Visit Online Store
          </a>
          <a href="/admin/login.php" class="px-6 py-3 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition-colors">
            Admin Dashboard
          </a>
        </div>
      </div>
    <?php else: ?>

      <!-- Setup Form -->
      <form method="POST" class="space-y-5">
        
        <!-- MySQL Details -->
        <div class="space-y-3">
          <h3 class="font-bold text-xs uppercase tracking-wider text-slate-400">1. MySQL Database Configuration</h3>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Host</label>
              <input type="text" name="db_host" required value="127.0.0.1" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Database Name</label>
              <input type="text" name="db_name" required value="fresh_fruits" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Username</label>
              <input type="text" name="db_user" required value="root" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
              <input type="password" name="db_pass" placeholder="Empty if default" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>
          </div>
        </div>

        <!-- Admin Details -->
        <div class="space-y-3 pt-3 border-t border-slate-100">
          <h3 class="font-bold text-xs uppercase tracking-wider text-slate-400">2. Administrator Credentials</h3>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Admin Username</label>
              <input type="text" name="admin_username" required value="admin" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Admin Email</label>
              <input type="email" name="admin_email" required value="admin@nafresh.in" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Admin Password</label>
            <input type="text" name="admin_password" required value="admin123" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono">
          </div>
        </div>

        <!-- Business Details -->
        <div class="space-y-3 pt-3 border-t border-slate-100">
          <h3 class="font-bold text-xs uppercase tracking-wider text-slate-400">3. Business Details</h3>
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Business Name</label>
            <input type="text" name="biz_name" required value="N.A Fresh Fruits & Coconuts" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
          </div>
        </div>

        <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs transition-colors shadow-xs">
          Test Connection & Run Setup
        </button>

      </form>
    <?php endif; ?>

  </div>

</body>
</html>
