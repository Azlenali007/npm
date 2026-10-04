<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Coupons Management
 */

$adminTitle = 'Discounts & Promo Coupons';
require_once __DIR__ . '/header.php';

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $desc = trim($_POST['description'] ?? '');
    $type = $_POST['discount_type'] ?? 'percentage';
    $val = (float)$_POST['discount_value'];
    $minOrder = (float)$_POST['min_order_amount'];
    $maxDisc = !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;

    if (!empty($code) && $val > 0) {
        $ins = $db->prepare("
            INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_discount, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
            ON DUPLICATE KEY UPDATE discount_value = VALUES(discount_value)
        ");
        $ins->execute([$code, $desc, $type, $val, $minOrder, $maxDisc]);
        $msg = "Coupon '{$code}' saved!";
    }
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $db->prepare("UPDATE coupons SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
    header("Location: /admin/coupons.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
    header("Location: /admin/coupons.php");
    exit;
}

$coupons = $db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>

<div class="space-y-6" x-data="{ addModal: false }">
  
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-bold text-2xl text-white">Coupons & Special Offers</h1>
      <p class="text-xs text-slate-400 mt-1">Manage promotional discount codes for fresh drinks and coconut orders.</p>
    </div>
    <button @click="addModal = true" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2">
      <span>+ Create Coupon</span>
    </button>
  </div>

  <?php if ($msg): ?>
    <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs font-bold">
      <?= e($msg) ?>
    </div>
  <?php endif; ?>

  <!-- Coupons - Responsive Card Grid (No Table) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($coupons as $c): ?>
      <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 hover:border-emerald-600/60 transition-all flex flex-col justify-between space-y-4 group shadow-xl relative overflow-hidden">
        
        <!-- Voucher Header & Code -->
        <div class="space-y-3">
          <div class="flex items-center justify-between gap-2">
            <span class="text-[10px] uppercase font-bold tracking-widest text-emerald-400">Jaora Offer</span>
            
            <a href="/admin/coupons.php?toggle=<?= $c['id'] ?>" 
               class="px-2.5 py-1 rounded-full text-[10px] font-black transition-colors <?= $c['is_active'] ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-slate-900 text-slate-400 border border-slate-700' ?>">
              <?= $c['is_active'] ? '● Active' : '● Disabled' ?>
            </a>
          </div>

          <!-- Dashed Promo Code Pill -->
          <div class="p-3 rounded-2xl bg-[#061009] border-2 border-dashed border-emerald-800/80 flex items-center justify-between">
            <span class="font-mono font-black text-lg text-white tracking-widest">
              <?= e($c['code']) ?>
            </span>
            <span class="px-2 py-0.5 rounded-lg bg-emerald-950 text-emerald-300 font-extrabold text-xs">
              <?= $c['discount_type'] === 'percentage' ? (float)$c['discount_value'] . '% OFF' : format_price($c['discount_value']) . ' OFF' ?>
            </span>
          </div>

          <p class="text-xs text-slate-300 leading-relaxed">
            <?= e($c['description']) ?>
          </p>
        </div>

        <!-- Terms & Min Order -->
        <div class="pt-3 border-t border-emerald-950 space-y-2.5 text-xs">
          <div class="flex items-center justify-between text-slate-400">
            <span>Minimum Order:</span>
            <span class="font-bold text-white"><?= format_price($c['min_order_amount']) ?></span>
          </div>

          <?php if (!empty($c['max_discount_amount'])): ?>
            <div class="flex items-center justify-between text-slate-400">
              <span>Max Discount:</span>
              <span class="font-bold text-white"><?= format_price($c['max_discount_amount']) ?></span>
            </div>
          <?php endif; ?>

          <!-- Action Buttons -->
          <div class="pt-2 flex items-center justify-between gap-2 border-t border-emerald-950">
            <a href="/admin/coupons.php?toggle=<?= $c['id'] ?>" 
               class="text-xs font-bold <?= $c['is_active'] ? 'text-amber-400 hover:underline' : 'text-emerald-400 hover:underline' ?>">
              <?= $c['is_active'] ? 'Disable Coupon' : 'Enable Coupon' ?>
            </a>

            <a href="/admin/coupons.php?delete=<?= $c['id'] ?>" 
               onclick="return confirm('Delete coupon <?= e(addslashes($c['code'])) ?>?')" 
               class="px-3 py-1.5 rounded-xl bg-rose-950/60 hover:bg-rose-900 border border-rose-900/60 text-rose-300 text-xs font-semibold transition-colors">
              Delete
            </a>
          </div>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <!-- ADD COUPON MODAL -->
  <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
    <div @click.away="addModal = false" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full space-y-4">
      <h2 class="font-bold text-xl text-white">Create New Coupon</h2>
      <form method="POST" class="space-y-4 text-xs">
        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Coupon Code (e.g. JAORA20)</label>
          <input type="text" name="code" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white uppercase font-mono">
        </div>
        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Offer Description</label>
          <input type="text" name="description" placeholder="e.g. 20% off on fresh tender coconuts" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Discount Type</label>
            <select name="discount_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
              <option value="percentage">Percentage (%)</option>
              <option value="fixed">Fixed Amount (₹)</option>
            </select>
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Value (% or ₹)</label>
            <input type="number" step="1" name="discount_value" required value="10" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Min Order Amount (₹)</label>
            <input type="number" step="1" name="min_order_amount" value="150" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Max Cap Discount (₹)</label>
            <input type="number" step="1" name="max_discount" value="50" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
        </div>
        <div class="pt-4 flex justify-end gap-3">
          <button @click="addModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
          <button type="submit" name="add_coupon" class="px-6 py-2 rounded-xl bg-emerald-600 text-white font-bold">Save Coupon</button>
        </div>
      </form>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
