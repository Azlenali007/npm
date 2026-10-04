<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Login
 */

require_once __DIR__ . '/../config/database.php';

if (is_admin_logged_in()) {
    header("Location: /admin/index.php");
    exit;
}

$error = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username/email and password.';
    } else {
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];
            header("Location: /admin/index.php");
            exit;
        } else {
            $error = 'Invalid administrative credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login – N.A Fresh Fruits & Coconuts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 min-h-screen py-16 px-4 flex items-center justify-center text-slate-100">

  <div class="max-w-md w-full bg-slate-800 rounded-3xl p-8 border border-slate-700 shadow-2xl space-y-6">
    
    <div class="text-center space-y-2">
      <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center mx-auto text-xl font-bold shadow-md">
        🔒
      </div>
      <h1 class="font-bold text-2xl text-white">N.A Admin Portal</h1>
      <p class="text-xs text-slate-400">Jaora Store Management System</p>
    </div>

    <?php if ($error): ?>
      <div class="p-3.5 rounded-2xl bg-rose-950/80 border border-rose-800 text-rose-200 text-xs font-bold">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Username or Email</label>
        <input type="text" name="username" required value="admin" class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
        <input type="password" name="password" required value="admin123" class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
      </div>

      <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-md transition-colors">
        Authenticate & Open Portal
      </button>
    </form>

    <div class="pt-4 border-t border-slate-700 text-center text-xs text-slate-400">
      <a href="/" class="hover:text-white transition-colors">← Back to Storefront</a>
    </div>

  </div>

</body>
</html>
