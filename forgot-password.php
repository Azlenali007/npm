<?php
/**
 * N.A Fresh Fruits & Coconuts - Forgot Password
 */

require_once __DIR__ . '/config/database.php';

$message = '';
$error = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneOrEmail = trim($_POST['phone_or_email'] ?? '');
    if (empty($phoneOrEmail)) {
        $error = 'Please enter your registered phone or email address.';
    } else {
        $stmt = $db->prepare("SELECT id, name, phone FROM users WHERE phone = ? OR email = ?");
        $stmt->execute([$phoneOrEmail, $phoneOrEmail]);
        $user = $stmt->fetch();
        if ($user) {
            $message = 'Password reset instructions have been logged. You may also contact our Jaora store at ' . get_setting('phone') . ' for instant verification.';
        } else {
            $error = 'No customer account found with that mobile number or email.';
        }
    }
}

$pageTitle = 'Forgot Password – N.A Fresh Fruits & Coconuts';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-coconut-cream min-h-screen py-16 flex items-center justify-center px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-sm space-y-6">
    <div class="text-center space-y-2">
      <h1 class="font-serif font-bold text-2xl text-slate-900">Reset Your Password</h1>
      <p class="text-xs text-slate-500">Enter your registered mobile number or email to receive reset instructions.</p>
    </div>

    <?php if ($message): ?>
      <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 text-xs font-bold leading-relaxed">
        <?= e($message) ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="p-4 rounded-2xl bg-rose-50 text-rose-800 text-xs font-bold">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mobile Number or Email</label>
        <input type="text" name="phone_or_email" required placeholder="e.g. 9826012345" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
      </div>
      <button type="submit" class="w-full py-3.5 rounded-2xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-sm shadow-xs transition-colors">
        Send Reset Link
      </button>
    </form>

    <div class="pt-4 border-t border-slate-100 text-center text-xs">
      <a href="/login.php" class="font-bold text-brand-700 hover:underline">← Back to Sign In</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
