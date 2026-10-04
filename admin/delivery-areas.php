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

  <!-- Areas Table -->
  <div class="rounded-3xl bg-slate-950 border border-slate-800 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase tracking-wider bg-slate-900/40">
            <th class="py-3.5 px-4">Area Name</th>
            <th class="py-3.5 px-3">PIN Code</th>
            <th class="py-3.5 px-3">Delivery Rate</th>
            <th class="py-3.5 px-3">Min Order</th>
            <th class="py-3.5 px-3">Est. Transit Time</th>
            <th class="py-3.5 px-3">Status</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
          <?php foreach ($areas as $a): ?>
            <tr class="hover:bg-slate-900/60 transition-colors">
              <td class="py-3 px-4 font-bold text-white">
                <?= e($a['area_name']) ?>
              </td>
              <td class="py-3 px-3 text-slate-400">
                <?= e($a['pincode']) ?>
              </td>
              <td class="py-3 px-3 font-serif font-bold text-emerald-400 text-sm">
                <?= format_price($a['delivery_charge']) ?>
              </td>
              <td class="py-3 px-3 text-slate-300 font-serif">
                <?= format_price($a['min_order_amount']) ?>
              </td>
              <td class="py-3 px-3 text-slate-400">
                <?= e($a['est_delivery_time']) ?>
              </td>
              <td class="py-3 px-3">
                <a href="/admin/delivery-areas.php?toggle=<?= $a['id'] ?>" 
                   class="px-2.5 py-1 rounded-full text-[10px] font-bold inline-block <?= $a['is_active'] ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-slate-800 text-slate-400' ?>">
                  <?= $a['is_active'] ? 'Active in Checkout' : 'Disabled (Hidden)' ?>
                </a>
              </td>
              <td class="py-3 px-4 text-right">
                <a href="/admin/delivery-areas.php?delete=<?= $a['id'] ?>" 
                   onclick="return confirm('Delete <?= e(addslashes($a['area_name'])) ?>?')"
                   class="px-3 py-1.5 rounded-lg bg-rose-950 text-rose-300 hover:bg-rose-900 text-xs font-semibold">
                  Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
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
