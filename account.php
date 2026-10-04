<?php
/**
 * N.A Fresh Fruits & Coconuts - Customer Account Dashboard
 */

require_once __DIR__ . '/config/database.php';

if (!is_logged_in()) {
    header("Location: /login.php?redirect=" . urlencode('/account.php'));
    exit;
}

$user = current_user();
$db = getDB();
$userId = $user['id'];

$msg = '';
$err = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (verify_csrf($_POST['csrf_token'] ?? '')) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (!empty($name) && !empty($phone)) {
            $upd = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            $upd->execute([$name, $email, $phone, $userId]);
            $msg = 'Profile details updated successfully!';
            $_SESSION['user_name'] = $name;
            $user = current_user(); // Refresh
        } else {
            $err = 'Name and mobile number are required.';
        }
    }
}

// Handle Add Address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    if (verify_csrf($_POST['csrf_token'] ?? '')) {
        $label = trim($_POST['label'] ?? 'Home');
        $recName = trim($_POST['recipient_name'] ?? $user['name']);
        $phone = trim($_POST['phone'] ?? $user['phone']);
        $address = trim($_POST['full_address'] ?? '');
        $landmark = trim($_POST['landmark'] ?? '');

        if (!empty($address)) {
            $ins = $db->prepare("
                INSERT INTO addresses (user_id, label, recipient_name, phone, full_address, landmark, is_default, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
            ");
            $ins->execute([$userId, $label, $recName, $phone, $address, $landmark]);
            $msg = 'New delivery address saved.';
        }
    }
}

// Handle Delete Address
if (isset($_GET['del_addr'])) {
    $delId = (int)$_GET['del_addr'];
    $db->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?")->execute([$delId, $userId]);
    header("Location: /account.php?tab=addresses");
    exit;
}

// Fetch Customer Orders
$stmt = $db->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count,
           i.invoice_number
    FROM orders o
    LEFT JOIN invoices i ON o.id = i.order_id
    WHERE o.user_id = ? OR o.customer_phone = ?
    ORDER BY o.id DESC
");
$stmt->execute([$userId, $user['phone']]);
$orders = $stmt->fetchAll();

// Fetch Saved Addresses
$stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll();

// Fetch Notifications
$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? OR user_id IS NULL ORDER BY id DESC LIMIT 15");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

// Fetch Wishlist Items
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM wishlists w
    JOIN products p ON w.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY w.id DESC
");
$stmt->execute([$userId]);
$wishlist = $stmt->fetchAll();

$currentTab = $_GET['tab'] ?? 'orders';

$pageTitle = 'My Account – N.A Fresh Fruits & Coconuts, Jaora';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-coconut-cream min-h-screen py-10" x-data="{ tab: '<?= e($currentTab) ?>' }">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Top Welcome Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-brand-800 text-white font-serif font-bold text-2xl flex items-center justify-center shadow-sm">
          <?= strtoupper(substr($user['name'], 0, 1)) ?>
        </div>
        <div>
          <h1 class="font-serif font-bold text-2xl text-slate-900 leading-tight"><?= e($user['name']) ?></h1>
          <p class="text-xs text-slate-500 mt-0.5"><?= e($user['phone']) ?> • <?= e($user['email'] ?: 'No email added') ?></p>
          <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-[10px] font-bold">
            Customer Since <?= date('M Y', strtotime($user['created_at'])) ?>
          </span>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <a href="/products.php" class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs shadow-xs transition-colors">
          Browse Fresh Menu
        </a>
        <a href="/logout.php" class="px-4 py-2.5 rounded-xl border border-slate-200 text-red-600 hover:bg-red-50 text-xs font-bold transition-colors">
          Sign Out
        </a>
      </div>
    </div>

    <?php if ($msg): ?>
      <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
        <?= e($msg) ?>
      </div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
        <?= e($err) ?>
      </div>
    <?php endif; ?>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 border-b border-slate-200 scrollbar-none">
      <button @click="tab = 'orders'" 
              class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-colors flex items-center gap-2 shrink-0"
              :class="tab === 'orders' ? 'bg-brand-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100'">
        <span>My Orders (<?= count($orders) ?>)</span>
      </button>

      <button @click="tab = 'addresses'" 
              class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-colors flex items-center gap-2 shrink-0"
              :class="tab === 'addresses' ? 'bg-brand-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100'">
        <span>Saved Addresses (<?= count($addresses) ?>)</span>
      </button>

      <button @click="tab = 'wishlist'" 
              class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-colors flex items-center gap-2 shrink-0"
              :class="tab === 'wishlist' ? 'bg-brand-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100'">
        <span>Wishlist (<?= count($wishlist) ?>)</span>
      </button>

      <button @click="tab = 'notifications'" 
              class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-colors flex items-center gap-2 shrink-0"
              :class="tab === 'notifications' ? 'bg-brand-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100'">
        <span>Notifications (<?= count($notifications) ?>)</span>
      </button>

      <button @click="tab = 'profile'" 
              class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-colors flex items-center gap-2 shrink-0"
              :class="tab === 'profile' ? 'bg-brand-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100'">
        <span>Profile Settings</span>
      </button>
    </div>

    <!-- TAB 1: MY ORDERS -->
    <div x-show="tab === 'orders'" class="space-y-4">
      <?php if (empty($orders)): ?>
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200">
          <p class="font-serif font-bold text-lg text-slate-800">No Orders Yet</p>
          <p class="text-xs text-slate-500 mt-1">You haven't placed any coconut or juice orders yet.</p>
          <a href="/products.php" class="inline-block mt-4 px-6 py-2.5 bg-brand-700 text-white rounded-xl text-xs font-bold">
            Order Fresh Drinks Now
          </a>
        </div>
      <?php else: ?>
        <div class="space-y-4">
          <?php foreach ($orders as $ord): ?>
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div class="space-y-1.5">
                <div class="flex items-center gap-3">
                  <span class="font-mono font-bold text-sm text-slate-900"><?= e($ord['order_number']) ?></span>
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                    <?php 
                    if ($ord['order_status'] === 'Delivered') echo 'bg-emerald-100 text-emerald-800';
                    elseif ($ord['order_status'] === 'Cancelled') echo 'bg-rose-100 text-rose-800';
                    else echo 'bg-amber-100 text-amber-800';
                    ?>">
                    <?= e($ord['order_status']) ?>
                  </span>
                  <span class="text-xs text-slate-400">• <?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></span>
                </div>

                <p class="text-xs text-slate-600">
                  <span class="font-semibold text-slate-900"><?= (int)$ord['item_count'] ?> drink item(s)</span>
                  • Area: <?= e($ord['area_name']) ?>
                  • Paid via: <span class="font-medium"><?= e($ord['payment_method']) ?></span> (<?= e($ord['payment_status']) ?>)
                </p>
              </div>

              <div class="flex items-center gap-4 pt-3 sm:pt-0 border-t sm:border-0 border-slate-100">
                <div class="text-right">
                  <span class="text-xs text-slate-400 block">Total</span>
                  <span class="font-serif font-bold text-lg text-brand-900"><?= format_price($ord['total_amount']) ?></span>
                </div>
                <div class="flex items-center gap-2">
                  <a href="/order-success.php?id=<?= $ord['id'] ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition-colors">
                    Track
                  </a>
                  <a href="/invoice.php?id=<?= $ord['id'] ?>" target="_blank" class="px-4 py-2 bg-brand-50 hover:bg-brand-100 text-brand-800 rounded-xl text-xs font-bold transition-colors" title="Invoice">
                    Invoice
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- TAB 2: SAVED ADDRESSES -->
    <div x-show="tab === 'addresses'" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($addresses as $addr): ?>
          <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-3 relative">
            <div class="flex items-center justify-between">
              <span class="px-2.5 py-0.5 rounded-full bg-brand-100 text-brand-800 text-[10px] font-bold">
                <?= e($addr['label'] ?: 'Home') ?>
              </span>
              <a href="/account.php?del_addr=<?= $addr['id'] ?>" onclick="return confirm('Delete this address?')" class="text-xs text-red-500 hover:underline">
                Delete
              </a>
            </div>
            <div>
              <p class="font-bold text-sm text-slate-900"><?= e($addr['recipient_name']) ?></p>
              <p class="text-xs text-slate-500"><?= e($addr['phone']) ?></p>
              <p class="text-xs text-slate-700 mt-2 leading-relaxed"><?= nl2br(e($addr['full_address'])) ?></p>
              <?php if (!empty($addr['landmark'])): ?>
                <p class="text-[11px] text-slate-400 mt-1">Landmark: <?= e($addr['landmark']) ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Add Address Card -->
      <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs max-w-xl">
        <h3 class="font-serif font-bold text-base text-slate-900 mb-4">Add New Jaora Delivery Address</h3>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Address Label</label>
              <input type="text" name="label" placeholder="e.g. Home, Shop, Office" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Recipient Name</label>
              <input type="text" name="recipient_name" value="<?= e($user['name']) ?>" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs">
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
            <input type="tel" name="phone" value="<?= e($user['phone']) ?>" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Complete Address</label>
            <textarea name="full_address" required rows="2" placeholder="House/Shop no, street, colony name in Jaora" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Landmark (Optional)</label>
            <input type="text" name="landmark" placeholder="e.g. Near Municipal Garden" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs">
          </div>
          <button type="submit" name="add_address" class="px-5 py-2.5 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
            Save Address
          </button>
        </form>
      </div>
    </div>

    <!-- TAB 3: WISHLIST -->
    <div x-show="tab === 'wishlist'">
      <?php if (empty($wishlist)): ?>
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200">
          <p class="font-serif font-bold text-lg text-slate-800">Your Wishlist is Empty</p>
          <p class="text-xs text-slate-500 mt-1">Save your favourite coconuts or juices to order anytime.</p>
          <a href="/products.php" class="inline-block mt-4 px-6 py-2.5 bg-brand-700 text-white rounded-xl text-xs font-bold">
            Explore Menu
          </a>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <?php foreach ($wishlist as $product): ?>
            <?php include __DIR__ . '/includes/product-card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- TAB 4: NOTIFICATIONS -->
    <div x-show="tab === 'notifications'" class="space-y-3">
      <?php if (empty($notifications)): ?>
        <div class="bg-white rounded-3xl p-10 text-center border border-slate-200 text-xs text-slate-500">
          No notifications yet.
        </div>
      <?php else: ?>
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs divide-y divide-slate-100">
          <?php foreach ($notifications as $n): ?>
            <div class="py-3.5 flex items-start gap-3">
              <span class="w-2.5 h-2.5 rounded-full bg-brand-500 mt-1 shrink-0"></span>
              <div class="flex-1">
                <h4 class="font-bold text-xs text-slate-900"><?= e($n['title']) ?></h4>
                <p class="text-xs text-slate-600 mt-0.5"><?= e($n['message']) ?></p>
                <span class="text-[10px] text-slate-400 mt-1 block"><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- TAB 5: PROFILE SETTINGS -->
    <div x-show="tab === 'profile'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs max-w-xl">
      <h3 class="font-serif font-bold text-lg text-slate-900 mb-4">Edit Profile</h3>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name</label>
          <input type="text" name="name" required value="<?= e($user['name']) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mobile Number</label>
          <input type="tel" name="phone" required value="<?= e($user['phone']) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
          <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500">
        </div>
        <button type="submit" name="update_profile" class="px-6 py-2.5 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
          Update Profile
        </button>
      </form>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
