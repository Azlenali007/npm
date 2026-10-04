<?php
/**
 * N.A Fresh Fruits & Coconuts - SaaS Executive Admin Login
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
        $error = 'Please enter administrative username/email and password.';
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
            $error = 'Invalid credentials. Please verify your administrative access.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Authentication – N.A Fresh Fruits & Coconuts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
  </style>
</head>
<body class="bg-[#040b06] min-h-screen py-12 px-4 flex items-center justify-center text-slate-100 relative overflow-hidden">

  <!-- Ambient Emerald Illumination -->
  <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-600/10 rounded-full blur-[120px] pointer-events-none"></div>
  <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-[120px] pointer-events-none"></div>

  <div class="max-w-md w-full bg-[#08150d] rounded-[2.5rem] p-8 sm:p-10 border border-emerald-900/40 shadow-[0_25px_60px_rgba(0,0,0,0.8)] relative z-10 space-y-7">
    
    <!-- Brand Header -->
    <div class="text-center space-y-3">
      <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center mx-auto text-2xl shadow-xl shadow-emerald-950/60 border border-emerald-400/30">
        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <ellipse cx="12" cy="13" rx="8" ry="7" />
          <path d="M12 6v7" />
          <path d="M9 10l3 3 5-5" />
        </svg>
      </div>
      <div>
        <h1 class="font-black text-2xl text-white tracking-tight">N.A Fresh Fruits</h1>
        <p class="text-xs text-emerald-400 font-bold uppercase tracking-widest mt-1">SaaS Control Center • Jaora</p>
      </div>
    </div>

    <!-- Error Banner -->
    <?php if ($error): ?>
      <div class="p-3.5 rounded-2xl bg-rose-950/80 border border-rose-800 text-rose-200 text-xs font-bold text-center">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-[11px] font-extrabold text-slate-300 uppercase tracking-wider mb-1.5">Username or Administrative Email</label>
        <div class="relative">
          <input type="text" 
                 name="username" 
                 required 
                 value="admin" 
                 class="w-full px-4 py-3.5 rounded-2xl bg-[#050e08] border border-emerald-950/90 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition-all">
        </div>
      </div>

      <div>
        <label class="block text-[11px] font-extrabold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
        <div class="relative">
          <input type="password" 
                 name="password" 
                 required 
                 value="admin123" 
                 class="w-full px-4 py-3.5 rounded-2xl bg-[#050e08] border border-emerald-950/90 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition-all">
        </div>
      </div>

      <div class="p-3 rounded-2xl bg-emerald-950/40 border border-emerald-800/40 flex items-center justify-between text-xs">
        <span class="text-slate-400 font-medium">Demo Access:</span>
        <span class="font-mono text-emerald-400 font-bold">admin / admin123</span>
      </div>

      <button type="submit" 
              class="w-full py-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 active:scale-[0.99] text-white font-extrabold text-sm shadow-xl shadow-emerald-950/60 border border-emerald-500/20 transition-all flex items-center justify-center gap-2">
        <span>Authenticate & Enter Portal</span>
        <span>→</span>
      </button>
    </form>

    <div class="pt-4 border-t border-emerald-950/80 text-center text-xs text-slate-400">
      <a href="/" class="hover:text-emerald-400 transition-colors inline-flex items-center gap-1.5 font-bold">
        <span>← Return to Customer Storefront</span>
      </a>
    </div>

  </div>

</body>
</html>
