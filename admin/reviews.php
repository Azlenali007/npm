<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Customer Reviews
 */

$adminTitle = 'Customer Ratings & Reviews';
require_once __DIR__ . '/header.php';

$db = getDB();

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $db->prepare("UPDATE reviews SET status = 1 - status WHERE id = ?")->execute([$id]);
    header("Location: /admin/reviews.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
    header("Location: /admin/reviews.php");
    exit;
}

$reviews = $db->query("SELECT * FROM reviews ORDER BY id DESC")->fetchAll();
?>

<div class="space-y-6">
  
  <div>
    <h1 class="font-bold text-2xl text-white">Customer Feedback & Ratings</h1>
    <p class="text-xs text-slate-400 mt-1">Approve or moderate reviews posted by customers across Jaora.</p>
  </div>

  <div class="rounded-3xl bg-slate-950 border border-slate-800 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase tracking-wider bg-slate-900/40">
            <th class="py-3.5 px-4">Customer</th>
            <th class="py-3.5 px-3">Location</th>
            <th class="py-3.5 px-3">Rating</th>
            <th class="py-3.5 px-3">Comment</th>
            <th class="py-3.5 px-3">Status</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
          <?php foreach ($reviews as $r): ?>
            <tr class="hover:bg-slate-900/60 transition-colors">
              <td class="py-3 px-4 font-bold text-white">
                <?= e($r['customer_name']) ?>
              </td>
              <td class="py-3 px-3 text-slate-400">
                <?= e($r['location']) ?>
              </td>
              <td class="py-3 px-3 text-amber-400">
                <?php for ($i = 0; $i < $r['rating']; $i++): ?>★<?php endfor; ?>
              </td>
              <td class="py-3 px-3 text-slate-300 max-w-sm">
                <?= e($r['comment']) ?>
              </td>
              <td class="py-3 px-3">
                <a href="/admin/reviews.php?toggle=<?= $r['id'] ?>" class="px-2.5 py-1 rounded-full text-[10px] font-bold inline-block <?= $r['status'] ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-slate-800 text-slate-400' ?>">
                  <?= $r['status'] ? 'Approved' : 'Hidden' ?>
                </a>
              </td>
              <td class="py-3 px-4 text-right">
                <a href="/admin/reviews.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete review?')" class="px-3 py-1.5 rounded-lg bg-rose-950 text-rose-300 hover:bg-rose-900 text-xs font-semibold">
                  Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
