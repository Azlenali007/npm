<?php
/**
 * N.A Fresh Fruits & Coconuts - Customer Login
 */

require_once __DIR__ . '/config/database.php';

if (is_logged_in()) {
    header("Location: /account.php");
    exit;
}

$db = getDB();
$error = '';
$redirect = $_GET['redirect'] ?? '/account.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please try again.';
    } else {
        $loginInput = trim($_POST['login_input'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($loginInput) || empty($password)) {
            $error = 'Please enter your mobile number or email and password.';
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1");
            $stmt->execute([$loginInput, $loginInput]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                // Link active session cart to this user
                $sessionId = get_cart_session_id();
                $db->prepare("UPDATE carts SET user_id = ? WHERE session_id = ?")->execute([$user['id'], $sessionId]);

                header("Location: " . $redirect);
                exit;
            } else {
                $error = 'Invalid credentials. Please check your mobile/email and password.';
            }
        }
    }
}

$pageTitle = 'Sign In – N.A Fresh Fruits & Coconuts, Jaora';
$pageDesc = 'Log into your N.A Fresh Fruits account to track orders and manage saved addresses in Jaora.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-coconut-cream min-h-screen py-16 flex items-center justify-center px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-sm space-y-6">
    
    <div class="text-center space-y-2">
      <div class="w-12 h-12 rounded-2xl bg-brand-800 text-white flex items-center justify-center mx-auto text-xl font-bold shadow-xs">
        🥥
      </div>
      <h1 class="font-serif font-bold text-2xl sm:text-3xl text-slate-900 tracking-tight">
        Welcome Back
      </h1>
      <p class="text-xs text-slate-500">Sign in to your N.A Fresh Fruits & Coconuts account.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold flex items-center gap-2">
        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mobile Number or Email</label>
        <input type="text" name="login_input" required autofocus placeholder="e.g. 9826012345 or you@email.com" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
      </div>

      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
          <a href="/forgot-password.php" class="text-xs text-brand-700 font-bold hover:underline">Forgot?</a>
        </div>
        <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
      </div>

      <button type="submit" class="w-full py-3.5 rounded-2xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-sm shadow-xs transition-colors">
        Sign In
      </button>
    </form>

    <div class="pt-4 border-t border-slate-100 text-center text-xs text-slate-600">
      Don't have an account yet? 
      <a href="/register.php" class="font-bold text-brand-700 hover:underline">Create an account</a>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
