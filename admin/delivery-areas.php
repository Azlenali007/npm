<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Delivery Areas Management
 */

$adminTitle = 'Jaora Delivery Areas & Rates';
require_once __DIR__ . '/header.php';

$db = getDB();
$msg = '';

// Handle Add Area
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_area'])) {
    $name = trim($_POST['area_name'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '457226');
    $charge = (float)($_POST['delivery_charge'] ?? 15);
    $minOrder = (float)($_POST['min_order_amount'] ?? 100);
    $estTime = trim($_POST['est_delivery_time'] ?? '25-35 mins');

    if (!empty($name)) {
        $ins = $db->prepare("
            INSERT INTO delivery_areas (area_name, pincode, delivery_charge, min_order_amount, est_delivery_time, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        $ins->execute([$name, $pincode, $charge, $minOrder, $estTime]);
        $msg = "Area '{$name}' added successfully!";
    }
}

// Handle Toggle Active
if (isset($_GET['toggle'])) {
    $toggleId = (int)$_GET['toggle'];
    $db->prepare("UPDATE delivery_areas SET is_active = 1 - is_active WHERE id = ?")->execute([$toggleId]);
    header("Location: /admin/delivery-areas.php");
    exit;
}

// Handle Delete Area
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $db->prepare("DELETE FROM delivery_areas WHERE id = ?")->execute([$delId]);
    header("Location: /admin/delivery-areas.php");
    exit;
}

// Fetch All Areas
$areas = $db->query("SELECT * FROM delivery_areas ORDER BY is_active DESC, delivery_charge ASC")->fetchAll();
?>

<div class="space-y-6" x-data="{ addModal: false }">
  
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-bold text-2xl text-white">Jaora Delivery Zones</h1>
      <p class="text-xs text-slate-400 mt-1">Configure doorstep coverage areas, dynamic delivery rates, and minimum order values.</p>
    </div>
    <button @click="addModal = true" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2">
      <span>+ Add Delivery Area</span>
    </button>
  </div>

  <?php if ($msg): ?>
    <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs font-bold">
      <?= e($msg) ?>
    </div>
  <?php endif; ?>

  <!-- Delivery Areas - Responsive Card Grid (No Table) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($areas as $a): ?>
      <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 hover:border-emerald-600/60 transition-all flex flex-col justify-between space-y-4 group shadow-xl">
        
        <!-- Header -->
        <div class="flex items-start justify-between gap-2">
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-emerald-950 border border-emerald-800 text-emerald-400 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
              <h3 class="font-bold text-base text-white group-hover:text-emerald-400 transition-colors">
                <?= e($a['area_name']) ?>
              </h3>
              <span class="text-[11px] text-slate-400 font-mono">PIN: <?= e($a['pincode']) ?></span>
            </div>
          </div>

          <a href="/admin/delivery-areas.php?toggle=<?= $a['id'] ?>" 
             class="px-2.5 py-1 rounded-full text-[10px] font-black transition-colors <?= $a['is_active'] ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-slate-900 text-slate-400 border border-slate-700' ?>">
            <?= $a['is_active'] ? '● Active' : '● Disabled' ?>
          </a>
        </div>

        <!-- Metric Details -->
        <div class="grid grid-cols-3 gap-2 p-3.5 rounded-2xl bg-[#061009] border border-emerald-950 text-center">
          <div>
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Fee</span>
            <span class="font-black text-sm text-emerald-400 block mt-0.5">
              <?= format_price($a['delivery_charge']) ?>
            </span>
          </div>
          <div class="border-x border-emerald-950">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Min Order</span>
            <span class="font-bold text-sm text-slate-200 block mt-0.5">
              <?= format_price($a['min_order_amount']) ?>
            </span>
          </div>
          <div>
            <span class="text-[10px] uppercase font-bold text-slate-400 block">ETA</span>
            <span class="font-bold text-xs text-slate-300 block mt-0.5 truncate">
              <?= e($a['est_delivery_time']) ?>
            </span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-2 flex items-center justify-between gap-2 border-t border-emerald-950">
          <a href="/admin/delivery-areas.php?toggle=<?= $a['id'] ?>" 
             class="text-xs font-bold <?= $a['is_active'] ? 'text-amber-400 hover:underline' : 'text-emerald-400 hover:underline' ?>">
            <?= $a['is_active'] ? 'Disable Zone' : 'Enable Zone' ?>
          </a>

          <a href="/admin/delivery-areas.php?delete=<?= $a['id'] ?>" 
             onclick="return confirm('Delete <?= e(addslashes($a['area_name'])) ?>?')"
             class="px-3 py-1.5 rounded-xl bg-rose-950/60 hover:bg-rose-900 border border-rose-900/60 text-rose-300 text-xs font-semibold transition-colors">
            Delete
          </a>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <!-- ADD AREA MODAL -->
  <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
    <div @click.away="addModal = false" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full space-y-4">
      <h2 class="font-bold text-xl text-white">Add Jaora Delivery Area</h2>
      <form method="POST" class="space-y-4 text-xs">
        <div>
          <label class="block font-bold text-slate-400 uppercase mb-1">Area Name *</label>
          <input type="text" name="area_name" required placeholder="e.g. Mahavir Colony" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Delivery Charge (₹)</label>
            <input type="number" step="1" name="delivery_charge" required value="20" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Min Order (₹)</label>
            <input type="number" step="1" name="min_order_amount" required value="100" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">PIN Code</label>
            <input type="text" name="pincode" value="457226" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
          <div>
            <label class="block font-bold text-slate-400 uppercase mb-1">Est. Transit Time</label>
            <input type="text" name="est_delivery_time" value="25-35 mins" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white">
          </div>
        </div>
        <div class="pt-4 flex justify-end gap-3">
          <button @click="addModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
          <button type="submit" name="add_area" class="px-6 py-2 rounded-xl bg-emerald-600 text-white font-bold">Save Area</button>
        </div>
      </form>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
