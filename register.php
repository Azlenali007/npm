<?php
/**
 * N.A Fresh Fruits & Coconuts - Customer Registration
 */

require_once __DIR__ . '/config/database.php';

if (is_logged_in()) {
    header("Location: /account.php");
    exit;
}

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = preg_replace('/[^0-9]/', '', trim($_POST['phone'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $address = trim($_POST['address'] ?? '');
        $landmark = trim($_POST['landmark'] ?? '');

        if (empty($name) || empty($phone) || empty($password)) {
            $error = 'Please fill in your name, mobile number, and password.';
        } elseif (strlen($phone) < 10) {
            $error = 'Please enter a valid 10-digit mobile number.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            // Check if phone or email already taken
            $stmt = $db->prepare("SELECT id FROM users WHERE phone = ? OR (email != '' AND email = ?)");
            $stmt->execute([$phone, $email]);
            if ($stmt->fetch()) {
                $error = 'An account with this phone number or email already exists. Please sign in.';
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $ins = $db->prepare("
                    INSERT INTO users (name, email, phone, password, address, landmark, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $ins->execute([$name, $email, $phone, $hashed, $address, $landmark]);
                $newUserId = (int)$db->lastInsertId();

                // Save default address
                if (!empty($address)) {
                    $insAddr = $db->prepare("
                        INSERT INTO addresses (user_id, label, recipient_name, phone, full_address, landmark, is_default, created_at)
                        VALUES (?, 'Home', ?, ?, ?, ?, 1, NOW())
                    ");
                    $insAddr->execute([$newUserId, $name, $phone, $address, $landmark]);
                }

                // Add welcome notification
                add_user_notification(
                    $newUserId,
                    'Welcome to N.A Fresh Fruits & Coconuts!',
                    'Your account has been created. Enjoy 100% raw tender coconut water and cold-pressed juices delivered to your doorstep in Jaora.',
                    '/products.php',
                    'promo'
                );

                session_regenerate_id(true);
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['user_name'] = $name;

                // Link cart
                $sessionId = get_cart_session_id();
                $db->prepare("UPDATE carts SET user_id = ? WHERE session_id = ?")->execute([$newUserId, $sessionId]);

                header("Location: /account.php");
                exit;
            }
        }
    }
}

$pageTitle = 'Create Account – N.A Fresh Fruits & Coconuts, Jaora';
$pageDesc = 'Register for fast ordering of fresh coconut water and natural cold-pressed juices in Jaora, MP.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-coconut-cream min-h-screen py-16 flex items-center justify-center px-4 sm:px-6 lg:px-8">
  <div class="max-w-lg w-full bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-sm space-y-6">
    
    <div class="text-center space-y-2">
      <div class="w-12 h-12 rounded-2xl bg-brand-800 text-white flex items-center justify-center mx-auto text-xl font-bold shadow-xs">
        🌿
      </div>
      <h1 class="font-serif font-bold text-2xl sm:text-3xl text-slate-900 tracking-tight">
        Create Your Account
      </h1>
      <p class="text-xs text-slate-500">Order faster with saved addresses and track live drink preparation in Jaora.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold flex items-center gap-2">
        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name *</label>
          <input type="text" name="name" required placeholder="e.g. Faizan Khan" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mobile Number *</label>
          <input type="tel" name="phone" required placeholder="10-digit number" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
        <input type="email" name="email" placeholder="e.g. name@example.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Default Jaora Address (Optional)</label>
        <input type="text" name="address" placeholder="e.g. Shop 12, Station Road or House 45, Shastri Colony" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password *</label>
          <input type="password" name="password" required placeholder="Min 6 characters" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Confirm Password *</label>
          <input type="password" name="confirm_password" required placeholder="Repeat password" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
        </div>
      </div>

      <div class="pt-2">
        <button type="submit" class="w-full py-3.5 rounded-2xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-sm shadow-xs transition-colors">
          Create Account & Start Ordering
        </button>
      </div>
    </form>

    <div class="pt-4 border-t border-slate-100 text-center text-xs text-slate-600">
      Already have an account? 
      <a href="/login.php" class="font-bold text-brand-700 hover:underline">Sign In</a>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
